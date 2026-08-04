@section('header_title', 'Sensor Analytics')

<div class="flex flex-col gap-6" wire:poll.3s>

    @php
        $status = $latestReading?->status ?? 'safe';
        $distanceCm = (float)($latestReading?->distance_cm ?? 25.0);
        $tempC = (float)($latestReading?->temperature_c ?? 33.3);
        $humidityRH = (float)($latestReading?->humidity_percent ?? 57.0);

        $waterLevelCm = round(200 - $distanceCm, 1);

        $statusTitle = match($status) {
            'danger'  => 'DANGER',
            'caution' => 'STANDBY',
            default   => 'SAFE',
        };

        $statusPillStyle = match($status) {
            'danger'  => 'bg-rose-100 text-rose-700 border-rose-200',
            'caution' => 'bg-amber-100 text-amber-700 border-amber-200',
            default   => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        };

        $probColor = match(true) {
            $floodProbability >= 75 => 'text-rose-600 border-rose-200 bg-rose-50',
            $floodProbability >= 40 => 'text-amber-600 border-amber-200 bg-amber-50',
            default                 => 'text-emerald-600 border-emerald-200 bg-emerald-50',
        };

        $probBarColor = match(true) {
            $floodProbability >= 75 => 'bg-rose-600',
            $floodProbability >= 40 => 'bg-amber-500',
            default                 => 'bg-emerald-600',
        };

        $probLabel = match(true) {
            $floodProbability >= 75 => '🚨 HIGH RISK',
            $floodProbability >= 40 => '⚠️ STANDBY',
            default                 => '✅ SAFE',
        };
    @endphp

    {{-- ── 1. TOP SECTION: PRIMARY MONITORING DEVICE + REALTIME READINGS ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Left: Primary Monitoring Device Banner (7 Cols) --}}
        <div class="rainova-card lg:col-span-7 p-6 sm:p-8 flex flex-col justify-between relative overflow-hidden bg-white">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[11px] font-extrabold text-sky-600 uppercase tracking-widest block mb-2">
                        PRIMARY MONITORING DEVICE
                    </span>
                    <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        Bedadung River - Baratan Station
                    </h1>
                    <p class="text-slate-500 text-xs sm:text-sm font-medium mt-2 leading-relaxed">
                        Water level is rising, monitor sensor data continuously.
                    </p>
                </div>

                <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $statusPillStyle }}">
                    {{ $statusTitle }}
                </span>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 pulse-live"></span>
                    Pos Sensor 01 Sumbersari
                </span>
                <span class="font-mono text-slate-400">ID: BEDADUNG_01</span>
            </div>
        </div>

        {{-- Right: Realtime Readings Card (5 Cols) --}}
        <div class="rainova-card lg:col-span-5 p-6 flex flex-col justify-between">
            <div class="pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900 tracking-tight">Realtime Readings</h2>
                <span class="text-[11px] text-slate-400 font-medium">Latest telemetry metrics</span>
            </div>

            <div class="grid grid-cols-2 gap-4 my-2">
                {{-- Water Level --}}
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[11px] font-semibold text-slate-500 block">Water Level</span>
                    <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1 block font-mono">
                        {{ number_format($waterLevelCm, 1) }} cm
                    </span>
                </div>

                {{-- Rainfall Status --}}
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[11px] font-semibold text-slate-500 block">Rainfall Status</span>
                    <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1 block uppercase">
                        {{ $rainStatus }}
                    </span>
                </div>

                {{-- Temperature --}}
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[11px] font-semibold text-slate-500 block">Temperature</span>
                    <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1 block font-mono">
                        {{ number_format($currentTemp, 1) }} °C
                    </span>
                </div>

                {{-- Humidity --}}
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-[11px] font-semibold text-slate-500 block">Humidity</span>
                    <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1 block font-mono">
                        {{ number_format($currentHumidity, 0) }}% RH
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- ── 2. MIDDLE SECTION: 2 LINE CHARTS SIDE-BY-SIDE ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Left Chart: Current Trend (Last 10 Readings) --}}
        <div class="rainova-card p-6 flex flex-col justify-between gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight">Current Trend</h2>
                    <span class="text-xs text-slate-400 font-medium">Last 10 readings</span>
                </div>
                <span class="px-3 py-1 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 font-mono text-xs font-bold">
                    {{ number_format($waterLevelCm, 1) }} cm
                </span>
            </div>

            {{-- SVG Smooth Line Chart (10 data) --}}
            <div class="relative h-64 w-full">
                <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 100">
                    <defs>
                        <linearGradient id="chartGrad10" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#0284c7" stop-opacity="0.2"/>
                            <stop offset="100%" stop-color="#0284c7" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    @php
                        $pts10 = collect($recent10Readings);
                        $pointsStr10 = $pts10->map(function($d, $i) use ($pts10) {
                            $x = count($pts10) > 1 ? ($i / (count($pts10) - 1)) * 100 : 50;
                            $y = min(88, max(12, 100 - (($d['water_level'] / 250) * 100)));
                            return "{$x},{$y}";
                        })->join(' ');
                        $fill10 = "M0," . min(88, max(12, 100 - ((($recent10Readings[0]['water_level'] ?? 180) / 250) * 100))) .
                                  ($pointsStr10 ? " L" . str_replace(' ', ' L', $pointsStr10) : '') .
                                  " L100,100 L0,100 Z";
                        $line10 = $recent10Readings ? "M" . implode(' L', array_map(function($d, $i) use ($pts10) {
                            $x = count($pts10) > 1 ? ($i / (count($pts10) - 1)) * 100 : 50;
                            $y = min(88, max(12, 100 - (($d['water_level'] / 250) * 100)));
                            return "{$x},{$y}";
                        }, $recent10Readings, array_keys($recent10Readings))) : "M0,50 L100,50";
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

        {{-- Right Chart: 24-Hour History (Hourly water level) --}}
        <div class="rainova-card p-6 flex flex-col justify-between gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight">24-Hour History</h2>
                    <span class="text-xs text-slate-400 font-medium">Hourly water level trend</span>
                </div>
                <span class="px-3 py-1 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 font-mono text-xs font-bold">
                    {{ number_format($waterLevelCm, 1) }} cm
                </span>
            </div>

            {{-- SVG Smooth Line Chart (24 Jam) --}}
            <div class="relative h-64 w-full">
                <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 100">
                    <defs>
                        <linearGradient id="chartGrad24" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#0284c7" stop-opacity="0.2"/>
                            <stop offset="100%" stop-color="#0284c7" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    @php
                        $pts24 = collect($hourly24Readings);
                        $pointsStr24 = $pts24->map(function($d, $i) use ($pts24) {
                            $x = count($pts24) > 1 ? ($i / (count($pts24) - 1)) * 100 : 50;
                            $y = min(88, max(12, 100 - (($d['water_level'] / 250) * 100)));
                            return "{$x},{$y}";
                        })->join(' ');
                        $fill24 = "M0," . min(88, max(12, 100 - ((($hourly24Readings[0]['water_level'] ?? 180) / 250) * 100))) .
                                  ($pointsStr24 ? " L" . str_replace(' ', ' L', $pointsStr24) : '') .
                                  " L100,100 L0,100 Z";
                        $line24 = $hourly24Readings ? "M" . implode(' L', array_map(function($d, $i) use ($pts24) {
                            $x = count($pts24) > 1 ? ($i / (count($pts24) - 1)) * 100 : 50;
                            $y = min(88, max(12, 100 - (($d['water_level'] / 250) * 100)));
                            return "{$x},{$y}";
                        }, $hourly24Readings, array_keys($hourly24Readings))) : "M0,50 L100,50";
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

    {{-- ── 3. SENSOR DATA TABLE ── --}}
    <div class="rainova-card p-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Sensor Data Table</h2>
                <p class="text-xs text-slate-400 mt-0.5">Complete historical telemetry records from HC-SR04 & DHT11</p>
            </div>
            <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-600 text-xs font-mono font-semibold">
                Total: {{ count($tableReadings) }} Records
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">Sensor Location</th>
                        <th class="py-3 px-4 text-right">Distance (cm)</th>
                        <th class="py-3 px-4 text-right">Water Level (cm)</th>
                        <th class="py-3 px-4 text-right">Temperature (°C)</th>
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
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4 font-bold text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-700">{{ $row['timestamp'] }}</td>
                        <td class="py-3 px-4 font-sans text-slate-600">Bedadung - Baratan</td>
                        <td class="py-3 px-4 text-right text-slate-700">{{ number_format($row['distance'], 1) }} cm</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">{{ number_format($row['water_level'], 1) }} cm</td>
                        <td class="py-3 px-4 text-right text-slate-700">{{ number_format($row['temp'], 1) }} °C</td>
                        <td class="py-3 px-4 text-right text-slate-700">{{ number_format($row['humidity'], 0) }}% RH</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $rowPill }}">
                                {{ strtoupper($row['status']) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 font-sans text-xs">
                            No telemetry records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
{{-- ApexCharts CDN --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let chart = null;

        function initOrUpdateChart() {
            const chartData = @json($recent10Readings);
            if (!chartData || chartData.length === 0) return;

            const isMobile = window.innerWidth < 768;

            const categories = chartData.map(d => d.time);
            const distances  = chartData.map(d => d.distance);
            const humidities = chartData.map(d => d.humidity);
            const temps      = chartData.map(d => d.temp);

            const options = {
                series: [
                    { name: 'Water Distance (cm)', type: 'area', data: distances },
                    { name: 'Humidity (%)', type: 'line', data: humidities },
                    { name: 'Temperature (°C)', type: 'line', data: temps }
                ],
                chart: {
                    height: isMobile ? 280 : 320,
                    type: 'line',
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 400 }
                },
                stroke: {
                    curve: 'smooth',
                    width: isMobile ? [2.5, 1.5, 1.5] : [3, 2, 2],
                    dashArray: [0, 3, 3]
                },
                fill: {
                    type: ['gradient', 'solid', 'solid'],
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.3,
                        opacityTo: 0.02,
                        stops: [0, 90, 100]
                    }
                },
                colors: ['#0284c7', '#4f46e5', '#f59e0b'],
                markers: { size: isMobile ? [3, 0, 0] : [4, 0, 0] },
                xaxis: {
                    categories: categories,
                    tickAmount: isMobile ? 5 : undefined,
                    labels: {
                        rotate: 0,
                        style: { colors: '#64748b', fontSize: isMobile ? '10px' : '11px', fontFamily: 'JetBrains Mono' }
                    }
                },
                yaxis: [
                    {
                        show: true,
                        title: { text: isMobile ? '' : 'Distance (cm)', style: { color: '#0284c7', fontSize: '11px' } },
                        labels: { style: { colors: '#0284c7', fontSize: isMobile ? '10px' : '11px', fontFamily: 'JetBrains Mono' } },
                        min: 0,
                        max: 35
                    },
                    {
                        opposite: true,
                        show: true,
                        title: { text: isMobile ? '' : 'Humidity (%) / Temp (°C)', style: { color: '#4f46e5', fontSize: '11px' } },
                        labels: { style: { colors: '#4f46e5', fontSize: isMobile ? '10px' : '11px', fontFamily: 'JetBrains Mono' } },
                        min: 20,
                        max: 100
                    }
                ],
                annotations: {
                    yaxis: [
                        {
                            y: 10,
                            borderColor: '#f43f5e',
                            label: {
                                borderColor: '#f43f5e',
                                style: { color: '#fff', background: '#f43f5e', fontSize: isMobile ? '8px' : '10px' },
                                text: isMobile ? 'Danger (<10cm)' : 'Danger Threshold (<10cm)'
                            }
                        },
                        {
                            y: 20,
                            borderColor: '#f59e0b',
                            label: {
                                borderColor: '#f59e0b',
                                style: { color: '#fff', background: '#f59e0b', fontSize: isMobile ? '8px' : '10px' },
                                text: isMobile ? 'Standby (10-20cm)' : 'Standby Threshold (10-20cm)'
                            }
                        }
                    ]
                },
                legend: { show: false },
                tooltip: {
                    shared: true,
                    intersect: false,
                    theme: 'light',
                    style: { fontSize: '11px', fontFamily: 'Poppins' }
                },
                grid: { borderColor: '#f1f5f9' }
            };

            const chartEl = document.querySelector("#sfewsApexChart");
            if (chartEl) {
                if (chart) {
                    chart.updateOptions(options);
                } else {
                    chart = new ApexCharts(chartEl, options);
                    chart.render();
                }
            }
        }

        initOrUpdateChart();

        window.addEventListener('resize', () => initOrUpdateChart());
        document.addEventListener('livewire:navigated', () => initOrUpdateChart());
        if (window.Livewire) {
            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                succeed(() => {
                    setTimeout(() => initOrUpdateChart(), 100);
                });
            });
        }
    });
</script>
@endpush
