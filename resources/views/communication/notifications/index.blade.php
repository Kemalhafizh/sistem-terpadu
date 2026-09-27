<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </span>
                    Unified Notification Center & Enterprise Inbox
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pusat pesan dan pengumuman terpadu lintas sistem {{ school_name() }}.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('notifications.preferences') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Pengaturan Preferensi Saluran
                </a>

                @if($unreadCount > 0)
                    <form action="{{ route('notifications.read_all') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Tandai Semua Dibaca ({{ $unreadCount }})
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Category Filter Tabs -->
            <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-4">
                <a href="{{ route('notifications.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ empty($category) && ! $unreadOnly ? 'bg-indigo-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                    Semua Pesan
                </a>
                <a href="{{ route('notifications.index', ['unread_only' => 1]) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $unreadOnly ? 'bg-indigo-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                    Belum Dibaca ({{ $unreadCount }})
                </a>
                <a href="{{ route('notifications.index', ['category' => 'academic']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $category === 'academic' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                    Akademik
                </a>
                <a href="{{ route('notifications.index', ['category' => 'finance']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $category === 'finance' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                    Keuangan
                </a>
                <a href="{{ route('notifications.index', ['category' => 'discipline']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $category === 'discipline' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                    Kedisiplinan & BK
                </a>
                <a href="{{ route('notifications.index', ['category' => 'system']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $category === 'system' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100' }}">
                    Sistem & Keamanan
                </a>
            </div>

            <!-- Notifications List Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($notifications as $notif)
                    <div class="p-6 transition-colors flex flex-col md:flex-row items-start justify-between gap-4 {{ $notif->isRead() ? 'bg-white dark:bg-gray-800 opacity-80' : 'bg-indigo-50/40 dark:bg-indigo-950/20' }}">
                        <div class="flex items-start gap-4">
                            <div class="p-3 rounded-2xl {{ $notif->isRead() ? 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' : 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded font-mono text-[10px] font-bold uppercase bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-gray-300">
                                        {{ $notif->event_category }}
                                    </span>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $notif->title }}</h4>
                                    @if(! $notif->isRead())
                                        <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $notif->body }}</p>
                                <div class="flex items-center gap-4 text-[11px] font-mono text-gray-400 pt-1">
                                    <span>Waktu: {{ $notif->created_at->format('d M Y H:i') }}</span>
                                    <span>Saluran: {{ strtoupper($notif->channel) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end md:self-center">
                            @if($notif->action_url)
                                <a href="{{ $notif->action_url }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                    Buka Tautan
                                </a>
                            @endif

                            @if(! $notif->isRead())
                                <form action="{{ route('notifications.read', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-bold transition-all">
                                        Tandai Dibaca
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-gray-400 italic space-y-3">
                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-sm font-medium">Kotak masuk notifikasi Anda kosong.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $notifications->links() }}
            </div>

            <!-- Admin Test Notification Trigger Card -->
            @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin')
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                    <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Uji Coba Pancaran Notifikasi Terpadu (Admin Panel)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pancarkan notifikasi simulasi untuk menguji sistem multi-channel delivery.</p>
                    </div>

                    <form action="{{ route('notifications.test_dispatch') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe Event</label>
                            <select name="event_type" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                                <option value="academic.grade_published">academic.grade_published (Akademik)</option>
                                <option value="finance.spp_due">finance.spp_due (Keuangan)</option>
                                <option value="discipline.point_added">discipline.point_added (Kedisiplinan)</option>
                                <option value="system.alert">system.alert (Sistem & Keamanan)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Notifikasi</label>
                            <input type="text" name="title" required value="Pemberitahuan Penerbitan Nilai Rapor" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                        </div>

                        <div>
                            <button type="submit" class="w-full px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                                Pancarkan Pesan Uji Coba
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
