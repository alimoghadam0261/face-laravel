<div>
    {{-- ===== SEARCH & FILTER BAR ===== --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   wire:model.live="search"
                   placeholder="جستجو بر اساس نام، کد ملی یا شماره..."
                   class="w-full pl-10 pr-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"/>
        </div>

        <div class="flex gap-2">
            <select wire:model.live="category_filter"
                    class="px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="all">همه دسته‌بندی‌ها</option>
                <option value="vip">VIP (طلا)</option>
                <option value="employee">کارمندان (سبز)</option>
                <option value="guest">مهمان‌ها (آبی)</option>
            </select>

            <button wire:click="openCreateModal()"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition flex items-center gap-2 shadow-lg shadow-emerald-900/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden sm:inline">شخص جدید</span>
            </button>
        </div>
    </div>

    {{-- ===== PERSONS TABLE ===== --}}
    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden shadow-xl">
        @if($this->persons->count() > 0)
            {{-- Desktop: full table (hidden on phones) --}}
            <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-800/50 border-b border-gray-700">
                        <th class="text-left px-4 py-3 text-gray-400 font-medium uppercase text-xs tracking-wider">عکس</th>
                        <th class="text-left px-4 py-3 text-gray-400 font-medium uppercase text-xs tracking-wider">نام کامل</th>
                        <th class="text-left px-4 py-3 text-gray-400 font-medium uppercase text-xs tracking-wider">کد ملی</th>
                        <th class="text-left px-4 py-3 text-gray-400 font-medium uppercase text-xs tracking-wider">دسته‌بندی</th>
                        <th class="text-left px-4 py-3 text-gray-400 font-medium uppercase text-xs tracking-wider hidden md:table-cell">تلفن</th>
                        <th class="text-right px-4 py-3 text-gray-400 font-medium uppercase text-xs tracking-wider">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($this->persons as $person)
                        <tr class="hover:bg-gray-800/40 transition">
                            <td class="px-4 py-3">
                                <img src="{{ $person->photo_url }}"
                                     alt="{{ $person->full_name }}"
                                     class="w-10 h-10 rounded-full object-cover border-2 border-gray-700 shadow-md"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($person->full_name) }}&background=333&color=fff&size=80'">
                            </td>
                            <td class="px-4 py-3 text-white font-medium">{{ $person->full_name }}</td>
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $person->national_code }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $person->category_badge_class }}">
                                    {{ $person->category_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 hidden md:table-cell">{{ $person->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openEditModal({{ $person->id }})"
                                            class="p-2 hover:bg-emerald-900/30 rounded-lg text-emerald-400 hover:text-emerald-300 transition" title="ویرایش">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="deletePerson({{ $person->id }})"
                                            class="p-2 hover:bg-red-900/30 rounded-lg text-red-400 hover:text-red-300 transition" title="حذف"
                                            wire:confirm="آیا مطمئن هستید که می‌خواهید این شخص را حذف کنید؟">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            {{-- Mobile: compact card list --}}
            <div class="md:hidden divide-y divide-gray-800">
                @foreach($this->persons as $person)
                    <div class="flex items-center gap-3 p-4">
                        <img src="{{ $person->photo_url }}" alt="{{ $person->full_name }}"
                             class="w-11 h-11 rounded-full object-cover border-2 border-gray-700 shadow-md shrink-0"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($person->full_name) }}&background=333&color=fff&size=88'">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-white font-medium truncate">{{ $person->full_name }}</p>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $person->category_badge_class }}">{{ $person->category_label }}</span>
                            </div>
                            <p class="text-gray-500 text-xs font-mono mt-0.5">{{ $person->national_code }}</p>
                        </div>
                        <div class="flex flex-col gap-1 shrink-0">
                            <button wire:click="openEditModal({{ $person->id }})"
                                    class="p-2 hover:bg-emerald-900/30 rounded-lg text-emerald-400 hover:text-emerald-300 transition" title="ویرایش">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="deletePerson({{ $person->id }})"
                                    class="p-2 hover:bg-red-900/30 rounded-lg text-red-400 hover:text-red-300 transition" title="حذف"
                                    wire:confirm="آیا مطمئن هستید که می‌خواهید این شخص را حذف کنید؟">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="px-4 py-3 border-t border-gray-800 flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs text-gray-500">
                    نمایش {{ $this->persons->count() }} از {{ $this->persons->total() }} نفر
                </p>
                <div class="flex gap-1">
                    @if($this->persons->onFirstPage())
                        <button disabled class="px-3 py-1.5 text-gray-600 bg-gray-800 rounded disabled:opacity-50">←</button>
                    @else
                        <button wire:click="previousPage" class="px-3 py-1.5 text-gray-300 bg-gray-800 hover:bg-gray-700 rounded transition">←</button>
                    @endif

                    @foreach(range(1, $this->persons->lastPage()) as $page)
                        @if($page >= $this->persons->currentPage() - 1 && $page <= $this->persons->currentPage() + 1)
                            @if($page == $this->persons->currentPage())
                                <span class="px-3 py-1.5 text-white bg-emerald-600 rounded">{{ $page }}</span>
                            @else
                                <button wire:click="gotoPage({{ $page }})" class="px-3 py-1.5 text-gray-400 bg-gray-800 hover:bg-gray-700 rounded transition">{{ $page }}</button>
                            @endif
                        @endif
                    @endforeach

                    @if($this->persons->hasMorePages())
                        <button wire:click="nextPage" class="px-3 py-1.5 text-gray-300 bg-gray-800 hover:bg-gray-700 rounded transition">→</button>
                    @else
                        <button disabled class="px-3 py-1.5 text-gray-600 bg-gray-800 rounded disabled:opacity-50">→</button>
                    @endif
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-16">
                <svg class="w-20 h-20 text-gray-800 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-gray-500 text-lg mb-1">هیچ شخصی یافت نشد</p>
                <p class="text-gray-600 text-sm">با تغییر فیلترها یا جستجو نتایج بهبود یافته</p>
            </div>
        @endif
    </div>

    {{-- ===== TOAST NOTIFICATIONS (event-driven) ===== --}}
    <div x-data="{ show: false, message: '' }"
         x-on:person-saved.window="show = true; message = 'معلومات با موفقیت ذخیره شد'; setTimeout(() => show = false, 3500)"
         x-on:person-deleted.window="show = true; message = 'شخص با موفقیت حذف شد'; setTimeout(() => show = false, 3500)"
         x-cloak>
        <div x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed bottom-6 right-6 bg-emerald-600 text-white px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 z-50 border border-emerald-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span x-text="message">موفق</span>
            <button class="ml-2 text-white/70 hover:text-white" @click="show = false">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

