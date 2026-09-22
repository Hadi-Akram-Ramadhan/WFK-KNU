<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    private string $botToken;
    private array $chatIds;
    private string $baseUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', '');
        $chatIdStr = config('services.telegram.chat_id', '');
        $this->chatIds = array_filter(array_map('trim', explode(',', $chatIdStr)));
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Send a flood emergency alert.
     */
    public function sendFloodAlert(
        string $nodeId,
        string $nodeName,
        float  $distanceCm,
        string $status,
        float  $riseRate,
        string $aiResponse = ''
    ): bool {
        $statusEmoji = match($status) {
            'danger'  => '🚨',
            'caution' => '⚠️',
            default   => '✅',
        };

        $timestamp = now()->setTimezone('Asia/Jakarta')->format('H:i:s WIB');

        $msg  = "{$statusEmoji} *BEDADUNG SFEWS — PERINGATAN DINI* {$statusEmoji}\n\n";
        $msg .= "📍 *Node:* {$nodeId} — {$nodeName}\n";
        $msg .= "📊 *Jarak Air:* `{$distanceCm} cm`\n";
        $msg .= "📈 *Laju Kenaikan:* `+{$riseRate} cm/menit`\n";
        $msg .= "⚡ *Status:* `" . strtoupper($status) . "`\n";
        $msg .= "🕐 *Waktu:* {$timestamp}\n\n";

        if ($aiResponse) {
            $msg .= "🤖 *Analisis AI:*\n_{$aiResponse}_\n\n";
        }

        $msg .= "🔗 [Buka Dashboard](" . config('app.url') . "/dashboard)";

        return $this->sendMessage($msg);
    }

    /**
     * Send a system status message (startup, recovery, etc.).
     */
    public function sendSystemStatus(string $message): bool
    {
        return $this->sendMessage("⚙️ *SFEWS System:* {$message}");
    }

    /**
     * Send raw Markdown message to all configured chat IDs (broadcast).
     */
    public function sendMessage(string $text): bool
    {
        if (empty($this->botToken) || empty($this->chatIds)) {
            Log::warning('[Telegram] Bot token or Chat ID not configured.');
            return false;
        }

        $success = true;

        foreach ($this->chatIds as $chatId) {
            try {
                $response = Http::timeout(5)->post("{$this->baseUrl}/sendMessage", [
                    'chat_id'    => $chatId,
                    'text'       => $text,
                    'parse_mode' => 'Markdown',
                ]);

                if (!$response->successful()) {
                    Log::error('[Telegram] Failed to send to ' . $chatId, ['response' => $response->body()]);
                    $success = false;
                }
            } catch (\Exception $e) {
                Log::error("[Telegram] Exception for {$chatId}: " . $e->getMessage());
                $success = false;
            }
        }

        return $success;
    }
}
