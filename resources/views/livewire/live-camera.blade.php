<div class="space-y-6" x-data="{
    streamOk: false,
    // Derive the AI-engine host from the current browser host so that a phone
    // on the same Wi-Fi reaches the engine at http://<pc-ip>:8003 instead of
    // 127.0.0.1 (which would point at the phone itself).
    streamUrl: (location.protocol === 'https:' || location.port === '8443')
        ? '/api/stream'
        : (location.hostname === '127.0.0.1' || location.hostname === 'localhost')
        ? '{{ config('services.ai.url') }}/api/stream'
        : 'http://' + location.hostname + ':8003/api/stream'
}"
     wire:poll.1500ms.visible="pollTracks"
     x-on:refresh-status.window="$wire.refreshStatus()">

    {{-- ===== HEADER ===== --}}
    {{-- Toolbar: the page title lives in the dashboard header (no duplicate) --}}
    <div class="flex flex-wrap items-center gap-x-4 gap-y-3 mb-2">
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full border"
             :class="streamOk ? 'bg-emerald-900/30 border-emerald-700/50' : 'bg-gray-900 border-gray-700'">
            <span class="w-2 h-2 rounded-full" :class="streamOk ? 'bg-emerald-500 animate-pulse' : 'bg-gray-600'"></span>
            <span class="text-xs" :class="streamOk ? 'text-emerald-400' : 'text-gray-500'"
                  x-text="streamOk ? 'در حال پخش' : 'آفلاین'">آفلاین</span>
        </div>
        <a href="/mobile.html" target="_blank"
           class="flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600/20 border border-emerald-600/40 hover:bg-emerald-600/30 text-emerald-300 text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            دوربین گوشی
        </a>
        <p class="text-xs text-gray-500 sm:hidden">برای تست روی لپ‌تاپ، در صفحه گوشی دکمه 💻 لپ‌تاپ را بزنید</p>
    </div>

    {{-- ===== ERROR BANNER ===== --}}
    @if($error)
        <div class="bg-red-900/30 border border-red-700/50 rounded-xl p-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-red-300 text-sm">{{ $error }}</span>
        </div>
    @endif

    {{-- ===== STATUS BADGES ===== --}}
    <div class="flex flex-wrap gap-3">
        <div class="px-4 py-2 bg-gray-900 border border-gray-800 rounded-xl text-sm">
            <span class="text-gray-500">وضعیت: </span>
            <span class="font-semibold" :class="$wire.running ? 'text-emerald-400' : 'text-red-400'"
                  x-text="$wire.running ? 'روشن' : 'خاموش'">—</span>
        </div>
        <div class="px-4 py-2 bg-gray-900 border border-gray-800 rounded-xl text-sm">
            <span class="text-gray-500">چهره‌های فعال: </span>
            <span class="font-semibold text-blue-400" x-text="$wire.faces">0</span>
        </div>
        <div class="px-4 py-2 bg-gray-900 border border-gray-800 rounded-xl text-sm">
            <span class="text-gray-500">افراد در دیتابیس: </span>
            <span class="font-semibold text-purple-400" x-text="$wire.personsInCache">0</span>
        </div>
    </div>

    {{-- ===== CAMERA CONTROLS + STREAM ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden shadow-xl">
                <div class="relative bg-black aspect-video">
                    <img x-bind:src="streamUrl"
                         alt="Live stream"
                         class="w-full h-full object-contain"
                         x-on:load="streamOk = true"
                         x-on:error="streamOk = false"
                         :class="streamOk ? 'opacity-100' : 'opacity-0'">

                    <div x-show="!streamOk"
                         class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-gray-500">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <p>دوربین روشن نیست</p>
                        <p class="text-xs text-gray-600">برای شروع، دکمه «روشن کردن دوربین» را بزنید</p>
                    </div>
                </div>

                <div class="p-4 space-y-3 border-t border-gray-800">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1" for="sourceUrl">
                            آدرس دوربین گوشی (اختیاری) — مثال: http://192.168.1.5:8080/video — خالی = وب‌کم سیستم
                        </label>
                        <input id="sourceUrl" x-ref="sourceInput" type="text" wire:model.debounce.500ms="sourceUrl"
                               dir="ltr" placeholder="http://192.168.1.5:8080/video"
                               class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-600 focus:outline-none focus:border-emerald-600">
                        <div class="flex items-center justify-between mt-1">
                            <p class="text-[11px] text-gray-600 leading-5">
                                دوربین گوشی را با یک اپ IP Webcam (پخش MJPEG) روی همان شبکه وای‌فای اجرا کنید؛
                                برای انتخاب دوربین جلو از <code class="text-gray-500" dir="ltr">?1</code> یا <code class="text-gray-500" dir="ltr">?cam=front</code>
                                و دوربین عقب از <code class="text-gray-500" dir="ltr">?0</code> یا <code class="text-gray-500" dir="ltr">?cam=back</code> استفاده کنید.
                            </p>
                            <div class="flex gap-1 shrink-0 flex-wrap">
                                <button type="button" title="دوربین جلو" x-on:click="$refs.sourceInput.value = 'http://192.168.1.5:8080/video?1'"
                                        class="px-2 py-1 text-[11px] bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded text-gray-400">جلو</button>
                                <button type="button" title="دوربین عقب" x-on:click="$refs.sourceInput.value = 'http://192.168.1.5:8080/video?0'"
                                        class="px-2 py-1 text-[11px] bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded text-gray-400">عقب</button>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <span class="text-xs text-gray-500">پخش زنده از موتور هوش مصنوعی (MJPEG)</span>
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="startCamera"
                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition flex items-center gap-2 shadow-lg shadow-emerald-900/30">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                                روشن کردن دوربین
                            </button>
                            <button wire:click="stopCamera"
                                    class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium rounded-lg transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <rect x="6" y="6" width="12" height="12" rx="1"/>
                                </svg>
                                خاموش کردن
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== LIVE RECOGNITION PANEL ===== --}}
        <div class="space-y-4">
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-white font-semibold text-sm">افراد شناسایی‌شده (زنده)</h3>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-900/40 text-emerald-400"
                          x-text="$wire.faces + ' چهره'">0</span>
                </div>

                <div class="space-y-3 max-h-[420px] overflow-y-auto">
                    <template x-for="track in $wire.tracks" :key="track.track_id">
                        <div class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-lg border border-gray-700/50">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shrink-0"
                                 :class="track.category === 'vip' ? 'bg-yellow-500 text-black' :
                                         (track.category === 'employee' ? 'bg-emerald-600' : 'bg-blue-600')">
                                <span x-text="(track.full_name || '?').charAt(0)">؟</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-white text-sm font-medium truncate"
                                   x-text="track.full_name || 'ناشناس'">—</p>
                                <p class="text-gray-500 text-xs font-mono" x-text="track.national_code || '—'">—</p>
                            </div>
                            <div class="text-left shrink-0">
                                <p class="text-xs font-semibold"
                                   :class="track.similarity > 0.5 ? 'text-emerald-400' : 'text-gray-500'"
                                   x-text="(track.similarity * 100).toFixed(0) + '%'">0%</p>
                                <p class="text-[10px] text-gray-600 uppercase"
                                   x-text="track.category">—</p>
                            </div>
                        </div>
                    </template>

                    <div x-show="$wire.tracks.length === 0"
                         class="text-center py-8 text-gray-600 text-sm">
                        <p>هنوز کسی شناسایی نشده</p>
                        <p class="text-xs mt-1">با روشن کردن دوربین، افراد جلوی لنز شناسایی می‌شوند</p>
                    </div>
                </div>
            </div>

            {{-- ===== RECENTLY SEEN ===== --}}
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
                <h3 class="text-white font-semibold text-sm mb-3">آخرین افراد دیده‌شده</h3>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <template x-for="[key, p] in Object.entries($wire.recent)" :key="key">
                        <div class="flex items-center justify-between text-sm px-3 py-2 bg-gray-800/40 rounded-lg">
                            <span class="text-gray-300 truncate" x-text="p.full_name">—</span>
                            <span class="text-gray-600 text-xs font-mono" x-text="p.seen_at">—</span>
                        </div>
                    </template>
                    <p x-show="Object.keys($wire.recent).length === 0" class="text-gray-600 text-sm text-center py-4">
                        —
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
