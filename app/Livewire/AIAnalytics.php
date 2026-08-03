<?php

namespace App\Livewire;

use App\Models\SensorNode;
use App\Models\AIAnalysis;
use App\Models\SensorReading;
use Livewire\Component;
use Livewire\Attributes\On;

class AIAnalytics extends Component
{
    public ?SensorNode $node = null;
    public ?AIAnalysis $latestAnalysis = null;
    public array $chartData = [];
    public string $currentStatus = 'safe';
    public float $currentDistance = 0;
    public array $automatedActions = [];

    public function mount(): void
    {
        $this->loadData();
    }

    #[On('echo:sfews,sensor.updated')]
    public function onSensorUpdate(array $data): void
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
        }

        // Build chart data (last 60 minutes)
        $this->chartData = SensorReading::where('sensor_node_id', $this->node->id)
            ->where('created_at', '>=', now()->subMinutes(60))
            ->orderBy('created_at')
            ->get()
            ->map(fn($r) => [
                'time'     => $r->created_at->format('H:i'),
                'distance' => (float) $r->distance_cm,
                'status'   => $r->status,
            ])
            ->values()
            ->toArray();

        // Automated actions derived from latest analysis
        $this->automatedActions = $this->latestAnalysis
            ? collect($this->latestAnalysis->recommended_actions ?? [])
                ->map(fn($action, $i) => ['label' => $action, 'done' => $i < 2])
                ->values()
                ->toArray()
            : [];
    }

    public function render()
    {
        return view('livewire.ai-analytics');
    }
}
