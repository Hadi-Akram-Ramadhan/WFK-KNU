@extends('layouts.app')

@section('title', 'Alert Center — Bedadung SFEWS')
@section('header_title', 'Alert Center')

@section('content')
<div class="space-y-6">

    {{-- ── SUMMARY CARDS ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Total Alerts --}}
        <div class="rainova-card p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-2xl text-slate-500 ms-fill">notifications_active</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-slate-900">{{ $totalAlerts }}</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">Total Active Alerts</div>
            </div>
        </div>

        {{-- Danger --}}
        <div class="rainova-card p-5 flex items-center gap-4 border-l-4 border-rose-500">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-2xl text-rose-500 ms-fill">emergency</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-rose-600">{{ $dangerCount }}</div>
                <div class="text-xs text-rose-500 font-semibold mt-0.5">🚨 DANGER Alerts</div>
            </div>
        </div>

        {{-- Caution --}}
        <div class="rainova-card p-5 flex items-center gap-4 border-l-4 border-amber-400">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-2xl text-amber-500 ms-fill">warning</span>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-amber-600">{{ $cautionCount }}</div>
                <div class="text-xs text-amber-500 font-semibold mt-0.5">⚠️ CAUTION Alerts</div>
            </div>
        </div>
    </div>

    {{-- ── NODE STATUS OVERVIEW ── --}}
    <div class="rainova-card p-5">
        <h2 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-lg text-sky-500">sensors</span>
            Current Node Status
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @forelse ($nodes as $node)
                @php
                    $latest = $node->readings->first();
                    $status = $latest->status ?? 'unknown';
                    $colorMap = [
                        'safe'    => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-700', 'dot' => 'bg-emerald-500'],
                        'caution' => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'badge' => 'bg-amber-100 text-amber-700',   'dot' => 'bg-amber-400'],
                        'danger'  => ['bg' => 'bg-rose-50',    'text' => 'text-rose-700',     'badge' => 'bg-rose-100 text-rose-700',     'dot' => 'bg-rose-500'],
                        'unknown' => ['bg' => 'bg-slate-50',   'text' => 'text-slate-500',    'badge' => 'bg-slate-100 text-slate-500',   'dot' => 'bg-slate-400'],
                    ];
                    $c = $colorMap[$status] ?? $colorMap['unknown'];
                @endphp
                <div class="p-3.5 rounded-xl border border-slate-100 {{ $c['bg'] }} flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 truncate">{{ $node->name }}</span>
                        <span class="w-2.5 h-2.5 rounded-full {{ $c['dot'] }} flex-shrink-0"></span>
                    </div>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full w-fit {{ $c['badge'] }} uppercase tracking-wide">
                        {{ strtoupper($status) }}
                    </span>
                    @if ($latest)
                        <span class="text-[11px] text-slate-500 font-mono">
                            WL: {{ number_format($latest->water_level_m, 2) }}m · {{ number_format($latest->capacity_percent, 0) }}%
                        </span>
                    @endif
                </div>
            @empty
                <div class="col-span-full text-center text-sm text-slate-400 py-4">No sensor nodes found.</div>
            @endforelse
        </div>
    </div>

    {{-- ── ALERT LOG TABLE ── --}}
    <div class="rainova-card overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                <span class="material-symbols-outlined text-lg text-rose-500 ms-fill">error</span>
                Recent Alert Events
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-rose-100 text-rose-600 ml-1">{{ $totalAlerts > 99 ? '99+' : $totalAlerts }}</span>
            </h2>
            <span class="text-xs text-slate-400">Last 100 events</span>
        </div>

        @if ($alerts->isEmpty())
            <div class="p-12 text-center">
                <span class="material-symbols-outlined text-5xl text-emerald-400 ms-fill block mb-3">check_circle</span>
                <div class="text-slate-600 font-semibold">All Clear — No Active Alerts</div>
                <div class="text-sm text-slate-400 mt-1">All sensor nodes are reporting safe levels.</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Severity</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Sensor Node</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Water Level</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Capacity</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Rise Rate</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($alerts as $alert)
                            @php
                                $isDanger = $alert->status === 'danger';
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors {{ $isDanger ? 'bg-rose-50/30' : 'bg-amber-50/20' }}">
                                <td class="px-5 py-3.5">
                                    @if ($isDanger)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700">
                                            <span class="material-symbols-outlined text-[14px] ms-fill">emergency</span>
                                            DANGER
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700">
                                            <span class="material-symbols-outlined text-[14px] ms-fill">warning</span>
                                            CAUTION
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-semibold text-slate-800">{{ $alert->node->name ?? 'Node #'.$alert->sensor_node_id }}</span>
                                    <span class="text-[11px] text-slate-400 block">{{ $alert->node->location ?? '—' }}</span>
                                </td>
                                <td class="px-5 py-3.5 font-mono font-semibold text-slate-700">
                                    {{ number_format($alert->water_level_m, 3) }} m
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-slate-100 rounded-full max-w-[60px]">
                                            <div class="h-2 rounded-full {{ $isDanger ? 'bg-rose-500' : 'bg-amber-400' }}"
                                                 style="width: {{ min(100, $alert->capacity_percent) }}%"></div>
                                        </div>
                                        <span class="text-xs font-mono font-semibold text-slate-600">{{ number_format($alert->capacity_percent, 1) }}%</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-600">
                                    {{ $alert->rise_rate_cm_per_min > 0 ? '+' : '' }}{{ number_format($alert->rise_rate_cm_per_min, 2) }} cm/min
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500">
                                    <span class="font-mono">{{ $alert->created_at->format('d M Y, H:i:s') }}</span>
                                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $alert->created_at->diffForHumans() }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
