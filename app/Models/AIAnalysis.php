<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIAnalysis extends Model
{
    use HasFactory;

    protected $table = 'ai_analyses';

    protected $fillable = [
        'sensor_node_id',
        'sensor_reading_id',
        'trigger',
        'prompt_sent',
        'ai_response',
        'risk_level',
        'flood_probability_percent',
        'weather_condition',
        'recommended_actions',
        'model_used',
        'response_time_ms',
    ];

    protected $casts = [
        'recommended_actions'       => 'array',
        'flood_probability_percent' => 'integer',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(SensorNode::class, 'sensor_node_id');
    }

    public function reading(): BelongsTo
    {
        return $this->belongsTo(SensorReading::class, 'sensor_reading_id');
    }

    public function getRiskLevelColorAttribute(): string
    {
        return match($this->risk_level) {
            'critical' => 'error',
            'high'     => 'tertiary',
            'medium'   => 'secondary',
            'low'      => 'primary',
            default    => 'primary',
        };
    }
}
