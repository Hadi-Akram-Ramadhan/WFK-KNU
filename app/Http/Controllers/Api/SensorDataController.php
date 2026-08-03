<?php

namespace App\Http\Controllers\Api;

use App\Events\NewSensorDataReceived;
use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeFloodDataWithAI;
use App\Models\HardwareCommand;
use App\Models\SensorNode;
use App\Models\SensorReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SensorDataController extends Controller
{
    /**
     * POST /api/sensor/data
     *
     * Receive distance data from Wemos D1 Mini.
     * Expected JSON: { "node_id": "BEDADUNG_01", "distance": 8.5 }
     */
    public function ingest(Request $request): JsonResponse
    {
        // Validate request body
        $validator = Validator::make($request->all(), [
            'node_id'  => 'required|string|max:50',
            'distance' => 'required|numeric|min:0|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $nodeId      = $request->input('node_id');
        $distanceCm  = (float) $request->input('distance');

        // Find the node — authenticate by API token header
        $token = $request->bearerToken();
        $node  = SensorNode::where('node_id', $nodeId)
            ->where('api_token', $token)
            ->first();

        if (!$node) {
            Log::warning("[API] Unauthorized sensor attempt: node={$nodeId}");
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Calculate derived values
        $status      = SensorReading::statusFromDistance($distanceCm);
        $waterLevel  = SensorReading::distanceToWaterLevel($distanceCm, (float) $node->sensor_height_cm);
        $capacity    = SensorReading::distanceToCapacity($distanceCm, (float) $node->sensor_height_cm);

        // Calculate rise rate from last 5 readings
        $riseRate = $this->calculateRiseRate($node, $distanceCm);

        // Store reading
        $reading = SensorReading::create([
            'sensor_node_id'       => $node->id,
            'distance_cm'          => $distanceCm,
            'water_level_m'        => $waterLevel,
            'status'               => $status,
            'rise_rate_cm_per_min' => $riseRate,
            'capacity_percent'     => $capacity,
        ]);

        // Update node status & last_seen
        $node->update([
            'status'    => $status === 'safe' ? 'online' : 'warning',
            'last_seen' => now(),
        ]);

        // Broadcast real-time to dashboard via WebSocket
        broadcast(new NewSensorDataReceived($reading, $node))->toOthers();

        // Dispatch async AI analysis job when danger detected
        if ($status === 'danger') {
            AnalyzeFloodDataWithAI::dispatchAfterResponse($reading, 'danger_threshold');
            Log::info("[API] Danger detected on {$nodeId} ({$distanceCm}cm) — AI job dispatched after response");
        }

        // Get pending hardware commands to return to Wemos
        $pendingCommands = HardwareCommand::where('sensor_node_id', $node->id)
            ->where('executed', false)
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($cmd) {
                $cmd->markExecuted();
                return [
                    'type'    => $cmd->command_type,
                    'payload' => $cmd->payload,
                ];
            });

        return response()->json([
            'success'  => true,
            'status'   => $status,
            'commands' => $pendingCommands,
        ]);
    }

    /**
     * GET /api/sensor/nodes
     * Returns list of all sensor nodes with latest readings.
     */
    public function nodes(): JsonResponse
    {
        $nodes = SensorNode::with(['latestReading'])->get()->map(fn($node) => [
            'node_id'       => $node->node_id,
            'name'          => $node->name,
            'latitude'      => (float) $node->latitude,
            'longitude'     => (float) $node->longitude,
            'status'        => $node->status,
            'is_online'     => $node->is_online,
            'latest'        => $node->latestReading ? [
                'distance_cm'    => (float) $node->latestReading->distance_cm,
                'water_level_m'  => (float) $node->latestReading->water_level_m,
                'status'         => $node->latestReading->status,
                'capacity'       => (float) ($node->latestReading->capacity_percent ?? 0),
                'updated_at'     => $node->latestReading->created_at->toISOString(),
            ] : null,
        ]);

        return response()->json(['success' => true, 'nodes' => $nodes]);
    }

    /**
     * Calculate rise rate (cm/min) from recent readings.
     */
    private function calculateRiseRate(SensorNode $node, float $currentDistanceCm): float
    {
        $lastReadings = SensorReading::where('sensor_node_id', $node->id)
            ->latest()
            ->take(5)
            ->get();

        if ($lastReadings->count() < 2) return 0.0;

        $oldest = $lastReadings->last();
        $timeDiffMinutes = max(0.1, $oldest->created_at->diffInSeconds(now()) / 60);

        // Negative rise_rate means water is going down (safe)
        // Positive rise_rate means water is rising (distance decreasing = dangerous)
        $distanceChange = (float) $oldest->distance_cm - $currentDistanceCm;
        return round($distanceChange / $timeDiffMinutes, 4);
    }
}
