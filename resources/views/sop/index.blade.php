<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </span>
                    Policy & Standard Operating Procedure (SOP) Governance
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Repositori resmi Prosedur Operasional Standar (SOP) institusi dengan manajemen versi dan pelacakan persetujuan.</p>
            </div>
            <a href="{{ route('sop.compliance') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Laporan Kepatuhan Staf (Compliance Rate)
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Create SOP Draft Form Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Terbitkan Dokumen Kebijakan / SOP Baru</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Buat aturan operasional resmi untuk disebarkan dan disetujui oleh seluruh staf.</p>
                </div>

                <form action="{{ route('sop.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kode Dokumen SOP</label>
                        <input type="text" name="code" required value="SOP-IT-2026-001" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Dokumen Kebijakan</label>
                        <input type="text" name="title" required value="SOP Tata Kelola Keamanan Kata Sandi & Akses Sistem" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kategori Kebijakan</label>
                        <select name="category" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="it">IT & Cyber Security</option>
                            <option value="academic">Akademik & Pembelajaran</option>
                            <option value="administrative">Tata Usaha & Administrasi</option>
                            <option value="financial">Keuangan & Anggaran</option>
                            <option value="safety">Keselamatan & K3</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Berlaku Effective</label>
                        <input type="date" name="effective_date" required value="{{ date('Y-m-d') }}" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jadwal Peninjauan Ulang (Review)</label>
                        <input type="date" name="review_date" required value="{{ date('Y-m-d', strtotime('+1 year')) }}" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Isi Dokumen Prosedur Operasional Standar (HTML)</label>
                        <textarea name="content_html" rows="4" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm p-4 font-mono">1. Setiap civitas akademika wajib memperbarui kata sandi secara berkala setiap 90 hari.
2. Panjang kata sandi minimal 12 karakter mengombinasikan huruf kapital, angka, dan simbol.
3. Dilarang membagikan kredensial autentikasi kepada pihak ketiga tanpa izin sah Tim IT GRC.</textarea>
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30">
                            Terbitkan SOP Ke Repositori
                        </button>
                    </div>
                </form>
            </div>

            <!-- Searchable SOP Catalog Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Katalog Dokumen SOP Resmi Institusi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih dokumen untuk membaca detail dan menandatangani konfirmasi persetujuan.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Kode & Judul SOP</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4">Versi</th>
                                <th class="px-6 py-4">Mulai Berlaku</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi Pembacaan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($sops as $sop)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold block text-[11px]">{{ $sop->code }}</span>
                                        <span class="font-bold text-gray-900 dark:text-white block">{{ $sop->title }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-gray-500 text-[11px] uppercase">{{ $sop->category }}</td>
                                    <td class="px-6 py-4 font-mono font-bold text-xs">v{{ $sop->version }}.0</td>
                                    <td class="px-6 py-4 font-mono text-gray-500 text-[11px]">{{ $sop->effective_date->format('d M Y') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 font-mono text-[10px] font-bold rounded uppercase">
                                            {{ $sop->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('sop.show', $sop->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                            Buka Dokumen SOP
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada dokumen SOP terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
