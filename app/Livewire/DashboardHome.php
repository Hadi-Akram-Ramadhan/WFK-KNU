<?php

namespace App\Livewire;

use App\Models\SensorNode;
use App\Models\SensorReading;
use App\Models\AIAnalysis;
use Livewire\Component;

class DashboardHome extends Component
{
    public ?SensorNode $node = null;
    public ?SensorReading $latestReading = null;
    public ?AIAnalysis $latestAnalysis = null;
    public array $recentReadings = [];
    public array $recentAlerts = [];
    public string $rainStatus = 'CLEAR';
    public float $waterLevelCm = 186.5;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->node = SensorNode::with(['latestReading', 'analyses' => fn($q) => $q->latest()->limit(1)])
            ->first();

        if ($this->node) {
            $this->latestReading = $this->node->latestReading;
            $this->latestAnalysis = $this->node->analyses->first();

            if ($this->latestReading) {
                // If humidity > 85%, rain status is RAINY, otherwise CLEAR
                $humidity = (float) ($this->latestReading->humidity_percent ?? 57.0);
                $this->rainStatus = $humidity > 85 ? 'RAINY' : 'CLEAR';

                // Display water level in cm
                $dist = (float) $this->latestReading->distance_cm;
                $this->waterLevelCm = round(200 - $dist, 1);
            }

            // Get last 10 readings for quick chart
            $this->recentReadings = SensorReading::where('sensor_node_id', $this->node->id)
                ->latest()
                ->take(10)
                ->get()
                ->reverse()
                ->map(fn($r) => [
                    'time'        => $r->created_at->format('H:i'),
                    'water_level' => round(200 - (float) $r->distance_cm, 1),
                    'distance'    => (float) $r->distance_cm,
                    'status'      => $r->status,
                ])
                ->values()
                ->toArray();

            // Recent Alerts list in English
            $this->recentAlerts = AIAnalysis::where('sensor_node_id', $this->node->id)
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($a) => [
                    'id'          => $a->id,
                    'time'        => $a->created_at->format('H:i'),
                    'title'       => match($a->risk_level) {
                        'critical', 'high' => 'Danger threshold detected',
                        'medium'           => 'Standby status detected',
                        default            => 'River status normal',
                    },
                    'desc'        => $a->ai_response,
                    'risk_level'  => $a->risk_level,
                ])
                ->toArray();
        }
    }

    public function render()
    {
        $this->loadData();
        return view('livewire.dashboard-home');
    }
}
