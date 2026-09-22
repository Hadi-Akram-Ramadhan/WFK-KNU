<?php

namespace App\Console\Commands;

use App\Jobs\AnalyzeFloodDataWithAI;
use App\Models\SensorNode;
use App\Models\SensorReading;
use Illuminate\Console\Command;

class TestAIAnalysis extends Command
{
    protected $signature = 'ai:test {--node=BEDADUNG_01}';
    protected $description = 'Generate test sensor data and trigger AI analysis';

    public function handle(): int
    {
        $nodeId = $this->option('node');

        $this->info("🔍 Finding node: {$nodeId}");

        $node = SensorNode::where('node_id', $nodeId)->first();

        if (!$node) {
            $this->error("❌ Node not found. Creating test node...");
            $node = SensorNode::create([
                'node_id' => $nodeId,
                'name' => 'Test Checkpoint',
                'latitude' => -8.168567,
                'longitude' => 113.700339,
                'status' => 'online',
                'sensor_height_cm' => 20.0,
                'api_token' => 'test-token',
                'last_seen' => now(),
            ]);
        }

        // Create danger scenario
        $this->info("🌊 Creating danger scenario (distance: 2.5cm)...");

        $reading = SensorReading::create([
            'sensor_node_id' => $node->id,
            'distance_cm' => 2.5,
            'temperature_c' => 29.5,
            'humidity_percent' => 88.0,
            'water_level_m' => 0.175,
            'status' => 'danger',
            'rise_rate_cm_per_min' => 3.2,
            'capacity_percent' => 87.5,
        ]);

        $this->info("✅ Reading #{$reading->id} created");
        $this->newLine();

        $this->info("🤖 Dispatching AI analysis job...");
        AnalyzeFloodDataWithAI::dispatchSync($reading, 'manual_test');

        $analysis = $node->analyses()->latest()->first();

        if ($analysis) {
            $this->newLine();
            $this->info("✅ Analysis completed!");
            $this->line("Risk Level: {$analysis->risk_level}");
            $this->line("Flood Probability: {$analysis->flood_probability_percent}%");
            $this->line("Model: {$analysis->model_used}");
            $this->line("Response Time: {$analysis->response_time_ms}ms");
            $this->newLine();
            $this->line("AI Response:");
            $this->comment($analysis->ai_response);
        } else {
            $this->error("❌ No analysis generated");
        }

        return Command::SUCCESS;
    }
}
