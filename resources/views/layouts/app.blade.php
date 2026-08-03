<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Informasi Siaga Banjir') — Bedadung SFEWS Jember</title>
    <meta name="description" content="Portal Informasi Keselamatan & Peringatan Dini Banjir Sungai Bedadung untuk Warga Jember. Powered by AI.">

    {{-- Google Fonts: Poppins & JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    {{-- TailwindCSS CDN --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Poppins', 'sans-serif'],
                        'mono': ['JetBrains Mono', 'monospace'],
                    },
                    boxShadow: {
                        'clean': '0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.02)',
                        'card': '0 10px 30px -5px rgba(15, 23, 42, 0.06), 0 4px 12px -2px rgba(15, 23, 42, 0.03)',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #f8fafc;
            background-image:
                radial-gradient(at 10% 10%, rgba(224, 242, 254, 0.6) 0px, transparent 40%),
                radial-gradient(at 90% 10%, rgba(238, 242, 254, 0.6) 0px, transparent 40%),
                radial-gradient(at 50% 90%, rgba(241, 245, 249, 0.8) 0px, transparent 50%);
            background-attachment: fixed;
            color: #0f172a;
            font-family: 'Poppins', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .clean-card {
            background: rgba(255, 255, 255, 0.90);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
            transition: all 0.2s ease-in-out;
        }

        .pulse-live {
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse-ring {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.1); }
        }

        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0.5rem); }

        .material-symbols-outlined { font-variation-settings: 'FILL' 0; }
        .ms-fill { font-variation-settings: 'FILL' 1; }
    </style>

    {{-- ── Real Pusher & Echo CDN Setup to Eliminate All Console Errors ── --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        try {
            window.Pusher = Pusher;
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: 'sfews_key',
                cluster: 'mt1',
                forceTLS: true
            });
        } catch (e) {
            console.warn('[Echo] Running in polling fallback mode');
        }
    </script>

    @livewireStyles
</head>
<body class="min-h-screen pb-24 md:pb-0 md:pt-20 text-slate-800">

    {{-- ── Desktop Public Navbar ── --}}
    <header class="hidden md:flex fixed top-0 w-full z-50 justify-between items-center px-8 py-3.5 bg-white/85 backdrop-blur-md border-b border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center shadow-sm">
                <span class="material-symbols-outlined text-2xl ms-fill">waves</span>
            </div>
            <div>
                <span class="font-bold text-lg text-slate-900 tracking-tight block leading-tight">Siaga Bedadung</span>
                <span class="text-[11px] font-medium text-slate-500 tracking-wide uppercase">Peringatan Banjir untuk Warga Jember</span>
            </div>
        </div>

        <div class="flex items-center gap-6">
            {{-- Public Status Pill --}}
            <div class="flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200/80 text-xs font-mono font-medium text-slate-600">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 pulse-live"></span>
                <span>Pos Pemantauan Sumbersari</span>
                <span class="text-slate-300">•</span>
                <span class="text-emerald-600 font-semibold">AKTIF</span>
            </div>

            {{-- Citizen Nav links --}}
            <nav class="flex gap-1.5 bg-slate-100/80 p-1 rounded-xl border border-slate-200/60">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center px-4 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150
                          {{ request()->routeIs('dashboard') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <span class="material-symbols-outlined mr-1.5 text-[17px] {{ request()->routeIs('dashboard') ? 'ms-fill text-sky-600' : '' }}">home</span>
                    Beranda Warga
                </a>
                <a href="{{ route('map') }}"
                   class="flex items-center px-4 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150
                          {{ request()->routeIs('map') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <span class="material-symbols-outlined mr-1.5 text-[17px] {{ request()->routeIs('map') ? 'ms-fill text-sky-600' : '' }}">map</span>
                    Peta Sungai
                </a>
                <a href="{{ route('analytics') }}"
                   class="flex items-center px-4 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150
                          {{ request()->routeIs('analytics') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <span class="material-symbols-outlined mr-1.5 text-[17px] {{ request()->routeIs('analytics') ? 'ms-fill text-sky-600' : '' }}">health_and_safety</span>
                    Panduan & AI
                </a>
            </nav>

            {{-- Emergency SOS button --}}
            <a href="tel:112" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 text-white font-semibold text-xs hover:bg-rose-700 transition-all shadow-sm" title="Hubungi Call Center BPBD">
                <span class="material-symbols-outlined text-[16px] ms-fill">call</span>
                <span>Darurat 112</span>
            </a>
        </div>
    </header>

    {{-- ── Mobile Top Header ── --}}
    <header class="md:hidden fixed top-0 w-full z-50 flex justify-between items-center px-5 py-3 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-sky-600 text-white flex items-center justify-center">
                <span class="material-symbols-outlined text-xl ms-fill">waves</span>
            </div>
            <span class="font-bold text-base text-slate-900 tracking-tight">Siaga Bedadung</span>
        </div>
        <a href="tel:112" class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-600 text-white font-bold text-xs">
            <span class="material-symbols-outlined text-[14px]">call</span> 112
        </a>
    </header>

    {{-- ── Main Canvas ── --}}
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 md:pt-8">
        @yield('content')
    </main>

    {{-- ── Mobile Bottom Navigation for Citizens ── --}}
    <nav class="md:hidden fixed bottom-0 w-full z-50 flex justify-around items-center px-2 py-2 bg-white/90 backdrop-blur-md border-t border-slate-200 shadow-lg rounded-t-2xl pb-safe">
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center p-2 rounded-xl text-xs font-medium transition-all
                  {{ request()->routeIs('dashboard') ? 'text-sky-600 font-semibold' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('dashboard') ? 'ms-fill' : '' }}">home</span>
            <span class="text-[10px] mt-0.5">Beranda</span>
        </a>
        <a href="{{ route('map') }}"
           class="flex flex-col items-center p-2 rounded-xl text-xs font-medium transition-all
                  {{ request()->routeIs('map') ? 'text-sky-600 font-semibold' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('map') ? 'ms-fill' : '' }}">map</span>
            <span class="text-[10px] mt-0.5">Peta</span>
        </a>
        <a href="{{ route('analytics') }}"
           class="flex flex-col items-center p-2 rounded-xl text-xs font-medium transition-all
                  {{ request()->routeIs('analytics') ? 'text-sky-600 font-semibold' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('analytics') ? 'ms-fill' : '' }}">health_and_safety</span>
            <span class="text-[10px] mt-0.5">Panduan AI</span>
        </a>
        <a href="tel:112"
           class="flex flex-col items-center p-2 rounded-xl text-xs font-medium text-rose-600">
            <span class="material-symbols-outlined text-[22px] ms-fill">call</span>
            <span class="text-[10px] mt-0.5">SOS 112</span>
        </a>
    </nav>

    @livewireScripts
    @stack('scripts')
</body>
</html>
