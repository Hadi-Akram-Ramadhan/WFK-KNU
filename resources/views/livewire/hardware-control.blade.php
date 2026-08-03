<div class="flex flex-col gap-6 py-4" wire:poll.3s>
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Kontrol Hardware & Aktuator</h1>
        <p class="text-slate-500 text-sm font-normal mt-1">Pengaturan posisi servo pintu air, sirine darurat, dan log kontrol edge</p>
    </div>

    {{-- Flash Notification --}}
    @if($flashMessage)
    <div class="clean-card px-4 py-3 flex items-center gap-3 border-emerald-200 bg-emerald-50/80 text-emerald-800 text-xs font-semibold" x-data x-init="setTimeout(() => $el.remove(), 3500)">
        <span class="material-symbols-outlined text-emerald-600 text-lg ms-fill">check_circle</span>
        <span>{{ $flashMessage }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- ── Left: Actuator Controls (7 Cols) ── --}}
        <section class="lg:col-span-7 clean-card p-6 flex flex-col gap-6">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-lg">settings_remote</span>
                </div>
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kontrol Aktuator Remote</h2>
            </div>

            <div class="space-y-6">
                {{-- Automated Floodgate Switch --}}
                <div class="flex items-center justify-between gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Mode Otomatis Pintu Air</h3>
                        <p class="text-xs font-normal text-slate-500 mt-0.5">Servo bergerak sesuai deteksi ambang batas AI / sensor</p>
                    </div>
                    <button wire:click="toggleAutomatedMode"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all border
                                {{ $automatedMode ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-slate-200 text-slate-600 border-slate-300' }}">
                        {{ $automatedMode ? 'AKTIF (AUTO)' : 'NON-AKTIF' }}
                    </button>
                </div>

                {{-- Servo Angle Slider --}}
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 flex flex-col gap-3">
                    <div class="flex justify-between items-end">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Pengaturan Sudut Servo Manual</h3>
                            <p class="text-xs font-normal text-slate-500 mt-0.5">Posisi katup pintu air (0° tertutup, 90° terbuka penuh)</p>
                        </div>
                        <div class="px-3 py-1 rounded-lg bg-white border border-slate-200 shadow-xs">
                            <span class="font-mono text-xl font-bold text-slate-900">{{ $servoAngle }}°</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <input type="range" min="0" max="90" step="5"
                               wire:model="servoAngle"
                               class="w-full cursor-pointer accent-sky-600">
                        <div class="flex justify-between font-mono text-[11px] text-slate-400 mt-2">
                            <span>0° (Tertutup)</span>
                            <span>45° (Separuh)</span>
                            <span>90° (Terbuka Penuh)</span>
                        </div>
                    </div>

                    <button wire:click="setServoAngle"
                            class="mt-1 w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition-all shadow-xs flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">send</span>
                        Kirim Posisi Servo Ke Wemos
                    </button>
                </div>

                {{-- Siren Emergency Test --}}
                <div class="p-4 rounded-xl bg-rose-50/50 border border-rose-200/60 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-rose-900">Uji Coba Sirine Darurat</h3>
                        <p class="text-xs font-normal text-rose-700/80 mt-0.5">Menyalahkan buzzer / sirine fisik selama 3 detik</p>
                    </div>
                    <button wire:click="testSiren"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">volume_up</span>
                        TES SIRINE
                    </button>
                </div>
            </div>
        </section>

        {{-- ── Right: System Logs (5 Cols) ── --}}
        <section class="lg:col-span-5 clean-card p-6 flex flex-col justify-between gap-4">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-slate-700 text-lg">terminal</span>
                        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Log Perintah Hardware</h2>
                    </div>
                    <button wire:click="loadLogs" class="text-slate-400 hover:text-slate-700 transition-all" title="Refresh">
                        <span class="material-symbols-outlined text-sm">refresh</span>
                    </button>
                </div>

                <div class="mt-4 space-y-2.5 overflow-y-auto max-h-[380px] pr-1">
                    @forelse($systemLogs as $log)
                    @php
                        $badgeStyle = match($log['type']) {
                            'SIREN'          => 'bg-rose-100 text-rose-700',
                            'SERVO'          => 'bg-sky-100 text-sky-700',
                            'AUTOMATED_MODE' => 'bg-amber-100 text-amber-700',
                            default          => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60 text-xs">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-mono text-[11px] text-slate-400">{{ $log['time'] }}</span>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold uppercase {{ $badgeStyle }}">
                                    {{ $log['type'] }}
                                </span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600 uppercase">
                                    {{ strtoupper($log['source']) }}
                                </span>
                            </div>
                        </div>
                        <div class="font-mono text-slate-700 text-[11px]">
                            @foreach($log['payload'] as $k => $v)
                                <span class="text-slate-400">{{ $k }}:</span> {{ is_bool($v) ? ($v ? 'true' : 'false') : $v }}
                                @if(!$loop->last) <span class="text-slate-300">|</span> @endif
                            @endforeach
                        </div>
                        <div class="mt-1.5 flex items-center gap-1 text-[10px] font-mono font-medium {{ $log['executed'] ? 'text-emerald-600' : 'text-slate-400' }}">
                            <span class="material-symbols-outlined text-[12px]">{{ $log['executed'] ? 'check_circle' : 'schedule' }}</span>
                            <span>{{ $log['executed'] ? 'Executed by Wemos' : 'Pending Wemos poll' }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="py-10 text-center">
                        <span class="material-symbols-outlined text-3xl text-slate-300 mb-1">history</span>
                        <p class="text-xs font-medium text-slate-400">Belum ada riwayat perintah.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <button wire:click="loadLogs" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-all border border-slate-200/80">
                Refresh Logs
            </button>
        </section>
    </div>
</div>
