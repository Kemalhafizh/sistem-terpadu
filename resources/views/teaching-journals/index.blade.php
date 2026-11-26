<x-app-layout>
    <x-slot name="title">Jurnal Mengajar Guru</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight flex items-center gap-2">
                    <span></span> Jurnal Mengajar Harian Guru
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Daftar pencatatan aktivitas pembelajaran, metode, kehadiran siswa, dan tinjauan kelas harian.</p>
            </div>
            @if(auth()->user()->role === 'guru')
            <a href="{{ route('teaching-journals.create') }}" class="inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white rounded-xl py-2.5 px-5 text-sm font-black uppercase tracking-wider shadow-lg shadow-primary-500/20 hover:shadow-primary-500/35 transition-all duration-300 transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Isi Jurnal Baru
            </a>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Panel Ringkasan KPI (Glassmorphic Cards) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-xl text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Jurnal</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $journals->total() }}</h3>
                </div>
            </div>
            
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-gray-100 dark:bg-gray-700/50 rounded-xl text-gray-600 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Draft</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $journals->where('status', 'draft')->count() }}</h3>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-amber-100 dark:bg-amber-900/50 rounded-xl text-amber-600 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Diajukan</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $journals->where('status', 'submitted')->count() }}</h3>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Disetujui</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $journals->where('status', 'reviewed')->count() }}</h3>
                </div>
            </div>
        </div>

        <!-- Panel Filter Multi-opsi & Export -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 border-b border-gray-100 dark:border-gray-700/50 pb-4">
                <h4 class="text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Pencarian & Filter Data
                </h4>
                @if(in_array(auth()->user()->role, ['waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin']))
                <form action="{{ route('teaching-journals.export-pdf') }}" method="GET" class="flex flex-wrap items-center gap-2 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-2xl border border-gray-200/40 dark:border-gray-700/30">
                    <select name="month" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2.5 py-1.5 text-xs text-gray-700 dark:text-gray-300 focus:outline-none">
                        <option value="">Pilih Bulan</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                    <select name="year" required class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2.5 py-1.5 text-xs text-gray-700 dark:text-gray-300 focus:outline-none">
                        <option value="2026" selected>2026</option>
                        <option value="2025">2025</option>
                    </select>
                    <select name="teacher_id" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2.5 py-1.5 text-xs text-gray-700 dark:text-gray-300 focus:outline-none max-w-[150px]">
                        <option value="">Semua Guru</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white rounded-xl py-1.5 px-3.5 text-xs font-bold transition-all flex items-center shadow-md shadow-red-500/20">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Ekspor PDF Rekap
                    </button>
                </form>
                @endif
            </div>

            <form action="{{ route('teaching-journals.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @if(auth()->user()->role !== 'guru')
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Guru Pengajar</label>
                    <select name="teacher_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                        <option value="">Semua Guru</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Kelas</label>
                    <select name="classroom_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                        <option value="">Semua Kelas</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}" {{ request('classroom_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Status Jurnal</label>
                    <select name="status" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (Selesai Disimpan)</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Menunggu Review</option>
                        <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Disetujui / Reviewed</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Bulan</label>
                        <select name="month" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-2 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                            <option value="">Bulan</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Tahun</label>
                        <select name="year" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-2 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                            <option value="">Tahun</option>
                            <option value="2026" {{ request('year') == 2026 ? 'selected' : '' }}>2026</option>
                            <option value="2025" {{ request('year') == 2025 ? 'selected' : '' }}>2025</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-end space-x-2 @if(auth()->user()->role === 'guru') md:col-start-4 @endif">
                    <button type="submit" class="w-full bg-gray-800 dark:bg-gray-200 hover:bg-gray-900 dark:hover:bg-white text-white dark:text-gray-950 font-bold py-2.5 px-4 rounded-xl text-sm transition-all text-center">
                        Cari / Filter
                    </button>
                    <a href="{{ route('teaching-journals.index') }}" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 p-2.5 rounded-xl text-sm font-bold text-center hover:bg-gray-300 dark:hover:bg-gray-600 transition-all">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Jurnal Mengajar -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Tanggal / Jam</th>
                            @if(auth()->user()->role !== 'guru')
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Nama Guru</th>
                            @endif
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Kelas / Mapel</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Materi / Topik</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/40 dark:divide-gray-700/40">
                        @forelse($journals as $j)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-gray-200">
                                <div>{{ $j->teaching_date->format('d M Y') }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">Jam Ke-{{ $j->jam_ke ?: '-' }}</div>
                            </td>
                            @if(auth()->user()->role !== 'guru')
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $j->teacher->name }}
                            </td>
                            @endif
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $j->classroom->name }}</span>
                                <div class="text-xs font-medium text-gray-500 mt-0.5">{{ $j->subject->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 font-medium max-w-[200px] truncate">
                                {{ Str::limit($j->topic, 45) }}
                            </td>
                            <td class="px-6 py-4">
                                @if($j->status === 'draft')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300 border border-gray-200 dark:border-gray-700/50">DRAFT</span>
                                @elseif($j->status === 'submitted')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50">MENUNGGU REVIEW</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50">REVIEWED</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-sm">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('teaching-journals.show', $j->uuid) }}" class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 font-bold transition-all">Detail</a>
                                    @if($j->status === 'draft' && auth()->user()->role === 'guru')
                                    <a href="{{ route('teaching-journals.edit', $j->uuid) }}" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300 font-bold transition-all">Edit</a>
                                    <form action="{{ route('teaching-journals.destroy', $j->uuid) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus draf ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-bold transition-all">Hapus</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 font-medium italic">Belum ada rekam data jurnal mengajar yang tersimpan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($journals->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/50">
                {{ $journals->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
