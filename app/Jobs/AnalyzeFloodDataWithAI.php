<?php

namespace App\Jobs;

use App\Models\AIAnalysis;
use App\Models\SensorNode;
use App\Models\SensorReading;
use App\Models\HardwareCommand;
use App\Services\OllamaService;
use App\Services\TelegramNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeFloodDataWithAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max retry attempts for this job.
     */
    public int $tries = 2;

    /**
     * Timeout in seconds (Ollama can be slow on CPU).
     */
    public int $timeout = 90;

    public function __construct(
        public readonly SensorReading $reading,
        public readonly string        $trigger = 'danger_threshold',
    ) {}

    public function handle(OllamaService $ollama, TelegramNotificationService $telegram): void
    {
        $node = $this->reading->node;

        if (!$node) {
            Log::error('[AIJob] Node not found for reading #' . $this->reading->id);
            return;
        }

        Log::info("[AIJob] Starting AI analysis for node {$node->node_id}, trigger: {$this->trigger}");

        // Gather last 30 minutes of readings for context
        $recentReadings = $node->recentReadings(30)->get()
            ->map(fn($r) => [
                'time'               => $r->created_at->setTimezone('Asia/Jakarta')->format('H:i'),
                'distance_cm'        => (float) $r->distance_cm,
                'status'             => $r->status,
                'rise_rate_cm_per_min' => (float) ($r->rise_rate_cm_per_min ?? 0),
            ])
            ->toArray();

        if (empty($recentReadings)) {
            $recentReadings = [[
                'time'               => now()->format('H:i'),
                'distance_cm'        => (float) $this->reading->distance_cm,
                'status'             => $this->reading->status,
                'rise_rate_cm_per_min' => 0,
            ]];
        }

        // Call Ollama
        $result = $ollama->analyzeFloodData(
            readings: $recentReadings,
            nodeId:   $node->node_id,
            nodeName: $node->name,
            trigger:  $this->trigger,
        );

        // Store analysis in DB
        $analysis = AIAnalysis::create([
            'sensor_node_id'            => $node->id,
            'sensor_reading_id'         => $this->reading->id,
            'trigger'                   => $this->trigger,
            'prompt_sent'               => "Context: {$node->node_id} | Readings: " . count($recentReadings),
            'ai_response'               => $result['ai_response'],
            'risk_level'                => $result['risk_level'],
            'flood_probability_percent' => $result['flood_probability_percent'] ?? 15,
            'weather_condition'         => $result['weather_condition'] ?? null,
            'recommended_actions'       => $result['recommended_actions'],
            'model_used'                => $result['model_used'],
            'response_time_ms'          => $result['response_time_ms'],
        ]);

        Log::info("[AIJob] Analysis stored #{$analysis->id}, risk: {$result['risk_level']}, time: {$result['response_time_ms']}ms");

        // Auto-create hardware commands based on AI risk
        if (in_array($result['risk_level'], ['high', 'critical'])) {
            // Open floodgate servo
            HardwareCommand::create([
                'sensor_node_id' => $node->id,
                'command_type'   => 'servo',
                'payload'        => ['angle' => 90, 'reason' => 'AI danger threshold'],
                'source'         => 'ai',
            ]);
            // Activate siren
            HardwareCommand::create([
                'sensor_node_id' => $node->id,
                'command_type'   => 'siren',
                'payload'        => ['active' => true, 'reason' => 'AI danger threshold'],
                'source'         => 'ai',
            ]);
        }

        // Send Telegram alert if critical
        if (in_array($result['risk_level'], ['high', 'critical'])) {
            $telegram->sendFloodAlert(
                nodeId:     $node->node_id,
                nodeName:   $node->name,
                distanceCm: (float) $this->reading->distance_cm,
                status:     $this->reading->status,
                riseRate:   (float) ($this->reading->rise_rate_cm_per_min ?? 0),
                aiResponse: $result['ai_response'],
            );
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[AIJob] Job failed for reading #' . $this->reading->id . ': ' . $exception->getMessage());
    }
}
