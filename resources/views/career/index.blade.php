<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight tracking-tight">
                {{ __('Portal Karir & Info Beasiswa') }}
            </h2>
            <!-- Humas quick access button if auth user has management rights -->
            @if(auth()->user()->role === 'waka_humas' || auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('career-portal.manage') }}" class="px-5 py-2.5 bg-gray-900 text-white rounded-xl text-xs font-bold hover:bg-gray-800 transition-all shadow-lg flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span>WORKSPACE HUMAS</span>
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8 w-full space-y-8">
        <!-- Search & Filter Card -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row gap-4 items-center justify-between">
            <!-- Filter Tabs -->
            <div class="flex space-x-2 w-full md:w-auto">
                <a href="{{ route('career-portal.index') }}" 
                   class="px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ !request('type') ? 'bg-primary-600 text-white' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    Semua Info
                </a>
                <a href="{{ route('career-portal.index', ['type' => 'job'] + request()->except('type')) }}" 
                   class="px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request('type') === 'job' ? 'bg-blue-600 text-white' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    Lowongan Kerja
                </a>
                <a href="{{ route('career-portal.index', ['type' => 'scholarship'] + request()->except('type')) }}" 
                   class="px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request('type') === 'scholarship' ? 'bg-emerald-600 text-white' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    Info Beasiswa
                </a>
            </div>

            <!-- Search Input -->
            <form action="{{ route('career-portal.index') }}" method="GET" class="flex w-full md:w-96 space-x-2">
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari posisi atau nama instansi..."
                       class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-xl text-xs focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                <button type="submit" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                    Cari
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center space-x-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Grid Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($opportunities as $opt)
                <!-- Opportunity Card -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between hover:shadow-xl transition-all duration-300 relative group overflow-hidden">
                    <div class="space-y-6">
                        <!-- Top Header -->
                        <div class="flex justify-between items-start">
                            <div>
                                @if($opt->type === 'job')
                                    <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                        JOB / LOWONGAN
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                        SCHOLARSHIP
                                    </span>
                                @endif
                            </div>
                            <div class="text-[10px] font-bold text-red-500 bg-red-50 dark:bg-red-950/20 px-2 py-1 rounded">
                                Selesai: {{ $opt->closing_date->format('d M Y') }}
                            </div>
                        </div>

                        <!-- Info Title -->
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 leading-tight group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                {{ $opt->title }}
                            </h3>
                            <p class="text-sm font-bold text-gray-400 dark:text-gray-500 mt-1">
                                {{ $opt->company_name }}
                            </p>
                        </div>

                        <!-- Description & Requirements (Short preview) -->
                        <div class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed line-clamp-3">
                            {{ $opt->description }}
                        </div>
                    </div>

                    <!-- Bottom Buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-50 dark:border-gray-700/50 flex items-center space-x-3">
                        <a href="{{ route('career-portal.show', $opt) }}" class="flex-1 text-center py-3 bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900 border border-gray-100 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold transition-all">
                            Lihat Detail
                        </a>
                        
                        @if($opt->file_path)
                            <a href="{{ route('career-portal.download', $opt) }}" class="px-4 py-3 bg-primary-50 dark:bg-primary-950/20 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-950/40 rounded-xl transition-all flex items-center justify-center" title="Unduh Brosur PDF">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="md:col-span-3 bg-white dark:bg-gray-800 rounded-[2.5rem] p-12 text-center border-2 border-dashed border-gray-100 dark:border-gray-700">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900/50 text-gray-300 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Tidak Ada Kesempatan Ditemukan</h3>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">Saat ini belum ada info lowongan karir atau beasiswa yang terdaftar atau cocok dengan pencarian Anda.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $opportunities->links() }}
        </div>
    </div>
</x-app-layout>
