<x-app-layout>
    <x-slot name="title">Program Kerja OSIS</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    📋 Program Kerja OSIS (Proker)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Catat perolehan realisasi, timeline pelaksanaan, dan ulasan anggaran proker.
                </p>
            </div>
            <a href="{{ route('osis.dashboard') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅️ Kembali
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Tambah Program Kerja -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl self-start">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-4">📋 Rencana Proker Baru</h3>
            
            <form action="{{ route('osis.work-programs.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Program Kerja</label>
                    <input type="text" name="title" required placeholder="LDKMS Pengurus OSIS..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Bidang / Sekbid Penanggungjawab</label>
                    <select name="sekbid" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                        <option value="Sekbid 1: Keimanan & Ketaqwaan YME">Sekbid 1: Keimanan & Ketaqwaan YME</option>
                        <option value="Sekbid 2: Budi Pekerti & Akhlak Mulia">Sekbid 2: Budi Pekerti & Akhlak Mulia</option>
                        <option value="Sekbid 3: Kepribadian Unggul & Bela Negara">Sekbid 3: Kepribadian Unggul & Bela Negara</option>
                        <option value="Sekbid 4: Prestasi, Seni, & Olahraga">Sekbid 4: Prestasi, Seni, & Olahraga</option>
                        <option value="Sekbid 5: Demokrasi, HAM, & Politik">Sekbid 5: Demokrasi, HAM, & Politik</option>
                        <option value="Sekbid 6: Kreativitas & Kewirausahaan">Sekbid 6: Kreativitas & Kewirausahaan</option>
                        <option value="Sekbid 7: Kualitas Jasmani & Kesehatan">Sekbid 7: Kualitas Jasmani & Kesehatan</option>
                        <option value="Sekbid 8: Sastra & Seni">Sekbid 8: Sastra & Seni</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Anggaran Rencana (Rp)</label>
                        <input type="number" name="budget_plan" min="0" required placeholder="e.g. 1500000" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Rencana</label>
                        <input type="date" name="planned_date" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Deskripsi Rencana Kegiatan</label>
                    <textarea name="description" rows="3" required placeholder="Jelaskan maksud dan tujuan kegiatan..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-xs text-gray-800 dark:text-white focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                    🚀 Buat Rencana Proker
                </button>
            </form>
        </div>

        <!-- Daftar Program Kerja & Form LPJ -->
        <div class="space-y-6 lg:col-span-2">
            @forelse($programs as $index => $prog)
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
                    <div class="flex justify-between items-start flex-wrap gap-2">
                        <div>
                            <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 font-extrabold text-[9px] rounded-lg">
                                {{ strtoupper($prog->sekbid) }}
                            </span>
                            <h3 class="font-extrabold text-gray-800 dark:text-white text-base mt-1.5">{{ $prog->title }}</h3>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-black {{ $prog->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($prog->status === 'in_progress' ? 'bg-indigo-50 text-indigo-700' : 'bg-amber-50 text-amber-700') }}">
                            {{ strtoupper($prog->status) }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-500 leading-relaxed">{{ $prog->description }}</p>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                        <div class="p-2 bg-gray-55/50 dark:bg-gray-900/50 rounded-xl">
                            <span class="block text-[8px] text-gray-400 font-bold uppercase">Anggaran Rencana</span>
                            <p class="font-extrabold text-gray-800 dark:text-white mt-0.5">Rp {{ number_format($prog->budget_plan, 0, ',', '.') }}</p>
                        </div>
                        <div class="p-2 bg-gray-55/50 dark:bg-gray-900/50 rounded-xl">
                            <span class="block text-[8px] text-gray-400 font-bold uppercase">Realisasi Dana</span>
                            <p class="font-extrabold text-teal-650 mt-0.5">Rp {{ number_format($prog->budget_actual, 0, ',', '.') }}</p>
                        </div>
                        <div class="p-2 bg-gray-55/50 dark:bg-gray-900/50 rounded-xl">
                            <span class="block text-[8px] text-gray-400 font-bold uppercase">Tanggal Rencana</span>
                            <p class="font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $prog->planned_date ? $prog->planned_date->format('d M Y') : '-' }}</p>
                        </div>
                        <div class="p-2 bg-gray-55/50 dark:bg-gray-900/50 rounded-xl">
                            <span class="block text-[8px] text-gray-400 font-bold uppercase">Tanggal Realisasi</span>
                            <p class="font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $prog->actual_date ? $prog->actual_date->format('d M Y') : '-' }}</p>
                        </div>
                    </div>

                    <!-- Update Realisasi / Form LPJ -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50">
                        <h4 class="font-bold text-xs text-gray-800 dark:text-white mb-2">📋 Laporan Pertanggungjawaban (LPJ) Realisasi</h4>
                        <form action="{{ route('osis.work-programs.update', $prog->id) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase">Status Progres</label>
                                    <select name="status" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                                        <option value="planned" {{ $prog->status === 'planned' ? 'selected' : '' }}>Planned</option>
                                        <option value="in_progress" {{ $prog->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $prog->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $prog->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase">Dana Realisasi Aktual (Rp)</label>
                                    <input type="number" name="budget_actual" value="{{ $prog->budget_actual }}" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1 text-xs text-gray-800 dark:text-white focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase">Tanggal Aktual</label>
                                    <input type="date" name="actual_date" value="{{ $prog->actual_date ? $prog->actual_date->toDateString() : '' }}" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1 text-xs text-gray-800 dark:text-white">
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase">Foto Dokumentasi Kegiatan</label>
                                    <input type="file" name="photos[]" multiple class="w-full text-[10px] text-gray-400 file:mr-2 file:py-1 file:px-3 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-gray-400 uppercase">Laporan Keterangan LPJ</label>
                                    <textarea name="report" rows="2" placeholder="Ulasan pelaksanaan kegiatan LPJ..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl p-3 text-xs text-gray-800 dark:text-white focus:outline-none">{{ $prog->report }}</textarea>
                                </div>
                                <div class="flex justify-end pt-1">
                                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white rounded-xl py-2 px-6 text-xs font-bold transition-all shadow-md">
                                        💾 Simpan LPJ
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400 italic text-center py-12">Belum ada rencana program kerja terdaftar.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
