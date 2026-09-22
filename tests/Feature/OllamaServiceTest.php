<?php

namespace Tests\Feature;

use App\Services\OllamaService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaServiceTest extends TestCase
{
    public function test_ollama_fallback_when_unavailable(): void
    {
        Http::fake(['*' => Http::response('', 500)]);

        $service = new OllamaService();
        $result = $service->analyzeFloodData(
            readings: [['time' => '08:00', 'distance_cm' => 15.0, 'status' => 'safe', 'temperature_c' => 28.0, 'humidity_percent' => 75.0, 'rise_rate_cm_per_min' => 0]],
            nodeId: 'TEST-01',
            nodeName: 'Test',
            trigger: 'test'
        );

        $this->assertEquals('fallback', $result['model_used']);
        $this->assertArrayHasKey('ai_response', $result);
        $this->assertArrayHasKey('flood_probability_percent', $result);
    }

    public function test_ollama_parses_valid_response(): void
    {
        Http::fake([
            '*/api/tags' => Http::response(['models' => []], 200),
            '*/api/chat' => Http::response([
                'message' => [
                    'content' => json_encode([
                        'risk_level' => 'low',
                        'flood_probability_percent' => 10,
                        'weather_condition' => 'Clear',
                        'ai_response' => 'Water level stable',
                        'recommended_actions' => ['Monitor', 'Stay alert'],
                    ])
                ]
            ], 200),
        ]);

        $service = new OllamaService();
        $result = $service->analyzeFloodData(
            readings: [['time' => '08:00', 'distance_cm' => 15.0, 'status' => 'safe', 'temperature_c' => 28.0, 'humidity_percent' => 75.0, 'rise_rate_cm_per_min' => 0]],
            nodeId: 'TEST-01',
            nodeName: 'Test',
            trigger: 'test'
        );

        $this->assertEquals('low', $result['risk_level']);
        $this->assertEquals(10, $result['flood_probability_percent']);
        $this->assertEquals('Water level stable', $result['ai_response']);
    }

    public function test_ollama_connection_check(): void
    {
        Http::fake(['*/api/tags' => Http::response([], 200)]);

        $service = new OllamaService();
        $this->assertTrue($service->isAvailable());
    }
}
