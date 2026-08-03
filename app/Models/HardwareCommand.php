<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HardwareCommand extends Model
{
    use HasFactory;

    protected $fillable = [
        'sensor_node_id',
        'command_type',
        'payload',
        'source',
        'executed',
        'executed_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'executed'    => 'boolean',
        'executed_at' => 'datetime',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(SensorNode::class, 'sensor_node_id');
    }

    public function markExecuted(): void
    {
        $this->update([
            'executed'    => true,
            'executed_at' => now(),
        ]);
    }
}
