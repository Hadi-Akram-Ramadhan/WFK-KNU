<?php

namespace App\Events;

use App\Models\SensorReading;
use App\Models\SensorNode;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewSensorDataReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly SensorReading $reading,
        public readonly SensorNode    $node,
    ) {}

    /**
     * Broadcast on the public 'sfews' channel.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('sfews'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'sensor.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'node_id'            => $this->node->node_id,
            'node_name'          => $this->node->name,
            'distance_cm'        => (float) $this->reading->distance_cm,
            'water_level_m'      => (float) $this->reading->water_level_m,
            'status'             => $this->reading->status,
            'rise_rate'          => (float) ($this->reading->rise_rate_cm_per_min ?? 0),
            'capacity_percent'   => (float) ($this->reading->capacity_percent ?? 0),
            'timestamp'          => $this->reading->created_at->toISOString(),
        ];
    }
}
