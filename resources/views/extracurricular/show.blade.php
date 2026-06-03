<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('ekskul.index') }}" class="text-xs font-black uppercase text-emerald-600 dark:text-emerald-400 hover:underline tracking-wider">← KEMBALI KE KATALOG</a>
                </div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center mt-2">
                    <span class="w-8 h-8 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </span>
                    {{ $extracurricular->name }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">SMA ISLAM SULTAN AGUNG 1 • PROFIL DETAIL EKSTRAKURIKULER</p>
            </div>
            
            @if(auth()->user()->role === 'student' && !$isMember)
                <form method="POST" action="{{ route('ekskul.register', $extracurricular->id) }}">
                    @csrf
                    @php
                        $myCount = \App\Models\ExtracurricularMember::where('student_id', auth()->user()->student->id)->where('status', 'active')->count();
                        $isFull = !is_null($extracurricular->max_members) && $extracurricular->activeMembers()->count() >= $extracurricular->max_members;
                    @endphp
                    <button type="submit" 
                        {{ ($isFull || $myCount >= 2) ? 'disabled' : '' }}
                        class="inline-flex items-center px-5 py-2.5 {{ ($isFull || $myCount >= 2) ? 'bg-slate-300 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed border-transparent' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/10 hover:shadow-emerald-600/20 active:scale-95 border border-emerald-500/20' }} rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Daftar Ekskul Ini
                    </button>
                </form>
            @elseif(auth()->user()->role === 'student' && $isMember)
                <span class="inline-flex items-center px-4 py-2 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30 rounded-xl text-xs font-black uppercase tracking-wider">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Anda Anggota Aktif
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" x-data="{ activeTab: 'profile' }">
            
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="rounded-2xl p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 flex items-center text-emerald-800 dark:text-emerald-300 animate-fade-in">
                    <svg class="w-5 h-5 mr-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Layout Grid: Banner & Core Info -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Banner Card -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] overflow-hidden border border-gray-100 dark:border-gray-700/50 shadow-lg flex flex-col group h-fit">
                    <div class="h-56 bg-slate-100 dark:bg-gray-900 relative">
                        @if($extracurricular->cover_image)
                            <img src="{{ asset('storage/' . $extracurricular->cover_image) }}" alt="{{ $extracurricular->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center">
                                <svg class="w-20 h-20 text-emerald-600/30 dark:text-emerald-400/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="space-y-2">
                            <h3 class="text-xl font-extrabold text-gray-900 dark:text-white leading-tight tracking-tight">{{ $extracurricular->name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">{{ $extracurricular->description ?? 'Tidak ada deskripsi tambahan.' }}</p>
                        </div>
                        
                        <div class="border-t border-gray-100 dark:border-gray-700/50 pt-4 space-y-3 text-xs font-semibold text-gray-500 dark:text-gray-400">
                            <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-800">
                                <span>Guru Pembina:</span>
                                <span class="text-gray-900 dark:text-white font-extrabold">{{ $extracurricular->advisor->name }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-800">
                                <span>Hari Latihan:</span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">{{ $extracurricular->schedule_day }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-800">
                                <span>Jam Latihan:</span>
                                <span class="text-gray-900 dark:text-white font-extrabold">{{ $extracurricular->schedule_time }} WIB</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-800">
                                <span>Lokasi Latihan:</span>
                                <span class="text-gray-900 dark:text-white font-extrabold">{{ $extracurricular->location }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span>Jumlah Anggota:</span>
                                <span class="text-gray-900 dark:text-white font-extrabold">
                                    {{ $extracurricular->activeMembers()->count() }} / {{ $extracurricular->max_members ?? 'Bebas' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Contents Panel (Tabs & Content) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Glassmorphic Tabs Navigation -->
                    <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[1.5rem] p-2 shadow-sm border border-gray-100 dark:border-gray-700/50 flex space-x-2">
                        <button @click="activeTab = 'profile'" 
                                :class="activeTab === 'profile' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                                class="flex-1 py-3 text-xs font-black uppercase tracking-wider rounded-xl transition-all duration-300">
                            Profil & Roster Anggota
                        </button>
                        <button @click="activeTab = 'gallery'" 
                                :class="activeTab === 'gallery' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                                class="flex-1 py-3 text-xs font-black uppercase tracking-wider rounded-xl transition-all duration-300">
                            Dokumentasi & Kegiatan
                        </button>
                    </div>

                    <!-- Tab Panel 1: Profile & Member Roster -->
                    <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-300" class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-8">
                        <div>
                            <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                                <span class="w-2 h-6 bg-emerald-600 rounded-full mr-3"></span>
                                Roster Pengurus & Anggota Aktif
                            </h4>
                            <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Daftar siswa resmi yang tergabung secara aktif</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-700 text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                        <th class="py-3 px-4">Nama Siswa</th>
                                        <th class="py-3 px-4">Kelas</th>
                                        <th class="py-3 px-4">Jabatan</th>
                                        <th class="py-3 px-4">Tgl Bergabung</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    @forelse($extracurricular->activeMembers as $member)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-gray-900/50 transition-colors">
                                            <td class="py-3 px-4 flex items-center gap-3">
                                                <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 rounded-lg flex items-center justify-center font-extrabold text-sm">
                                                    {{ substr($member->student->user->name, 0, 1) }}
                                                </div>
                                                <span class="font-extrabold text-gray-900 dark:text-white">{{ $member->student->user->name }}</span>
                                            </td>
                                            <td class="py-3 px-4 text-gray-500 dark:text-gray-400">
                                                {{ $member->student->classroom->name ?? '-' }}
                                            </td>
                                            <td class="py-3 px-4">
                                                @php
                                                    $roleColors = [
                                                        'ketua' => 'bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/30',
                                                        'wakil_ketua' => 'bg-orange-50 dark:bg-orange-950/30 text-orange-700 dark:text-orange-400 border border-orange-200 dark:border-orange-900/30',
                                                        'sekretaris' => 'bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-900/30',
                                                        'bendahara' => 'bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border border-teal-200 dark:border-teal-900/30',
                                                        'anggota' => 'bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800'
                                                    ];
                                                    $rColor = $roleColors[$member->role] ?? $roleColors['anggota'];
                                                @endphp
                                                <span class="px-2.5 py-1 {{ $rColor }} rounded-lg text-[9px] font-black uppercase tracking-wider">
                                                    {{ str_replace('_', ' ', $member->role) }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-gray-400 dark:text-gray-500">
                                                {{ $member->joined_at->translatedFormat('d M Y') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-8 text-center text-gray-400 dark:text-gray-500">
                                                Belum ada anggota terdaftar aktif di ekstrakurikuler ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Panel 2: Activity Gallery Timeline -->
                    <div x-show="activeTab === 'gallery'" x-transition:enter="transition ease-out duration-300" class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-8" style="display:none;">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                                    <span class="w-2 h-6 bg-emerald-600 rounded-full mr-3"></span>
                                    Galeri & Jurnal Latihan
                                </h4>
                                <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Laporan berkala program kerja dan dokumentasi pembina</p>
                            </div>
                        </div>

                        <!-- Timeline & Cards -->
                        <div class="space-y-8 relative before:absolute before:left-6 before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-100 dark:before:bg-gray-700/50">
                            @forelse($extracurricular->activities as $activity)
                                <div class="relative pl-12 group">
                                    <!-- Timeline Node Circle -->
                                    <div class="absolute left-3.5 top-2 w-6 h-6 bg-emerald-50 dark:bg-emerald-950/40 border-2 border-emerald-500 rounded-full flex items-center justify-center z-10 transition-transform group-hover:scale-125 duration-300">
                                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                                    </div>
                                    
                                    <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 p-6 rounded-3xl space-y-4 hover:border-emerald-500/30 transition-all duration-300">
                                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                                            <div>
                                                <h5 class="text-base font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $activity->title }}</h5>
                                                <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-widest font-black mt-0.5">
                                                    {{ $activity->activity_date->translatedFormat('d F Y') }} • Jurnal Latihan
                                                </p>
                                            </div>
                                            <span class="self-start sm:self-center px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                                Oleh: {{ $activity->creator->name }}
                                            </span>
                                        </div>

                                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $activity->description }}</p>

                                        <!-- Gallery Grid (Nested drop paths) -->
                                        @if($activity->gallery_paths && count($activity->gallery_paths) > 0)
                                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 pt-2">
                                                @foreach($activity->gallery_paths as $path)
                                                    <div class="h-24 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800 shadow-sm relative group/img cursor-pointer">
                                                        <img src="{{ asset('storage/' . $path) }}" alt="Gallery" class="w-full h-full object-cover group-hover/img:scale-110 transition-transform duration-300">
                                                        <a href="{{ asset('storage/' . $path) }}" target="_blank" class="absolute inset-0 bg-black/40 opacity-0 group-hover/img:opacity-100 flex items-center justify-center text-white transition-opacity duration-300">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m4-3H6"></path>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-gray-400 dark:text-gray-500 pl-12">
                                    Belum ada dokumentasi kegiatan latihan yang diunggah oleh Guru Pembina.
                                </div>
                            @endforelse
                        </div>
                    </div>
                    
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
