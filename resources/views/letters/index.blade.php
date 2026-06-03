<x-app-layout>
    <x-slot name="title">Manajemen Surat Digital</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight flex items-center gap-2">
                    <span>✉️</span> Manajemen Dokumen & Surat Digital
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pembuatan, penerbitan, pencetakan, dan pengarsipan surat-surat resmi sekolah.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(in_array(auth()->user()->role, ['superadmin', 'admin', 'staf_tu']))
                <a href="{{ route('letters.templates.index') }}" class="inline-flex items-center justify-center bg-gray-250 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-850 dark:text-gray-150 rounded-xl py-2.5 px-4 text-sm font-black uppercase tracking-wider transition-all duration-300">
                    ⚙️ Kelola Template
                </a>
                <a href="{{ route('letters.bulk') }}" class="inline-flex items-center justify-center bg-teal-600 hover:bg-teal-700 text-white rounded-xl py-2.5 px-4 text-sm font-black uppercase tracking-wider transition-all duration-300 shadow-md">
                    👥 Cetak Massal
                </a>
                <button @click="$dispatch('open-modal', 'create-letter-modal')" class="inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white rounded-xl py-2.5 px-5 text-sm font-black uppercase tracking-wider shadow-lg shadow-primary-500/20 hover:shadow-primary-500/35 transition-all duration-300 transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                    Buat Surat Baru
                </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Panel Ringkasan KPI -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-xl text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l8-5.333a2 2 0 012.22 0l8 5.333A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-2.25-1.5a2 2 0 00-2.22 0l-2.25 1.5"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Surat Resmi</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $letters->total() }}</h3>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-amber-100 dark:bg-amber-900/50 rounded-xl text-amber-600 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Draft Disimpan</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $letters->where('status', 'draft')->count() }}</h3>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Diterbitkan (Issued)</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $letters->where('status', 'issued')->count() }}</h3>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
            <h4 class="text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Pencarian & Filter Surat
            </h4>

            <form action="{{ route('letters.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Tipe/Template Surat</label>
                    <select name="template_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500">
                        <option value="">Semua Tipe</option>
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}" {{ request('template_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Status Penerbitan</label>
                    <select name="status" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (Belum Bernomor)</option>
                        <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Diterbitkan (Issued)</option>
                        <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Dibatalkan (Revoked)</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2 items-end">
                    <div class="flex-grow">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Pencarian Kata Kunci</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor surat atau nama penerima..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500">
                    </div>
                    <button type="submit" class="bg-gray-800 dark:bg-gray-200 hover:bg-gray-900 dark:hover:bg-white text-white dark:text-gray-950 font-bold py-2.5 px-6 rounded-xl text-sm transition-all text-center">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-gray-700/30 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-wider">Nomor Surat</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-wider">Template / Tipe</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-wider">Penerima</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-wider">Tanggal Terbit</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/40 dark:divide-gray-700/40">
                        @forelse($letters as $letter)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-all">
                                <td class="px-6 py-4">
                                    <span class="font-extrabold text-sm text-gray-800 dark:text-white">
                                        {{ $letter->letter_number ?? 'Draf Surat' }}
                                    </span>
                                    <p class="text-[10px] text-gray-400 font-semibold mt-0.5">UUID: {{ substr($letter->uuid, 0, 8) }}...</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-bold border border-blue-100 dark:border-blue-900/30">
                                        {{ $letter->template->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($letter->student)
                                        <span class="font-bold text-sm text-gray-800 dark:text-white flex items-center gap-1.5">
                                            🎓 {{ $letter->student->name }}
                                        </span>
                                        <p class="text-[11px] text-gray-400 mt-0.5">NISN: {{ $letter->student->nisn }} | Kelas: {{ $letter->student->classroom?->name ?? '-' }}</p>
                                    @elseif($letter->employee)
                                        <span class="font-bold text-sm text-gray-800 dark:text-white flex items-center gap-1.5">
                                            🧑‍🏫 {{ $letter->employee->name }}
                                        </span>
                                        <p class="text-[11px] text-gray-400 mt-0.5">NIP: {{ $letter->employee->nip ?? '-' }}</p>
                                    @else
                                        <span class="text-sm font-semibold text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-sm text-gray-700 dark:text-gray-300">
                                        {{ $letter->issue_date->translatedFormat('d M Y') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($letter->status === 'draft')
                                        <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-lg text-xs font-bold border border-amber-100 dark:border-amber-900/30">
                                            DRAF
                                        </span>
                                    @elseif($letter->status === 'issued')
                                        <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-lg text-xs font-bold border border-emerald-100 dark:border-emerald-900/30">
                                            DITERBITKAN
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg text-xs font-bold border border-red-100 dark:border-red-900/30">
                                            DIBATALKAN
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-1 whitespace-nowrap">
                                    <a href="{{ route('letters.show', $letter->id) }}" class="inline-flex items-center bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg px-3 py-1.5 text-xs font-bold transition-all">
                                        👁️ Detail
                                    </a>
                                    @if($letter->status === 'draft' && in_array(auth()->user()->role, ['staf_tu', 'admin', 'superadmin']))
                                        <form action="{{ route('letters.issue', $letter->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menerbitkan surat ini? Setelah diterbitkan, nomor surat akan otomatis dihasilkan dan surat tidak dapat diubah lagi.')" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold transition-all">
                                                ✍️ Terbitkan
                                            </button>
                                        </form>
                                        <form action="{{ route('letters.destroy', $letter->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Hapus draf surat ini?')" class="bg-red-100 hover:bg-red-200 text-red-600 rounded-lg p-1.5 text-xs font-bold transition-all">
                                                🗑️
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400 font-semibold">
                                    Tidak ada data surat yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($letters->hasPages())
                <div class="px-6 py-4 border-t border-gray-200/50 dark:border-gray-700/50">
                    {{ $letters->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Pilihan Template -->
    <x-modal name="create-letter-modal" focusable>
        <div class="p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                Pilih Jenis Surat Resmi
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Silakan pilih salah satu template aktif untuk mulai menyusun draf surat resmi.
            </p>

            <form action="{{ route('letters.create') }}" method="GET" class="mt-6 space-y-4">
                <div>
                    <label for="template_id" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Template Surat</label>
                    <select name="template_id" id="template_id" required class="w-full bg-gray-55 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                        <option value="">-- Pilih Template --</option>
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="$dispatch('close')" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-bold py-2.5 px-5 rounded-xl text-sm transition-all">
                        Batal
                    </button>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition-all">
                        Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
