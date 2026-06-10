<aside 
    x-init="
        if (window.innerWidth >= 1024 && window.innerWidth < 1440 && localStorage.getItem('sidebarCollapsed') === null) {
            sidebarCollapsed = true;
        }
        window.addEventListener('resize', () => {
            if (window.innerWidth < 1280 && window.innerWidth >= 1024 && !sidebarCollapsed) {
                // sidebarCollapsed = true; // Optional: auto-collapse on resize
            }
        });
    "
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        sidebarCollapsed ? 'md:w-20' : 'md:w-64 lg:w-72 xl:w-64'
    ]" 
    class="fixed inset-y-0 left-0 z-30 bg-primary-900 dark:bg-black text-white flex flex-col shadow-xl transition-all duration-300 md:relative md:translate-x-0 overflow-x-hidden"
    aria-label="Sidebar Navigation"
>
    <!-- Logo -->
    <div class="px-6 py-6 border-b border-primary-800 dark:border-gray-800 bg-primary-950/50 dark:bg-gray-900/50 flex items-center justify-center min-h-[97px]">
        <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'space-x-3 w-full'">
            <div class="h-12 w-12 bg-white rounded-xl shadow-lg flex items-center justify-center p-1 overflow-hidden shrink-0">
                <img src="{{ $appearance->logo_path ? asset('storage/' . $appearance->logo_path) : asset('images/logo-kotak.png') }}" alt="SA1 Logo" class="h-full object-contain" 
                     onerror="this.parentElement.innerHTML = '<span class=\'text-primary-700 font-bold text-xs text-center\'>SA-1</span>'">
            </div>
            <div x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">
                @if($appearance->app_name === 'Sistem Terpadu SMA ISA 1')
                    <h1 class="text-sm font-extrabold text-white leading-tight uppercase tracking-tight">SMA Islam</h1>
                    <p class="text-[10px] font-semibold text-primary-300 tracking-widest uppercase">Sultan Agung 1</p>
                @else
                    <h1 class="text-sm font-extrabold text-white leading-tight uppercase tracking-tight overflow-hidden text-ellipsis max-w-[150px]" title="{{ $appearance->app_name }}">
                        {{ Str::limit($appearance->app_name, 20) }}
                    </h1>
                @endif
            </div>
        </div>
    </div>
    <!-- Menu -->
    <nav 
        id="sidebar-nav" 
        class="flex-1 overflow-y-auto py-4 px-3 space-y-1 overflow-x-hidden"
        @scroll.debounce.100ms="sessionStorage.setItem('sidebar-scroll', $el.scrollTop)"
        x-init="setTimeout(() => { $el.scrollTop = sessionStorage.getItem('sidebar-scroll') || 0 }, 100)"
    >
        {{-- Helper component for links --}}
        @php
            $linkClass = "block px-3 py-2.5 rounded-lg font-medium transition-colors relative group sidebar-item ";
            $activeClass = "bg-primary-800 text-white shadow-sm border border-primary-700/50";
            $inactiveClass = "text-primary-100 hover:bg-primary-800/60 hover:text-white";
        @endphp

        <a href="{{ route('dashboard') }}" class="{{ $linkClass }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.dashboard') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.dashboard') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.dashboard') }}</div>
        </a>

        <a href="{{ route('calendar.index') }}" class="{{ $linkClass }} {{ request()->routeIs('calendar.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.calendar') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.calendar') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.calendar') }}</div>
        </a>

        <a href="{{ auth()->user()->role === 'student' ? route('gamification.my-progress') : route('gamification.leaderboard') }}" class="{{ $linkClass }} {{ request()->routeIs('gamification.*') ? $activeClass : $inactiveClass }}" title="Pusat Gamifikasi">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.375m-1.5 5.625v-3.375c0-.621.504-1.125 1.125-1.125h.375m-1.5 1.5H9m3-9h.008v.008H12V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM12 15.75h.007v.008H12v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM9.75 4.5h4.5M9.75 4.5h-.75M14.25 4.5h.75M9 4.5h.75m4.5 0h-.75m-4.5 0v10.5m4.5-10.5v10.5M4.5 7.875a3.375 3.375 0 003.375 3.375h.75V4.5h-.75A3.375 3.375 0 004.5 7.875zm15 0a3.375 3.375 0 01-3.375 3.375h-.75V4.5h.75a3.375 3.375 0 013.375 3.375z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Pusat Gamifikasi</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">Pusat Gamifikasi</div>
        </a>

        @if(!in_array(auth()->user()->role, ['student', 'parent']))
        <a href="{{ route('teaching-journals.index') }}" class="{{ $linkClass }} {{ request()->routeIs('teaching-journals.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.journal') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.journal') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.journal') }}</div>
        </a>

        <a href="{{ route('meeting-notes.index') }}" class="{{ $linkClass }} {{ request()->routeIs('meeting-notes.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.meeting_notes') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.meeting_notes') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.meeting_notes') }}</div>
        </a>
        @endif

        @if(in_array(auth()->user()->role, ['guru', 'waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin']))
        <a href="{{ route('curriculum.index') }}" class="{{ $linkClass }} {{ request()->routeIs('curriculum.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.curriculum') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.331 0 4.476.884 6.084 2.34M12 6.042A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18c-2.331 0-4.476.884-6.084 2.34"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.curriculum') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.curriculum') }}</div>
        </a>
        @endif

        @if(in_array(auth()->user()->role, ['waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin']))
        <a href="{{ route('eds.index') }}" class="{{ $linkClass }} {{ request()->routeIs('eds.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.eds') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.eds') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.eds') }}</div>
        </a>
        @endif
        
        <a href="{{ route('my-hrd.portal') }}" class="{{ $linkClass }} {{ request()->routeIs('my-hrd.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.hrd_portal') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.hrd_portal') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.hrd_portal') }}</div>
        </a>

        <a href="{{ route('helpdesk.tickets.index') }}" class="{{ $linkClass }} {{ request()->routeIs('helpdesk.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.helpdesk') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.helpdesk') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.helpdesk') }}</div>
        </a>

        @if(!in_array(auth()->user()->role, ['student', 'parent']))
        <a href="{{ route('assets.loans.index') }}" class="{{ $linkClass }} {{ request()->routeIs('assets.loans.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.asset_loans') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.asset_loans') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.asset_loans') }}</div>
        </a>
        @endif

        @if(auth()->user()->isAdmin() || auth()->user()->hasRole('kepala_sekolah'))
        <a href="{{ route('announcements.index') }}" class="{{ $linkClass }} {{ request()->routeIs('announcements.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.announcements') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.announcements') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.announcements') }}</div>
        </a>
        <a href="{{ route('assets.index') }}" class="{{ $linkClass }} {{ request()->routeIs('assets.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.assets') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.assets') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.assets') }}</div>
        </a>
        @endif
        
        {{-- Master Data: Hanya Admin & Kepsek --}}
        @if(auth()->user()->isAdmin() || auth()->user()->hasRole('kepala_sekolah'))
        <div class="pt-4 pb-2" x-show="!sidebarCollapsed">
            <p class="px-3 text-xs font-semibold text-primary-300 uppercase tracking-wider whitespace-nowrap">{{ __('navigation.header_master_data') }}</p>
        </div>
        <div class="pt-4 pb-2 text-center" x-show="sidebarCollapsed" style="display:none;">
            <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest" title="{{ __('navigation.header_master_data') }}">•</p>
        </div>
        @endif

        {{-- Keislaman (Tahfidz & Mutaba'ah) --}}
        <div class="pt-4 pb-2" x-show="!sidebarCollapsed">
            <p class="px-3 text-xs font-semibold text-primary-300 uppercase tracking-wider whitespace-nowrap">{{ __('navigation.header_islamic') }}</p>
        </div>
        <div class="pt-4 pb-2 text-center" x-show="sidebarCollapsed" style="display:none;">
            <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest" title="{{ __('navigation.header_islamic') }}">•</p>
        </div>
        
        {{-- Tahfidz Redirect Logic --}}
        @php
            $tahfidzRoute = '#';
            if(auth()->user()->role === 'student' && auth()->user()->student) {
                $tahfidzRoute = route('tahfidz.show', auth()->user()->student->id);
            } elseif(auth()->user()->role === 'parent' && auth()->user()->children->first()) {
                $tahfidzRoute = route('tahfidz.show', auth()->user()->children->first()->id);
            } elseif(in_array(auth()->user()->role, ['admin', 'superadmin', 'guru', 'bk', 'kepala_sekolah', 'staf_tu'])) {
                $firstStudent = \App\Models\Student::first();
                $tahfidzRoute = $firstStudent ? route('tahfidz.show', $firstStudent->id) : route('students.index');
            }
        @endphp

        <a href="{{ $tahfidzRoute }}" class="{{ $linkClass }} {{ request()->routeIs('tahfidz.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.tahfidz') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 19 7.5 19s3.332-.477 4.5-1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 19 16.5 19c-1.746 0-3.332-.477-4.5-1.253"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.tahfidz') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.tahfidz') }}</div>
        </a>

        <a href="{{ route('mutabaah.index') }}" class="{{ $linkClass }} {{ request()->routeIs('mutabaah.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.mutabaah') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.mutabaah') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.mutabaah') }}</div>
        </a>

        <a href="{{ route('event-attendance.index') }}" class="{{ $linkClass }} {{ request()->routeIs('event-attendance.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.event_attendance') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.event_attendance') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.event_attendance') }}</div>
        </a>

        <div class="pt-4 pb-2" x-show="!sidebarCollapsed">
            <p class="px-3 text-xs font-semibold text-primary-300 uppercase tracking-wider whitespace-nowrap">{{ __('navigation.header_personnel') }}</p>
        </div>
        <div class="pt-4 pb-2 text-center" x-show="sidebarCollapsed" style="display:none;">
            <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest" title="{{ __('navigation.header_personnel') }}">•</p>
        </div>
        @if(auth()->user()->isAdmin() || auth()->user()->hasRole('kepala_sekolah'))
        <a href="{{ route('users.index') }}" class="{{ $linkClass }} {{ request()->routeIs('users.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.users') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.users') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.users') }}</div>
        </a>
        <a href="{{ route('admin.logs') }}" class="{{ $linkClass }} {{ request()->routeIs('admin.logs') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.audit_logs') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.audit_logs') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.audit_logs') }}</div>
        </a>
        <a href="{{ route('admin.settings.index') }}" class="{{ $linkClass }} {{ request()->routeIs('admin.settings.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.system_settings') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.system_settings') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.system_settings') }}</div>
        </a>

        <a href="{{ route('admin.chatbot.faqs') }}" class="{{ $linkClass }} {{ request()->routeIs('admin.chatbot.*') ? $activeClass : $inactiveClass }}" title="Kelola FAQ Chatbot">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Kelola FAQ Chatbot</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">Kelola FAQ Chatbot</div>
        </a>

        @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'staf_tu']))
        <a href="{{ route('import-export.index') }}" class="{{ $linkClass }} {{ request()->routeIs('import-export.*') ? $activeClass : $inactiveClass }}" title="Import / Export Hub">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Import/Export Hub</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">Import/Export Hub</div>
        </a>

        <a href="{{ route('admin.scheduled-reports.index') }}" class="{{ $linkClass }} {{ request()->routeIs('admin.scheduled-reports.*') ? $activeClass : $inactiveClass }}" title="Laporan Terjadwal">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Laporan Terjadwal</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">Laporan Terjadwal</div>
        </a>

        <a href="{{ route('admin.integrations.index') }}" class="{{ $linkClass }} {{ request()->routeIs('admin.integrations.*') ? $activeClass : $inactiveClass }}" title="Integrasi Pemerintah">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Integrasi Pemerintah</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">Integrasi Pemerintah</div>
        </a>

        <a href="{{ route('admin.appearance.index') }}" class="{{ $linkClass }} {{ request()->routeIs('admin.appearance.*') ? $activeClass : $inactiveClass }}" title="Kustomisasi Tampilan">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-3.012 3.012m11-3.012a3 3 0 013.012 3.012M9.53 16.122a3 3 0 11-6.024 0M17.518 16.122a3 3 0 106.024 0M12 3v9"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21H5a2 2 0 01-2-2V9a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Kustomisasi Tampilan</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">Kustomisasi Tampilan</div>
        </a>
        @endif
        @endif
        
        {{-- Modul Akademik: Admin, Kepsek, Waka Kurikulum, Staf TU --}}
        @if((auth()->user()->isAdmin() || auth()->user()->hasRole('staf_tu')) && (auth()->user()->role != 'waka_sarpras' && auth()->user()->role != 'waka_humas' && auth()->user()->role != 'waka_kesiswaan'))
        <div class="pt-4 pb-2" x-show="!sidebarCollapsed">
            <p class="px-3 text-xs font-semibold text-primary-300 uppercase tracking-wider whitespace-nowrap">{{ __('navigation.header_academic') }}</p>
        </div>
        <div class="pt-4 pb-2 text-center" x-show="sidebarCollapsed" style="display:none;">
            <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest" title="{{ __('navigation.header_academic') }}">•</p>
        </div>
        <a href="{{ route('academic-years.index') }}" class="{{ $linkClass }} {{ request()->routeIs('academic-years.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.academic_years') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.academic_years') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.academic_years') }}</div>
        </a>
        <a href="{{ route('subjects.index') }}" class="{{ $linkClass }} {{ request()->routeIs('subjects.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.subjects') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.subjects') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.subjects') }}</div>
        </a>
        <a href="{{ route('classrooms.index') }}" class="{{ $linkClass }} {{ request()->routeIs('classrooms.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.classrooms') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.classrooms') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.classrooms') }}</div>
        </a>
        <a href="{{ route('students.index') }}" class="{{ $linkClass }} {{ request()->routeIs('students.index') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.students') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.students') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.students') }}</div>
        </a>
        <a href="{{ route('mutations.index') }}" class="{{ $linkClass }} {{ request()->routeIs('mutations.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.student_mutations') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.student_mutations') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.student_mutations') }}</div>
        </a>
        <a href="{{ route('alumni.index') }}" class="{{ $linkClass }} {{ request()->routeIs('alumni.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.alumni') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.alumni') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.alumni') }}</div>
        </a>

        <a href="{{ route('schedules.grid') }}" class="{{ $linkClass }} {{ request()->routeIs('schedules.grid') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.schedule_plotting') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.schedule_plotting') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.schedule_plotting') }}</div>
        </a>

        <a href="{{ route('schedules.presets.index') }}" class="{{ $linkClass }} {{ request()->routeIs('schedules.presets.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.schedule_presets') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.schedule_presets') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.schedule_presets') }}</div>
        </a>

        <a href="{{ route('schedules.teacher') }}" class="{{ $linkClass }} {{ request()->routeIs('schedules.teacher') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.schedule_teacher') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.schedule_teacher') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.schedule_teacher') }}</div>
        </a>

        <a href="{{ route('schedules.room') }}" class="{{ $linkClass }} {{ request()->routeIs('schedules.room') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.schedule_room') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.schedule_room') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.schedule_room') }}</div>
        </a>

        <a href="{{ route('schedules.conflicts') }}" class="{{ $linkClass }} {{ request()->routeIs('schedules.conflicts') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.schedule_conflicts') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.schedule_conflicts') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.schedule_conflicts') }}</div>
        </a>
        @endif

        {{-- Modul Kesiswaan: Admin, Waka Kesiswaan, BK, Guru, Staf TU --}}
        @if(in_array(auth()->user()->role, ['waka_kesiswaan', 'bk', 'admin', 'superadmin', 'guru', 'staf_tu']))
        <div class="pt-4 pb-2" x-show="!sidebarCollapsed">
            <p class="px-3 text-xs font-semibold text-primary-300 uppercase tracking-wider whitespace-nowrap">{{ __('navigation.header_student') }}</p>
        </div>
        <div class="pt-4 pb-2 text-center" x-show="sidebarCollapsed" style="display:none;">
            <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest" title="{{ __('navigation.header_student') }}">•</p>
        </div>
        
        @if(auth()->user()->hasRole('waka_kesiswaan') || auth()->user()->hasRole('bk') || auth()->user()->isAdmin())
        <a href="{{ route('points.index') }}" class="{{ $linkClass }} {{ request()->routeIs('points.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.points') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.points') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.points') }}</div>
        </a>
        <a href="{{ route('point-categories.index') }}" class="{{ $linkClass }} {{ request()->routeIs('point-categories.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.point_categories') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.point_categories') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.point_categories') }}</div>
        </a>
        @endif

        {{-- UKS Digital --}}
        <a href="{{ route('uks.dashboard') }}" class="{{ $linkClass }} {{ request()->routeIs('uks.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.uks') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.uks') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.uks') }}</div>
        </a>

        {{-- Kelola Ekskul --}}
        @if(in_array(auth()->user()->role, ['guru', 'bk', 'waka_kesiswaan', 'admin', 'superadmin']))
        <a href="{{ route('ekskul.manage') }}" class="{{ $linkClass }} {{ request()->routeIs('ekskul.manage') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.ekskul') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.ekskul') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.ekskul') }}</div>
        </a>
        @endif

        {{-- Keuangan OSIS --}}
        @if(in_array(auth()->user()->role, ['waka_kesiswaan', 'admin', 'superadmin']))
        <a href="{{ route('osis.dashboard') }}" class="{{ $linkClass }} {{ request()->routeIs('osis.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.osis') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1m-10-6h3m12 0h3"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.osis') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.osis') }}</div>
        </a>
        @endif
        @endif

        {{-- Lembaga MPK --}}
        @if(in_array(auth()->user()->role, ['waka_kesiswaan', 'admin', 'superadmin', 'kepala_sekolah', 'guru', 'student']))
        @php
            $mpkRoute = in_array(auth()->user()->role, ['waka_kesiswaan', 'admin', 'superadmin', 'kepala_sekolah', 'guru']) 
                ? route('mpk.index') 
                : route('mpk.aspirations.index');
        @endphp
        <a href="{{ $mpkRoute }}" class="{{ $linkClass }} {{ request()->routeIs('mpk.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.mpk') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.mpk') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.mpk') }}</div>
        </a>
        @endif

        {{-- Modul Sarpras: Admin, Waka Sarpras, Kepala Sekolah --}}
        @if(auth()->user()->hasRole('waka_sarpras') || auth()->user()->isAdmin() || auth()->user()->hasRole('kepala_sekolah'))
        <div class="pt-4 pb-2" x-show="!sidebarCollapsed">
            <p class="px-3 text-xs font-semibold text-primary-300 uppercase tracking-wider whitespace-nowrap">{{ __('navigation.header_sarpras') }}</p>
        </div>
        <div class="pt-4 pb-2 text-center" x-show="sidebarCollapsed" style="display:none;">
            <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest" title="{{ __('navigation.header_sarpras') }}">•</p>
        </div>
        @if(auth()->user()->hasRole('waka_sarpras') || auth()->user()->isAdmin())
        <a href="{{ route('assets.index') }}" class="{{ $linkClass }} {{ request()->routeIs('assets.index') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.inventaris_assets') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.inventaris_assets') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.inventaris_assets') }}</div>
        </a>
        @endif
        <a href="{{ route('assets.maintenances.index') }}" class="{{ $linkClass }} {{ request()->routeIs('assets.maintenances.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.assets_maintenance') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.assets_maintenance') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.assets_maintenance') }}</div>
        </a>
        <a href="{{ route('assets.depreciation') }}" class="{{ $linkClass }} {{ request()->routeIs('assets.depreciation') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.assets_depreciation') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.assets_depreciation') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.assets_depreciation') }}</div>
        </a>
        @endif

        {{-- Modul Humas: Admin, Waka Humas --}}
        @if(auth()->user()->hasRole('waka_humas') || auth()->user()->isAdmin())
        <div class="pt-4 pb-2" x-show="!sidebarCollapsed">
            <p class="px-3 text-xs font-semibold text-primary-300 uppercase tracking-wider whitespace-nowrap">{{ __('navigation.header_humas') }}</p>
        </div>
        <div class="pt-4 pb-2 text-center" x-show="sidebarCollapsed" style="display:none;">
            <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest" title="{{ __('navigation.header_humas') }}">•</p>
        </div>
        <a href="{{ route('announcements.index') }}" class="{{ $linkClass }} {{ request()->routeIs('announcements.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.news_announcements') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.news_announcements') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.news_announcements') }}</div>
        </a>
        @endif

        {{-- Modul Administrasi: Admin, Staf TU --}}
        @if(auth()->user()->hasRole('staf_tu') || auth()->user()->isAdmin())
        <div class="pt-4 pb-2" x-show="!sidebarCollapsed">
            <p class="px-3 text-xs font-semibold text-primary-300 uppercase tracking-wider whitespace-nowrap">{{ __('navigation.header_administration') }}</p>
        </div>
        <div class="pt-4 pb-2 text-center" x-show="sidebarCollapsed" style="display:none;">
            <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest" title="{{ __('navigation.header_administration') }}">•</p>
        </div>
        <a href="{{ route('finance.billings.index') }}" class="{{ $linkClass }} {{ request()->routeIs('finance.billings.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.finance_administration') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.finance_administration') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.finance_administration') }}</div>
        </a>
        @if(auth()->user()->isAdmin() || auth()->user()->hasRole('kepala_sekolah'))
        <a href="{{ route('finance.reports.executive') }}" class="{{ $linkClass }} {{ request()->routeIs('finance.reports.executive') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.finance_analysis') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.finance_analysis') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.finance_analysis') }}</div>
        </a>
        @endif

        @if(in_array(auth()->user()->role, ['kepala_sekolah', 'admin', 'superadmin']))
        <a href="{{ route('analytics.index') }}" class="{{ $linkClass }} {{ request()->routeIs('analytics.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.school_analytics') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.school_analytics') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.school_analytics') }}</div>
        </a>
        @endif

        @if(in_array(auth()->user()->role, ['superadmin', 'admin', 'staf_tu', 'kepala_sekolah']))
        <a href="{{ route('letters.index') }}" class="{{ $linkClass }} {{ request()->routeIs('letters.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.digital_letters') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l8-5.333a2 2 0 012.22 0l8 5.333A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-2.25-1.5a2 2 0 00-2.22 0l-2.25 1.5"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.digital_letters') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.digital_letters') }}</div>
        </a>
        @endif
        @endif

        {{-- Modul HRD: Admin, Superadmin, Kepala Sekolah --}}
        @if(auth()->user()->isAdmin() || auth()->user()->hasRole('kepala_sekolah'))
        <div class="pt-4 pb-2" x-show="!sidebarCollapsed">
            <p class="px-3 text-xs font-semibold text-primary-300 uppercase tracking-wider whitespace-nowrap">{{ __('navigation.header_staff') }}</p>
        </div>
        <div class="pt-4 pb-2 text-center" x-show="sidebarCollapsed" style="display:none;">
            <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest" title="{{ __('navigation.header_staff') }}">•</p>
        </div>
        <a href="{{ route('hrd.employees.index') }}" class="{{ $linkClass }} {{ request()->routeIs('hrd.employees.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.employee_directory') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.employee_directory') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.employee_directory') }}</div>
        </a>
        @endif

        {{-- Modul Guru: Guru, Admin --}}
        @if(auth()->user()->hasRole('guru') || auth()->user()->isAdmin())
        <div class="pt-4 pb-2" x-show="!sidebarCollapsed">
            <p class="px-3 text-xs font-semibold text-primary-300 uppercase tracking-wider whitespace-nowrap">{{ __('navigation.header_teacher') }}</p>
        </div>
        <div class="pt-4 pb-2 text-center" x-show="sidebarCollapsed" style="display:none;">
            <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest" title="{{ __('navigation.header_teacher') }}">•</p>
        </div>
        <a href="{{ route('attendances.create') }}" class="{{ $linkClass }} {{ request()->routeIs('attendances.create') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.student_attendance') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.student_attendance') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.student_attendance') }}</div>
        </a>
        <a href="{{ route('attendances.index') }}" class="{{ $linkClass }} {{ request()->routeIs('attendances.index') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.attendance_recap') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.attendance_recap') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.attendance_recap') }}</div>
        </a>
        <a href="{{ route('grades.create') }}" class="{{ $linkClass }} {{ request()->routeIs('grades.create') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.grade_input') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.grade_input') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.grade_input') }}</div>
        </a>
        <a href="{{ route('grades.index') }}" class="{{ $linkClass }} {{ request()->routeIs('grades.index') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.grade_recap') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.grade_recap') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.grade_recap') }}</div>
        </a>
        <a href="{{ route('daily-assessments.index') }}" class="{{ $linkClass }} {{ request()->routeIs('daily-assessments.*') && !request()->routeIs('daily-assessments.remedial') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.daily_assessments') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.daily_assessments') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.daily_assessments') }}</div>
        </a>
        <a href="{{ route('daily-assessments.remedial') }}" class="{{ $linkClass }} {{ request()->routeIs('daily-assessments.remedial') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.remedial_list') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.remedial_list') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.remedial_list') }}</div>
        </a>
        <a href="{{ route('report-cards.index') }}" class="{{ $linkClass }} {{ request()->routeIs('report-cards.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.ledger_report') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 19 7.5 19s3.332-.477 4.5-1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 19 16.5 19c-1.746 0-3.332-.477-4.5-1.253"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.ledger_report') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.ledger_report') }}</div>
        </a>
        <a href="{{ route('todos.index') }}" class="{{ $linkClass }} {{ request()->routeIs('todos.*') ? $activeClass : $inactiveClass }}" title="Agenda & Todo List">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">Agenda & Todo</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">Agenda & Todo</div>
        </a>
        @endif

        {{-- Ekstrakurikuler --}}
        <a href="{{ route('ekskul.index') }}" class="{{ $linkClass }} {{ request()->routeIs('ekskul.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.ekskul') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.ekskul') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.ekskul') }}</div>
        </a>

        {{-- Smart Library --}}
        <div class="mt-8 mb-2 px-3" x-show="!sidebarCollapsed">
            <p class="text-[10px] font-black text-primary-300 uppercase tracking-[0.2em] opacity-50 whitespace-nowrap">{{ __('navigation.header_learning_resources') }}</p>
        </div>
        <div class="mt-8 mb-2 text-center" x-show="sidebarCollapsed" style="display:none;">
            <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest opacity-50" title="{{ __('navigation.header_learning_resources') }}">•</p>
        </div>
        <a href="{{ route('library.index') }}" class="{{ $linkClass }} {{ request()->routeIs('library.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.library') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.library') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.library') }}</div>
        </a>

        {{-- E-Learning Ringan --}}
        <a href="{{ route('elearning.index') }}" class="{{ $linkClass }} {{ request()->routeIs('elearning.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.e_learning') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.e_learning') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.e_learning') }}</div>
        </a>

        {{-- Ujian Online CBT --}}
        <a href="{{ route('cbt.exams.index') }}" class="{{ $linkClass }} {{ request()->routeIs('cbt.exams.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.cbt') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.cbt') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.cbt') }}</div>
        </a>

        {{-- Forum Diskusi --}}
        <a href="{{ route('forum.index') }}" class="{{ $linkClass }} {{ request()->routeIs('forum.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.forum') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.forum') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.forum') }}</div>
        </a>

        {{-- Survei Digital --}}
        <a href="{{ route('surveys.index') }}" class="{{ $linkClass }} {{ request()->routeIs('surveys.*') ? $activeClass : $inactiveClass }}" title="{{ __('navigation.surveys') }}">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ __('navigation.surveys') }}</span>
            </div>
            <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ __('navigation.surveys') }}</div>
        </a>

    </nav>
</aside>
