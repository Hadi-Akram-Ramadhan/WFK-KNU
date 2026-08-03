<?php

namespace App\Livewire;

use App\Models\SensorNode;
use App\Models\AIAnalysis;
use App\Models\SensorReading;
use Livewire\Component;

class AIAnalytics extends Component
{
    public ?SensorNode $node = null;
    public ?AIAnalysis $latestAnalysis = null;
    public array $chartData = [];
    public string $currentStatus = 'safe';
    public float $currentDistance = 25.0;
    public float $currentTemp = 28.5;
    public float $currentHumidity = 78.0;
    public int $floodProbability = 15;
    public string $weatherCondition = 'Kondisi Berawan';
    public array $automatedActions = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->node = SensorNode::with(['latestReading'])->first();

        if (!$this->node) return;

        $this->latestAnalysis = AIAnalysis::where('sensor_node_id', $this->node->id)
            ->latest()
            ->first();

        $latest = $this->node->latestReading;
        if ($latest) {
            $this->currentStatus   = $latest->status;
            $this->currentDistance = (float) $latest->distance_cm;
            $this->currentTemp     = (float) ($latest->temperature_c ?? 28.5);
            $this->currentHumidity = (float) ($latest->humidity_percent ?? 78.0);
        }

        // Determine AI Flood Probability
        if ($this->latestAnalysis?->flood_probability_percent) {
            $this->floodProbability = $this->latestAnalysis->flood_probability_percent;
        } else {
            $this->floodProbability = match($this->currentStatus) {
                'danger'  => min(98, 80 + ($this->currentHumidity > 85 ? 15 : 5)),
                'caution' => min(79, 45 + ($this->currentHumidity > 80 ? 20 : 10)),
                default   => max(5, min(30, 15 + ($this->currentHumidity > 85 ? 10 : 0))),
            };
        }

        // Weather condition label
        if ($this->latestAnalysis?->weather_condition) {
            $this->weatherCondition = $this->latestAnalysis->weather_condition;
        } else {
            $this->weatherCondition = "Kelembapan Udara {$this->currentHumidity}% — " .
                ($this->currentHumidity > 85 ? 'Terdeteksi Potensi Hujan Lebat di Hulu' : 'Kondisi Berawan Potensi Hujan Ringan');
        }

        // Build multi-sensor time series for ApexCharts (last 60 minutes)
        $this->chartData = SensorReading::where('sensor_node_id', $this->node->id)
            ->where('created_at', '>=', now()->subMinutes(60))
            ->orderBy('created_at')
            ->get()
            ->map(fn($r) => [
                'time'        => $r->created_at->format('H:i'),
                'distance'    => (float) $r->distance_cm,
                'temperature' => (float) ($r->temperature_c ?? 28.5),
                'humidity'    => (float) ($r->humidity_percent ?? 78.0),
                'status'      => $r->status,
            ])
            ->values()
            ->toArray();

        // Citizen actions from AI analysis or dynamic fallback based on status
        $aiActions = $this->latestAnalysis?->recommended_actions ?? [];

        if (!empty($aiActions) && is_array($aiActions)) {
            $this->automatedActions = collect($aiActions)
                ->map(fn($action) => ['label' => trim(preg_replace('/[\*\#]/', '', $action)), 'done' => true])
                ->values()
                ->toArray();
        } else {
            $this->automatedActions = match($this->currentStatus) {
                'danger' => [
                    ['label' => 'Amankan dokumen penting dan barang elektronik ke tempat tinggi / lantai 2', 'done' => true],
                    ['label' => 'Siapkan Tas Siaga Bencana (P3K, senter, air minum, dan pakaian)', 'done' => true],
                    ['label' => 'Segera evakuasi ke posko BPBD / tempat tinggi yang aman', 'done' => true],
                ],
                'caution' => [
                    ['label' => 'Pantau perkembangan ketinggian air secara berkala', 'done' => true],
                    ['label' => 'Awasi anak-anak dan hindari beraktivitas di dekat bantaran sungai', 'done' => true],
                    ['label' => 'Pastikan daya baterai HP terisi penuh untuk komunikasi darurat', 'done' => true],
                ],
                default => [
                    ['label' => 'Kondisi air sungai normal, pantau informasi berkala', 'done' => true],
                    ['label' => 'Jaga kebersihan alur sungai dan hindari membuang sampah', 'done' => true],
                    ['label' => 'Simpan nomor penting Call Center BPBD Jember 112', 'done' => true],
                ]
            };
        }
    }

    public function render()
    {
        $this->loadData();
        return view('livewire.ai-analytics');
    }
}
