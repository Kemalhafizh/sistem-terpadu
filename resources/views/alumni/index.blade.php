<x-app-layout>
    <x-slot name="title">Manajemen Data Alumni</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    🎓 Kelola Data Alumni
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Administrasi dan pelacakan kelulusan siswa SMA Islam Sultan Agung 1 Semarang.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('alumni.dashboard') }}" class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                    📊 Dashboard Stats
                </a>
                <a href="{{ route('alumni.bulk-graduate') }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                    ⚡ Kelulusan Massal
                </a>
                <a href="{{ route('alumni.create') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                    ➕ Tambah Manual
                </a>
                <a href="{{ route('alumni.export.excel') }}" class="inline-flex items-center justify-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                    ⬇️ Ekspor CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Filter & Pencarian -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
            <form method="GET" action="{{ route('alumni.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Cari Alumni</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NISN..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                        <option value="unemployed" {{ request('activity') == 'unemployed' ? 'selected' : '' }}>Mencari Kerja</option>
                        <option value="other" {{ request('activity') == 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                        🔍 Filter
                    </button>
                    <a href="{{ route('alumni.index') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all text-center">
                        🔄 Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Download Buku Alumni PDF Card -->
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6 rounded-3xl text-white shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h3 class="font-extrabold text-lg">📚 Cetak Buku Profil Alumni</h3>
                <p class="text-xs text-blue-100 mt-1">Dapatkan data cetak dokumen Buku Angkatan Alumni dalam format PDF portrait resmi.</p>
            </div>
            <div class="flex items-center gap-2 bg-white/10 p-2 rounded-2xl">
                <select id="pdf-year-select" class="bg-gray-900/40 border border-white/20 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none">
                    @if($graduationYears->isEmpty())
                        <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                    @else
                        @foreach($graduationYears as $yr)
                            <option value="{{ $yr }}">Angkatan {{ $yr }}</option>
                        @endforeach
                    @endif
                </select>
                <button onclick="downloadAlumniBook()" class="bg-white text-blue-600 hover:bg-blue-50 rounded-xl px-4 py-1.5 text-xs font-black transition-all shadow-sm">
                    ⬇️ Unduh PDF
                </button>
            </div>
        </div>

        <!-- Tabel Alumni -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-150 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Alumni</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Angkatan</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Aktivitas</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tempat Kegiatan</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Kontak</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tracer Study</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @forelse($alumni as $al)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-all">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                                            {{ strtoupper(substr($al->student->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('alumni.show', $al->uuid) }}" class="font-bold text-gray-800 dark:text-white hover:text-blue-600 transition-colors">
                                                {{ $al->student->name }}
                                            </a>
                                            <p class="text-[10px] text-gray-400 mt-0.5">NISN: {{ $al->student->nisn ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-700 dark:text-gray-300">
                                    {{ $al->graduation_year }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($al->current_activity === 'study')
                                        <span class="px-2 py-1 rounded-lg text-xs font-bold bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30">Kuliah</span>
                                    @elseif($al->current_activity === 'work')
                                        <span class="px-2 py-1 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">Bekerja</span>
                                    @elseif($al->current_activity === 'entrepreneur')
                                        <span class="px-2 py-1 rounded-lg text-xs font-bold bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-900/30">Wirausaha</span>
                                    @elseif($al->current_activity === 'unemployed')
                                        <span class="px-2 py-1 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30">Mencari Kerja</span>
                                    @else
                                        <span class="px-2 py-1 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Lainnya</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $al->institution_name ?? '-' }}</span>
                                    @if($al->current_position)
                                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $al->current_position }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 space-y-0.5">
                                    <p class="font-medium">{{ $al->email ?? $al->student->user->email }}</p>
                                    <p>{{ $al->phone_number ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if($al->tracerResponse)
                                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                                            ✅ Mengisi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-red-500 dark:text-red-400 font-bold text-xs">
                                            ❌ Belum
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-1">
                                    <a href="{{ route('alumni.show', $al->uuid) }}" class="inline-flex items-center justify-center p-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-xl text-gray-600 dark:text-gray-300 transition-all" title="Detail Profil">
                                        👁️
                                    </a>
                                    <a href="{{ route('alumni.edit', $al->uuid) }}" class="inline-flex items-center justify-center p-2 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/20 dark:hover:bg-amber-900/30 rounded-xl text-amber-600 dark:text-amber-400 transition-all border border-amber-100 dark:border-amber-900/30" title="Edit Data">
                                        ✏️
                                    </a>
                                    <form action="{{ route('alumni.destroy', $al->uuid) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus data alumni ini? Status siswa akan dikembalikan ke Aktif.')" class="inline-flex items-center justify-center p-2 bg-red-50 hover:bg-red-100 dark:bg-red-950/20 dark:hover:bg-red-900/30 rounded-xl text-red-600 dark:text-red-400 transition-all border border-red-100 dark:border-red-900/30" title="Hapus Data">
                                            🗑️
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-gray-400">
                                    Tidak ada data alumni yang cocok dengan pencarian/filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($alumni->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                    {{ $alumni->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function downloadAlumniBook() {
            const year = document.getElementById('pdf-year-select').value;
            if(year) {
                window.location.href = `/alumni/export/pdf/${year}`;
            }
        }
    </script>
</x-app-layout>
