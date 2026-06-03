<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('career-portal.index') }}" class="w-9 h-9 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl flex items-center justify-center text-gray-500 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight tracking-tight">
                {{ __('Detail Informasi Kesempatan') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto space-y-8">
        <!-- Main Card Detail -->
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 space-y-8">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-50 dark:border-gray-700/50 pb-6">
                <div class="space-y-2">
                    <div>
                        @if($opportunity->type === 'job')
                            <span class="px-3 py-1 bg-blue-50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-black uppercase tracking-wider">
                                JOB / LOWONGAN PEKERJAAN
                            </span>
                        @else
                            <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 rounded-lg text-xs font-black uppercase tracking-wider">
                                INFO BEASISWA
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-gray-100 leading-tight">
                        {{ $opportunity->title }}
                    </h1>
                    <p class="text-sm font-bold text-gray-400 dark:text-gray-500">
                        Penyelenggara: {{ $opportunity->company_name }}
                    </p>
                </div>
                
                <!-- Closing Date Badge -->
                <div class="p-4 bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 rounded-2xl text-center flex-shrink-0">
                    <p class="text-[9px] font-black uppercase tracking-widest text-red-400">Batas Akhir</p>
                    <p class="text-lg font-black mt-1">{{ $opportunity->closing_date->format('d M Y') }}</p>
                    @if($opportunity->closing_date->isPast())
                        <span class="inline-block mt-1 px-2 py-0.5 bg-red-500 text-white rounded text-[9px] font-bold uppercase animate-pulse">Selesai</span>
                    @endif
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Left Section: Details (2/3) -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Deskripsi -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Deskripsi Info</h4>
                        <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line bg-gray-50/50 dark:bg-gray-900/30 p-6 rounded-2xl border border-gray-50 dark:border-gray-700/50">
                            {{ $opportunity->description }}
                        </div>
                    </div>

                    <!-- Persyaratan -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Kualifikasi & Persyaratan</h4>
                        <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line bg-gray-50/50 dark:bg-gray-900/30 p-6 rounded-2xl border border-gray-50 dark:border-gray-700/50">
                            {{ $opportunity->requirements }}
                        </div>
                    </div>
                </div>

                <!-- Right Section: Sidebar Actions (1/3) -->
                <div class="space-y-6">
                    <div class="p-6 bg-gray-50 dark:bg-gray-900/30 rounded-3xl border border-gray-100 dark:border-gray-700 space-y-6 flex flex-col justify-between">
                        <div>
                            <h4 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Hubungi Kontak</h4>
                            
                            <div class="space-y-4">
                                <div class="flex items-start space-x-3">
                                    <div class="w-8 h-8 bg-primary-50 dark:bg-primary-950/20 text-primary-600 dark:text-primary-400 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-[10px] font-black text-gray-400 uppercase">Kirim Lamaran Ke</p>
                                        <a href="mailto:{{ $opportunity->contact_email }}" class="text-xs font-bold text-primary-600 hover:underline break-words block">
                                            {{ $opportunity->contact_email }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 pt-6 border-t border-gray-100 dark:border-gray-700/50">
                            <!-- Download Brochure -->
                            @if($opportunity->file_path)
                                <a href="{{ route('career-portal.download', $opportunity) }}" class="w-full text-center py-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold transition-all flex items-center justify-center space-x-2 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Unduh Brosur PDF</span>
                                </a>
                            @endif

                            <!-- Apply Button -->
                            <a href="mailto:{{ $opportunity->contact_email }}?subject=Lamaran: {{ rawurlencode($opportunity->title) }}" class="w-full text-center py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black shadow-lg shadow-primary-900/10 transition-all flex items-center justify-center space-x-2 block">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span>Lamar Sekarang</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
