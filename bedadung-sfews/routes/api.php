<?php

use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\HardwareController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Bedadung SFEWS
|--------------------------------------------------------------------------
|
| POST /api/sensor/data        → Terima data dari Wemos D1 Mini
| GET  /api/sensor/nodes       → Daftar semua node & status terkini
| GET  /api/hardware/commands/{nodeId} → Pending commands untuk Wemos
| POST /api/hardware/command   → Kirim command dari dashboard
| GET  /api/hardware/logs/{nodeId}     → Log hardware commands
|
*/

Route::prefix('sensor')->group(function () {
    Route::post('/data',  [SensorDataController::class, 'ingest']);
    Route::get('/nodes',  [SensorDataController::class, 'nodes']);
});

Route::prefix('hardware')->group(function () {
    Route::get('/commands/{nodeId}',  [HardwareController::class, 'getPendingCommands']);
    Route::post('/command',           [HardwareController::class, 'sendCommand']);
    Route::get('/logs/{nodeId}',      [HardwareController::class, 'getLogs']);
});
