@section('header_title', 'Command Dashboard')

<div class="flex flex-col gap-6" wire:poll.3s>

    @php
        $status = $latestReading?->status ?? 'safe';
        $distanceCm = (float)($latestReading?->distance_cm ?? 25);
        $tempC = (float)($latestReading?->temperature_c ?? 33.3);
        $humidityRH = (float)($latestReading?->humidity_percent ?? 57.0);

        $statusTitle = match($status) {
            'danger'  => 'DANGER',
            'caution' => 'STANDBY',
            default   => 'SAFE',
        };

        $statusDesc = match($status) {
            'danger'  => 'Critical water level! Residents along the riverbank are advised to prepare for evacuation.',
            'caution' => 'Water level is rising, monitor river conditions.',
            default   => 'Water level is normal, river flow is smooth.',
        };

        $bannerGradient = match($status) {
            'danger'  => 'from-rose-600 via-rose-500 to-red-600',
            'caution' => 'from-amber-600 via-orange-500 to-amber-500',
            default   => 'from-emerald-600 via-teal-500 to-emerald-500',
        };

        $statusPillBg = match($status) {
            'danger'  => 'bg-rose-50 text-rose-700 border-rose-200',
            'caution' => 'bg-amber-50 text-amber-700 border-amber-200',
            default   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        };
    @endphp

    {{-- ── 1. TOP ROW: HERO ALERT BANNER + LIVE SENSOR LOCATION ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Left: Hero Alert Banner (8 Cols) --}}
        <div class="lg:col-span-8 bg-gradient-to-r {{ $bannerGradient }} rounded-3xl p-6 sm:p-8 text-white relative overflow-hidden shadow-lg flex flex-col justify-between min-h-[280px]">
            {{-- Waterdrop Illustration Background --}}
            <div class="absolute -right-6 -bottom-8 opacity-25 pointer-events-none">
                <span class="material-symbols-outlined text-[200px] ms-fill">water_drop</span>
            </div>

            <div>
                {{-- Status Pill Tag --}}
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-xs font-semibold text-white tracking-wide mb-4">
                    <span class="w-2 h-2 rounded-full bg-white pulse-live"></span>
                    <span>Current River Status</span>
                </div>

                {{-- Huge Status Title --}}
                <h1 class="text-4xl sm:text-6xl font-black tracking-tight leading-none text-white drop-shadow-sm">
                    {{ $statusTitle }}
                </h1>
                <p class="text-white/90 text-sm sm:text-base font-medium mt-3 max-w-lg leading-relaxed">
                    {{ $statusDesc }}
                </p>
            </div>

            {{-- 3 Pill Stats at Bottom of Banner --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-6 pt-6 border-t border-white/20 z-10">
                <div class="px-4 py-2.5 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20">
                    <span class="text-[11px] text-white/70 font-medium block">Water Level</span>
                    <span class="font-mono text-base font-bold text-white mt-0.5 block">{{ number_format($waterLevelCm, 1) }} cm</span>
                </div>
                <div class="px-4 py-2.5 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20">
                    <span class="text-[11px] text-white/70 font-medium block">Rain Status</span>
                    <span class="font-mono text-base font-bold text-white mt-0.5 block">{{ $rainStatus }}</span>
                </div>
                <div class="px-4 py-2.5 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20">
                    <span class="text-[11px] text-white/70 font-medium block">Monitoring Area</span>
                    <span class="font-sans text-xs font-bold text-white truncate mt-0.5 block">Bedadung River</span>
                </div>
            </div>
        </div>

        {{-- Right: Live Sensor Location Card (4 Cols) --}}
        <div class="lg:col-span-4 rainova-card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Live Sensor Location</h3>
                    <span class="text-[11px] text-slate-500 font-medium mt-0.5 block">Bedadung River • {{ $statusTitle }}</span>
                </div>
                <a href="{{ route('map') }}" class="px-3 py-1.5 rounded-xl bg-sky-50 text-sky-600 hover:bg-sky-100 border border-sky-100 text-xs font-bold transition-all flex items-center gap-1">
                    Open Map
                </a>
            </div>

            {{-- Map Embed Box --}}
            <div class="relative w-full h-44 rounded-2xl overflow-hidden my-3 border border-slate-200">
                <iframe
                    src="https://maps.google.com/maps?q=-8.172111,113.725111&z=15&output=embed"
                    class="w-full h-full border-0"
                    loading="lazy">
                </iframe>
                <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full border border-slate-200 text-[10px] font-bold text-slate-700 flex items-center gap-1.5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-amber-500 pulse-live"></span>
                    <span>Baratan - Sumbersari</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. MIDDLE ROW: 4 STAT CARDS ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- Card 1: Curah Hujan --}}
        <div class="rainova-card p-5 flex flex-col justify-between relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                    <span class="material-symbols-outlined text-2xl ms-fill">water_drop</span>
                </div>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold uppercase bg-slate-100 text-slate-500">
                    REALTIME
                </span>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 block">Rainfall Status</span>
                <span class="text-2xl font-black text-slate-900 tracking-tight mt-1 block uppercase">{{ $rainStatus }}</span>
            </div>
        </div>

        {{-- Card 2: Tinggi Air --}}
        <div class="rainova-card p-5 flex flex-col justify-between relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center border border-teal-100">
                    <span class="material-symbols-outlined text-2xl ms-fill">waves</span>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $statusPillBg }}">
                    {{ $statusTitle }}
                </span>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 block">Water Level</span>
                <div class="flex items-baseline gap-1.5 mt-1">
                    <span class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($waterLevelCm, 1) }}</span>
                    <span class="font-mono text-sm font-bold text-slate-500">cm</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Suhu & Kelembapan --}}
        <div class="rainova-card p-5 flex flex-col justify-between relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <span class="material-symbols-outlined text-2xl ms-fill">thermostat</span>
                </div>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold uppercase bg-slate-100 text-slate-500">
                    ENVIRONMENT
                </span>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 block">Temperature</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($tempC, 1) }}</span>
                    <span class="font-mono text-base font-bold text-slate-500">°C</span>
                    <span class="text-xs text-slate-400 font-mono ml-auto">{{ number_format($humidityRH, 0) }}% RH</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Status Sensor --}}
        <div class="rainova-card p-5 flex flex-col justify-between relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100">
                    <span class="material-symbols-outlined text-2xl ms-fill">memory</span>
                </div>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold uppercase bg-slate-100 text-slate-500">
                    DEVICE
                </span>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 block">Sensor Status</span>
                @if ($sensorOnline)
                    <span class="text-xl font-bold text-emerald-600 tracking-tight mt-1 block">Online</span>
                    <span class="text-[11px] text-emerald-500 font-semibold block mt-0.5">Last data: {{ $lastSeenAgo }}</span>
                @else
                    <span class="text-xl font-bold text-slate-400 tracking-tight mt-1 block">No Device</span>
                    <span class="text-[11px] text-slate-400 font-medium block mt-0.5">
                        {{ $lastSeenAgo ? 'Last seen: '.$lastSeenAgo : 'Awaiting sensor...' }}
                    </span>
                @endif
            </div>
        </div>

    </div>

    {{-- ── 3. BOTTOM ROW: WATER LEVEL TREND + RECENT ALERTS ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Left: Water Level Trend Chart (8 Cols) --}}
        <div class="lg:col-span-8 rainova-card p-6 flex flex-col justify-between gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight">Water Level Trend</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Realtime water level graph (cm)</p>
                </div>
                <a href="{{ route('analytics') }}" class="px-4 py-1.5 bg-sky-50 text-sky-600 hover:bg-sky-100 border border-sky-100 rounded-xl text-xs font-bold transition-all">
                    Analyze
                </a>
            </div>

            {{-- SVG Smooth Trend Chart --}}
            <div class="relative h-64 w-full mt-2">
                <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 100">
                    <defs>
                        <linearGradient id="trendGradRainova" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#0284c7" stop-opacity="0.25"/>
                            <stop offset="100%" stop-color="#0284c7" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    @php
                        $chartPts = collect($recentReadings);
                        $points = $chartPts->map(function($d, $i) use ($chartPts) {
                            $x = count($chartPts) > 1 ? ($i / (count($chartPts) - 1)) * 100 : 50;
                            $y = min(90, max(10, 100 - (($d['water_level'] / 250) * 100)));
                            return "{$x},{$y}";
                        })->join(' ');
                        $fillPath = "M0," . min(90, max(10, 100 - ((($recentReadings[0]['water_level'] ?? 180) / 250) * 100))) .
                                    ($points ? " L" . str_replace(' ', ' L', $points) : '') .
                                    " L100,100 L0,100 Z";
                        $linePath = $recentReadings ? "M" . implode(' L', array_map(function($d, $i) use ($chartPts) {
                            $x = count($chartPts) > 1 ? ($i / (count($chartPts) - 1)) * 100 : 50;
                            $y = min(90, max(10, 100 - (($d['water_level'] / 250) * 100)));
                            return "{$x},{$y}";
                        }, $recentReadings, array_keys($recentReadings))) : "M0,50 L100,50";
                    @endphp
                    <path d="{{ $fillPath }}" fill="url(#trendGradRainova)"/>
                    <path d="{{ $linePath }}" fill="none" stroke="#0284c7" stroke-width="3" vector-effect="non-scaling-stroke"/>
                </svg>

                <div class="absolute bottom-0 w-full flex justify-between font-mono text-[10px] text-slate-400 pt-2 border-t border-slate-100">
                    @foreach(array_slice($recentReadings, -6) as $r)
                        <span>{{ $r['time'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right: Recent Alerts Log List (4 Cols) --}}
        <div class="lg:col-span-4 rainova-card p-6 flex flex-col justify-between gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight">Recent Alerts</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Recent system alert log</p>
                </div>
                <a href="{{ route('alerts') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700">All</a>
            </div>

            <div class="space-y-3 my-1">
                @forelse($recentAlerts as $alert)
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
                        {{ $alert['risk_level'] === 'critical' || $alert['risk_level'] === 'high' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700' }}">
                        <span class="material-symbols-outlined text-base">warning</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-800 truncate">{{ $alert['title'] }}</span>
                            <span class="text-[10px] font-mono text-slate-400 ml-1">{{ $alert['time'] }}</span>
                        </div>
                        <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $alert['desc'] }}</p>
                    </div>
                </div>
                @empty
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/60 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-base ms-fill">warning</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-amber-900 block">Standby status detected</span>
                        <p class="text-[11px] text-amber-700 mt-0.5 leading-snug">Water level reached {{ number_format($waterLevelCm, 1) }} cm and needs continuous monitoring.</p>
                    </div>
                </div>
                @endforelse
            </div>

            <div class="pt-2">
                <a href="{{ route('analytics') }}" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold text-center block transition-all">
                    View Full Analysis
                </a>
            </div>
        </div>

    </div>

</div>
