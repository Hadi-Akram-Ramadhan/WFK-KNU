<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\AlertController;
use Illuminate\Support\Facades\Route;

Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
Route::get('/map',       [MapController::class,       'index'])->name('map');
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
Route::get('/alerts',    [AlertController::class,     'index'])->name('alerts');
Route::get('/control',   [ControlController::class,   'index'])->name('control');
