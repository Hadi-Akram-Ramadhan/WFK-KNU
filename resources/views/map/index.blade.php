@extends('layouts.app')
@section('title', 'Live Geospatial Map — Bedadung SFEWS')
@section('header_title', 'Sensor Map')

@section('content')
<div class="flex flex-col gap-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Live Geospatial Node Map</h1>
        <p class="text-slate-500 text-sm font-normal mt-1">Physical location mapping of ultrasonic sensors along the Bedadung River stream</p>
    </div>

    {{-- Leaflet Map Box --}}
    <section class="clean-card overflow-hidden p-2" style="height: 480px;">
        <div id="sfews-map" class="w-full h-full rounded-xl"></div>
    </section>

    {{-- Node Cards Below Map --}}
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($nodes as $node)
        @php
            $latest = $node->latestReading;
            $status = $latest?->status ?? 'offline';
            $textColor = match($status) {
                'danger'  => 'text-rose-600',
                'caution' => 'text-amber-600',
                'safe'    => 'text-emerald-600',
                default   => 'text-slate-500',
            };
            $badgeBg = match($status) {
                'danger'  => 'bg-rose-50 text-rose-700 border-rose-200',
                'caution' => 'bg-amber-50 text-amber-700 border-amber-200',
                'safe'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                default   => 'bg-slate-100 text-slate-600 border-slate-200',
            };
        @endphp
        <div id="node-card-{{ $node->node_id }}" class="clean-card p-5 flex flex-col justify-between gap-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">{{ $node->node_id }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $node->name }}</p>
                </div>
                <span id="node-badge-{{ $node->node_id }}" class="flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold uppercase border {{ $node->is_online ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                    <span id="node-dot-{{ $node->node_id }}" class="w-1.5 h-1.5 rounded-full {{ $node->is_online ? 'bg-emerald-500 pulse-live' : 'bg-slate-400' }}"></span>
                    <span>{{ $node->is_online ? 'Online' : 'Offline' }}</span>
                </span>
            </div>

            @if($latest)
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/60 text-center">
                    <p id="node-dist-{{ $node->node_id }}" class="font-mono text-xl font-bold {{ $textColor }}">
                        {{ number_format($latest->distance_cm, 1) }}
                    </p>
                    <p class="text-[11px] font-medium text-slate-400">Water Distance (cm)</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/60 text-center">
                    <p class="font-mono text-xl font-bold {{ $textColor }}">
                        {{ number_format($latest->capacity_percent, 0) }}%
                    </p>
                    <p class="text-[11px] font-medium text-slate-400">Capacity</p>
                </div>
            </div>

            <div class="flex items-center justify-between pt-1 text-xs">
                <span id="node-status-badge-{{ $node->node_id }}" class="px-2.5 py-0.5 rounded-md font-semibold uppercase border {{ $badgeBg }}">
                    {{ strtoupper($status) }}
                </span>
                <span id="node-time-{{ $node->node_id }}" class="font-mono text-[11px] text-slate-400">
                    {{ $latest->created_at->diffForHumans() }}
                </span>
            </div>
            @else
            <p class="text-xs text-slate-400 text-center py-4">No sensor data detected yet</p>
            @endif
        </div>
        @endforeach

    </section>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const nodes = @json($nodesJson);

    const map = L.map('sfews-map').setView([-8.1680, 113.7003], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);

    const STATUS_COLORS = { danger: '#f43f5e', caution: '#d97706', safe: '#10b981', offline: '#64748b' };

    // Store markers by node_id for live update
    const markerMap = {};

    nodes.forEach(node => {
        const color = STATUS_COLORS[node.status] || STATUS_COLORS.offline;
        const marker = L.circleMarker([node.lat, node.lng], {
            radius: 10, fillColor: color, color: '#ffffff',
            weight: 2.5, opacity: 1, fillOpacity: 0.9,
        }).addTo(map);

        marker.bindPopup(`
            <div id="popup-${node.node_id}" style="font-family:'Poppins',sans-serif; min-width:160px;">
                <p style="font-size:10px;color:#64748b;margin:0 0 2px;font-family:'JetBrains Mono',monospace;">${node.node_id}</p>
                <p style="font-size:13px;font-weight:600;margin:0 0 4px;color:#0f172a;">${node.name}</p>
                <p id="popup-dist-${node.node_id}" style="font-size:24px;font-weight:700;font-family:'JetBrains Mono',monospace;color:${color};margin:2px 0;">${node.distance.toFixed(1)}<span style="font-size:12px"> cm</span></p>
                <span id="popup-status-${node.node_id}" style="display:inline-block;padding:2px 8px;border-radius:6px;background:${color}15;color:${color};font-size:10px;font-weight:700;letter-spacing:0.05em;margin-top:4px;">${node.status.toUpperCase()}</span>
            </div>
        `);

        markerMap[node.node_id] = marker;
    });

    // ── LIVE STATUS POLLING ── update marker + node cards every 2s
    async function pollMapStatus() {
        try {
            const res  = await fetch('/api/status/live', { cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();

            // Only one node (BEDADUNG_01) for now — update all markers
            const nodeId   = 'BEDADUNG_01';
            const status   = data.online ? (data.status || 'offline') : 'offline';
            const color    = STATUS_COLORS[status] || STATUS_COLORS.offline;
            const distance = data.water_level !== null ? (200 - data.water_level).toFixed(1) : '--';

            // Update Leaflet marker color
            const marker = markerMap[nodeId];
            if (marker) {
                marker.setStyle({ fillColor: color });
            }

            // Update popup content (only if popup is open)
            const popupDist   = document.getElementById(`popup-dist-${nodeId}`);
            const popupStatus = document.getElementById(`popup-status-${nodeId}`);
            if (popupDist)   { popupDist.style.color = color; popupDist.innerHTML = distance + '<span style="font-size:12px"> cm</span>'; }
            if (popupStatus) { popupStatus.textContent = status.toUpperCase(); popupStatus.style.color = color; popupStatus.style.background = color + '20'; }

            // Update node card below map
            const card = document.getElementById(`node-card-${nodeId}`);
            if (!card) return;

            const badgeEl    = document.getElementById(`node-badge-${nodeId}`);
            const dotEl      = document.getElementById(`node-dot-${nodeId}`);
            const distEl     = document.getElementById(`node-dist-${nodeId}`);
            const statusEl   = document.getElementById(`node-status-badge-${nodeId}`);
            const timeEl     = document.getElementById(`node-time-${nodeId}`);

            const BADGE_CLASSES = {
                danger:  'bg-emerald-50 text-emerald-700 border-emerald-200',
                caution: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                safe:    'bg-emerald-50 text-emerald-700 border-emerald-200',
                offline: 'bg-slate-100 text-slate-500 border-slate-200',
            };
            const STATUS_BADGE = {
                danger:  'bg-rose-100 text-rose-700',
                caution: 'bg-amber-100 text-amber-700',
                safe:    'bg-emerald-100 text-emerald-700',
                offline: 'bg-slate-100 text-slate-500',
            };
            const TEXT_COLORS = {
                danger: 'text-rose-600', caution: 'text-amber-600', safe: 'text-emerald-600', offline: 'text-slate-500',
            };

            if (badgeEl) {
                badgeEl.className = `flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold uppercase border ${data.online ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'}`;
                badgeEl.querySelector('span:first-child') && (badgeEl.querySelector('span:first-child').className = `w-1.5 h-1.5 rounded-full ${data.online ? 'bg-emerald-500 pulse-live' : 'bg-slate-400'}`);
                const labelSpan = badgeEl.querySelector('span:last-child');
                if (labelSpan) labelSpan.textContent = data.online ? 'Online' : 'Offline';
            }
            if (distEl)    { distEl.className = `font-mono text-xl font-bold ${TEXT_COLORS[status] || TEXT_COLORS.offline}`; distEl.textContent = distance; }
            if (statusEl)  { statusEl.className = `px-2.5 py-0.5 rounded-md font-semibold uppercase border ${STATUS_BADGE[status] || STATUS_BADGE.offline}`; statusEl.textContent = status.toUpperCase(); }
            if (timeEl)    { timeEl.textContent = 'just now'; }

        } catch (e) { /* ignore network errors */ }
    }

    pollMapStatus();
    setInterval(pollMapStatus, 2000);
</script>
@endpush
