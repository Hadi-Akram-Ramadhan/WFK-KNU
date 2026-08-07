<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'sensor_node_id',
        'distance_cm',
        'temperature_c',
        'humidity_percent',
        'water_level_m',
        'status',
        'rise_rate_cm_per_min',
        'capacity_percent',
    ];

    protected $casts = [
        'distance_cm' => 'decimal:2',
        'temperature_c' => 'decimal:2',
        'humidity_percent' => 'decimal:2',
        'water_level_m' => 'decimal:3',
        'rise_rate_cm_per_min' => 'decimal:4',
        'capacity_percent' => 'decimal:2',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(SensorNode::class, 'sensor_node_id');
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'danger' => 'error',
            'caution' => 'tertiary',
            'safe' => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Get status emoji.
     */
    public function getStatusEmojiAttribute(): string
    {
        return match ($this->status) {
            'danger' => '🚨',
            'caution' => '⚠️',
            'safe' => '✅',
            default => '❓',
        };
    }

    /**
     * Determine status from distance reading (<=3.0cm is Danger/Evacuate, 3.0-5.0cm Caution/Standby, >5.0cm Safe).
     */
    public static function statusFromDistance(float $distanceCm): string
    {
        if ($distanceCm <= 3.0) return 'danger';
        if ($distanceCm <= 5.0) return 'caution';
        return 'safe';
    }

    /**
     * Convert distance from sensor (cm) to water level height (m).
     * Sensor to bottom = 20cm, Sensor to top = 10cm. Cup height = 10cm.
     */
    public static function distanceToWaterLevel(float $distanceCm, float $sensorHeightCm = 20.0): float
    {
        $waterLevelCm = max(0, min(10.0, 20.0 - $distanceCm));
        return round($waterLevelCm / 100, 3);
    }

    /**
     * Calculate capacity percentage (0-100%) inside plastic cup.
     * 20cm distance = 0% capacity (empty cup).
     * 10cm distance = 100% capacity (full cup to top rim).
     */
    public static function distanceToCapacity(float $distanceCm, float $sensorHeightCm = 20.0): float
    {
        $percent = ((20.0 - $distanceCm) / 10.0) * 100;
        return round(max(0, min(100, $percent)), 2);
    }
}
