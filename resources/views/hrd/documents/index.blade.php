<x-app-layout>
    <x-slot name="title">Berkas Kepegawaian - {{ $employee->user->name }}</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Dokumen Digital Pegawai
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Pegawai: {{ $employee->user->name }} | NIY/NIP: {{ $employee->employee_number }}
                </p>
            </div>
            <a href="{{ route('hrd.employees.show', $employee->id) }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                 Lihat Profil
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Upload Berkas -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl self-start">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-4"> Unggah Berkas Baru</h3>
            
            <form action="{{ route('hrd.employees.documents.store', $employee->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama / Judul Dokumen</label>
                    <input type="text" name="title" required placeholder="SK Pengangkatan Yayasan..." class="w-full bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Jenis Dokumen</label>
                    <select name="type" required class="w-full bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="sk">SK Pengangkatan / Tugas</option>
                        <option value="kontrak">Kontrak Kerja</option>
                        <option value="sertifikasi">Sertifikat Kompetensi / Guru</option>
                        <option value="ijazah">Ijazah / Transkrip</option>
                        <option value="ktp">KTP / Identitas Diri</option>
                        <option value="npwp">NPWP</option>
                        <option value="bpjs">BPJS Kesehatan / Ketenagakerjaan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih File (PDF/JPG/PNG, Max 4MB)</label>
                    <input type="file" name="file" required class="w-full text-xs text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Terbit</label>
                        <input type="date" name="issue_date" class="w-full bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Masa Berlaku (Expiry)</label>
                        <input type="date" name="expiry_date" class="w-full bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Catatan Tambahan</label>
                    <textarea name="notes" rows="3" placeholder="Tulis keterangan dokumen jika diperlukan..." class="w-full bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-xs text-gray-800 dark:text-white focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                     Unggah Dokumen
                </button>
            </form>
        </div>

        <!-- Daftar Dokumen Terunggah -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl lg:col-span-2 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm"> Berkas Tersimpan</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Berkas</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Masa Berlaku</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @forelse($employee->documents as $doc)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-all">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800 dark:text-white">{{ $doc->title }}</div>
                                    @if($doc->notes)
                                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $doc->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400 font-extrabold text-[10px] rounded-lg">
                                        {{ strtoupper($doc->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    @if($doc->expiry_date)
                                        <span class="font-medium {{ $doc->expiry_date->isPast() ? 'text-rose-600 dark:text-rose-400 font-bold' : '' }}">
                                            {{ $doc->expiry_date->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic">Tanpa Expiry</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-1.5 px-3 text-xs font-bold transition-all">
                                         Buka
                                    </a>
                                    <form action="{{ route('hrd.employees.documents.destroy', [$employee->id, $doc->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white rounded-xl py-1.5 px-3 text-xs font-bold transition-all shadow-md">
                                             Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-12 text-gray-400 italic">
                                    Belum ada dokumen digital diunggah untuk pegawai ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
