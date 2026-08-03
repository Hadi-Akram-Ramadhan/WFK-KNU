<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    private string $botToken;
    private string $chatId;
    private string $baseUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', '');
        $this->chatId   = config('services.telegram.chat_id', '');
        $this->baseUrl  = "https://api.telegram.org/bot{$this->botToken}";
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

        $msg .= "🔗 [Buka Dashboard](https://your-vps-domain.com/dashboard)";

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
     * Send raw Markdown message to Telegram.
     */
    public function sendMessage(string $text): bool
    {
        if (empty($this->botToken) || empty($this->chatId)) {
            Log::warning('[Telegram] Bot token or Chat ID not configured.');
            return false;
        }

        try {
            $response = Http::post("{$this->baseUrl}/sendMessage", [
                'chat_id'    => $this->chatId,
                'text'       => $text,
                'parse_mode' => 'Markdown',
            ]);

            if (!$response->successful()) {
                Log::error('[Telegram] Failed to send', ['response' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('[Telegram] Exception: ' . $e->getMessage());
            return false;
        }
    }
}
