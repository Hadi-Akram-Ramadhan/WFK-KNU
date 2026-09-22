<?php

namespace App\Console\Commands;

use App\Models\SensorNode;
use App\Models\SensorReading;
use Illuminate\Console\Command;

class SimulateSensorScenarios extends Command
{
    protected $signature = 'sensor:simulate {scenario=all}';
    protected $description = 'Simulate different sensor scenarios (safe/caution/danger/all)';

    private array $scenarios = [
        'safe' => [
            'distance_cm' => 18.5,
            'temperature_c' => 27.5,
            'humidity_percent' => 65.0,
            'description' => '✅ SAFE - Normal water level',
        ],
        'caution' => [
            'distance_cm' => 4.2,
            'temperature_c' => 28.5,
            'humidity_percent' => 78.0,
            'description' => '⚠️ CAUTION - Water rising, monitor closely',
        ],
        'danger' => [
            'distance_cm' => 2.1,
            'temperature_c' => 29.8,
            'humidity_percent' => 88.0,
            'description' => '🚨 DANGER - Critical water level, evacuate!',
        ],
    ];

    public function handle(): int
    {
        $scenario = $this->argument('scenario');

        if ($scenario === 'all') {
            return $this->runAllScenarios();
        }

        if (!isset($this->scenarios[$scenario])) {
            $this->error("Invalid scenario: {$scenario}");
            $this->info("Available: safe, caution, danger, all");
            return Command::FAILURE;
        }

        return $this->runScenario($scenario);
    }

    private function runAllScenarios(): int
    {
        $this->info("🎬 Running all sensor scenarios...");
        $this->newLine();

        foreach (['safe', 'caution', 'danger'] as $scenario) {
            $this->runScenario($scenario, false);
            $this->newLine();

            if ($scenario !== 'danger') {
                $this->info("⏳ Waiting 3 seconds...");
                sleep(3);
            }
        }

        $this->newLine();
        $this->info("✅ All scenarios completed!");
        $this->info("📊 Check dashboard: http://localhost:8000");
        $this->info("📱 Check Telegram for danger notification");

        return Command::SUCCESS;
    }

    private function runScenario(string $scenario, bool $showSummary = true): int
    {
        $data = $this->scenarios[$scenario];

        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📡 SCENARIO: " . strtoupper($scenario));
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->comment($data['description']);
        $this->newLine();

        // Find or create node
        $node = SensorNode::firstOrCreate(
            ['node_id' => 'BEDADUNG_01'],
            [
                'name' => 'Checkpoint Alpha - Sumbersari',
                'latitude' => -8.168567,
                'longitude' => 113.700339,
                'status' => 'online',
                'sensor_height_cm' => 20.0,
                'api_token' => 'bedadung-sfews-secret-token-01',
                'last_seen' => now(),
            ]
        );

        // Calculate derived values
        $status = SensorReading::statusFromDistance($data['distance_cm']);
        $waterLevel = SensorReading::distanceToWaterLevel($data['distance_cm'], 20.0);
        $capacity = SensorReading::distanceToCapacity($data['distance_cm'], 20.0);

        // Create reading
        $reading = SensorReading::create([
            'sensor_node_id' => $node->id,
            'distance_cm' => $data['distance_cm'],
            'temperature_c' => $data['temperature_c'],
            'humidity_percent' => $data['humidity_percent'],
            'water_level_m' => $waterLevel,
            'status' => $status,
            'rise_rate_cm_per_min' => $this->calculateRiseRate($scenario),
            'capacity_percent' => $capacity,
        ]);

        // Display data
        $this->table(
            ['Metric', 'Value', 'Status'],
            [
                ['Distance to Water', number_format($data['distance_cm'], 1) . ' cm', $this->getStatusIcon($status)],
                ['Water Level', number_format($waterLevel * 100, 1) . ' cm', ''],
                ['Capacity', number_format($capacity, 1) . '%', ''],
                ['Temperature', number_format($data['temperature_c'], 1) . ' °C', $this->getTempIcon($data['temperature_c'])],
                ['Humidity', number_format($data['humidity_percent'], 1) . '%', $this->getHumidityIcon($data['humidity_percent'])],
                ['Rise Rate', number_format($reading->rise_rate_cm_per_min ?? 0, 2) . ' cm/min', ''],
            ]
        );

        $this->newLine();
        $this->info("✅ Reading #{$reading->id} created");
        $this->comment("   Node: {$node->node_id}");
        $this->comment("   Status: " . strtoupper($status));
        $this->comment("   Time: " . now()->format('H:i:s'));

        // Trigger AI analysis for danger/caution
        if (in_array($status, ['danger', 'caution'])) {
            $this->newLine();
            $this->info("🤖 Triggering AI analysis...");

            \App\Jobs\AnalyzeFloodDataWithAI::dispatch($reading, 'manual_simulation');

            $this->comment("   AI job queued (run queue worker to process)");
            if ($status === 'danger') {
                $this->comment("   📱 Telegram notification will be sent");
            }
        }

        if ($showSummary) {
            $this->newLine();
            $this->info("📊 View on dashboard: http://localhost:8000");
            $this->info("🔄 Refresh dashboard to see changes");
        }

        return Command::SUCCESS;
    }

    private function calculateRiseRate(string $scenario): float
    {
        return match($scenario) {
            'danger' => 3.5,    // Fast rise
            'caution' => 1.2,   // Moderate rise
            'safe' => 0.1,      // Minimal change
            default => 0.0,
        };
    }

    private function getStatusIcon(string $status): string
    {
        return match($status) {
            'danger' => '🚨 DANGER',
            'caution' => '⚠️ CAUTION',
            'safe' => '✅ SAFE',
            default => '❓ UNKNOWN',
        };
    }

    private function getTempIcon(float $temp): string
    {
        if ($temp >= 30) return '🔥 High';
        if ($temp >= 25) return '☀️ Normal';
        return '❄️ Cool';
    }

    private function getHumidityIcon(float $humidity): string
    {
        if ($humidity >= 85) return '💧 Very High (Rain likely)';
        if ($humidity >= 70) return '💧 High';
        return '☁️ Normal';
    }
}
