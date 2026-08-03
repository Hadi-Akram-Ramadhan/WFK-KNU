<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Bedadung SFEWS</title>
    <meta name="description" content="Sistem Peringatan Dini Banjir Sungai Bedadung, Jember. Real-time flood monitoring powered by AI.">

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
                    colors: {
                        brand: {
                            50:  '#f0f7ff',
                            100: '#e0effe',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            900: '#0c4a6e',
                        },
                        slate: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    fontFamily: {
                        'sans': ['Poppins', 'sans-serif'],
                        'mono': ['JetBrains Mono', 'monospace'],
                    },
                    boxShadow: {
                        'clean': '0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.02)',
                        'card': '0 10px 30px -5px rgba(15, 23, 42, 0.06), 0 4px 12px -2px rgba(15, 23, 42, 0.03)',
                        'glow-danger': '0 0 25px rgba(225, 29, 72, 0.25)',
                    }
                }
            }
        }
    </script>

    {{-- Custom Clean Aesthetic Styles --}}
    <style>
        body {
            background-color: #f8fafc;
            background-image:
                radial-gradient(at 10% 10%, rgba(224, 242, 254, 0.6) 0px, transparent 40%),
                radial-gradient(at 90% 10%, rgba(238, 242, 255, 0.6) 0px, transparent 40%),
                radial-gradient(at 50% 90%, rgba(241, 245, 249, 0.8) 0px, transparent 50%);
            background-attachment: fixed;
            color: #0f172a;
            font-family: 'Poppins', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .clean-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
            transition: all 0.2s ease-in-out;
        }

        .clean-card:hover {
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.07);
            border-color: rgba(203, 213, 225, 0.9);
        }

        .danger-card-clean {
            background: rgba(255, 241, 242, 0.85);
            backdrop-filter: blur(12px);
            border: 1.5px solid rgba(253, 164, 175, 0.8);
            box-shadow: 0 8px 30px rgba(225, 29, 72, 0.12);
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

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    @livewireStyles
</head>
<body class="min-h-screen pb-24 md:pb-0 md:pt-20 text-slate-800">

    {{-- ── Desktop Clean Navigation Bar ── --}}
    <header class="hidden md:flex fixed top-0 w-full z-50 justify-between items-center px-8 py-3.5 bg-white/80 backdrop-blur-md border-b border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-sm">
                <span class="material-symbols-outlined text-2xl ms-fill text-sky-400">waves</span>
            </div>
            <div>
                <span class="font-bold text-lg text-slate-900 tracking-tight block leading-tight">Bedadung SFEWS</span>
                <span class="text-[11px] font-medium text-slate-500 tracking-wide uppercase">Smart Flood Warning</span>
            </div>
        </div>

        <div class="flex items-center gap-6">
            {{-- Node status pill --}}
            <div class="flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200/80 text-xs font-mono font-medium text-slate-600">
                <span class="w-2 h-2 rounded-full bg-emerald-500 pulse-live"></span>
                <span>BEDADUNG_01</span>
                <span class="text-slate-300">•</span>
                <span class="text-emerald-600 font-semibold">ONLINE</span>
            </div>

            {{-- Nav links --}}
            <nav class="flex gap-1.5 bg-slate-100/80 p-1 rounded-xl border border-slate-200/60">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center px-4 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150
                          {{ request()->routeIs('dashboard') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <span class="material-symbols-outlined mr-1.5 text-[17px] {{ request()->routeIs('dashboard') ? 'ms-fill text-sky-600' : '' }}">dashboard</span>
                    Home
                </a>
                <a href="{{ route('map') }}"
                   class="flex items-center px-4 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150
                          {{ request()->routeIs('map') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <span class="material-symbols-outlined mr-1.5 text-[17px] {{ request()->routeIs('map') ? 'ms-fill text-sky-600' : '' }}">map</span>
                    Map
                </a>
                <a href="{{ route('analytics') }}"
                   class="flex items-center px-4 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150
                          {{ request()->routeIs('analytics') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <span class="material-symbols-outlined mr-1.5 text-[17px] {{ request()->routeIs('analytics') ? 'ms-fill text-sky-600' : '' }}">psychology</span>
                    AI Analytics
                </a>
                <a href="{{ route('control') }}"
                   class="flex items-center px-4 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150
                          {{ request()->routeIs('control') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <span class="material-symbols-outlined mr-1.5 text-[17px] {{ request()->routeIs('control') ? 'ms-fill text-sky-600' : '' }}">settings_remote</span>
                    Control
                </a>
            </nav>

            {{-- Emergency SOS button --}}
            <button class="flex items-center justify-center w-9 h-9 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all border border-rose-200" title="SOS Darurat">
                <span class="material-symbols-outlined text-[18px] ms-fill">emergency</span>
            </button>
        </div>
    </header>

    {{-- ── Mobile Top Header ── --}}
    <header class="md:hidden fixed top-0 w-full z-50 flex justify-between items-center px-5 py-3 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center">
                <span class="material-symbols-outlined text-xl ms-fill text-sky-400">waves</span>
            </div>
            <span class="font-bold text-base text-slate-900 tracking-tight">Bedadung SFEWS</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-xs font-mono font-medium text-slate-500">ONLINE</span>
        </div>
    </header>

    {{-- ── Main Canvas ── --}}
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 md:pt-8">
        @yield('content')
    </main>

    {{-- ── Mobile Bottom Navigation ── --}}
    <nav class="md:hidden fixed bottom-0 w-full z-50 flex justify-around items-center px-2 py-2 bg-white/90 backdrop-blur-md border-t border-slate-200 shadow-lg rounded-t-2xl pb-safe">
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center p-2 rounded-xl text-xs font-medium transition-all
                  {{ request()->routeIs('dashboard') ? 'text-sky-600 font-semibold' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('dashboard') ? 'ms-fill' : '' }}">dashboard</span>
            <span class="text-[10px] mt-0.5">Home</span>
        </a>
        <a href="{{ route('map') }}"
           class="flex flex-col items-center p-2 rounded-xl text-xs font-medium transition-all
                  {{ request()->routeIs('map') ? 'text-sky-600 font-semibold' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('map') ? 'ms-fill' : '' }}">map</span>
            <span class="text-[10px] mt-0.5">Map</span>
        </a>
        <a href="{{ route('analytics') }}"
           class="flex flex-col items-center p-2 rounded-xl text-xs font-medium transition-all
                  {{ request()->routeIs('analytics') ? 'text-sky-600 font-semibold' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('analytics') ? 'ms-fill' : '' }}">psychology</span>
            <span class="text-[10px] mt-0.5">AI</span>
        </a>
        <a href="{{ route('control') }}"
           class="flex flex-col items-center p-2 rounded-xl text-xs font-medium transition-all
                  {{ request()->routeIs('control') ? 'text-sky-600 font-semibold' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('control') ? 'ms-fill' : '' }}">settings_remote</span>
            <span class="text-[10px] mt-0.5">Control</span>
        </a>
    </nav>

    @livewireScripts
    @stack('scripts')
</body>
</html>
