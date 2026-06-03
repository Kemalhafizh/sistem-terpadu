<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                    <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                    {{ __('Profil Bimbingan & Konseling Holistik') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">Analisis Terpadu BK & Kedisiplinan: {{ $student->user->name }}</p>
            </div>
            
            <div>
                <a href="{{ route('counseling.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 transition-all shadow-sm">
                    ← Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Profil Ringkas & Stat Poin -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                    <div class="w-24 h-24 bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 rounded-[2rem] flex items-center justify-center font-extrabold text-4xl shadow-sm shrink-0">
                        {{ substr($student->user->name, 0, 1) }}
                    </div>
                    <div class="flex-1 text-center md:text-left space-y-2">
                        <div class="flex flex-col md:flex-row md:items-center gap-2">
                            <h3 class="text-xl font-black text-gray-900 dark:text-white">{{ $student->user->name }}</h3>
                            <span class="inline-block px-3 py-1 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 rounded-full text-xs font-black uppercase tracking-wider">
                                {{ $student->classroom->name ?? 'Belum ada kelas' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">NIS: {{ $student->nis }} • NISN: {{ $student->nisn }} • Jenis Kelamin: {{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                        
                        @if($totalPoints >= 50)
                            <div class="mt-4 inline-flex items-center px-4 py-2 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 text-red-800 dark:text-red-300 rounded-2xl text-xs font-bold gap-2">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                PERINGATAN: Siswa berada dalam batas akumulasi poin kritis ({{ $totalPoints }} Poin)
                            </div>
                        @else
                            <div class="mt-4 inline-flex items-center px-4 py-2 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900/50 text-green-800 dark:text-green-300 rounded-2xl text-xs font-bold gap-2">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Poin Kedisiplinan Terpantau Aman ({{ $totalPoints }} / 50 Poin)
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Grid Konten Detail -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Timeline Riwayat Konseling -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                    <h3 class="text-base font-black text-gray-900 dark:text-white uppercase tracking-wider mb-6 flex items-center">
                        <span class="w-2.5 h-5 bg-indigo-600 rounded mr-3"></span>
                        Riwayat Pertemuan Konseling
                    </h3>

                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse($sessions as $session)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-100 dark:bg-gray-800" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm font-extrabold text-gray-900 dark:text-white">
                                                        Sesi {{ ucfirst($session->type) }} ({{ ucfirst($session->category) }})
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Alasan: <span class="italic">"{{ $session->reason }}"</span></p>
                                                    @if($session->session_notes)
                                                        <p class="text-xs text-gray-400 mt-2 line-clamp-2">Catatan: {{ $session->session_notes }}</p>
                                                    @endif
                                                    <p class="text-[10px] font-black uppercase text-gray-400 tracking-wider mt-2">
                                                        {{ $session->scheduled_at->translatedFormat('d M Y') }} • Konselor: {{ $session->counselor->name ?? '-' }}
                                                    </p>
                                                </div>
                                                <div class="text-right text-xs whitespace-nowrap">
                                                    @if($session->status === 'completed')
                                                        <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-[10px] font-black uppercase border border-green-200">Completed</span>
                                                    @elseif($session->status === 'scheduled')
                                                        <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase border border-indigo-200">Scheduled</span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded-full bg-gray-50 text-gray-700 text-[10px] font-black uppercase border border-gray-200">{{ $session->status }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <div class="text-center py-10 bg-gray-50 dark:bg-gray-900/20 rounded-3xl border border-dashed border-gray-200 dark:border-gray-800">
                                    <p class="text-xs font-bold text-gray-400 dark:text-gray-500">Belum ada riwayat sesi konseling.</p>
                                </div>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- Buku Pelanggaran Disiplin (Poin BK) -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                    <h3 class="text-base font-black text-gray-900 dark:text-white uppercase tracking-wider mb-6 flex items-center">
                        <span class="w-2.5 h-5 bg-red-500 rounded mr-3"></span>
                        Buku Saku Poin Kedisiplinan
                    </h3>

                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse($pointLogs as $log)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-100 dark:bg-gray-800" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full {{ $log->points > 0 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }} flex items-center justify-center">
                                                    @if($log->points > 0)
                                                        ⚠️
                                                    @else
                                                        🏆
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm font-extrabold text-gray-900 dark:text-white">
                                                        {{ $log->category->name ?? $log->description }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-1">{{ $log->description ?: '-' }}</p>
                                                    <p class="text-[10px] font-black uppercase text-gray-400 tracking-wider mt-2">
                                                        {{ $log->created_at->translatedFormat('d M Y') }} • Dibuat oleh: {{ $log->creator->name ?? '-' }}
                                                    </p>
                                                </div>
                                                <div class="text-right text-xs whitespace-nowrap font-black">
                                                    <span class="{{ $log->points > 0 ? 'text-red-600' : 'text-green-600' }}">
                                                        {{ $log->points > 0 ? '+' : '' }}{{ $log->points }} Poin
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <div class="text-center py-10 bg-gray-50 dark:bg-gray-900/20 rounded-3xl border border-dashed border-gray-200 dark:border-gray-800">
                                    <p class="text-xs font-bold text-gray-400 dark:text-gray-500">Siswa belum memiliki catatan pelanggaran / prestasi.</p>
                                </div>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
