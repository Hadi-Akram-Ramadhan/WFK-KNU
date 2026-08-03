<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    private string $baseUrl;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('ollama.url', 'http://localhost:11434');
        $this->model   = config('ollama.model', 'llama3.2:3b');
        $this->timeout = config('ollama.timeout', 60);
    }

    /**
     * Analyze flood sensor data and return AI recommendation.
     *
     * @param  array  $readings   Recent distance readings (cm) with timestamps
     * @param  string $nodeId     Node identifier (e.g. "BEDADUNG_01")
     * @param  string $nodeName   Node human-readable name
     * @param  string $trigger    'danger_threshold' | 'periodic_summary'
     * @return array{risk_level: string, ai_response: string, recommended_actions: array, model_used: string, response_time_ms: int}
     */
    public function analyzeFloodData(
        array $readings,
        string $nodeId,
        string $nodeName,
        string $trigger = 'danger_threshold'
    ): array {
        $startTime = microtime(true);

        $prompt = $this->buildFloodPrompt($readings, $nodeId, $nodeName, $trigger);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/chat", [
                    'model'  => $this->model,
                    'stream' => false,
                    'options' => [
                        'num_predict' => 128,
                        'temperature' => 0.2,
                    ],
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ]);

            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                Log::error('[Ollama] API Error', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->fallbackResponse($responseTimeMs);
            }

            $content = $response->json('message.content', '');
            return $this->parseResponse($content, $responseTimeMs);

        } catch (\Exception $e) {
            Log::error('[Ollama] Connection Error: ' . $e->getMessage());
            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
            return $this->fallbackResponse($responseTimeMs);
        }
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
Kamu adalah AI Asisten Sistem Peringatan Dini Banjir (SFEWS) untuk Sungai Bedadung, Jember, Jawa Timur.
Tugasmu adalah menganalisis data sensor tinggi air secara real-time dan memberikan rekomendasi tindakan darurat.

Panduan sensor:
- Sensor ultrasonic dipasang di atas sungai, mengukur jarak ke permukaan air.
- Jarak < 10 cm: STATUS DANGER (air hampir menyentuh sensor / level kritis)
- Jarak 10-20 cm: STATUS CAUTION (waspada, air sedang naik)
- Jarak > 20 cm: STATUS SAFE (aman)

Berikan respons dalam format JSON yang valid:
{
  "risk_level": "low|medium|high|critical",
  "summary": "Ringkasan situasi dalam 1-2 kalimat",
  "recommended_actions": ["Aksi 1", "Aksi 2", "Aksi 3"],
  "time_to_critical": "Estimasi waktu ke level kritis (atau null jika sudah kritis/aman)"
}

Selalu gunakan Bahasa Indonesia. Berikan respons JSON saja, tanpa teks tambahan di luar JSON.
PROMPT;
    }

    private function buildFloodPrompt(
        array $readings,
        string $nodeId,
        string $nodeName,
        string $trigger
    ): string {
        $readingsText = collect($readings)
            ->map(fn($r) => "- [{$r['time']}] Jarak: {$r['distance_cm']} cm | Status: {$r['status']}")
            ->join("\n");

        $latestReading = end($readings);
        $currentDistance = $latestReading['distance_cm'] ?? 'N/A';
        $currentStatus   = $latestReading['status'] ?? 'unknown';
        $riseRate        = $latestReading['rise_rate_cm_per_min'] ?? 0;

        $triggerText = match($trigger) {
            'danger_threshold' => 'ALERT: Ambang batas BAHAYA telah terlampaui!',
            'periodic_summary' => 'Laporan analisis periodik (setiap 30 menit)',
            default            => 'Analisis rutin',
        };

        return <<<PROMPT
Node Sensor: {$nodeId} ({$nodeName})
Pemicu Analisis: {$triggerText}
Waktu Analisis: {$this->getCurrentWIBTime()}

--- DATA SENSOR 30 MENIT TERAKHIR ---
{$readingsText}

--- STATUS SAAT INI ---
Jarak Terkini: {$currentDistance} cm
Status: {$currentStatus}
Laju Kenaikan: {$riseRate} cm/menit

Berikan analisis risiko banjir dan rekomendasi tindakan untuk tim BPBD Jember.
PROMPT;
    }

    private function parseResponse(string $content, int $responseTimeMs): array
    {
        // Extract JSON from response (Ollama sometimes adds extra text)
        preg_match('/\{.*\}/s', $content, $matches);
        $jsonStr = $matches[0] ?? '{}';

        try {
            $parsed = json_decode($jsonStr, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::warning('[Ollama] Failed to parse JSON response', ['content' => $content]);
            return $this->fallbackResponse($responseTimeMs);
        }

        return [
            'risk_level'          => $parsed['risk_level'] ?? 'high',
            'ai_response'         => $parsed['summary'] ?? $content,
            'recommended_actions' => $parsed['recommended_actions'] ?? [],
            'time_to_critical'    => $parsed['time_to_critical'] ?? null,
            'model_used'          => $this->model,
            'response_time_ms'    => $responseTimeMs,
        ];
    }

    private function fallbackResponse(int $responseTimeMs): array
    {
        return [
            'risk_level'          => 'high',
            'ai_response'         => 'AI tidak dapat dihubungi. Pantau manual dan ikuti prosedur standar BPBD.',
            'recommended_actions' => [
                'Pantau sensor secara manual',
                'Hubungi koordinator BPBD segera',
                'Siapkan jalur evakuasi darurat',
            ],
            'time_to_critical'    => null,
            'model_used'          => 'fallback',
            'response_time_ms'    => $responseTimeMs,
        ];
    }

    private function getCurrentWIBTime(): string
    {
        return now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s T');
    }

    /**
     * Test if Ollama is reachable.
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");
            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }
}
