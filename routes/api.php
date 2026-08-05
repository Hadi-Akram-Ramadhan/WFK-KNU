<?php

use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\HardwareController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Bedadung SFEWS
|--------------------------------------------------------------------------
|
| GET  /api/sensor/data        → Check API status
| POST /api/sensor/data        → Ingest data from Wemos D1 Mini
| GET  /api/sensor/nodes       → List nodes & latest status
| GET  /api/hardware/commands/{nodeId} → Pending commands for Wemos
| POST /api/hardware/command   → Send command from dashboard
| GET  /api/hardware/logs/{nodeId}     → Hardware command logs
|
*/

Route::prefix('sensor')->group(function () {
    Route::get('/data',   [SensorDataController::class, 'status']);
    Route::post('/data',  [SensorDataController::class, 'ingest']);
    Route::get('/nodes',  [SensorDataController::class, 'nodes']);
});

Route::prefix('hardware')->group(function () {
    Route::get('/commands/{nodeId}',  [HardwareController::class, 'getPendingCommands']);
    Route::post('/command',           [HardwareController::class, 'sendCommand']);
    Route::get('/logs/{nodeId}',      [HardwareController::class, 'getLogs']);
});
