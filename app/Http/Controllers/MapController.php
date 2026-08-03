<?php

namespace App\Http\Controllers;

use App\Models\SensorNode;

class MapController extends Controller
{
    public function index()
    {
        $nodes = SensorNode::with(['latestReading'])->get();

        // Pre-map for Leaflet (avoids Blade @json arrow function parser issue)
        $nodesJson = $nodes->map(function ($n) {
            return [
                'node_id'  => $n->node_id,
                'name'     => $n->name,
                'lat'      => (float) $n->latitude,
                'lng'      => (float) $n->longitude,
                'status'   => $n->latestReading?->status ?? 'offline',
                'distance' => (float) ($n->latestReading?->distance_cm ?? 0),
            ];
        })->values()->toArray();

        return view('map.index', compact('nodes', 'nodesJson'));
    }
}
