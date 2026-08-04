<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Command Dashboard') — Bedadung SFEWS</title>
    <meta name="description" content="SMART FLOOD & RAIN MONITORING SYSTEM — Bedadung River Early Warning System">

    {{-- Google Fonts: Poppins & JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    {{-- TailwindCSS CDN --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        navy: {
                            800: '#0f172a',
                            900: '#0b132b',
                            950: '#060a17',
                        }
                    },
                    fontFamily: {
                        'sans': ['Poppins', 'sans-serif'],
                        'mono': ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #f1f5f9;
            color: #0f172a;
            font-family: 'Poppins', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .rainova-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.03);
            transition: all 0.2s ease-in-out;
        }

        .rainova-sidebar {
            background: linear-gradient(180deg, #0b132b 0%, #0f172a 100%);
        }

        .pulse-live {
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse-ring {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.1); }
        }

        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0.5rem); }

        .material-symbols-outlined { font-variation-settings: 'FILL' 0; }
        .ms-fill { font-variation-settings: 'FILL' 1; }
    </style>

    {{-- Global JS Guard for Pusher & Echo --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script>
        const dummyChannel = { listen: function() { return this; }, stopListening: function() { return this; } };
        window.Echo = window.Echo || {
            private: function() { return dummyChannel; },
            channel: function() { return dummyChannel; },
            encryptedPrivate: function() { return dummyChannel; },
            presence: function() { return dummyChannel; },
            listen: function() { return this; },
            leave: function() {},
            disconnect: function() {}
        };
    </script>

    @livewireStyles
</head>
<body class="min-h-screen flex flex-col md:flex-row text-slate-800 antialiased">

    {{-- ── 1. DARK MIDNIGHT NAVY SIDEBAR (DESKTOP) ── --}}
    <aside class="hidden md:flex md:w-64 lg:w-72 rainova-sidebar min-h-screen flex-col justify-between p-5 text-white flex-shrink-0 z-40 shadow-xl border-r border-slate-800">
        <div class="space-y-8">
            {{-- Brand Header --}}
            <div class="flex items-center gap-3 px-2 pt-2">
                <div class="w-11 h-11 rounded-2xl bg-sky-500/20 border border-sky-400/40 flex items-center justify-center text-sky-400 shadow-inner">
                    <span class="material-symbols-outlined text-2xl ms-fill">cloud_download</span>
                </div>
                <div>
                    <h1 class="font-extrabold text-xl tracking-tight text-white leading-none">BEDADUNG SFEWS</h1>
                    <span class="text-[11px] font-semibold text-slate-400 tracking-wider block mt-1 uppercase">Flood Monitoring</span>
                </div>
            </div>

            {{-- Sidebar Navigation Menu --}}
            <nav class="space-y-1.5">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold transition-all duration-150
                          {{ request()->routeIs('dashboard') ? 'bg-slate-800/90 text-white border border-slate-700/80 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('dashboard') ? 'ms-fill text-sky-400' : '' }}">space_dashboard</span>
                        Dashboard
                    </span>
                </a>

                <a href="{{ route('analytics') }}"
                   class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold transition-all duration-150
                          {{ request()->routeIs('analytics') ? 'bg-slate-800/90 text-white border border-slate-700/80 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('analytics') ? 'ms-fill text-sky-400' : '' }}">query_stats</span>
                        Sensor Analytics
                    </span>
                </a>

                <a href="{{ route('map') }}"
                   class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold transition-all duration-150
                          {{ request()->routeIs('map') ? 'bg-slate-800/90 text-white border border-slate-700/80 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('map') ? 'ms-fill text-sky-400' : '' }}">location_on</span>
                        Sensor Map
                    </span>
                </a>

                <a href="{{ route('alerts') }}"
                   class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold transition-all duration-150
                          {{ request()->routeIs('alerts') ? 'bg-slate-800/90 text-white border border-slate-700/80 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('alerts') ? 'ms-fill text-rose-400' : '' }}">notifications_active</span>
                        Alert Center
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-rose-900/60 text-rose-300 border border-rose-800/60">99+</span>
                </a>

                <a href="{{ route('control') }}"
                   class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold transition-all duration-150
                          {{ request()->routeIs('control') ? 'bg-slate-800/90 text-white border border-slate-700/80 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">
                    <span class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-lg {{ request()->routeIs('control') ? 'ms-fill text-sky-400' : '' }}">settings_remote</span>
                        Hardware Control
                    </span>
                </a>
            </nav>
        </div>

        {{-- Sidebar Footer Status Box --}}
        <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-slate-800 text-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 pulse-live"></span>
                <div>
                    <span class="font-bold text-white block leading-snug">MQTT Connected</span>
                    <span class="text-[10px] text-slate-400">Device Active & Synchronized</span>
                </div>
            </div>
        </div>
    </aside>

    {{-- ── 2. MAIN CANVAS (RIGHT SIDE) ── --}}
    <div class="flex-1 flex flex-col min-w-0 pb-20 md:pb-8">

        {{-- Top Bar Header --}}
        <header class="w-full bg-white/90 backdrop-blur-md border-b border-slate-200 px-5 sm:px-8 py-4 flex justify-between items-center sticky top-0 z-30 shadow-xs">
            <div>
                <span class="text-[10px] sm:text-[11px] font-extrabold uppercase tracking-widest text-sky-600 block">
                    SMART FLOOD & RAIN MONITORING SYSTEM
                </span>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight mt-0.5">
                    @yield('header_title', 'Command Dashboard')
                </h1>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                {{-- Digital Sync Badge --}}
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200/80 text-xs font-mono font-semibold text-slate-600">
                    <span class="text-slate-400 text-[10px]">Last Sync:</span>
                    <span id="digitalClock">{{ now()->format('H:i:s') }}</span>
                </div>

                {{-- Notification Bell button --}}
                <div class="relative">
                    <button class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 flex items-center justify-center transition-all">
                        <span class="material-symbols-outlined text-lg">notifications</span>
                    </button>
                    <span class="absolute -top-1 -right-1 px-1.5 py-0.5 rounded-full text-[9px] font-mono font-bold bg-rose-600 text-white shadow-xs">99+</span>
                </div>
            </div>
        </header>

        {{-- Main Page Content --}}
        <main class="p-4 sm:p-6 lg:p-8 flex-1 max-w-7xl w-full mx-auto">
            @yield('content')
        </main>
    </div>

    {{-- ── 3. MOBILE BOTTOM NAVIGATION ── --}}
    <nav class="md:hidden fixed bottom-0 w-full z-50 flex justify-around items-center px-2 py-2 bg-slate-900 text-white border-t border-slate-800 shadow-2xl rounded-t-2xl pb-safe">
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center p-2 rounded-xl text-xs font-medium transition-all
                  {{ request()->routeIs('dashboard') ? 'text-sky-400 font-semibold' : 'text-slate-400' }}">
            <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('dashboard') ? 'ms-fill' : '' }}">space_dashboard</span>
            <span class="text-[10px] mt-0.5">Dashboard</span>
        </a>
        <a href="{{ route('analytics') }}"
           class="flex flex-col items-center p-2 rounded-xl text-xs font-medium transition-all
                  {{ request()->routeIs('analytics') ? 'text-sky-400 font-semibold' : 'text-slate-400' }}">
            <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('analytics') ? 'ms-fill' : '' }}">query_stats</span>
            <span class="text-[10px] mt-0.5">Analytics</span>
        </a>
        <a href="{{ route('map') }}"
           class="flex flex-col items-center p-2 rounded-xl text-xs font-medium transition-all
                  {{ request()->routeIs('map') ? 'text-sky-400 font-semibold' : 'text-slate-400' }}">
            <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('map') ? 'ms-fill' : '' }}">location_on</span>
            <span class="text-[10px] mt-0.5">Map</span>
        </a>
        <a href="{{ route('alerts') }}"
           class="flex flex-col items-center p-2 rounded-xl text-xs font-medium transition-all
                  {{ request()->routeIs('alerts') ? 'text-rose-400 font-semibold' : 'text-slate-400' }}">
            <span class="material-symbols-outlined text-[22px] {{ request()->routeIs('alerts') ? 'ms-fill' : '' }}">notifications_active</span>
            <span class="text-[10px] mt-0.5">Alerts</span>
        </a>
    </nav>

    <script>
        setInterval(() => {
            const clockEl = document.getElementById('digitalClock');
            if (clockEl) {
                const now = new Date();
                clockEl.innerText = now.toTimeString().split(' ')[0];
            }
        }, 1000);
    </script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
