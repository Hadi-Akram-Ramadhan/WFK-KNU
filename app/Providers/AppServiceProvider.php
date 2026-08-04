<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Models\SensorReading;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Force HTTPS in production to prevent Mixed Content errors with Livewire assets
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Share real-time sidebar data to all views
        View::composer('*', function ($view) {
            try {
                // Real alert count (danger + caution readings)
                $alertCount = SensorReading::whereIn('status', ['danger', 'caution'])->count();

                // Sensor is "online" if there's a reading within the last 5 minutes
                $lastReading = SensorReading::latest()->first();
                $sensorOnline = $lastReading && $lastReading->created_at->diffInMinutes(now()) <= 5;
                $lastSeenAgo  = $lastReading ? $lastReading->created_at->diffForHumans() : null;
            } catch (\Exception $e) {
                $alertCount   = 0;
                $sensorOnline = false;
                $lastSeenAgo  = null;
            }

            $view->with([
                'globalAlertCount' => $alertCount,
                'sensorOnline'     => $sensorOnline,
                'lastSeenAgo'      => $lastSeenAgo,
            ]);
        });
    }
}
