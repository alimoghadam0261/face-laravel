<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Glass AI - داشبورد مدیریت چهره</title>
    <meta name="description" content="سیستم مدیریت چهره هوشمند با هوش مصنوعی">
    <meta name="theme-color" content="#0a0a0f">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #111118; }
        ::-webkit-scrollbar-thumb { background: #2d2d3a; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #3d3d4a; }

        body {
            background: #0a0a0f;
            color: #e5e5ea;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .glow-border {
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.15), inset 0 0 15px rgba(16, 185, 129, 0.05);
        }

        .sidebar-gradient {
            background: linear-gradient(180deg, rgba(16,185,129,0.08) 0%, rgba(59,130,246,0.04) 100%);
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 5px rgba(16,185,129,0.3); }
            50% { box-shadow: 0 0 20px rgba(16,185,129,0.5); }
        }

        .animate-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
    </style>

    @livewireStyles
</head>
<body x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">

        {{-- ===== MOBILE SIDEBAR OVERLAY ===== --}}
        <div x-show="sidebarOpen" x-transition.opacity.duration.200ms
             x-on:click="sidebarOpen = false" x-cloak
             class="fixed inset-0 bg-black/60 z-30 md:hidden"></div>

        {{-- ===== SIDEBAR (off-canvas on mobile, static on desktop) ===== --}}
        <aside x-cloak
               class="fixed md:static inset-y-0 right-0 z-40 w-64 flex-shrink-0 sidebar-gradient border-r border-gray-800 flex flex-col overflow-y-auto transition-transform duration-200 ease-out"
               :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'">
            <div class="px-6 py-5 border-b border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white tracking-tight">Smart Glass</h1>
                        <p class="text-xs text-gray-500">سیستم مدیریت چهره</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-emerald-600/20 text-emerald-400 border border-emerald-500/20 font-medium' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }} transition group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>داشبورد</span>
                </a>

                <a href="{{ route('live') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('live') ? 'bg-emerald-600/20 text-emerald-400 border border-emerald-500/20 font-medium' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }} transition group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span>دوربین زنده</span>
                    <span class="ml-auto text-xs text-emerald-500/60 font-mono">LIVE</span>
                </a>

                <a href="{{ route('persons') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('persons') ? 'bg-emerald-600/20 text-emerald-400 border border-emerald-500/20 font-medium' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }} transition group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                    <span>مدیریت اشخاص</span>
                </a>

                <a href="#"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:bg-gray-800 hover:text-gray-200 transition group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>آمار و تحلیلات</span>
                </a>
            </nav>

            <div class="px-4 py-4 border-t border-gray-800">
                <div class="flex items-center gap-3 px-3 py-2.5 bg-gray-800/50 rounded-xl">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                        ا
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">管理员</p>
                        <p class="text-xs text-gray-500 truncate">admin@glass1.local</p>
                    </div>
                    <span class="text-xs text-gray-600 bg-gray-800 px-2 py-0.5 rounded-full">سطح ۱</span>
                </div>
            </div>
        </aside>

        {{-- ===== MAIN CONTENT ===== --}}
        <main class="flex-1 min-w-0 overflow-y-auto bg-[#0a0a0f]">
            <div class="p-4 sm:p-6 md:p-8">

                {{-- Page Header (title adapts to the active page) --}}
                <div class="flex items-center justify-between gap-3 flex-wrap mb-6 md:mb-8">
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- Mobile hamburger --}}
                        <button type="button" x-on:click="sidebarOpen = true" aria-label="باز کردن منو"
                                class="md:hidden p-2.5 bg-gray-900 border border-gray-800 rounded-xl text-gray-300 hover:text-white transition shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div class="min-w-0">
                            <h2 class="text-xl md:text-2xl font-bold text-white">
                                @if(request()->routeIs('live'))
                                    دوربین زنده و شناسایی چهره
                                @else
                                    مدیریت اشخاص
                                @endif
                            </h2>
                            <p class="text-gray-500 mt-1 text-sm md:text-base">
                                @if(request()->routeIs('live'))
                                    شناسایی و ردیابی افراد ثبت‌شده در دیتابیس به‌صورت زنده
                                @else
                                    مدیریت مدارک و چهره افراد – همگام با موتور هوش مصنوعی
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="hidden sm:flex items-center gap-3">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-900/30 border border-emerald-700/50 rounded-full">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-glow"></span>
                            <span class="text-xs text-emerald-400 font-medium">هوش مصنوعی فعال</span>
                        </div>
                    </div>
                </div>

                {{-- Livewire Component --}}
                @if(request()->routeIs('live'))
                    <livewire:live-camera />
                @else
                    <livewire:person-manager />
                @endif

            </div>
        </main>
    </div>

    @livewireScripts
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Auto-dismiss toasts
            setTimeout(() => {
                document.querySelectorAll('.fixed.bottom-6.right-6').forEach(el => {
                    el.style.transition = 'opacity 0.3s ease';
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 300);
                });
            }, 4000);
        });
    </script>
</body>
</html>
