<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ControlController;
use Illuminate\Support\Facades\Route;

Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
Route::get('/map',       [MapController::class,       'index'])->name('map');
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
Route::get('/control',   [ControlController::class,   'index'])->name('control');
