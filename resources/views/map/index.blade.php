@extends('layouts.app')
@section('title', 'Live Map')
@section('content')
<div class="flex flex-col gap-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Live Peta Geospasial Node</h1>
        <p class="text-slate-500 text-sm font-normal mt-1">Pemetaan lokasi fisik sensor ultrasonik di sepanjang Aliran Sungai Bedadung</p>
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
        <div class="clean-card p-5 flex flex-col justify-between gap-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">{{ $node->node_id }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $node->name }}</p>
                </div>
                <span class="flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold uppercase border {{ $node->is_online ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $node->is_online ? 'bg-emerald-500 pulse-live' : 'bg-slate-400' }}"></span>
                    {{ $node->is_online ? 'Online' : 'Offline' }}
                </span>
            </div>

            @if($latest)
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/60 text-center">
                    <p class="font-mono text-xl font-bold {{ $textColor }}">
                        {{ number_format($latest->distance_cm, 1) }}
                    </p>
                    <p class="text-[11px] font-medium text-slate-400">Jarak Air (cm)</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/60 text-center">
                    <p class="font-mono text-xl font-bold {{ $textColor }}">
                        {{ number_format($latest->capacity_percent, 0) }}%
                    </p>
                    <p class="text-[11px] font-medium text-slate-400">Kapasitas</p>
                </div>
            </div>

            <div class="flex items-center justify-between pt-1 text-xs">
                <span class="px-2.5 py-0.5 rounded-md font-semibold uppercase border {{ $badgeBg }}">
                    {{ strtoupper($status) }}
                </span>
                <span class="font-mono text-[11px] text-slate-400">
                    {{ $latest->created_at->diffForHumans() }}
                </span>
            </div>
            @else
            <p class="text-xs text-slate-400 text-center py-4">Belum ada data sensor terdeteksi</p>
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

    const colors = { danger: '#f43f5e', caution: '#d97706', safe: '#10b981', offline: '#64748b' };

    nodes.forEach(node => {
        const color = colors[node.status] || colors.offline;
        const marker = L.circleMarker([node.lat, node.lng], {
            radius: 10, fillColor: color, color: '#ffffff',
            weight: 2.5, opacity: 1, fillOpacity: 0.9,
        }).addTo(map);

        marker.bindPopup(`
            <div style="font-family:'Poppins',sans-serif; min-width:160px;">
                <p style="font-size:10px;color:#64748b;margin:0 0 2px;font-family:'JetBrains Mono',monospace;">${node.node_id}</p>
                <p style="font-size:13px;font-weight:600;margin:0 0 4px;color:#0f172a;">${node.name}</p>
                <p style="font-size:24px;font-weight:700;font-family:'JetBrains Mono',monospace;color:${color};margin:2px 0;">${node.distance.toFixed(1)}<span style="font-size:12px"> cm</span></p>
                <span style="display:inline-block;padding:2px 8px;border-radius:6px;background:${color}15;color:${color};font-size:10px;font-weight:700;letter-spacing:0.05em;margin-top:4px;">${node.status.toUpperCase()}</span>
            </div>
        `);
    });
</script>
@endpush
