<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Hamburger for Sidebar -->
                <button @click="sidebarOpen = !sidebarOpen" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out mr-2" aria-label="Buka Sidebar Mobile">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Desktop Sidebar Toggle -->
                <button @click="toggleSidebarCollapse()" class="hidden md:inline-flex items-center justify-center p-2 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800 focus:outline-none transition-all duration-200 mr-4" aria-label="Toggle Lebar Sidebar">
                    <svg class="h-6 w-6 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 6h8M14 12h8M14 18h8" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l-7 7 7 7" />
                    </svg>
                </button>

                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('navigation.dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Notifications & Theme Toggle & Settings Dropdown -->
            <div class="flex items-center ms-6 space-x-2 sm:space-x-4">
                <!-- Notification Bell -->
                <div class="relative" x-data="{ 
                    open: false,
                    unreadCount: {{ auth()->user()->unreadNotifications->count() }},
                    notifications: {{ json_encode(auth()->user()->unreadNotifications->take(5)->map(fn($n) => ['id' => $n->id, 'message' => $n->data['message'] ?? 'Notifikasi baru', 'time' => $n->created_at->diffForHumans()])) }},
                    init() {
                        if (window.Echo) {
                            window.Echo.private('user.{{ auth()->id() }}')
                                .listen('.new-notification', (e) => {
                                    this.unreadCount++;
                                    this.notifications.unshift({
                                        id: e.notification.id || Math.random(),
                                        message: e.notification.message,
                                        time: 'Baru saja'
                                    });
                                    // Show custom toast if dispatchEvent / Alpine toast exists
                                    window.dispatchEvent(new CustomEvent('toast', { 
                                        detail: { 
                                            title: '🔔 Notifikasi Baru', 
                                            message: e.notification.message, 
                                            type: 'info' 
                                        } 
                                    }));
                                });
                        }
                    }
                }">
                    <button @click="open = !open" :class="unreadCount > 0 ? 'notif-shake' : ''" class="p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 relative" aria-label="Buka Notifikasi">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span x-show="unreadCount > 0" class="absolute top-2 right-2 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white dark:ring-gray-800" style="display: none;"></span>
                    </button>

                    <!-- Dropdown Panel -->
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 z-50 overflow-hidden" style="display:none">
                        <div class="p-4 border-b border-gray-50 dark:border-gray-700 flex justify-between items-center">
                            <h3 class="font-black text-gray-800 dark:text-gray-100 text-sm uppercase tracking-widest">Notifikasi</h3>
                            <a href="#" x-show="unreadCount > 0" onclick="event.preventDefault(); document.getElementById('mark-all-read-form').submit();" class="text-[10px] font-bold text-primary-600 hover:underline uppercase" style="display: none;">Tandai semua dibaca</a>
                            <form id="mark-all-read-form" action="{{ route('notifications.markAllRead') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <template x-for="notification in notifications" :key="notification.id">
                                <a :href="'/notifications/read/' + notification.id" class="block p-4 border-b border-gray-50 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                    <p class="text-xs text-gray-800 dark:text-gray-200 font-medium" x-text="notification.message"></p>
                                    <p class="text-[10px] text-gray-400 mt-1" x-text="notification.time"></p>
                                </a>
                            </template>
                            <div x-show="notifications.length === 0" class="p-10 text-center">
                                <svg class="w-10 h-10 text-gray-200 dark:text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-xs text-gray-400 italic">Tidak ada notifikasi baru.</p>
                            </div>
                        </div>
                        <a href="{{ route('notifications.index') }}" class="block p-3 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest border-t border-gray-50 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">Lihat Semua</a>
                    </div>
                </div>

                <!-- Language Switcher Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 flex items-center space-x-1" 
                            title="{{ __('navigation.language') }}"
                            aria-label="Pilih Bahasa">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 2.16-.452 4.28-1.313 6.25L9.302 6.83a17.965 17.965 0 002.39-2.203H12.75z"></path>
                        </svg>
                        <span class="text-xs font-bold uppercase tracking-wider hidden md:inline-block">
                            {{ app()->getLocale() }}
                        </span>
                    </button>
                    <!-- Dropdown Panel -->
                    <div x-show="open" @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 z-50 overflow-hidden" 
                         style="display:none">
                        <div class="p-1.5 space-y-1">
                            <form action="{{ route('preferences.locale', 'id') }}" method="POST" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-between {{ app()->getLocale() === 'id' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-900' }}">
                                    <span>🇮🇩 Indonesia</span>
                                    @if(app()->getLocale() === 'id')
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </button>
                            </form>
                            <form action="{{ route('preferences.locale', 'en') }}" method="POST" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-between {{ app()->getLocale() === 'en' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-900' }}">
                                    <span>🇺🇸 English</span>
                                    @if(app()->getLocale() === 'en')
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </button>
                            </form>
                            <form action="{{ route('preferences.locale', 'ar') }}" method="POST" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-between {{ app()->getLocale() === 'ar' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-900' }}">
                                    <span>🇸🇦 العربية</span>
                                    @if(app()->getLocale() === 'ar')
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Theme Toggle (Plain JS + Alpine for Icon) -->
                <button 
                    onclick="toggleTheme()"
                    x-data="{ isDark: document.documentElement.classList.contains('dark') }"
                    @theme-changed.window="isDark = $event.detail"
                    class="p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                    title="Toggle Dark Mode"
                    aria-label="Toggle Dark Mode"
                >
                    <!-- Sun Icon (visible in dark) -->
                    <svg x-show="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.243 16.243l.707.707M7.757 7.757l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                    </svg>
                    <!-- Moon Icon (visible in light) -->
                    <svg x-show="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition ease-in-out duration-150">
                            <div class="hidden sm:block text-gray-700 dark:text-gray-300">{{ Auth::user()->name }}</div>
                            <div class="sm:hidden">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>

                            <div class="ms-1 hidden sm:block">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('navigation.dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
