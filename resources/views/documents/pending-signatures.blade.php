<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                    </span>
                    Digital Signature & E-Stamp Workflow
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Antrean penandatanganan dokumen resmi institusi berbasis kriptografi SHA-256 dan verifikasi QR Code.</p>
            </div>
            <a href="{{ route('documents.signature_history') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Riwayat Audit Tanda Tangan
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Request Signature Trigger Form (Admin / Authorized User) -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Buat Permintaan Tanda Tangan Digital Baru</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Ajukan dokumen resmi ke antrean penandatanganan pejabat berwenang.</p>
                </div>

                <form action="{{ route('documents.test_request') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe Dokumen Resmi</label>
                        <select name="document_type" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="Rapor Akademik">Rapor Akademik Siswa</option>
                            <option value="Surat Keputusan (SK)">Surat Keputusan (SK) Pengangkatan</option>
                            <option value="Kwitansi Pembayaran">Kwitansi Digital SPP</option>
                            <option value="Surat Keterangan">Surat Keterangan Sekolah</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">ID Dokumen Target</label>
                        <input type="number" name="document_id" value="{{ rand(100, 999) }}" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white font-mono rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Peran Penandatangan</label>
                        <select name="signer_role" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="Kepala Sekolah">Kepala Sekolah</option>
                            <option value="Wakil Kepala Sekolah">Wakil Kepala Sekolah</option>
                            <option value="Bendahara Sekolah">Bendahara Sekolah</option>
                            <option value="Wali Kelas">Wali Kelas</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30">
                            Ajukan Permintaan Tanda Tangan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Pending Documents Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Antrean Dokumen Menunggu Tanda Tangan Anda</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih dokumen untuk memeriksa pratinjau dan membubuhi e-stamp digital.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Kode QR Verifikasi</th>
                                <th class="px-6 py-4">Tipe Dokumen</th>
                                <th class="px-6 py-4">ID Dokumen</th>
                                <th class="px-6 py-4">Jabatan Signer</th>
                                <th class="px-6 py-4">Tanggal Pengajuan</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($pendingSignatures as $sig)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $sig->qr_verification_code }}
                                    </td>
                                    <td class="px-6 py-4 font-bold">{{ $sig->document_type }}</td>
                                    <td class="px-6 py-4 font-mono">#{{ $sig->document_id }}</td>
                                    <td class="px-6 py-4 font-mono text-gray-500">{{ $sig->signer_role }}</td>
                                    <td class="px-6 py-4 font-mono text-gray-500">{{ $sig->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('documents.sign_form', $sig->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                            Buka & Tanda Tangani
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                        Tidak ada dokumen yang menunggu tanda tangan Anda saat ini.
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
