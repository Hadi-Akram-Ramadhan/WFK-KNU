<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SensorNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'node_id',
        'name',
        'latitude',
        'longitude',
        'status',
        'sensor_height_cm',
        'last_seen',
        'api_token',
    ];

    protected $casts = [
        'latitude'         => 'decimal:8',
        'longitude'        => 'decimal:8',
        'sensor_height_cm' => 'decimal:2',
        'last_seen'        => 'datetime',
    ];

    public function readings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    public function latestReading(): HasOne
    {
        return $this->hasOne(SensorReading::class)->latestOfMany();
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(AIAnalysis::class);
    }

    public function hardwareCommands(): HasMany
    {
        return $this->hasMany(HardwareCommand::class);
    }

    public function pendingCommands(): HasMany
    {
        return $this->hasMany(HardwareCommand::class)->where('executed', false)->latest();
    }

    /**
     * Get readings from the last N minutes for trend analysis.
     */
    public function recentReadings(int $minutes = 30): HasMany
    {
        return $this->hasMany(SensorReading::class)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->orderBy('created_at');
    }

    /**
     * Check if node is online (seen in last 20 seconds).
     */
    public function getIsOnlineAttribute(): bool
    {
        return $this->last_seen && $this->last_seen->diffInSeconds(now()) <= 20;
    }
}
