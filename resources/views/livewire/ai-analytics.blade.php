<div class="flex flex-col gap-6 py-4" wire:poll.2s>
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Panduan AI & Keselamatan Warga</h1>
        <p class="text-slate-500 text-sm font-normal mt-1">Sistem analisis risiko pintar dan langkah-langkah siaga bencana banjir Sungai Bedadung</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- ── Telemetry SVG Chart Card (8 Cols) ── --}}
        <section class="lg:col-span-8 clean-card p-6 flex flex-col gap-6">
            <div class="flex justify-between items-start pb-2 border-b border-slate-100">
                <div>
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Grafik Riwayat Air Sungai (60 Menit)</h2>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="font-mono text-3xl font-bold tracking-tight
                            {{ $currentStatus === 'danger' ? 'text-rose-600' : ($currentStatus === 'caution' ? 'text-amber-600' : 'text-emerald-600') }}">
                            {{ number_format($currentDistance, 1) }}
                        </span>
                        <span class="font-mono text-sm text-slate-400">cm dari sensor</span>
                    </div>
                </div>

                {{-- Status badge --}}
                <div class="flex items-center gap-2 px-3 py-1 rounded-full border text-xs font-semibold uppercase tracking-wider
                    {{ $currentStatus === 'danger' ? 'bg-rose-50 border-rose-200 text-rose-700' : ($currentStatus === 'caution' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700') }}">
                    <span class="w-2 h-2 rounded-full pulse-live {{ $currentStatus === 'danger' ? 'bg-rose-500' : ($currentStatus === 'caution' ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                    <span>STATUS: {{ strtoupper($currentStatus) }}</span>
                </div>
            </div>

            {{-- SVG Chart Container --}}
            <div class="relative h-64 mt-2">
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none z-0">
                    <div class="w-full border-t border-emerald-300/40 border-dashed relative" style="height:33%">
                        <span class="absolute -top-3 right-0 font-mono text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Kondisi Aman (&gt;20cm)</span>
                    </div>
                    <div class="w-full border-t border-amber-300/40 border-dashed relative" style="height:33%">
                        <span class="absolute -top-3 right-0 font-mono text-[11px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Batas Waspada (10-20cm)</span>
                    </div>
                    <div class="w-full border-t border-rose-300/40 border-dashed relative" style="height:33%">
                        <span class="absolute -top-3 right-0 font-mono text-[11px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">Batas Bahaya (&lt;10cm)</span>
                    </div>
                </div>

                <svg class="absolute inset-0 w-full h-full z-10" preserveAspectRatio="none" viewBox="0 0 100 100">
                    <defs>
                        <linearGradient id="chartGradPublic" x1="0" x2="0" y1="0" y2="1">
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
                    <path d="{{ $fillPath }}" fill="url(#chartGradPublic)"/>
                    <path class="chart-line" d="{{ $linePath }}" fill="none"
                          stroke="{{ $currentStatus === 'danger' ? '#f43f5e' : ($currentStatus === 'caution' ? '#d97706' : '#0284c7') }}"
                          stroke-width="2.5" vector-effect="non-scaling-stroke"/>
                    <circle cx="{{ $lastX }}" cy="{{ $lastY }}" r="2" fill="{{ $currentStatus === 'danger' ? '#f43f5e' : '#0284c7' }}"/>
                </svg>

                <div class="absolute -bottom-6 w-full flex justify-between font-mono text-[11px] text-slate-400">
                    <span>-60 Menit</span><span>-45 Menit</span><span>-30 Menit</span><span>-15 Menit</span><span>Saat Ini</span>
                </div>
            </div>
        </section>

        {{-- ── Panduan AI Keselamatan Warga (4 Cols) ── --}}
        <section class="lg:col-span-4 clean-card p-6 flex flex-col justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-3 pb-3 border-b border-slate-100">
                    <span class="material-symbols-outlined text-slate-700 text-lg">verified_user</span>
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Langkah Keselamatan Warga</h2>
                </div>
                <p class="text-xs font-normal text-slate-500">Saran yang direkomendasikan sistem AI berdasarkan kondisi air sungai saat ini.</p>

                <div class="mt-4 space-y-2.5">
                    @forelse($automatedActions as $i => $action)
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/70 text-xs">
                        <span class="material-symbols-outlined text-emerald-600 text-[18px] mt-0.5">check_circle</span>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-800 leading-snug">{{ $action['label'] }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Dianjurkan untuk keamanan keluarga</p>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center">
                        <span class="material-symbols-outlined text-2xl text-slate-300 mb-1">checklist</span>
                        <p class="text-xs font-medium text-slate-400">Tidak ada tindakan darurat yang aktif.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <a href="tel:112" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold text-center transition-all flex items-center justify-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-[16px]">call</span>
                Hubungi Call Center BPBD 112
            </a>
        </section>
    </div>
</div>
