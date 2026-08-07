<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SimulateSensorData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sfews:simulate {--distance= : Jarak dalam cm (misal: 8.5 untuk DANGER, 15.0 untuk CAUTION, 25.0 untuk SAFE)} {--interval=3 : Interval kirim otomatis (detik)} {--auto : Mode simulasi gelombang air otomatis}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulasi pengiriman data sensor dari Wemos D1 Mini ke API Laravel local';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $baseUrl = config('app.url', 'http://127.0.0.1:8000');
        $token = env('WEMOS_API_TOKEN', 'bedadung-sfews-secret-token-01');

        $this->info("🌊 Starting SFEWS Local Sensor Simulator...");
        $this->comment("Target URL: {$baseUrl}/api/sensor/data");
        $this->comment("Node ID   : BEDADUNG_01");

        $auto = $this->option('auto');
        $fixedDistance = $this->option('distance');
        $interval = (int) $this->option('interval');

        if ($auto) {
            $this->info("🔄 Running automatic water level simulation loop. Press Ctrl+C to stop.\n");
            $currentDistance = 25.0; // Start at SAFE
            $direction = -1.5;      // Water rising (distance decreasing)

            while (true) {
                $currentDistance += $direction;
                if ($currentDistance <= 4.0) {
                    $direction = 2.0; // Air surut
                } elseif ($currentDistance >= 28.0) {
                    $direction = -1.5; // Air naik
                }

                $this->sendReading($baseUrl, $token, round($currentDistance, 1));
                sleep($interval);
            }
        } elseif ($fixedDistance !== null) {
            $this->sendReading($baseUrl, $token, (float) $fixedDistance);
        } else {
            // Interactive Mode
            $this->info("💡 Mode Interaktif. Masukkan jarak air (cm):");
            $this->line("   < 10 cm  => DANGER 🚨");
            $this->line("   10-20 cm => CAUTION ⚠️");
            $this->line("   > 20 cm  => SAFE ✅");
            $this->line("   Ketik 'exit' untuk keluar.\n");

            while (true) {
                $val = $this->ask('Jarak sensor ke air (cm)');
                if (strtolower($val) === 'exit')
                    break;
                if (!is_numeric($val)) {
                    $this->error('Masukkan angka yang valid!');
                    continue;
                }
                $this->sendReading($baseUrl, $token, (float) $val);
            }
        }

        return 0;
    }

    private function sendReading(string $baseUrl, string $token, float $distance)
    {
        $statusLabel = match(true) {
            $distance <= 3.0 => '<fg=red>🚨 DANGER</>',
            $distance <= 5.0 => '<fg=yellow>⚠️ CAUTION</>',
            default => '<fg=green>✅ SAFE</>',
        };

        $temp = round(27.5 + (rand(-10, 10) / 10), 1);
        $hum  = round($distance <= 3 ? 88.5 + (rand(-20, 20) / 10) : ($distance <= 5 ? 82.0 + (rand(-15, 15) / 10) : 74.0 + (rand(-15, 15) / 10)), 1);

        $this->line("SENDING: Distance = {$distance} cm | Suhu = {$temp}°C | Kelembapan = {$hum}% [{$statusLabel}]");

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->post("{$baseUrl}/api/sensor/data", [
                    'node_id' => 'BEDADUNG_01',
                    'distance' => $distance,
                    'temperature' => $temp,
                    'humidity' => $hum,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $cmdCount = count($data['commands'] ?? []);
                $this->info("   ↳ Response OK! Status: {$data['status']} | Pending Commands: {$cmdCount}");
            } else {
                $this->error("   ↳ HTTP Error {$response->status()}: " . $response->body());
            }
        } catch (\Exception $e) {
            $this->error("   ↳ Failed to connect to server: " . $e->getMessage());
        }
    }
}
