<x-app-layout>
    <x-slot name="title">Profil Detail Alumni - {{ $alumni->student->name }}</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 no-print">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Profil Detail Alumni
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Informasi lengkap mengenai status pendidikan dan karier alumni.
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('alumni.directory') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                    ⬅ Direktori
                </a>
                @if(in_array(auth()->user()->role, ['waka_humas', 'admin', 'superadmin']))
                    <a href="{{ route('alumni.edit', $alumni->uuid) }}" class="bg-amber-500 hover:bg-amber-600 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                         Edit Profil
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Kolom Kiri: Ringkasan & Kontak -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-6">
            <div class="text-center space-y-3 pb-6 border-b border-gray-100 dark:border-gray-700/50">
                <div class="w-24 h-24 mx-auto rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-2xl shadow-inner">
                    {{ strtoupper(substr($alumni->student->name, 0, 2)) }}
                </div>
                <div>
                    <h3 class="font-black text-lg text-gray-800 dark:text-white">{{ $alumni->student->name }}</h3>
                    <p class="text-xs text-gray-400 mt-1">Angkatan Lulus: {{ $alumni->graduation_year }}</p>
                </div>
            </div>

            <!-- Detail Kontak -->
            <div class="space-y-4">
                <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider"> Informasi Kontak</h4>
                
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Surel (Email)</span>
                        <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5 break-all">
                            {{ $alumni->email ?? $alumni->student->user->email }}
                        </p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">No. Telepon / WA</span>
                        <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5">
                            {{ $alumni->phone_number ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Domisili Saat Ini</span>
                        <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5">
                            @if($alumni->city)
                                {{ $alumni->city }}, {{ $alumni->province ?? '' }} ({{ $alumni->country ?? 'Indonesia' }})
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Media Sosial -->
            <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider"> Media Sosial</h4>
                <div class="space-y-2">
                    @if($alumni->linkedin_url)
                        <a href="{{ $alumni->linkedin_url }}" target="_blank" class="flex items-center gap-2 p-2.5 bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 rounded-xl text-xs font-bold border border-blue-100 dark:border-blue-900/30 hover:bg-blue-100/50 dark:hover:bg-blue-900/40 transition-all">
                             LinkedIn Profile
                        </a>
                    @else
                        <span class="block text-xs text-gray-400 italic">LinkedIn tidak tersemat</span>
                    @endif

                    @if($alumni->instagram_url)
                        <a href="{{ $alumni->instagram_url }}" target="_blank" class="flex items-center gap-2 p-2.5 bg-pink-50 dark:bg-pink-950/20 text-pink-700 dark:text-pink-400 rounded-xl text-xs font-bold border border-pink-100 dark:border-pink-900/30 hover:bg-pink-100/50 dark:hover:bg-pink-900/40 transition-all">
                             Instagram Profile
                        </a>
                    @else
                        <span class="block text-xs text-gray-400 italic">Instagram tidak tersemat</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Detail Pendidikan SMA & Tracer Study -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Pendidikan SMA ISA 1 -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
                <h3 class="font-extrabold text-gray-800 dark:text-white text-base"> Riwayat Akademik Sekolah</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Nomor Induk Siswa (NIS)</span>
                        <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5">{{ $alumni->student->nis ?? '-' }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">NIS Nasional (NISN)</span>
                        <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5">{{ $alumni->student->nisn ?? '-' }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Kelas Terakhir</span>
                        <p class="font-bold text-gray-800 dark:text-gray-200 mt-0.5">{{ $alumni->student->classroom?->name ?? '-' }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Status Pengguna</span>
                        <p class="font-bold mt-0.5 text-blue-600 dark:text-blue-400">ALUMNI TERVERIFIKASI</p>
                    </div>
                </div>
            </div>

            <!-- Aktivitas & Tracer Study Karier -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
                <h3 class="font-extrabold text-gray-800 dark:text-white text-base"> Status & Aktivitas Pascakelulusan</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Aktivitas Utama</span>
                        <p class="font-extrabold text-gray-800 dark:text-gray-200 mt-1">
                            @if($alumni->current_activity === 'study')
                                 Melanjutkan Studi (Kuliah)
                            @elseif($alumni->current_activity === 'work')
                                 Bekerja / Bekerja Profesional
                            @elseif($alumni->current_activity === 'entrepreneur')
                                 Berwirausaha / Bisnis Mandiri
                            @elseif($alumni->current_activity === 'unemployed')
                                 Sedang Mencari Pekerjaan
                            @else
                                 Lainnya
                            @endif
                        </p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Nama Institusi / Universitas</span>
                        <p class="font-extrabold text-gray-800 dark:text-gray-200 mt-1">{{ $alumni->institution_name ?? '-' }}</p>
                    </div>

                    @if($alumni->current_position)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 md:col-span-2">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Jabatan Pekerjaan / Program Studi</span>
                        <p class="font-extrabold text-gray-900 dark:text-gray-100 mt-1">{{ $alumni->current_position }}</p>
                    </div>
                    @endif
                </div>

                @if($alumni->tracerResponse)
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50 space-y-4">
                        <h4 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider"> Hasil Kuesioner Tracer Study</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                            <div class="p-3 bg-indigo-50/50 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-900/20">
                                <span class="block text-[10px] font-bold text-gray-400 uppercase">Nama Tempat Kerja</span>
                                <p class="font-bold text-indigo-950 dark:text-indigo-300 mt-1">{{ $alumni->tracerResponse->employer_name ?? '-' }}</p>
                            </div>
                            <div class="p-3 bg-indigo-50/50 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-900/20">
                                <span class="block text-[10px] font-bold text-gray-400 uppercase">Kisaran Pendapatan</span>
                                <p class="font-bold text-indigo-950 dark:text-indigo-300 mt-1">{{ $alumni->tracerResponse->salary_range ?? '-' }}</p>
                            </div>
                            <div class="p-3 bg-indigo-50/50 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-900/20">
                                <span class="block text-[10px] font-bold text-gray-400 uppercase">Tahun Mulai Kegiatan</span>
                                <p class="font-bold text-indigo-950 dark:text-indigo-300 mt-1">{{ $alumni->tracerResponse->entry_year ?? '-' }}</p>
                            </div>
                            <div class="p-3 bg-indigo-50/50 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-900/20">
                                <span class="block text-[10px] font-bold text-gray-400 uppercase">Skor Keselarasan Kurikulum</span>
                                <div class="flex items-center gap-1 mt-1 font-bold text-indigo-950 dark:text-indigo-300">
                                    <span>{{ $alumni->tracerResponse->relevance_score }} / 5</span>
                                    <span class="text-amber-500"></span>
                                </div>
                            </div>
                            <div class="p-3 bg-indigo-50/50 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-900/20">
                                <span class="block text-[10px] font-bold text-gray-400 uppercase">Skor Kepuasan Lulusan</span>
                                <div class="flex items-center gap-1 mt-1 font-bold text-indigo-950 dark:text-indigo-300">
                                    <span>{{ $alumni->tracerResponse->satisfaction_score }} / 5</span>
                                    <span class="text-amber-500"></span>
                                </div>
                            </div>
                        </div>

                        @if($alumni->tracerResponse->suggestions)
                            <div class="p-4 bg-gray-50/50 dark:bg-gray-900/50 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 text-xs">
                                <span class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Saran untuk Peningkatan Sekolah</span>
                                <p class="italic text-gray-800 dark:text-gray-300 leading-relaxed">"{{ $alumni->tracerResponse->suggestions }}"</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-950/20 text-yellow-800 dark:text-yellow-400 rounded-2xl border border-yellow-100 dark:border-yellow-900/30 text-xs flex items-center justify-between">
                        <div>
                            <p class="font-bold"> Data Tracer Study Belum Diisi</p>
                            <p class="mt-0.5">Alumni ini belum mengirimkan tanggapan kuesioner kelulusan.</p>
                        </div>
                        @if(auth()->user()->student && auth()->user()->student->id === $alumni->student_id)
                            <a href="{{ route('alumni.tracer-study.form') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white rounded-xl py-1.5 px-3 font-bold transition-all shadow-sm">
                                Isi Kuesioner
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
