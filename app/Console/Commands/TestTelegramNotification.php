<?php

namespace App\Console\Commands;

use App\Services\TelegramNotificationService;
use Illuminate\Console\Command;

class TestTelegramNotification extends Command
{
    protected $signature = 'telegram:test {message?}';
    protected $description = 'Test Telegram bot connection';

    public function handle(TelegramNotificationService $telegram): int
    {
        $msg = $this->argument('message') ?? '🧪 Test notifikasi dari Bedadung SFEWS';

        $this->info('📤 Sending test message to Telegram...');

        if ($telegram->sendMessage($msg)) {
            $this->info('✅ Message sent successfully');
            return Command::SUCCESS;
        }

        $this->error('❌ Failed to send message');
        $this->warn('→ Check TELEGRAM_BOT_TOKEN & TELEGRAM_CHAT_ID in .env');
        return Command::FAILURE;
    }
}
