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
            $this->recentReadings = $this->node->recentReadings(60)
                ->get()
                ->map(fn($r) => [
                    'time'     => $r->created_at->format('H:i'),
                    'distance' => (float) $r->distance_cm,
                    'status'   => $r->status,
                ])
                ->values()
                ->toArray();
        }
    }

    public function render()
    {
        $this->loadData();
        return view('livewire.dashboard-home');
    }
}
