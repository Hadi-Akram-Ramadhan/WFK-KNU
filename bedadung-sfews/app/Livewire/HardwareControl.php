<?php

namespace App\Livewire;

use App\Http\Controllers\Api\HardwareController;
use App\Models\HardwareCommand;
use App\Models\SensorNode;
use Illuminate\Http\Request;
use Livewire\Component;

class HardwareControl extends Component
{
    public ?SensorNode $node = null;
    public bool $automatedMode = true;
    public int $servoAngle = 0;
    public bool $sirenActive = false;
    public array $systemLogs = [];
    public string $flashMessage = '';

    public function mount(): void
    {
        $this->node = SensorNode::first();
        $this->loadLogs();
    }

    public function toggleAutomatedMode(): void
    {
        $this->automatedMode = !$this->automatedMode;
        if ($this->node) {
            HardwareCommand::create([
                'sensor_node_id' => $this->node->id,
                'command_type' => 'automated_mode',
                'payload' => ['active' => $this->automatedMode],
                'source' => 'manual',
            ]);
        }
        $this->flashMessage = 'Mode otomatis ' . ($this->automatedMode ? 'diaktifkan' : 'dinonaktifkan');
        $this->loadLogs();
    }

    public function setServoAngle(): void
    {
        if ($this->node) {
            HardwareCommand::create([
                'sensor_node_id' => $this->node->id,
                'command_type' => 'servo',
                'payload' => ['angle' => $this->servoAngle, 'source' => 'manual_dashboard'],
                'source' => 'manual',
            ]);
        }
        $this->flashMessage = "Servo diatur ke {$this->servoAngle}°";
        $this->loadLogs();
    }

    public function testSiren(): void
    {
        if ($this->node) {
            HardwareCommand::create([
                'sensor_node_id' => $this->node->id,
                'command_type' => 'siren',
                'payload' => ['active' => true, 'duration_ms' => 3000, 'test' => true],
                'source' => 'manual',
            ]);
        }
        $this->flashMessage = 'Tes sirene dikirim ke hardware';
        $this->loadLogs();
    }

    public function loadLogs(): void
    {
        if (!$this->node)
            return;

        $this->systemLogs = HardwareCommand::where('sensor_node_id', $this->node->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($cmd) => [
                'time' => $cmd->created_at->setTimezone('Asia/Jakarta')->format('H:i:s'),
                'type' => strtoupper($cmd->command_type),
                'source' => $cmd->source,
                'payload' => $cmd->payload,
                'executed' => $cmd->executed,
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.hardware-control');
    }
}
