<?php

use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\HardwareController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Bedadung SFEWS
|--------------------------------------------------------------------------
|
| Rate limiting:
| - Public endpoints: 120/min per IP
| - Hardware endpoints: 300/min per token
| - Live status: 600/min (10 req/sec for dashboard polling)
|
*/

// Ultra-lightweight live status — cached 3s, heavy traffic allowed
Route::get('/status/live', [SensorDataController::class, 'liveStatus'])
    ->middleware('throttle:600,1');

Route::prefix('sensor')->middleware('throttle:120,1')->group(function () {
    Route::get('/data',   [SensorDataController::class, 'status']);
    Route::post('/data',  [SensorDataController::class, 'ingest'])->withoutMiddleware('throttle:120,1')->middleware('throttle:300,1');
    Route::get('/nodes',  [SensorDataController::class, 'nodes']);
});

Route::prefix('hardware')->middleware('throttle:300,1')->group(function () {
    Route::get('/commands/{nodeId}',  [HardwareController::class, 'getPendingCommands']);
    Route::post('/command',           [HardwareController::class, 'sendCommand']);
    Route::get('/logs/{nodeId}',      [HardwareController::class, 'getLogs']);
});


