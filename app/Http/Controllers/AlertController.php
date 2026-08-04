<?php

namespace App\Http\Controllers;

use App\Models\SensorReading;
use App\Models\SensorNode;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index()
    {
        // Get all danger & caution readings (most recent first), limit to last 100
        $alerts = SensorReading::with('node')
            ->whereIn('status', ['danger', 'caution'])
            ->latest()
            ->limit(100)
            ->get();

        // Count by severity
        $dangerCount  = $alerts->where('status', 'danger')->count();
        $cautionCount = $alerts->where('status', 'caution')->count();
        $totalAlerts  = $alerts->count();

        // Get latest reading per node (for active status overview)
        $nodes = SensorNode::with(['readings' => function ($q) {
            $q->latest()->limit(1);
        }])->get();

        return view('alert-center', compact(
            'alerts', 'dangerCount', 'cautionCount', 'totalAlerts', 'nodes'
        ));
    }
}
