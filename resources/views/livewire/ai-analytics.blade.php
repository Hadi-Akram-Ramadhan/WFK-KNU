<div class="flex flex-col gap-6 py-4" wire:poll.3s>
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-sky-100 text-sky-700 uppercase tracking-wider">
                    AI Multi-Sensor Analytics
                </span>
                <span class="text-xs font-mono text-slate-400">• Pos Sumbersari</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Analisis AI & Prediksi Banjir</h1>
            <p class="text-slate-500 text-xs sm:text-sm font-normal mt-1">
                Prediksi risiko banjir berbasis AI yang mengombinasikan sensor jarak air (HC-SR04) dan indikator cuaca DHT11 (Suhu & Kelembapan).
            </p>
        </div>

        {{-- Live Refresh Pill --}}
        <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200/80 shadow-xs text-xs font-medium text-slate-600">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 pulse-live"></span>
            <span>Update Otomatis Real-time</span>
        </div>
    </div>

    @php
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
            $floodProbability >= 75 => '🚨 POTENSI TINGGI',
            $floodProbability >= 40 => '⚠️ SIAGA WASPADA',
            default                 => '✅ AMAN TERKENDALI',
        };
    @endphp

    {{-- ── 3 CARDS STAT PREDIKSI AI MULTI-SENSOR ── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- Card 1: Probabilitas Banjir AI (%) --}}
        <div class="clean-card p-5 flex flex-col justify-between relative overflow-hidden">
            <div class="flex justify-between items-start mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                        <span class="material-symbols-outlined text-lg ms-fill">analytics</span>
                    </div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Probabilitas Banjir AI</span>
                </div>
                <span class="px-2.5 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wide {{ $probColor }}">
                    {{ $probLabel }}
                </span>
            </div>

            <div class="my-2">
                <div class="flex items-baseline gap-2">
                    <span class="font-mono text-4xl font-bold tracking-tight text-slate-900">{{ $floodProbability }}%</span>
                    <span class="text-xs text-slate-500 font-medium">peluang meluap</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Dihitung otomatis oleh AI Ollama dari tren data multi-sensor.</p>
            </div>

            <div class="mt-2 space-y-1">
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200/60">
                    <div class="h-full {{ $probBarColor }} rounded-full transition-all duration-700" style="width: {{ $floodProbability }}%"></div>
                </div>
            </div>
        </div>

        {{-- Card 2: Sensor DHT11 Suhu & Kelembapan Udara --}}
        <div class="clean-card p-5 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                        <span class="material-symbols-outlined text-lg ms-fill">device_thermostat</span>
                    </div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Indikator Cuaca (DHT11)</span>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold uppercase bg-slate-100 text-slate-600">
                    Sensor Aktif
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3 my-1">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[11px] text-slate-400 block font-medium">Kelembapan Udara</span>
                    <span class="font-mono text-xl font-bold text-indigo-600">{{ number_format($currentHumidity, 1) }}%</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-[11px] text-slate-400 block font-medium">Suhu Lingkungan</span>
                    <span class="font-mono text-xl font-bold text-amber-600">{{ number_format($currentTemp, 1) }}°C</span>
                </div>
            </div>

            <p class="text-[11px] text-slate-500 truncate font-medium">
                🌦️ {{ $weatherCondition }}
            </p>
        </div>

        {{-- Card 3: Status Terkini & Tren Ketinggian Air --}}
        <div class="clean-card p-5 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                        <span class="material-symbols-outlined text-lg ms-fill">water_lux</span>
                    </div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Jarak Air dari Sensor</span>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold uppercase bg-slate-100 text-slate-600">
                    HC-SR04
                </span>
            </div>

            <div class="my-1">
                <div class="flex items-baseline gap-2">
                    <span class="font-mono text-4xl font-bold text-slate-900">{{ number_format($currentDistance, 1) }}</span>
                    <span class="font-mono text-sm text-slate-500 font-semibold">cm</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    @if($currentDistance < 10)
                        🚨 Air berada di zona kritis (&lt;10cm). Potensi banjir sangat tinggi!
                    @elseif($currentDistance <= 20)
                        ⚠️ Air mendekati batas waspada (10-20cm). Pertahankan kesiapsiagaan.
                    @else
                        ✅ Ketinggian air normal (&gt;20cm). Aliran sungai lancar.
                    @endif
                </p>
            </div>

            <div class="pt-2 border-t border-slate-100 flex justify-between items-center text-xs">
                <span class="text-slate-400">Pembaruan Terakhir:</span>
                <span class="font-mono font-semibold text-slate-700">{{ now()->format('H:i:s') }} WIB</span>
            </div>
        </div>

    </div>

    {{-- ── INTERACTIVE MULTI-SENSOR APEXCHARTS DASHBOARD ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Main Chart Container (8 Cols) --}}
        <section class="lg:col-span-8 clean-card p-6 flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-2">
                <div>
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-sky-600 text-base">show_chart</span>
                        Grafik Telemetry Multi-Sensor (60 Menit Terakhir)
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Gabungan data Ketinggian Air (cm), Suhu (°C), dan Kelembapan Udara (%)</p>
                </div>

                {{-- Chart Legend Badges --}}
                <div class="flex items-center gap-3 text-[11px] font-medium">
                    <span class="flex items-center gap-1.5 text-sky-700">
                        <span class="w-3 h-1 rounded-full bg-sky-600"></span> Jarak Air (cm)
                    </span>
                    <span class="flex items-center gap-1.5 text-indigo-700">
                        <span class="w-3 h-1 rounded-full bg-indigo-600"></span> Kelembapan (%)
                    </span>
                    <span class="flex items-center gap-1.5 text-amber-700">
                        <span class="w-3 h-1 rounded-full bg-amber-500"></span> Suhu (°C)
                    </span>
                </div>
            </div>

            {{-- ApexCharts Script & Div Container --}}
            <div class="relative w-full h-80" id="multiSensorChartContainer" wire:ignore>
                <div id="sfewsApexChart" class="w-full h-full"></div>
            </div>
        </section>

        {{-- ── Panduan AI Keselamatan Warga (4 Cols) ── --}}
        <section class="lg:col-span-4 clean-card p-6 flex flex-col justify-between gap-4">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-700 text-lg">verified_user</span>
                        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Langkah Keselamatan Warga</h2>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold uppercase bg-slate-100 text-slate-600">AI Verified</span>
                </div>
                <p class="text-xs font-normal text-slate-500 mt-2 leading-relaxed">
                    Saran keselamatan publik yang direkomendasikan AI Ollama berdasarkan kondisi air & cuaca saat ini:
                </p>

                <div class="mt-4 space-y-2.5">
                    @foreach($automatedActions as $i => $action)
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/70 text-xs">
                        <span class="material-symbols-outlined text-emerald-600 text-[18px] mt-0.5">check_circle</span>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-800 leading-snug">{{ $action['label'] }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Dianjurkan untuk keselamatan keluarga</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100">
                <a href="tel:112" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold text-center transition-all flex items-center justify-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-base ms-fill">call</span>
                    Hubungi Call Center BPBD 112
                </a>
            </div>
        </section>
    </div>