{{-- ===== MODAL: CREATE / EDIT ===== --}}
<div x-data="{ open: @entangle('modal_open') }"
     x-on:open-create-modal.window="open = true; modalType = 'create'"
     x-on:open-edit-modal.window="open = true; modalType = 'edit'"
     x-on:close-modal.window="open = false"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-effect="if(open) document.body.style.overflow = 'hidden'; else document.body.style.overflow = ''">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="open = false"></div>

    <div class="relative bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
            <h2 class="text-xl font-bold text-white">
                @if($modal_mode === 'create')
                    <svg class="w-6 h-6 text-emerald-400 inline mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    ثبت شخص جدید
                @else
                    <svg class="w-6 h-6 text-blue-400 inline mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    ویرایش شخص
                @endif
            </h2>
            <button @click="open = false" class="text-gray-500 hover:text-gray-300 transition p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="px-6 py-5 space-y-5">
            @if($processing_embedding)
                <div class="bg-yellow-900/30 border border-yellow-700/50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-5 h-5 border-2 border-yellow-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-yellow-400 text-sm">در حال استخراج امبدینگ چهره از موتور هوش مصنوعی... لطفاً صبر کنید</p>
                </div>
            @endif

            {{-- Full Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">نام کامل *</label>
                <input type="text"
                       wire:model="full_name"
                       class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                       placeholder="نام و نام خانوادگی را وارد کنید">
                @error('full_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- National Code --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">کد ملی *</label>
                <input type="text"
                       wire:model="national_code"
                       class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition font-mono"
                       placeholder="۱۰ رقم کد ملی">
                @error('national_code') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">تلفن</label>
                <input type="text"
                       wire:model="phone"
                       class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                       placeholder="شماره تلفن همراه">
            </div>

            {{-- Category --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">دسته‌بندی *</label>
                <div class="flex gap-2">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" wire:model="category" value="vip" class="peer hidden">
                        <div class="flex items-center justify-center gap-2 px-4 py-3 bg-gray-800 border-2 border-gray-700 rounded-lg peer-checked:border-yellow-400 peer-checked:bg-yellow-900/30 peer-checked:text-yellow-400 text-gray-400 transition hover:border-gray-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                            VIP
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" wire:model="category" value="employee" class="peer hidden">
                        <div class="flex items-center justify-center gap-2 px-4 py-3 bg-gray-800 border-2 border-gray-700 rounded-lg peer-checked:border-emerald-500 peer-checked:bg-emerald-900/30 peer-checked:text-emerald-400 text-gray-400 transition hover:border-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            کارمند
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" wire:model="category" value="guest" class="peer hidden">
                        <div class="flex items-center justify-center gap-2 px-4 py-3 bg-gray-800 border-2 border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-900/30 peer-checked:text-blue-400 text-gray-400 transition hover:border-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            مهمان
                        </div>
                    </label>
                </div>
                @error('category') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Photo Upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">عکس چهره</label>
                <div class="flex gap-4">
                    <div class="flex-1">
                        <div class="border-2 border-dashed border-gray-700 rounded-xl p-4 text-center hover:border-emerald-600 transition cursor-pointer"
                             x-on:click="$refs.photoInput.click()">
                            @if($photo_preview)
                                <img src="{{ $photo_preview }}" alt="Preview" class="w-full h-32 object-cover rounded-lg mb-2 border border-gray-700">
                            @else
                                <svg class="w-10 h-10 mx-auto text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-gray-500 text-sm">عکس را آپلود کنید</p>
                            @endif
                            <input type="file" accept="image/*" class="hidden" wire:model="photo_file" x-ref="photoInput">
                        </div>
                    </div>
                    @if($photo_preview)
                        <button type="button" wire:click="photo_file = null; photo_path = null; photo_preview = null;" class="px-3 py-2 bg-red-900/30 border border-red-800 rounded-lg text-red-400 hover:text-red-300 transition text-sm">
                            حذف
                        </button>
                    @endif
                </div>
                @error('photo_file') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Address --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">آدرس</label>
                <textarea wire:model="address" rows="2"
                          class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition resize-none"
                          placeholder="آدرس سکونت"></textarea>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">توضیحات</label>
                <textarea wire:model="description" rows="3"
                          class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition resize-none"
                          placeholder="توضیحات اضافی درباره این شخص"></textarea>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-4 border-t border-gray-800 flex justify-end gap-3">
            <button @click="open = false"
                    class="px-5 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg transition">
                إلغاء
            </button>
            <button wire:click="savePerson"
                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition flex items-center gap-2 shadow-lg shadow-emerald-900/30 disabled:opacity-50 disabled:cursor-not-allowed">
                @if($processing_embedding)
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                @endif
                <span>ذخیره</span>
            </button>
        </div>
    </div>
</div>
