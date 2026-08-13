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

class SensorDataController extends Controller
{
    /**
     * GET /api/sensor/data
     * Status endpoint for browser checking.
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success'   => true,
            'service'   => 'Bedadung SFEWS Telemetry Ingestion API',
            'status'    => 'active',
            'endpoint'  => 'POST /api/sensor/data',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /api/status/live
     *
     * Ultra-lightweight endpoint for instant dashboard status polling.
     * Single DB query, no Livewire overhead — returns in < 10ms.
     * Used by vanilla JS setInterval on the dashboard hero banner.
     */
    public function liveStatus(): JsonResponse
    {
        $node = \App\Models\SensorNode::with('latestReading')->first();

        if (!$node || !$node->latestReading) {
            return response()->json([
                'online'      => false,
                'status'      => 'offline',
                'water_level' => null,
                'rain'        => 'NO DATA',
                'temp'        => null,
                'humidity'    => null,
                'ts'          => null,
            ])->header('Cache-Control', 'no-store');
        }

        $r      = $node->latestReading;
        $isLive = $r->created_at->diffInSeconds(now()) <= 20;

        return response()->json([
            'online'      => $isLive,
            'status'      => $isLive ? $r->status : 'offline',
            'water_level' => $isLive ? round(200 - (float) $r->distance_cm, 1) : null,
            'rain'        => $isLive ? ((float) ($r->humidity_percent ?? 0) > 85 ? 'RAINY' : 'CLEAR') : 'OFFLINE',
            'temp'        => $isLive ? (float) ($r->temperature_c ?? 0) : null,
            'humidity'    => $isLive ? (float) ($r->humidity_percent ?? 0) : null,
            'ts'          => $r->created_at->timestamp,
        ])->header('Cache-Control', 'no-store');
    }

    /**
     * POST /api/sensor/data
     *
     * Ingest sensor readings from Wemos D1 Mini / ESP8266.
     * Supports both distance_cm and distance keys, as well as optional temperature_c and humidity_percent.
     */
    public function ingest(Request $request): JsonResponse
    {
        $nodeId     = $request->input('node_id', 'BEDADUNG_01');
        $distanceCm = (float) ($request->input('distance_cm') ?? $request->input('distance') ?? 0);
        $tempC      = (float) ($request->input('temperature_c') ?? $request->input('temperature') ?? 28.5);
        $humidity   = (float) ($request->input('humidity_percent') ?? $request->input('humidity') ?? 65.0);

        if ($distanceCm <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid distance measurement (must be > 0)',
            ], 422);
        }

        // Find or create the node
        $node = SensorNode::firstOrCreate(
            ['node_id' => $nodeId],
            [
                'name'             => 'Checkpoint Alpha — Sumbersari',
                'latitude'         => -8.168567,
                'longitude'        => 113.700339,
                'status'           => 'online',
                'sensor_height_cm' => 30.0,
                'api_token'        => 'bedadung-sfews-secret-token-01',
            ]
        );

        // Calculate derived values
        $status     = SensorReading::statusFromDistance($distanceCm);
        $waterLevel = SensorReading::distanceToWaterLevel($distanceCm, (float) $node->sensor_height_cm);
        $capacity   = SensorReading::distanceToCapacity($distanceCm, (float) $node->sensor_height_cm);
        $riseRate   = $this->calculateRiseRate($node, $distanceCm);

        // Store reading
        $reading = SensorReading::create([
            'sensor_node_id'       => $node->id,
            'distance_cm'          => $distanceCm,
            'temperature_c'        => $tempC,
            'humidity_percent'     => $humidity,
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

        // Broadcast real-time to dashboard via WebSocket (if configured)
        try {
            broadcast(new NewSensorDataReceived($reading, $node))->toOthers();
        } catch (\Throwable $e) {
            // Ignore socket broadcast errors if Pusher isn't configured
        }

        // Dispatch async AI analysis job when danger detected (with 30-second cooldown)
        // Uses dispatchAfterResponse so HTTP POST response returns INSTANTLY to Wemos (20ms)
        if ($status === 'danger') {
            $lastAnalysisTime = \App\Models\AIAnalysis::where('sensor_node_id', $node->id)->latest()->value('created_at');
            if (!$lastAnalysisTime || \Carbon\Carbon::parse($lastAnalysisTime)->diffInSeconds(now()) >= 30) {
                try {
                    AnalyzeFloodDataWithAI::dispatchAfterResponse($reading, 'danger_threshold');
                } catch (\Throwable $e) {
                    Log::warning("[API] Could not queue AI job: " . $e->getMessage());
                }
            }
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

        Log::info("[API] Sensor data ingested from {$nodeId}: dist={$distanceCm}cm, temp={$tempC}°C, hum={$humidity}%, status={$status}");

        return response()->json([
            'success'  => true,
            'status'   => $status,
            'reading'  => [
                'id'          => $reading->id,
                'distance_cm' => $distanceCm,
                'status'      => $status,
                'created_at'  => $reading->created_at->toISOString(),
            ],
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
            'node_id'   => $node->node_id,
            'name'      => $node->name,
            'latitude'  => (float) $node->latitude,
            'longitude' => (float) $node->longitude,
            'status'    => $node->status,
            'is_online' => $node->is_online,
            'latest'    => $node->latestReading ? [
                'distance_cm'   => (float) $node->latestReading->distance_cm,
                'water_level_m' => (float) $node->latestReading->water_level_m,
                'status'        => $node->latestReading->status,
                'capacity'      => (float) ($node->latestReading->capacity_percent ?? 0),
                'updated_at'    => $node->latestReading->created_at->toISOString(),
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

        $distanceChange = (float) $oldest->distance_cm - $currentDistanceCm;
        return round($distanceChange / $timeDiffMinutes, 4);
    }
}
