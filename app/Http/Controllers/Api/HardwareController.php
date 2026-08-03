<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HardwareCommand;
use App\Models\SensorNode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HardwareController extends Controller
{
    /**
     * POST /api/hardware/command
     * Send a command to hardware (from dashboard).
     */
    public function sendCommand(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'node_id'      => 'required|string',
            'command_type' => 'required|in:servo,siren,automated_mode',
            'payload'      => 'required|array',
            'source'       => 'nullable|in:manual,ai,threshold',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $node = SensorNode::where('node_id', $request->node_id)->firstOrFail();

        $command = HardwareCommand::create([
            'sensor_node_id' => $node->id,
            'command_type'   => $request->command_type,
            'payload'        => $request->payload,
            'source'         => $request->source ?? 'manual',
        ]);

        return response()->json(['success' => true, 'command_id' => $command->id]);
    }

    /**
     * GET /api/hardware/commands/{nodeId}
     * Wemos polls this endpoint to get pending commands.
     */
    public function getPendingCommands(string $nodeId): JsonResponse
    {
        $node = SensorNode::where('node_id', $nodeId)->first();

        if (!$node) {
            return response()->json(['success' => false, 'message' => 'Node not found'], 404);
        }

        $commands = HardwareCommand::where('sensor_node_id', $node->id)
            ->where('executed', false)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($cmd) {
                $cmd->markExecuted();
                return [
                    'id'      => $cmd->id,
                    'type'    => $cmd->command_type,
                    'payload' => $cmd->payload,
                    'source'  => $cmd->source,
                ];
            });

        return response()->json(['success' => true, 'commands' => $commands]);
    }

    /**
     * GET /api/hardware/logs/{nodeId}
     * Get recent hardware command log.
     */
    public function getLogs(string $nodeId): JsonResponse
    {
        $node = SensorNode::where('node_id', $nodeId)->firstOrFail();

        $logs = HardwareCommand::where('sensor_node_id', $node->id)
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($cmd) => [
                'id'          => $cmd->id,
                'type'        => $cmd->command_type,
                'payload'     => $cmd->payload,
                'source'      => $cmd->source,
                'executed'    => $cmd->executed,
                'created_at'  => $cmd->created_at->setTimezone('Asia/Jakarta')->format('H:i:s'),
            ]);

        return response()->json(['success' => true, 'logs' => $logs]);
    }
}
