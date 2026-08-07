<div class="flex flex-col gap-6" wire:poll.5s>

    @php
        $hasReading = $hasData ?? false;
        $status = $currentStatus ?? 'no_data';

        $statusTitle = match($status) {
            'danger'  => 'DANGER',
            'caution' => 'STANDBY',
            'safe'    => 'SAFE',
            default   => 'NO DATA',
        };

        $statusPillStyle = match($status) {
            'danger'  => 'bg-rose-100 text-rose-700 border-rose-200',
            'caution' => 'bg-amber-100 text-amber-700 border-amber-200',
            'safe'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            default   => 'bg-slate-100 text-slate-500 border-slate-200',
        };

        $probColor = match(true) {
            !$hasReading            => 'text-slate-400',
            $floodProbability >= 75 => 'text-rose-600',
            $floodProbability >= 40 => 'text-amber-600',
            default                 => 'text-emerald-600',
        };

        $probBarColor = match(true) {
            !$hasReading            => 'bg-slate-300',
            $floodProbability >= 75 => 'bg-rose-500',
            $floodProbability >= 40 => 'bg-amber-500',
            default                 => 'bg-emerald-500',
        };

        $probRingColor = match(true) {
            !$hasReading            => '#94a3b8',
            $floodProbability >= 75 => '#f43f5e',
            $floodProbability >= 40 => '#f59e0b',
            default                 => '#10b981',
        };

        $probLabel = match(true) {
            !$hasReading            => 'AWAITING DATA',
            $floodProbability >= 75 => 'HIGH RISK',
            $floodProbability >= 40 => 'ELEVATED',
            default                 => 'LOW RISK',
        };

        $humRingColor = match(true) {
            !$hasReading                 => '#94a3b8',
            ($currentHumidity ?? 0) > 85 => '#818cf8',
            ($currentHumidity ?? 0) > 70 => '#60a5fa',
            default                      => '#34d399',
        };

        $humLabel = match(true) {
            !$hasReading                 => 'NO DATA',
            ($currentHumidity ?? 0) > 85 => 'HIGH',
            ($currentHumidity ?? 0) > 70 => 'MODERATE',
            default                      => 'NORMAL',
        };

        $riskLevel = match(true) {
            !$hasReading            => 'N/A',
            $floodProbability >= 75 => 'CRITICAL',
            $floodProbability >= 55 => 'HIGH',
            $floodProbability >= 35 => 'MEDIUM',
            default                 => 'LOW',
        };
    @endphp

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- SECTION 1: HERO HEADER STATION STATUS                          --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <div class="rainova-card lg:col-span-7 p-6 sm:p-8 flex flex-col justify-between relative overflow-hidden bg-white">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[11px] font-extrabold text-sky-600 uppercase tracking-widest block mb-2">
                        PRIMARY MONITORING STATION
                    </span>
                    <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        Bedadung River — Baratan
                    </h1>
                    <p class="text-slate-500 text-xs sm:text-sm font-medium mt-2 leading-relaxed">
                        {{ $hasReading ? 'Live sensor telemetry active — AI flood prediction running continuously.' : 'Awaiting Wemos D1 Mini hardware connection to start live telemetry.' }}
                    </p>
                </div>
                <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $statusPillStyle }}">
                    {{ $statusTitle }}
                </span>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $hasReading ? 'bg-emerald-500 pulse-live' : 'bg-slate-400' }}"></span>
                    Sensor 01 — Jl. Gajah Mada, Sumbersari, Jember
                </span>
                <span class="font-mono text-slate-400">ID: BEDADUNG_01</span>
            </div>
        </div>

        {{-- Realtime readings compact --}}
        <div class="rainova-card lg:col-span-5 p-6 flex flex-col justify-between">
            <div class="pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900 tracking-tight">Live Telemetry</h2>
                <span class="text-[11px] text-slate-400 font-medium">Real-time sensor metrics</span>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-4">
                <div class="p-3.5 rounded-2xl bg-sky-50 border border-sky-100">
                    <span class="text-[10px] font-semibold text-sky-600 block uppercase tracking-wider">Water Level</span>
                    <span class="text-xl font-black text-sky-900 tracking-tight mt-1 block font-mono">
                        {{ $hasReading ? number_format($waterLevelCm, 1) . ' cm' : '--' }}
                    </span>
                </div>
                <div class="p-3.5 rounded-2xl {{ $hasReading && $currentHumidity > 85 ? 'bg-indigo-50 border-indigo-100' : 'bg-slate-50 border-slate-100' }} border">
                    <span class="text-[10px] font-semibold text-slate-500 block uppercase tracking-wider">Rainfall</span>
                    <span class="text-xl font-black text-slate-900 tracking-tight mt-1 block uppercase">
                        {{ $rainStatus }}
                    </span>
                </div>
                <div class="p-3.5 rounded-2xl bg-orange-50 border border-orange-100">
                    <span class="text-[10px] font-semibold text-orange-600 block uppercase tracking-wider">Temperature</span>
                    <span class="text-xl font-black text-orange-900 tracking-tight mt-1 block font-mono">
                        {{ !is_null($currentTemp) ? number_format($currentTemp, 1) . ' °C' : '--' }}
                    </span>
                </div>
                <div class="p-3.5 rounded-2xl bg-violet-50 border border-violet-100">
                    <span class="text-[10px] font-semibold text-violet-600 block uppercase tracking-wider">Humidity</span>
                    <span class="text-xl font-black text-violet-900 tracking-tight mt-1 block font-mono">
                        {{ !is_null($currentHumidity) ? number_format($currentHumidity, 0) . '% RH' : '--' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- SECTION 2: AI PREDICTION DASHBOARD                             --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-sm">
                <span class="material-symbols-outlined text-white text-base">auto_awesome</span>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">AI Flood Prediction Dashboard</h2>
                <p class="text-xs text-slate-400">Real-time machine learning analysis based on live sensor input</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── CARD 1: Flood Probability Gauge ── --}}
            <div class="rainova-card p-6 flex flex-col items-center text-center">
                <span class="text-[11px] font-bold text-violet-600 uppercase tracking-widest mb-4">Flood Probability</span>

                {{-- SVG Gauge --}}
                <div class="relative w-44 h-28 mx-auto">
                    <svg viewBox="0 0 180 110" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <!-- Background arc -->
                        <path d="M 20,100 A 70,70 0 0,1 160,100" fill="none" stroke="#e2e8f0" stroke-width="14" stroke-linecap="round"/>
                        <!-- Colored fill arc (dynamic) -->
                        @php
                            $prob = $hasReading ? min(100, $floodProbability) : 0;
                            // Full arc = 180deg, so dasharray = 220 (approx circumference of half)
                            $arcTotal = 220;
                            $filled = round(($prob / 100) * $arcTotal, 1);
                        @endphp
                        <path
                            d="M 20,100 A 70,70 0 0,1 160,100"
                            fill="none"
                            stroke="{{ $probRingColor }}"
                            stroke-width="14"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $filled }} {{ $arcTotal }}"
                            style="transition: stroke-dasharray 0.8s ease;"
                        />
                        <!-- Center number -->
                        <text x="90" y="88" text-anchor="middle" font-size="30" font-weight="900" fill="{{ $probRingColor }}" font-family="JetBrains Mono, monospace">
                            {{ $hasReading ? $floodProbability : '--' }}
                        </text>
                        <text x="90" y="105" text-anchor="middle" font-size="12" fill="#94a3b8" font-family="Poppins, sans-serif">
                            {{ $hasReading ? '%' : 'NO DATA' }}
                        </text>
                    </svg>
                </div>

                <div class="mt-3 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border
                    {{ $hasReading && $floodProbability >= 75 ? 'bg-rose-100 text-rose-700 border-rose-200' :
                      ($hasReading && $floodProbability >= 40 ? 'bg-amber-100 text-amber-700 border-amber-200' :
                      ($hasReading ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-400 border-slate-200')) }}">
                    {{ $probLabel }}
                </div>

                <!-- 3-Zone Bar -->
                <div class="w-full mt-5">
                    <div class="flex rounded-full overflow-hidden h-2 bg-slate-100 gap-0.5">
                        <div class="h-full rounded-l-full bg-emerald-400" style="width: 35%"></div>
                        <div class="h-full bg-amber-400" style="width: 30%"></div>
                        <div class="h-full rounded-r-full bg-rose-400" style="width: 35%"></div>
                    </div>
                    <div class="flex justify-between text-[9px] font-semibold text-slate-400 mt-1 px-0.5">
                        <span>LOW</span><span>MEDIUM</span><span>HIGH</span>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 mt-4 leading-relaxed">
                    {{ $hasReading ? $weatherCondition : 'Connect sensor hardware to start AI analysis.' }}
                </p>
            </div>

            {{-- ── CARD 2: Humidity & Environment ── --}}
            <div class="rainova-card p-6 flex flex-col items-center text-center">
                <span class="text-[11px] font-bold text-violet-600 uppercase tracking-widest mb-4">Humidity Analysis</span>

                <div class="relative w-44 h-28 mx-auto">
                    <svg viewBox="0 0 180 110" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <path d="M 20,100 A 70,70 0 0,1 160,100" fill="none" stroke="#e2e8f0" stroke-width="14" stroke-linecap="round"/>
                        @php
                            $hum = $hasReading ? min(100, $currentHumidity ?? 0) : 0;
                            $humFilled = round(($hum / 100) * 220, 1);
                        @endphp
                        <path
                            d="M 20,100 A 70,70 0 0,1 160,100"
                            fill="none"
                            stroke="{{ $humRingColor }}"
                            stroke-width="14"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $humFilled }} 220"
                            style="transition: stroke-dasharray 0.8s ease;"
                        />
                        <text x="90" y="88" text-anchor="middle" font-size="30" font-weight="900" fill="{{ $humRingColor }}" font-family="JetBrains Mono, monospace">
                            {{ $hasReading ? number_format($currentHumidity, 0) : '--' }}
                        </text>
                        <text x="90" y="105" text-anchor="middle" font-size="12" fill="#94a3b8" font-family="Poppins, sans-serif">
                            {{ $hasReading ? '% RH' : 'NO DATA' }}
                        </text>
                    </svg>
                </div>

                <div class="mt-3 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border
                    {{ $hasReading && ($currentHumidity ?? 0) > 85 ? 'bg-indigo-100 text-indigo-700 border-indigo-200' :
                      ($hasReading && ($currentHumidity ?? 0) > 70 ? 'bg-sky-100 text-sky-700 border-sky-100' :
                      ($hasReading ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-400 border-slate-200')) }}">
                    {{ $humLabel }}
                </div>

                <!-- Humidity Breakdown Bars -->
                <div class="w-full mt-5 space-y-2 text-left">
                    @php
                        $humVal = $hasReading ? ($currentHumidity ?? 0) : 0;
                        $tempVal = $hasReading ? ($currentTemp ?? 0) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-[10px] text-slate-500 mb-1">
                            <span class="font-semibold">Humidity</span>
                            <span class="font-mono font-bold text-violet-600">{{ $hasReading ? number_format($humVal, 0) . '%' : '--' }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-violet-500 rounded-full transition-all duration-700" style="width: {{ $hasReading ? min(100, $humVal) : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[10px] text-slate-500 mb-1">
                            <span class="font-semibold">Temperature</span>
                            <span class="font-mono font-bold text-orange-500">{{ $hasReading ? number_format($tempVal, 1) . '°C' : '--' }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-orange-400 rounded-full transition-all duration-700" style="width: {{ $hasReading ? min(100, ($tempVal / 50) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[10px] text-slate-500 mb-1">
                            <span class="font-semibold">Upstream Rain Risk</span>
                            <span class="font-mono font-bold text-sky-600">{{ $hasReading ? ($humVal > 85 ? 'HIGH' : ($humVal > 70 ? 'MED' : 'LOW')) : '--' }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700
                                {{ $humVal > 85 ? 'bg-rose-400' : ($humVal > 70 ? 'bg-amber-400' : 'bg-sky-400') }}"
                                style="width: {{ $hasReading ? min(100, (($humVal - 40) / 60) * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── CARD 3: AI Risk Assessment & Actions ── --}}
            <div class="rainova-card p-6 flex flex-col">
                <span class="text-[11px] font-bold text-violet-600 uppercase tracking-widest mb-4">AI Risk Assessment</span>

                <!-- Risk Level Badge -->
                <div class="rounded-2xl p-4 mb-4
                    {{ $hasReading && $floodProbability >= 75 ? 'bg-rose-50 border border-rose-200' :
                      ($hasReading && $floodProbability >= 40 ? 'bg-amber-50 border border-amber-200' :
                      ($hasReading ? 'bg-emerald-50 border border-emerald-200' : 'bg-slate-50 border border-slate-200')) }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Flood Risk Level</p>
                            <p class="text-2xl font-black mt-0.5
                                {{ $hasReading && $floodProbability >= 75 ? 'text-rose-600' :
                                  ($hasReading && $floodProbability >= 40 ? 'text-amber-600' :
                                  ($hasReading ? 'text-emerald-600' : 'text-slate-400')) }}">
                                {{ $riskLevel }}
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center
                            {{ $hasReading && $floodProbability >= 75 ? 'bg-rose-100' :
                              ($hasReading && $floodProbability >= 40 ? 'bg-amber-100' :
                              ($hasReading ? 'bg-emerald-100' : 'bg-slate-100')) }}">
                            <span class="material-symbols-outlined text-2xl
                                {{ $hasReading && $floodProbability >= 75 ? 'text-rose-500' :
                                  ($hasReading && $floodProbability >= 40 ? 'text-amber-500' :
                                  ($hasReading ? 'text-emerald-500' : 'text-slate-400')) }}">
                                {{ $hasReading && $floodProbability >= 75 ? 'crisis_alert' : ($hasReading && $floodProbability >= 40 ? 'warning' : 'check_circle') }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 w-full bg-white/60 rounded-full h-1.5 overflow-hidden">
                        <div class="h-full rounded-full {{ $probBarColor }} transition-all duration-700"
                             style="width: {{ $hasReading ? $floodProbability : 0 }}%"></div>
                    </div>
                </div>

                <!-- AI Recommended Actions -->
                <div class="flex-1">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">AI Guidance</p>
                    <ul class="space-y-2">
                        @forelse($automatedActions as $action)
                        <li class="flex items-start gap-2.5 text-xs text-slate-600 leading-relaxed">
                            <span class="mt-0.5 w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0
                                {{ $hasReading && $floodProbability >= 75 ? 'bg-rose-100' :
                                  ($hasReading && $floodProbability >= 40 ? 'bg-amber-100' : 'bg-emerald-100') }}">
                                <span class="material-symbols-outlined text-[11px]
                                    {{ $hasReading && $floodProbability >= 75 ? 'text-rose-600' :
                                      ($hasReading && $floodProbability >= 40 ? 'text-amber-600' : 'text-emerald-600') }}">
                                    {{ $hasReading && $floodProbability >= 75 ? 'priority_high' : 'check' }}
                                </span>
                            </span>
                            {{ $action['label'] }}
                        </li>
                        @empty
                        <li class="text-xs text-slate-400 italic">Connect hardware sensor to activate AI guidance.</li>
                        @endforelse
                    </ul>
                </div>

                @if($latestAnalysis)
                <div class="mt-4 pt-3 border-t border-slate-100 text-[10px] text-slate-400 font-mono flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-xs text-violet-400">smart_toy</span>
                    Last AI analysis: {{ $latestAnalysis->created_at->diffForHumans() }}
                </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- SECTION 3: MULTI-SENSOR CHART (Water + Humidity + Temp)        --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div class="rainova-card p-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Multi-Sensor Trend Chart</h2>
                <p class="text-xs text-slate-400 mt-0.5">Water level, humidity &amp; temperature — last 10 readings</p>
            </div>
            <div class="flex gap-3 text-[10px] font-semibold">
                <span class="flex items-center gap-1.5 text-sky-600"><span class="w-3 h-0.5 bg-sky-500 rounded inline-block"></span>Water</span>
                <span class="flex items-center gap-1.5 text-violet-600"><span class="w-3 h-0.5 bg-violet-500 rounded inline-block"></span>Humidity</span>
                <span class="flex items-center gap-1.5 text-orange-500"><span class="w-3 h-0.5 bg-orange-400 rounded inline-block"></span>Temp</span>
            </div>
        </div>
        {{-- wire:ignore prevents Livewire re-renders from destroying the ApexCharts instance --}}
        <div wire:ignore>
            <div id="sfewsApexChart" class="w-full min-h-72"></div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- SECTION 4: 2-COLUMN CHARTS (Trend + 24H History)               --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Left: Current Trend SVG --}}
        <div class="rainova-card p-6 flex flex-col gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Current Water Trend</h2>
                    <span class="text-xs text-slate-400">Last 10 sensor readings</span>
                </div>
                <span class="px-3 py-1 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 font-mono text-xs font-bold">
                    {{ $hasReading ? number_format($waterLevelCm, 1) . ' cm' : '--' }}
                </span>
            </div>
            <div class="relative h-56 w-full">
                <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 100">
                    <defs>
                        <linearGradient id="chartGrad10" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#0284c7" stop-opacity="0.22"/>
                            <stop offset="100%" stop-color="#0284c7" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    @php
                        $pts10 = collect($recent10Readings);
                        $line10 = $recent10Readings ? "M" . implode(' L', array_map(function($d, $i) use ($pts10) {
                            $x = count($pts10) > 1 ? ($i / (count($pts10) - 1)) * 100 : 50;
                            $y = min(88, max(12, 100 - (($d['water_level'] / 250) * 100)));
                            return "{$x},{$y}";
                        }, $recent10Readings, array_keys($recent10Readings))) : "M0,50 L100,50";
                        $firstY = count($recent10Readings) ? min(88, max(12, 100 - (($recent10Readings[0]['water_level'] / 250) * 100))) : 50;
                        $fill10 = "M0,{$firstY} L" . implode(' L', array_map(function($d, $i) use ($pts10) {
                            $x = count($pts10) > 1 ? ($i / (count($pts10) - 1)) * 100 : 50;
                            $y = min(88, max(12, 100 - (($d['water_level'] / 250) * 100)));
                            return "{$x},{$y}";
                        }, $recent10Readings, array_keys($recent10Readings))) . " L100,100 L0,100 Z";
                    @endphp
                    <path d="{{ $fill10 }}" fill="url(#chartGrad10)"/>
                    <path d="{{ $line10 }}" fill="none" stroke="#0284c7" stroke-width="2.5" vector-effect="non-scaling-stroke"/>
                </svg>
                <div class="absolute bottom-0 w-full flex justify-between font-mono text-[10px] text-slate-400 pt-2 border-t border-slate-100">
                    @foreach($recent10Readings as $r)
                        <span>{{ $r['time'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right: 24-Hour History SVG --}}
        <div class="rainova-card p-6 flex flex-col gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">24-Hour History</h2>
                    <span class="text-xs text-slate-400">Hourly water level trend</span>
                </div>
                <span class="px-3 py-1 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 font-mono text-xs font-bold">
                    {{ $hasReading ? number_format($waterLevelCm, 1) . ' cm' : '--' }}
                </span>
            </div>
            <div class="relative h-56 w-full">
                <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 100">
                    <defs>
                        <linearGradient id="chartGrad24" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#0284c7" stop-opacity="0.22"/>
                            <stop offset="100%" stop-color="#0284c7" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    @php
                        $pts24 = collect($hourly24Readings);
                        $line24 = $hourly24Readings ? "M" . implode(' L', array_map(function($d, $i) use ($pts24) {
                            $x = count($pts24) > 1 ? ($i / (count($pts24) - 1)) * 100 : 50;
                            $y = min(88, max(12, 100 - (($d['water_level'] / 250) * 100)));
                            return "{$x},{$y}";
                        }, $hourly24Readings, array_keys($hourly24Readings))) : "M0,50 L100,50";
                        $firstY24 = count($hourly24Readings) ? min(88, max(12, 100 - (($hourly24Readings[0]['water_level'] / 250) * 100))) : 50;
                        $fill24 = "M0,{$firstY24} L" . implode(' L', array_map(function($d, $i) use ($pts24) {
                            $x = count($pts24) > 1 ? ($i / (count($pts24) - 1)) * 100 : 50;
                            $y = min(88, max(12, 100 - (($d['water_level'] / 250) * 100)));
                            return "{$x},{$y}";
                        }, $hourly24Readings, array_keys($hourly24Readings))) . " L100,100 L0,100 Z";
                    @endphp
                    <path d="{{ $fill24 }}" fill="url(#chartGrad24)"/>
                    <path d="{{ $line24 }}" fill="none" stroke="#0284c7" stroke-width="2.5" vector-effect="non-scaling-stroke"/>
                </svg>
                <div class="absolute bottom-0 w-full flex justify-between font-mono text-[10px] text-slate-400 pt-2 border-t border-slate-100">
                    @foreach(array_slice($hourly24Readings, -6) as $r)
                        <span>{{ $r['time'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- SECTION 5: FULL SENSOR DATA TABLE                              --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div class="rainova-card p-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Sensor Data Log</h2>
                <p class="text-xs text-slate-400 mt-0.5">Historical telemetry from HC-SR04 (Distance), DHT11 (Temp & Humidity)</p>
            </div>
            <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-600 text-xs font-mono font-semibold">
                {{ count($tableReadings) }} Records
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">Location</th>
                        <th class="py-3 px-4 text-right">Distance (cm)</th>
                        <th class="py-3 px-4 text-right">Water Level (cm)</th>
                        <th class="py-3 px-4 text-right">Temp (°C)</th>
                        <th class="py-3 px-4 text-right">Humidity (%RH)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($tableReadings as $idx => $row)
                    @php
                        $rowPill = match($row['status']) {
                            'danger'  => 'bg-rose-100 text-rose-700',
                            'caution' => 'bg-amber-100 text-amber-700',
                            default   => 'bg-emerald-100 text-emerald-700',
                        };
                        $humBadge = ($row['humidity'] ?? 0) > 85 ? 'text-violet-600 font-bold' : 'text-slate-700';
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4 font-bold text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-700">{{ $row['timestamp'] }}</td>
                        <td class="py-3 px-4 font-sans text-slate-600">Bedadung - Baratan</td>
                        <td class="py-3 px-4 text-right text-slate-700">{{ number_format($row['distance'], 1) }}</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">{{ number_format($row['water_level'], 1) }}</td>
                        <td class="py-3 px-4 text-right text-orange-600 font-semibold">{{ number_format($row['temp'], 1) }}</td>
                        <td class="py-3 px-4 text-right {{ $humBadge }}">{{ number_format($row['humidity'], 0) }}%</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $rowPill }}">
                                {{ strtoupper($row['status']) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400 font-sans text-xs">
                            <span class="material-symbols-outlined text-3xl block mb-2 text-slate-300">sensors_off</span>
                            No telemetry records. Connect hardware sensor to begin data collection.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function () {
    let chart = null;
    let chartInitialized = false;

    const SERIES_NAMES = ['Water Level (cm)', 'Humidity (%RH)', 'Temperature (°C)'];

    function extractSeries(chartData) {
        return {
            categories: chartData.map(d => d.time),
            waterLevels: chartData.map(d => parseFloat(d.water_level) || 0),
            humidities:  chartData.map(d => parseFloat(d.humidity)    || 0),
            temps:       chartData.map(d => parseFloat(d.temp)        || 0),
        };
    }

    function buildChartOptions(chartData) {
        const isMobile = window.innerWidth < 768;
        const { categories, waterLevels, humidities, temps } = extractSeries(chartData);

        return {
            series: [
                { name: SERIES_NAMES[0], data: waterLevels },
                { name: SERIES_NAMES[1], data: humidities  },
                { name: SERIES_NAMES[2], data: temps       },
            ],
            chart: {
                height: isMobile ? 260 : 300,
                type: 'line',
                toolbar: { show: false },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 400,
                    dynamicAnimation: { enabled: true, speed: 400 },
                },
                background: 'transparent',
            },
            stroke: {
                curve: 'smooth',
                // First series uses area-style fill but still draws as line
                width: [3, 2.5, 2.5],
                dashArray: [0, 5, 5],
            },
            fill: {
                // Area fill only for the first series (Water Level)
                type: ['gradient', 'solid', 'solid'],
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.25,
                    opacityTo: 0.02,
                    stops: [0, 90, 100],
                },
            },
            colors: ['#0284c7', '#7c3aed', '#f97316'],
            markers: { size: [4, 3, 3], strokeWidth: 2, strokeColors: '#fff' },
            xaxis: {
                categories,
                tickAmount: isMobile ? 5 : 8,
                labels: {
                    rotate: -30,
                    rotateAlways: false,
                    style: { colors: '#64748b', fontSize: '10px', fontFamily: 'JetBrains Mono' },
                },
            },
            yaxis: [
                {
                    seriesName: SERIES_NAMES[0],
                    title: { text: isMobile ? '' : 'Water Level (cm)', style: { color: '#0284c7', fontSize: '11px' } },
                    labels: {
                        style: { colors: '#0284c7', fontSize: '11px', fontFamily: 'JetBrains Mono' },
                        formatter: val => val.toFixed(1),
                    },
                    min: 0, max: 250,
                },
                {
                    seriesName: SERIES_NAMES[1],
                    opposite: true,
                    title: { text: isMobile ? '' : 'Humidity (%RH)', style: { color: '#7c3aed', fontSize: '11px' } },
                    labels: {
                        style: { colors: '#7c3aed', fontSize: '11px', fontFamily: 'JetBrains Mono' },
                        formatter: val => val.toFixed(0) + '%',
                    },
                    min: 0, max: 100,
                },
                {
                    seriesName: SERIES_NAMES[2],
                    opposite: true,
                    show: false,
                    min: 0, max: 60,
                },
            ],
            annotations: {
                yaxis: [
                    { y: 210, borderColor: '#f43f5e', label: { borderColor: '#f43f5e', style: { color: '#fff', background: '#f43f5e', fontSize: '10px' }, text: 'Danger (>210cm)' } },
                    { y: 190, borderColor: '#f59e0b', label: { borderColor: '#f59e0b', style: { color: '#fff', background: '#f59e0b', fontSize: '10px' }, text: 'Standby (>190cm)' } },
                ],
            },
            legend: { show: true, position: 'top', horizontalAlign: 'right', fontSize: '11px', fontFamily: 'Poppins' },
            tooltip: {
                shared: true,
                intersect: false,
                theme: 'light',
                style: { fontSize: '11px', fontFamily: 'Poppins' },
                y: [
                    { formatter: val => val?.toFixed(1) + ' cm'  },
                    { formatter: val => val?.toFixed(0) + '% RH' },
                    { formatter: val => val?.toFixed(1) + ' °C'  },
                ],
            },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        };
    }

    function renderChart(chartData) {
        const el = document.querySelector('#sfewsApexChart');
        if (!el) return;

        if (!chartData || chartData.length === 0) {
            if (!chartInitialized) {
                el.innerHTML = '<div class="flex flex-col items-center justify-center h-64 text-slate-400 text-sm"><span class="material-symbols-outlined text-4xl mb-2">sensors_off</span>Connect sensor hardware to display chart data.</div>';
            }
            return;
        }

        if (chartInitialized && chart) {
            // ✅ Data-only update — series names preserved, no DOM destruction
            const { categories, waterLevels, humidities, temps } = extractSeries(chartData);
            chart.updateOptions({ xaxis: { categories } }, false, false, false);
            chart.updateSeries([
                { name: SERIES_NAMES[0], data: waterLevels },
                { name: SERIES_NAMES[1], data: humidities  },
                { name: SERIES_NAMES[2], data: temps       },
            ], true);
        } else {
            el.innerHTML = '';
            chart = new ApexCharts(el, buildChartOptions(chartData));
            chart.render().then(() => { chartInitialized = true; });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderChart(@json($recent10Readings));

        window.addEventListener('chartDataUpdated', function (e) {
            const newData = e.detail?.chartData ?? (Array.isArray(e.detail) ? e.detail : []);
            renderChart(newData);
        });

        window.addEventListener('resize', () => {
            if (chart && chartInitialized) {
                chart.updateOptions({ chart: { height: window.innerWidth < 768 ? 260 : 300 } });
            }
        });
    });
})();
</script>
@endpush


