<x-app-layout>
    <x-slot name="title">Direktori Alumni SISTA</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    🌐 Direktori & Jaringan Alumni
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Jelajahi profil alumni SMA Islam Sultan Agung 1 Semarang lintas angkatan.
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('alumni.network-map') }}" class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                    🗺️ Lihat Peta Sebaran
                </a>
                @if(in_array(auth()->user()->role, ['waka_humas', 'admin', 'superadmin']))
                    <a href="{{ route('alumni.index') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                        ⚙️ Kelola Alumni
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Pencarian & Filter Instan -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
            <form method="GET" action="{{ route('alumni.directory') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Cari Nama/Instansi</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, universitas, profesi..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tahun Angkatan</label>
                    <select name="year" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Angkatan</option>
                        @foreach($graduationYears as $yr)
                            <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>Angkatan {{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Aktivitas</label>
                    <select name="activity" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Aktivitas</option>
                        <option value="study" {{ request('activity') == 'study' ? 'selected' : '' }}>Kuliah</option>
                        <option value="work" {{ request('activity') == 'work' ? 'selected' : '' }}>Bekerja</option>
                        <option value="entrepreneur" {{ request('activity') == 'entrepreneur' ? 'selected' : '' }}>Wirausaha</option>
                        <option value="unemployed" {{ request('activity') == 'unemployed' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                        🔍 Cari
                    </button>
                    <a href="{{ route('alumni.directory') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all text-center">
                        🔄 Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Grid Card Kartu Alumni -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($alumni as $al)
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden flex flex-col justify-between hover:-translate-y-1 transition-all">
                    <div class="p-6 space-y-4">
                        <!-- Initials & Badge -->
                        <div class="flex justify-between items-start">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-sm">
                                {{ strtoupper(substr($al->student->name, 0, 2)) }}
                            </div>
                            
                            @if($al->current_activity === 'study')
                                <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30">Kuliah</span>
                            @elseif($al->current_activity === 'work')
                                <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">Kerja</span>
                            @elseif($al->current_activity === 'entrepreneur')
                                <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-900/30">Bisnis</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Lainnya</span>
                            @endif
                        </div>

                        <!-- Name & Angkatan -->
                        <div>
                            <h4 class="font-extrabold text-sm text-gray-800 dark:text-white line-clamp-1">{{ $al->student->name }}</h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">Lulusan Angkatan {{ $al->graduation_year }}</p>
                        </div>

                        <!-- Tempat Kegiatan -->
                        <div class="pt-3 border-t border-gray-100 dark:border-gray-700/50 text-xs">
                            <span class="block text-[9px] text-gray-400 uppercase font-bold tracking-wider">Aktivitas</span>
                            <p class="font-bold text-gray-700 dark:text-gray-300 mt-0.5 line-clamp-1">{{ $al->institution_name ?? '-' }}</p>
                            @if($al->current_position)
                                <p class="text-[10px] text-gray-400 mt-0.5 line-clamp-1">{{ $al->current_position }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Tombol Detail / Aksi -->
                    <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-900/20 border-t border-gray-100 dark:border-gray-700/50">
                        <a href="{{ route('alumni.show', $al->uuid) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2 text-xs font-bold transition-all shadow-sm">
                            👁️ Lihat Profil Lengkap
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-400">
                    Tidak ada alumni yang ditemukan di direktori.
                </div>
            @endforelse
        </div>

        @if($alumni->hasPages())
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl p-4 border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
                {{ $alumni->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
