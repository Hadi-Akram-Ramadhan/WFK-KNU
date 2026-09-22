<?php

namespace App\Console\Commands;

use App\Services\OllamaService;
use Illuminate\Console\Command;

class TestOllamaConnection extends Command
{
    protected $signature = 'ollama:test';
    protected $description = 'Test Ollama connection and model availability';

    public function handle(OllamaService $ollama): int
    {
        $this->info('🔍 Testing Ollama connection...');
        $this->newLine();

        $baseUrl = config('ollama.url');
        $model = config('ollama.model');

        $this->line("📡 URL: {$baseUrl}");
        $this->line("🤖 Model: {$model}");
        $this->newLine();

        if (!$ollama->isAvailable()) {
            $this->error('❌ Ollama not available');
            $this->warn('→ Start Ollama: ollama serve');
            $this->warn("→ Pull model: ollama pull {$model}");
            return Command::FAILURE;
        }

        $this->info('✅ Ollama is running');
        $this->newLine();

        // Test inference
        $this->info('🧪 Testing inference...');
        $start = microtime(true);

        $result = $ollama->analyzeFloodData(
            readings: [['time' => '08:00', 'distance_cm' => 15.5, 'status' => 'safe', 'temperature_c' => 28.0, 'humidity_percent' => 75.0, 'rise_rate_cm_per_min' => 0]],
            nodeId: 'TEST-01',
            nodeName: 'Test Node',
            trigger: 'manual_test'
        );

        $elapsed = round((microtime(true) - $start) * 1000);

        $this->info("✅ Response: {$elapsed}ms");
        $this->line("Risk: {$result['risk_level']} | Probability: {$result['flood_probability_percent']}%");
        $this->line("AI: {$result['ai_response']}");

        return Command::SUCCESS;
    }
}
