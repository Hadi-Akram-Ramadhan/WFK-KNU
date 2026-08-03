<div class="flex flex-col gap-6 py-4">

    @php
        $status = $latestReading?->status ?? 'safe';
        $distanceCm = (float)($latestReading?->distance_cm ?? 25);
        $capacity = (float)($latestReading?->capacity_percent ?? 0);
        $riseRate = (float)($latestReading?->rise_rate_cm_per_min ?? 0);

        $badgeBg = match($status) {
            'danger'  => 'bg-rose-50 text-rose-700 border-rose-200',
            'caution' => 'bg-amber-50 text-amber-700 border-amber-200',
            default   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        };

        $badgeDot = match($status) {
            'danger'  => 'bg-rose-500',
            'caution' => 'bg-amber-500',
            default   => 'bg-emerald-500',
        };

        $statusTitle = match($status) {
            'danger'  => 'Status Kritis — Potensi Luapan Air',
            'caution' => 'Status Waspada — Air Kian Meningkat',
            default   => 'Sungai Bedadung Kondisi Normal',
        };

        $statusSubtitle = match($status) {
            'danger'  => 'Tinggi air melampaui batas aman. Pintu air otomatis dipicu.',
            'caution' => 'Perubahan ketinggian air mendekati batas succeeded.',
            default   => 'Sensor mendeteksi jarak air dalam rentang aman standar BPBD.',
        };

        $gaugeColor = match($status) {
            'danger'  => '#f43f5e',
            'caution' => '#f59e0b',
            default   => '#10b981',
        };

        $gaugeTrack = match($status) {
            'danger'  => '#ffe4e6',
            'caution' => '#fef3c7',
            default   => '#d1fae5',
        };

        $circumference = 251.2;
        $dashOffset = $circumference * (1 - ($capacity / 100));
    @endphp

    {{-- ── Primary Hero Status Banner ── --}}
    <section class="clean-card p-6 md:p-8 flex flex-col md:flex-row gap-6 items-center justify-between {{ $status === 'danger' ? 'danger-card-clean' : '' }}">

        <div class="flex flex-col gap-3 max-w-xl text-center md:text-left">
            <div class="flex items-center justify-center md:justify-start gap-2.5">
                <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider border flex items-center gap-2 {{ $badgeBg }}">
                    <span class="w-2 h-2 rounded-full {{ $badgeDot }} pulse-live"></span>
                    STATUS: {{ strtoupper($status) }}
                </span>
                <span class="text-xs font-mono text-slate-400">Node: {{ $node?->node_id ?? 'BEDADUNG_01' }}</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-snug">
                {{ $statusTitle }}
            </h1>
            <p class="text-slate-600 text-sm font-normal leading-relaxed">
                {{ $statusSubtitle }}
            </p>

            <div class="pt-2 flex items-center justify-center md:justify-start gap-4 text-xs font-medium text-slate-500">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-slate-400">location_on</span>
                    {{ $node?->name ?? 'Checkpoint Alpha — Sumbersari' }}
                </span>
            </div>
        </div>

        {{-- Circular Meter Badge --}}
        <div class="bg-white/90 p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col items-center min-w-[210px]">
            <div class="relative w-36 h-36 flex items-center justify-center">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" fill="transparent" stroke="{{ $gaugeTrack }}" stroke-width="10" stroke-linecap="round"/>
                    <circle cx="50" cy="50" r="40" fill="transparent" stroke="{{ $gaugeColor }}"
                            stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $dashOffset }}"
                            stroke-linecap="round" stroke-width="10" class="transition-all duration-700 ease-out"/>
                </svg>
                <div class="absolute flex flex-col items-center justify-center">
                    <span class="font-mono text-3xl font-bold text-slate-900 tracking-tight">{{ number_format($capacity, 0) }}%</span>
                    <span class="text-[10px] font-semibold text-slate-400 tracking-widest uppercase mt-0.5">KAPASITAS</span>
                </div>
            </div>

            <div class="mt-3 text-center">
                <div class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-3 py-1 rounded-lg border border-slate-200">
                    {{ number_format($distanceCm, 1) }} cm dari sensor
                </div>
                @if($riseRate > 0)
                <div class="mt-2 text-xs font-mono font-medium text-rose-600 flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">trending_up</span>
                    +{{ number_format($riseRate, 1) }} cm/menit
                </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ── Bento Grid: AI Insight & Telemetry ── --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- AI Risk Summary (2 Cols) --}}
        <div class="clean-card p-6 md:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                            <span class="material-symbols-outlined text-lg ms-fill">psychology</span>
                        </div>
                        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Analisis Prediktif AI</h2>
                    </div>
                    @if($latestAnalysis)
                    <span class="text-[11px] font-mono text-slate-400">Model: {{ $latestAnalysis->model_used }}</span>
                    @endif
                </div>

                @if($latestAnalysis)
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-slate-500">Tingkat Risiko:</span>
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold uppercase tracking-wide
                            {{ $latestAnalysis->risk_level === 'critical' || $latestAnalysis->risk_level === 'high' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $latestAnalysis->risk_level }}
                        </span>
                    </div>

                    <p class="text-sm font-normal text-slate-700 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-200/60">
                        "{{ $latestAnalysis->ai_response }}"
                    </p>

                    @if($latestAnalysis->recommended_actions)
                    <div class="mt-3 pt-2">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-2">Rekomendasi Tindakan:</span>
                        <div class="space-y-1.5">
                            @foreach(array_slice($latestAnalysis->recommended_actions, 0, 3) as $act)
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-700">
                                <span class="material-symbols-outlined text-[15px] text-sky-600">check_circle</span>
                                <span>{{ $act }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <div class="py-8 text-center">
                    <span class="material-symbols-outlined text-3xl text-slate-300 mb-1">auto_awesome</span>
                    <p class="text-xs font-medium text-slate-500">Sistem AI siap. Analisis otomatis dipicu saat status berubah DANGER.</p>
                </div>
                @endif
            </div>

            <div class="mt-5 pt-3 border-t border-slate-100 flex justify-between items-center">
                <span class="text-xs text-slate-400 font-mono">Respon waktu: {{ $latestAnalysis?->response_time_ms ?? '0' }}ms</span>
                <a href="{{ route('analytics') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1">
                    Detail Proyeksi <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </a>
            </div>
        </div>

        {{-- Telemetry Quick Stats --}}
        <div class="clean-card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-lg">sensors</span>
                    </div>
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Telemetri Lingkungan</h2>
                </div>

                <div class="space-y-3.5">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-medium text-slate-600">Curah Hujan (1 Jam)</span>
                        <span class="font-mono text-sm font-semibold text-slate-900">— mm</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-medium text-slate-600">Kelembaban Tanah</span>
                        <span class="font-mono text-sm font-semibold text-slate-900">— %</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-medium text-slate-600">Debit Aliran Air</span>
                        <span class="font-mono text-sm font-semibold text-slate-900">— m³/s</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('analytics') }}" class="mt-4 text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1 py-2 bg-slate-100 rounded-xl transition-all">
                <span class="material-symbols-outlined text-[15px]">monitoring</span> Lihat Telemetri Lengkap
            </a>
        </div>
    </section>

</div>
