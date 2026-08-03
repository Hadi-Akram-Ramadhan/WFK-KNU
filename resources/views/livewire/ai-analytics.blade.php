<div class="flex flex-col gap-6 py-4">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">AI Analytics & Telemetri</h1>
        <p class="text-slate-500 text-sm font-normal mt-1">Pemantauan ketinggian air secara real-time dan histori respons otomatis AI</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- ── Telemetry SVG Chart Card (8 Cols) ── --}}
        <section class="lg:col-span-8 clean-card p-6 flex flex-col gap-6">
            <div class="flex justify-between items-start pb-2 border-b border-slate-100">
                <div>
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Telemetri Jarak Sensor</h2>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="font-mono text-3xl font-bold tracking-tight
                            {{ $currentStatus === 'danger' ? 'text-rose-600' : ($currentStatus === 'caution' ? 'text-amber-600' : 'text-emerald-600') }}">
                            {{ number_format($currentDistance, 1) }}
                        </span>
                        <span class="font-mono text-sm text-slate-400">cm</span>
                    </div>
                </div>

                {{-- Status badge --}}
                <div class="flex items-center gap-2 px-3 py-1 rounded-full border text-xs font-semibold uppercase tracking-wider
                    {{ $currentStatus === 'danger' ? 'bg-rose-50 border-rose-200 text-rose-700' : ($currentStatus === 'caution' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700') }}">
                    <span class="w-2 h-2 rounded-full pulse-live {{ $currentStatus === 'danger' ? 'bg-rose-500' : ($currentStatus === 'caution' ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                    <span>{{ strtoupper($currentStatus) }}</span>
                </div>
            </div>

            {{-- SVG Chart Container --}}
            <div class="relative h-64 mt-2">
                {{-- Threshold dashed lines --}}
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none z-0">
                    <div class="w-full border-t border-emerald-300/40 border-dashed relative" style="height:33%">
                        <span class="absolute -top-3 right-0 font-mono text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">20cm Safe</span>
                    </div>
                    <div class="w-full border-t border-amber-300/40 border-dashed relative" style="height:33%">
                        <span class="absolute -top-3 right-0 font-mono text-[11px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">10cm Caution</span>
                    </div>
                    <div class="w-full border-t border-rose-300/40 border-dashed relative" style="height:33%">
                        <span class="absolute -top-3 right-0 font-mono text-[11px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">0cm Danger</span>
                    </div>
                </div>

                {{-- SVG Render --}}
                <svg class="absolute inset-0 w-full h-full z-10" preserveAspectRatio="none" viewBox="0 0 100 100">
                    <defs>
                        <linearGradient id="chartGradClean" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="{{ $currentStatus === 'danger' ? '#f43f5e' : '#0284c7' }}" stop-opacity="0.2"/>
                            <stop offset="100%" stop-color="#0284c7" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>
                    @php
                        $points = collect($chartData)->map(function($d, $i) use ($chartData) {
                            $x = count($chartData) > 1 ? ($i / (count($chartData) - 1)) * 100 : 50;
                            $y = min(96, max(4, ($d['distance'] / 30) * 100));
                            return "{$x},{$y}";
                        })->join(' ');
                        $fillPath = "M" . ($chartData ? "0," . min(96, ($chartData[0]['distance'] / 30) * 100) : "0,50") .
                                    ($points ? " L" . str_replace(' ', ' L', $points) : '') .
                                    " L100,100 L0,100 Z";
                        $linePath = $chartData ? "M" . implode(' L', array_map(function($d, $i) use ($chartData) {
                            $x = count($chartData) > 1 ? ($i / (count($chartData) - 1)) * 100 : 50;
                            $y = min(96, max(4, ($d['distance'] / 30) * 100));
                            return "{$x},{$y}";
                        }, $chartData, array_keys($chartData))) : "M0,50 L100,50";
                        $lastPoint = $chartData ? end($chartData) : null;
                        $lastX = count($chartData) > 1 ? 100 : 50;
                        $lastY = $lastPoint ? min(96, max(4, ($lastPoint['distance'] / 30) * 100)) : 50;
                    @endphp
                    <path d="{{ $fillPath }}" fill="url(#chartGradClean)"/>
                    <path class="chart-line" d="{{ $linePath }}" fill="none"
                          stroke="{{ $currentStatus === 'danger' ? '#f43f5e' : ($currentStatus === 'caution' ? '#d97706' : '#0284c7') }}"
                          stroke-width="2.5" vector-effect="non-scaling-stroke"/>
                    <circle cx="{{ $lastX }}" cy="{{ $lastY }}" r="2" fill="{{ $currentStatus === 'danger' ? '#f43f5e' : '#0284c7' }}"/>
                </svg>

                <div class="absolute -bottom-6 w-full flex justify-between font-mono text-[11px] text-slate-400">
                    <span>-60m</span><span>-45m</span><span>-30m</span><span>-15m</span><span>Sekarang</span>
                </div>
            </div>
        </section>

        {{-- ── AI Automated Responses (4 Cols) ── --}}
        <section class="lg:col-span-4 clean-card p-6 flex flex-col justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-3 pb-3 border-b border-slate-100">
                    <span class="material-symbols-outlined text-slate-700 text-lg">smart_toy</span>
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tindakan Otomatis AI</h2>
                </div>
                <p class="text-xs font-normal text-slate-500">Protokol darurat dipicu secara real-time berdasarkan hasil analisis.</p>

                <div class="mt-4 space-y-2.5">
                    @forelse($automatedActions as $i => $action)
                    <div class="flex items-start gap-3 p-3 rounded-xl border transition-all
                        {{ $action['done'] ? 'bg-slate-50 border-slate-200' : 'bg-white border-slate-100 opacity-60' }}">
                        <div class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[14px]">
                                {{ $i === 0 ? 'campaign' : ($i === 1 ? 'door_sliding' : 'send') }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-800 leading-snug">{{ $action['label'] }}</p>
                            <p class="font-mono text-[10px] text-slate-400 mt-0.5">
                                {{ $action['done'] ? 'Dikonfirmasi • ' . now()->format('H:i') : 'Menunggu validasi' }}
                            </p>
                        </div>
                        <span class="material-symbols-outlined text-sm {{ $action['done'] ? 'text-emerald-600' : 'text-slate-300' }}">
                            {{ $action['done'] ? 'check_circle' : 'pending' }}
                        </span>
                    </div>
                    @empty
                    <div class="py-6 text-center">
                        <span class="material-symbols-outlined text-2xl text-slate-300 mb-1">checklist</span>
                        <p class="text-xs font-medium text-slate-400">Tidak ada aksi aktif.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <a href="{{ route('control') }}" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold text-center transition-all border border-slate-200/80">
                Buka Hardware Logs
            </a>
        </section>
    </div>

    {{-- AI Analysis Log Section --}}
    @if($latestAnalysis)
    <section class="clean-card p-6">
        <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Log Analisis Terakhir</h3>
            <span class="font-mono text-xs text-slate-400">{{ $latestAnalysis->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s') }} WIB</span>
        </div>
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/60">
            <p class="text-xs font-normal text-slate-700 leading-relaxed">{{ $latestAnalysis->ai_response }}</p>
        </div>
    </section>
    @endif
</div>
