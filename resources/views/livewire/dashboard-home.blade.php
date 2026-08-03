<div class="flex flex-col gap-6 py-4" wire:poll.2s>

    @php
        $status = $latestReading?->status ?? 'safe';
        $distanceCm = (float)($latestReading?->distance_cm ?? 25);
        $capacity = (float)($latestReading?->capacity_percent ?? 0);
        $riseRate = (float)($latestReading?->rise_rate_cm_per_min ?? 0);
        $waterLevelM = (float)($latestReading?->water_level_m ?? 0.05);

        $badgeStyle = match($status) {
            'danger'  => 'bg-rose-600 text-white shadow-rose-200',
            'caution' => 'bg-amber-500 text-white shadow-amber-200',
            default   => 'bg-emerald-600 text-white shadow-emerald-200',
        };

        $statusTitle = match($status) {
            'danger'  => '🚨 BAHAYA BANJIR — SEGERA EVAKUASI',
            'caution' => '⚠️ STATUS WASPADA — DEBIT AIR NAIK',
            default   => '✅ KONDISI AMAN — SUNGAI NORMAL',
        };

        $statusDesc = match($status) {
            'danger'  => 'Ketinggian air Sungai Bedadung telah melewati batas bahaya. Warga di daerah bantaran sungai disarankan segera mengamankan diri dan keluarga ke tempat tinggi.',
            'caution' => 'Air sungai mulai naik mendekati batas siaga. Warga diminta tetap waspada, mengawasi anak-anak, dan tidak beraktivitas di dekat tepi sungai.',
            default   => 'Ketinggian air Sungai Bedadung berada dalam rentang aman. Aktivitas warga di sekitar sungai dapat berlangsung secara normal.',
        };

        $progressColor = match($status) {
            'danger'  => 'bg-rose-600',
            'caution' => 'bg-amber-500',
            default   => 'bg-emerald-600',
        };
    @endphp

    {{-- ── PUBLIC BENTO GRID LAYOUT ── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-5">

        {{-- ── Bento Item 1: Status Utama untuk Warga (Spans 2 cols on md/lg) ── --}}
        <div class="clean-card p-6 md:col-span-2 lg:col-span-2 flex flex-col justify-between relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wide shadow-sm flex items-center gap-2 {{ $badgeStyle }}">
                    <span class="w-2 h-2 rounded-full bg-white pulse-live"></span>
                    {{ $statusTitle }}
                </span>
                <span class="text-xs font-mono font-medium text-slate-400">Pos Sumbersari</span>
            </div>

            <div class="my-3">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight leading-snug">
                    Informasi Siaga Banjir Sungai Bedadung
                </h1>
                <p class="text-slate-600 text-sm font-normal mt-2 leading-relaxed">
                    {{ $statusDesc }}
                </p>
            </div>

            {{-- Meter Kapasitas Air Sungai --}}
            <div class="mt-4 pt-4 border-t border-slate-100 space-y-2">
                <div class="flex justify-between items-center text-xs font-medium">
                    <span class="text-slate-600 font-semibold">Tingkat Ketinggian Air Terisi</span>
                    <span class="font-mono font-bold text-slate-900 text-sm">{{ number_format($capacity, 0) }}%</span>
                </div>
                <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200/60">
                    <div class="h-full {{ $progressColor }} rounded-full transition-all duration-700" style="width: {{ min(100, max(6, $capacity)) }}%"></div>
                </div>
                <div class="flex justify-between text-[11px] font-mono text-slate-400 pt-1">
                    <span>Aman (&gt;20cm)</span>
                    <span>Waspada (10-20cm)</span>
                    <span>Bahaya (&lt;10cm)</span>
                </div>
            </div>
        </div>

        {{-- ── Bento Item 2: Himbauan & Panduan AI untuk Warga (Spans 2 cols on lg) ── --}}
        <div class="clean-card p-6 lg:col-span-2 flex flex-col justify-between">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                        <span class="material-symbols-outlined text-lg ms-fill">health_and_safety</span>
                    </div>
                    <h2 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Petunjuk Keselamatan AI Warga</h2>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold uppercase bg-slate-100 text-slate-600">
                    Update Real-time
                </span>
            </div>

            @if($latestAnalysis)
            <div class="my-2 space-y-3">
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70">
                    <p class="text-xs font-normal text-slate-700 leading-relaxed">
                        "{{ $latestAnalysis->ai_response }}"
                    </p>
                </div>

                @if($latestAnalysis->recommended_actions)
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Langkah Penting Warga:</span>
                    <div class="space-y-1.5">
                        @foreach(array_slice($latestAnalysis->recommended_actions, 0, 3) as $act)
                        <div class="flex items-center gap-2 text-xs font-medium text-slate-700">
                            <span class="material-symbols-outlined text-[15px] text-emerald-600">task_alt</span>
                            <span>{{ $act }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @else
            <div class="py-6 text-center">
                <span class="material-symbols-outlined text-3xl text-slate-300 mb-1">nature_people</span>
                <p class="text-xs font-medium text-slate-500">Sistem AI memantau kondisi air sungai secara otomatis untuk keselamatan warga.</p>
            </div>
            @endif

            <div class="pt-3 border-t border-slate-100 flex justify-between items-center text-xs">
                <span class="font-mono text-[11px] text-slate-400">Pembaruan: {{ now()->format('H:i:s') }} WIB</span>
                <a href="{{ route('analytics') }}" class="font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1">
                    Panduan Lengkap <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </a>
            </div>
        </div>

        {{-- ── Bento Item 3: Angka Ketinggian Air untuk Warga ── --}}
        <div class="clean-card p-5 flex flex-col justify-between">
            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-100">
                <span class="material-symbols-outlined text-slate-600 text-base">straighten</span>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Data Ketinggian Air</h3>
            </div>
            <div class="space-y-3">
                <div>
                    <span class="text-xs text-slate-500 block">Jarak Air dari Sensor</span>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <span class="font-mono text-3xl font-bold text-slate-900">{{ number_format($distanceCm, 1) }}</span>
                        <span class="font-mono text-xs text-slate-500">cm</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                    <span class="text-xs text-slate-500">Tren Perubahan</span>
                    <span class="font-mono text-xs font-bold {{ $riseRate > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $riseRate > 0 ? '▲ Naik' : '▼ Surut' }} {{ abs(number_format($riseRate, 1)) }} cm/min
                    </span>
                </div>
            </div>
            <span class="text-[10px] font-mono text-slate-400 mt-4 block text-center">Otorefresh setiap 2 detik</span>
        </div>

        {{-- ── Bento Item 4: Kontak Darurat & Posko Evakuasi Warga ── --}}
        <div class="clean-card p-5 flex flex-col justify-between">
            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-100">
                <span class="material-symbols-outlined text-rose-600 text-base">support_agent</span>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kontak Bantuan Warga</h3>
            </div>
            <div class="space-y-2 text-xs">
                <a href="tel:112" class="flex items-center justify-between p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 hover:bg-rose-100 transition-all font-semibold">
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">call</span>
                        BPBD Call Center
                    </span>
                    <span class="font-mono font-bold text-sm">112</span>
                </a>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-slate-700 font-medium">
                    <span>Posko Evakuasi Sumbersari</span>
                    <span class="font-bold text-emerald-600">Buka</span>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-slate-700 font-medium">
                    <span>Ambulans Darurat Jember</span>
                    <span class="font-mono font-bold">118</span>
                </div>
            </div>
            <span class="text-[10px] text-slate-400 text-center block mt-3">Layanan Siaga 24 Jam BPBD Jember</span>
        </div>

        {{-- ── Bento Item 5: Peta Ringkas Wilayah Siaga (Spans 2 cols) ── --}}
        <div class="clean-card p-5 flex flex-col justify-between md:col-span-2 lg:col-span-2">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-600 text-base">pin_drop</span>
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Wilayah Pantauan Sungai Bedadung</h3>
                </div>
                <a href="{{ route('map') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1">
                    Buka Peta Interaktif <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </a>
            </div>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="font-semibold text-slate-800 block">Kecamatan Sumbersari</span>
                    <span class="text-slate-500 text-[11px] mt-0.5 block">Pos Sensor 01 — Bantaran Sungai utama</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="font-semibold text-slate-800 block">Kecamatan Kaliwates</span>
                    <span class="text-slate-500 text-[11px] mt-0.5 block">Pos Sensor 02 — Daerah hilir sungai</span>
                </div>
            </div>
        </div>

    </div>
</div>
