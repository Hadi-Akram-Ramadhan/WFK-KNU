<?php

namespace App\Livewire;

use App\Models\SensorNode;
use App\Models\AIAnalysis;
use App\Models\SensorReading;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AiAnalytics extends Component
{
    public ?SensorNode $node = null;
    public ?AIAnalysis $latestAnalysis = null;

    public string $currentStatus = 'safe';
    public float $currentDistance = 25.0;
    public float $currentTemp = 33.3;
    public float $currentHumidity = 57.0;
    public string $rainStatus = 'CLEAR';
    public float $waterLevelCm = 186.5;
    public int $floodProbability = 15;
    public string $weatherCondition = 'Partly Cloudy';
    public array $automatedActions = [];

    public array $recent10Readings = [];
    public array $hourly24Readings = [];
    public array $tableReadings = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->node = SensorNode::with(['latestReading'])->firstOrCreate(
            ['node_id' => 'BEDADUNG_01'],
            [
                'name'             => 'Checkpoint Alpha — Sumbersari',
                'latitude'         => -8.168567,
                'longitude'        => 113.700339,
                'status'           => 'offline',
                'sensor_height_cm' => 30.0,
                'api_token'        => 'bedadung-sfews-secret-token-01',
            ]
        );

        $this->latestAnalysis = AIAnalysis::where('sensor_node_id', $this->node->id)
            ->latest()
            ->first();

        $latest = $this->node->latestReading;
        if ($latest) {
            $this->currentStatus   = $latest->status;
            $this->currentDistance = (float) $latest->distance_cm;
            $this->currentTemp     = (float) ($latest->temperature_c ?? 33.3);
            $this->currentHumidity = (float) ($latest->humidity_percent ?? 57.0);
            $this->rainStatus      = $this->currentHumidity > 85 ? 'RAINY' : 'CLEAR';
            $this->waterLevelCm    = round(200 - $this->currentDistance, 1);
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

        // Weather condition label in English
        if ($this->latestAnalysis?->weather_condition) {
            $this->weatherCondition = $this->latestAnalysis->weather_condition;
        } else {
            $this->weatherCondition = "Humidity {$this->currentHumidity}% — " .
                ($this->currentHumidity > 85 ? 'Potential Heavy Rainfall Upstream' : 'Partly Cloudy Weather');
        }

        // Citizen actions from AI analysis or dynamic fallback based on status (English)
        $aiActions = $this->latestAnalysis?->recommended_actions ?? [];

        if (!empty($aiActions) && is_array($aiActions)) {
            $this->automatedActions = collect($aiActions)
                ->map(fn($action) => ['label' => trim(preg_replace('/[\*\#]/', '', $action)), 'done' => true])
                ->values()
                ->toArray();
        } else {
            $this->automatedActions = match($this->currentStatus) {
                'danger' => [
                    ['label' => 'Secure important documents and electronics to upper floors', 'done' => true],
                    ['label' => 'Prepare emergency kit (first aid, flashlight, water, clothing)', 'done' => true],
                    ['label' => 'Evacuate to designated safe shelters immediately if water continues to rise', 'done' => true],
                ],
                'caution' => [
                    ['label' => 'Monitor water level changes continuously', 'done' => true],
                    ['label' => 'Supervise children and avoid riverbank activities', 'done' => true],
                    ['label' => 'Ensure mobile phones are fully charged for emergency alerts', 'done' => true],
                ],
                default => [
                    ['label' => 'River water level normal, stay updated on local weather forecasts', 'done' => true],
                    ['label' => 'Keep river channels clean and avoid dumping waste', 'done' => true],
                    ['label' => 'Save emergency contacts for local disaster management (BPBD 112)', 'done' => true],
                ]
            };
        }

        // 1. Current Trend (10 Readings)
        $this->recent10Readings = SensorReading::where('sensor_node_id', $this->node->id)
            ->latest()
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn($r) => [
                'time'        => $r->created_at->format('H:i'),
                'water_level' => round(200 - (float) $r->distance_cm, 1),
                'distance'    => (float) $r->distance_cm,
                'temp'        => (float) ($r->temperature_c ?? 33.3),
                'humidity'    => (float) ($r->humidity_percent ?? 57.0),
                'status'      => $r->status,
            ])
            ->values()
            ->toArray();

        // 2. 24-Hour History
        $this->hourly24Readings = SensorReading::where('sensor_node_id', $this->node->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at')
            ->get()
            ->map(fn($r) => [
                'time'        => $r->created_at->format('H:i'),
                'water_level' => round(200 - (float) $r->distance_cm, 1),
                'distance'    => (float) $r->distance_cm,
                'status'      => $r->status,
            ])
            ->values()
            ->toArray();

        // 3. Sensor Data Table (20 records)
        $this->tableReadings = SensorReading::where('sensor_node_id', $this->node->id)
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'timestamp'   => $r->created_at->format('Y-m-d H:i:s'),
                'time'        => $r->created_at->format('H:i:s'),
                'water_level' => round(200 - (float) $r->distance_cm, 1),
                'distance'    => (float) $r->distance_cm,
                'temp'        => (float) ($r->temperature_c ?? 33.3),
                'humidity'    => (float) ($r->humidity_percent ?? 57.0),
                'status'      => $r->status,
            ])
            ->toArray();
    }

    public function render()
    {
        $this->loadData();
        return view('livewire.ai-analytics');
    }
}
