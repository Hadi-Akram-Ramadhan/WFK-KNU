<?php

namespace Tests\Feature;

use App\Models\SensorNode;
use App\Models\SensorReading;
use App\Services\TelegramNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    public function test_telegram_sends_to_multiple_groups(): void
    {
        config([
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.chat_id' => '123,456,789',
        ]);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $service = new TelegramNotificationService();
        $result = $service->sendMessage('Test message');

        $this->assertTrue($result);
        Http::assertSentCount(3); // 3 chat IDs
    }

    public function test_telegram_handles_missing_config(): void
    {
        config(['services.telegram.bot_token' => '']);

        $service = new TelegramNotificationService();
        $result = $service->sendMessage('Test');

        $this->assertFalse($result);
    }

    public function test_flood_alert_format(): void
    {
        config([
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.chat_id' => '123',
            'app.url' => 'https://sfews.test',
        ]);

        Http::fake();

        $service = new TelegramNotificationService();
        $service->sendFloodAlert(
            nodeId: 'TEST-01',
            nodeName: 'Test Node',
            distanceCm: 3.5,
            status: 'danger',
            riseRate: 2.5,
            aiResponse: 'Critical flood risk'
        );

        Http::assertSent(function ($request) {
            $body = $request->data();
            return str_contains($body['text'], 'BEDADUNG SFEWS')
                && str_contains($body['text'], 'TEST-01')
                && str_contains($body['text'], '3.5 cm')
                && str_contains($body['text'], 'https://sfews.test/dashboard');
        });
    }
}
