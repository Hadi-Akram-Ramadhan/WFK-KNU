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

    public string $rainStatus = 'NO DATA';
    public ?float $waterLevelCm = null;
    public ?float $tempC = null;
    public ?float $humidityRH = null;
    public string $statusTitle = 'NO DATA';
    public string $statusDesc = 'Awaiting sensor hardware connection. Connect Wemos D1 Mini to start live telemetry.';

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
                $status = $this->latestReading->status;

                $this->statusTitle = match($status) {
                    'danger'  => 'DANGER',
                    'caution' => 'STANDBY',
                    default   => 'SAFE',
                };

                $this->statusDesc = match($status) {
                    'danger'  => 'Critical water level! Residents along the riverbank are advised to prepare for evacuation.',
                    'caution' => 'Water level is rising, monitor river conditions.',
                    default   => 'Water level is normal, river flow is smooth.',
                };

                $humidity = (float) ($this->latestReading->humidity_percent ?? 0);
                $this->humidityRH = $humidity;
                $this->rainStatus = $humidity > 85 ? 'RAINY' : 'CLEAR';

                $this->tempC = (float) ($this->latestReading->temperature_c ?? 0);

                $dist = (float) $this->latestReading->distance_cm;
                $this->waterLevelCm = round(200 - $dist, 1);
            } else {
                $this->statusTitle = 'NO DATA';
                $this->statusDesc  = 'Awaiting sensor hardware connection. Connect Wemos D1 Mini to start live telemetry.';
                $this->rainStatus  = 'NO DATA';
                $this->waterLevelCm = null;
                $this->tempC = null;
                $this->humidityRH = null;
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