</div>

@push('scripts')
{{-- ApexCharts CDN --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let chart = null;

        function initOrUpdateChart() {
            const chartData = @json($chartData);
            if (!chartData || chartData.length === 0) return;

            const categories = chartData.map(d => d.time);
            const distances  = chartData.map(d => d.distance);
            const humidities = chartData.map(d => d.humidity);
            const temps      = chartData.map(d => d.temperature);

            const options = {
                series: [
                    { name: 'Jarak Air (cm)', type: 'area', data: distances },
                    { name: 'Kelembapan (%)', type: 'line', data: humidities },
                    { name: 'Suhu (°C)', type: 'line', data: temps }
                ],
                chart: {
                    height: 320,
                    type: 'line',
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 500 }
                },
                stroke: {
                    curve: 'smooth',
                    width: [3, 2, 2],
                    dashArray: [0, 4, 4]
                },
                fill: {
                    type: ['gradient', 'solid', 'solid'],
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                colors: ['#0284c7', '#4f46e5', '#f59e0b'],
                markers: { size: [4, 0, 0] },
                xaxis: {
                    categories: categories,
                    labels: { style: { colors: '#64748b', fontSize: '11px', fontFamily: 'JetBrains Mono' } }
                },
                yaxis: [
                    {
                        title: { text: 'Jarak Air (cm)', style: { color: '#0284c7', fontSize: '11px' } },
                        labels: { style: { colors: '#0284c7', fontFamily: 'JetBrains Mono' } },
                        min: 0,
                        max: 35
                    },
                    {
                        opposite: true,
                        title: { text: 'Kelembapan (%) / Suhu (°C)', style: { color: '#4f46e5', fontSize: '11px' } },
                        labels: { style: { colors: '#4f46e5', fontFamily: 'JetBrains Mono' } },
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
                                style: { color: '#fff', background: '#f43f5e', fontSize: '10px' },
                                text: 'Batas Bahaya (<10cm)'
                            }
                        },
                        {
                            y: 20,
                            borderColor: '#f59e0b',
                            label: {
                                borderColor: '#f59e0b',
                                style: { color: '#fff', background: '#f59e0b', fontSize: '10px' },
                                text: 'Batas Waspada (10-20cm)'
                            }
                        }
                    ]
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    theme: 'light',
                    style: { fontSize: '12px', fontFamily: 'Poppins' }
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

        // Re-init chart on Livewire updates
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
