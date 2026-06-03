<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Pusat Notifikasi Aplikasi') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pantau semua aktivitas, kehadiran siswa, poin konseling, dan status pembayaran Anda di satu tempat.</p>
            </div>
            
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.markAllRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Riwayat Notifikasi</h3>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($notifications as $notification)
                        <div class="p-6 transition-all hover:bg-gray-50/50 dark:hover:bg-gray-900/30 flex items-start justify-between gap-4 {{ $notification->read() ? 'opacity-75' : '' }}">
                            <div class="flex items-start gap-4">
                                <!-- Status Indicator Dot -->
                                <div class="mt-1.5 flex-shrink-0">
                                    @if(!$notification->read())
                                        <span class="block w-2.5 h-2.5 rounded-full bg-red-500 ring-4 ring-red-100 dark:ring-red-900/30"></span>
                                    @else
                                        <span class="block w-2.5 h-2.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                    @endif
                                </div>

                                <!-- Notification Content -->
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $notification->data['title'] ?? 'Pemberitahuan Baru' }}
                                    </h4>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 leading-relaxed">
                                        {{ $notification->data['message'] ?? '' }}
                                    </p>
                                    <span class="inline-block text-[10px] text-gray-400 dark:text-gray-500 font-medium mt-2">
                                        {{ $notification->created_at->diffForHumans() }} • {{ $notification->created_at->format('d M Y, H:i') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="flex-shrink-0">
                                <a href="{{ route('notifications.read', $notification->id) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-lg transition-colors">
                                    Buka
                                    <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-16 text-center">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                            <h4 class="text-sm font-bold text-gray-800 dark:text-white">Tidak Ada Notifikasi</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Semua pesan dan notifikasi sistem yang dikirimkan kepada Anda akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>

                @if($notifications->hasPages())
                    <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
