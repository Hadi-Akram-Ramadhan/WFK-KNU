<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\AlertController;
use App\Livewire\AiAnalytics;
use Illuminate\Support\Facades\Route;

Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
Route::get('/map',       [MapController::class,       'index'])->name('map');
Route::get('/analytics', AiAnalytics::class)->name('analytics');
Route::get('/alerts',    [AlertController::class,     'index'])->name('alerts');
