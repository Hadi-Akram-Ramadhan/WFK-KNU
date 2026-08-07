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

            // Check if latest reading was received in the last 20 seconds (live stream)
            $isLive = $this->latestReading && $this->latestReading->created_at->diffInSeconds(now()) <= 20;

            if ($isLive) {
                $status = $this->latestReading->status;

                $this->statusTitle = match($status) {
                    'danger'  => 'DANGER',
                    'caution' => 'STANDBY',
                    default   => 'SAFE',
                };

                $this->statusDesc = match($status) {
                    'danger'  => 'Critical water level! Immediate evacuation advised along Bedadung stream.',
                    'caution' => 'Water level is elevated, continuous monitoring recommended.',
                    default   => 'Water level is normal, smooth river flow.',
                };

                $humidity = (float) ($this->latestReading->humidity_percent ?? 0);
                $this->humidityRH = $humidity;
                $this->rainStatus = $humidity > 85 ? 'RAINY' : 'CLEAR';

                $this->tempC = (float) ($this->latestReading->temperature_c ?? 0);

                $dist = (float) $this->latestReading->distance_cm;
                $this->waterLevelCm = round(200 - $dist, 1);
            } else {
                $this->statusTitle  = 'OFFLINE';
                $this->statusDesc   = 'Hardware power disconnected. Connect Wemos D1 Mini to resume live telemetry.';
                $this->rainStatus   = 'OFFLINE';
                $this->waterLevelCm = null;
                $this->tempC        = null;
                $this->humidityRH   = null;
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

            // Recent Alerts: build from last sensor readings that are not 'safe',
            // falling back to AI analyses if available, always showing real data.
            $alertReadings = SensorReading::where('sensor_node_id', $this->node->id)
                ->whereIn('status', ['danger', 'caution'])
                ->latest()
                ->take(5)
                ->get();

            if ($alertReadings->isNotEmpty()) {
                $this->recentAlerts = $alertReadings->map(fn($r) => [
                    'id'         => $r->id,
                    'time'       => $r->created_at->diffForHumans(),
                    'title'      => match($r->status) {
                        'danger'  => '🔴 Danger Level Detected',
                        'caution' => '🟡 Standby Level Detected',
                        default   => '🟢 River Status Normal',
                    },
                    'desc'       => match($r->status) {
                        'danger'  => 'Water level at ' . number_format(200 - $r->distance_cm, 1) . ' cm — immediate evacuation may be required.',
                        'caution' => 'Water level at ' . number_format(200 - $r->distance_cm, 1) . ' cm — continuous monitoring advised.',
                        default   => 'Water level at ' . number_format(200 - $r->distance_cm, 1) . ' cm, river flow is normal.',
                    },
                    'risk_level' => $r->status,
                ])->toArray();
            } else {
                // Fallback: show last 5 readings of any status
                $this->recentAlerts = SensorReading::where('sensor_node_id', $this->node->id)
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn($r) => [
                        'id'         => $r->id,
                        'time'       => $r->created_at->diffForHumans(),
                        'title'      => match($r->status) {
                            'danger'  => '🔴 Danger Level Detected',
                            'caution' => '🟡 Standby Level Detected',
                            default   => '🟢 River Status Normal',
                        },
                        'desc'       => 'Water level: ' . number_format(200 - $r->distance_cm, 1) . ' cm | Humidity: ' . number_format($r->humidity_percent ?? 0, 0) . '% | Temp: ' . number_format($r->temperature_c ?? 0, 1) . '°C',
                        'risk_level' => $r->status,
                    ])->toArray();
            }
        }
    }

    public function render()
    {
        $this->loadData();
        return view('livewire.dashboard-home');
    }
}
