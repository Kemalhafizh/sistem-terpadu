<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('spmb.admin.dashboard') }}" class="mr-4 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Pendaftar: <span class="font-black text-emerald-600">{{ $registration->registration_number }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ modalOpen: false, modalImgSrc: '', modalTitle: '' }">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Informasi Status -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex flex-wrap justify-between items-center gap-4">
                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Status Kelulusan</span>
                    @if($registration->status == 'pending')
                        <span class="px-3 py-1 bg-amber-50 text-amber-700 font-bold border border-amber-200 rounded-lg text-xs uppercase">Menunggu Seleksi</span>
                    @elseif($registration->status == 'reviewing')
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 font-bold border border-blue-200 rounded-lg text-xs uppercase">Sedang Direview</span>
                    @elseif($registration->status == 'accepted')
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 rounded-lg text-xs uppercase">Diterima / Lulus</span>
                    @else
                        <span class="px-3 py-1 bg-red-50 text-red-700 font-bold border border-red-200 rounded-lg text-xs uppercase">Tidak Lulus</span>
                    @endif
                </div>

                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Status Verifikasi Berkas</span>
                    @if($registration->document_status == 'verified')
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 rounded-lg text-xs uppercase">Semua Berkas Terverifikasi</span>
                    @elseif($registration->document_status == 'rejected')
                        <span class="px-3 py-1 bg-red-50 text-red-700 font-bold border border-red-200 rounded-lg text-xs uppercase">Ada Berkas Ditolak</span>
                    @else
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 font-bold border border-blue-200 rounded-lg text-xs uppercase">Pengecekan Berkas</span>
                    @endif
                </div>

                <div class="flex gap-2">
                    @if($registration->status == 'pending' || $registration->status == 'reviewing')
                        <form action="{{ route('spmb.admin.status', $registration) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="reviewing">
                            <button type="submit" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold rounded-lg border border-blue-200 text-xs transition-all">Tandai Review</button>
                        </form>
                        <form action="{{ route('spmb.admin.status', $registration) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENOLAK pendaftar ini?');">
                            @csrf
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 font-bold rounded-lg border border-red-200 text-xs transition-all">Tolak Manual</button>
                        </form>
                        <form action="{{ route('spmb.admin.status', $registration) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin menerima anak ini dan membuatkan akun Siswa untuknya secara otomatis?');">
                            @csrf
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit" class="px-5 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-black rounded-lg text-xs uppercase tracking-wider shadow-md transition-all">Terima & Buat Akun</button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Detail Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Biodata & Nilai Rapor -->
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100">
                            <h3 class="font-bold text-slate-800 text-sm">Biodata Lengkap Calon Siswa</h3>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6 text-xs">
                                <div class="sm:col-span-1">
                                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</dt>
                                    <dd class="mt-1 text-sm font-black text-slate-900">{{ $registration->full_name }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NISN</dt>
                                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $registration->nisn ?: '-' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jenis Kelamin</dt>
                                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $registration->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tempat / Tanggal Lahir</dt>
                                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $registration->birth_place }}, {{ $registration->birth_date?->format('d F Y') ?: '-' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Asal Sekolah</dt>
                                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $registration->previous_school }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gelombang & Jalur Masuk</dt>
                                    <dd class="mt-1 text-sm font-bold text-slate-900">
                                        {{ $registration->spmbWave->name ?? 'Gelombang 1' }} | {{ $registration->spmbTrack->name ?? 'Reguler' }}
                                    </dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Wali / Orang Tua</dt>
                                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $registration->parent_name ?: '-' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">WhatsApp Orang Tua</dt>
                                    <dd class="mt-1 text-sm font-bold text-slate-900 flex items-center">
                                        {{ $registration->parent_phone }}
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $registration->parent_phone) }}" target="_blank" class="ml-2 text-green-500 hover:text-green-600"> Chat</a>
                                    </dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Lengkap</dt>
                                    <dd class="mt-1 text-sm text-slate-950 font-medium">{{ $registration->address ?: '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Nilai Rapor & Hasil Tes -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100">
                            <h3 class="font-bold text-slate-800 text-sm">Nilai Seleksi & Akademik</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-xs mb-6">
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-center">
                                    <span class="block text-[9px] font-bold text-slate-400 uppercase">Matematika</span>
                                    <strong class="text-sm font-black text-slate-800 mt-1 block">{{ $registration->rapor_scores['matematika'] ?? '-' }}</strong>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-center">
                                    <span class="block text-[9px] font-bold text-slate-400 uppercase">IPA</span>
                                    <strong class="text-sm font-black text-slate-800 mt-1 block">{{ $registration->rapor_scores['ipa'] ?? '-' }}</strong>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-center">
                                    <span class="block text-[9px] font-bold text-slate-400 uppercase">IPS</span>
                                    <strong class="text-sm font-black text-slate-800 mt-1 block">{{ $registration->rapor_scores['ips'] ?? '-' }}</strong>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-center">
                                    <span class="block text-[9px] font-bold text-slate-400 uppercase">B. Indonesia</span>
                                    <strong class="text-sm font-black text-slate-800 mt-1 block">{{ $registration->rapor_scores['indonesia'] ?? '-' }}</strong>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-center">
                                    <span class="block text-[9px] font-bold text-slate-400 uppercase">B. Inggris</span>
                                    <strong class="text-sm font-black text-slate-800 mt-1 block">{{ $registration->rapor_scores['inggris'] ?? '-' }}</strong>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-inter border-t border-slate-100 pt-6">
                                <div>
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hafalan Quran (Tahfidz)</span>
                                    <strong class="text-slate-800 mt-1 block">{{ $registration->tahfidz_data['juz_count'] ?? 0 }} Juz</strong>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nilai Ujian Masuk</span>
                                    <strong class="text-slate-800 mt-1 block">{{ $registration->entrance_test_score ?: 'Belum Tes' }}</strong>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Skor Terhitung & Peringkat</span>
                                    <strong class="text-emerald-700 mt-1 block">{{ $registration->total_score ?: '-' }} (Rank #{{ $registration->rank ?: '-' }})</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Berkas -->
                <div class="md:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100">
                            <h3 class="font-bold text-slate-800 text-sm">Unggahan Berkas & Verifikasi</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            @forelse($registration->spmbDocuments ?? [] as $doc)
                                <div class="border border-slate-200 rounded-xl p-4 space-y-3 bg-slate-50">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="text-xs font-black text-slate-800 capitalize">{{ $doc->document_type == 'kk' ? 'Kartu Keluarga' : ($doc->document_type == 'certificate' ? 'Ijazah / SKL' : $doc->document_type) }}</p>
                                            <span class="text-[9px] font-bold uppercase tracking-wider mt-0.5 block
                                                @if($doc->verification_status === 'verified') text-emerald-600
                                                @elseif($doc->verification_status === 'rejected') text-red-500
                                                @else text-amber-600 @endif"
                                            >
                                                {{ strtoupper($doc->verification_status) }}
                                            </span>
                                        </div>
                                        <button @click="modalOpen = true; modalImgSrc = '{{ route('spmb.admin.documents.file', $doc->id) }}'; modalTitle = 'Preview {{ ucfirst($doc->document_type) }}'" class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 text-[10px] font-bold rounded-lg shadow-sm">Preview</button>
                                    </div>

                                    @if($doc->verification_status === 'pending')
                                        <form action="{{ route('spmb.admin.documents.verify', $doc->id) }}" method="POST" class="space-y-2">
                                            @csrf
                                            <input type="text" name="rejection_reason" placeholder="Alasan jika ditolak..." class="block w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-[10px] focus:outline-none">
                                            <div class="flex gap-1.5">
                                                <button type="submit" name="verification_status" value="rejected" class="flex-1 py-1 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 font-bold rounded-lg text-[10px] uppercase">Tolak</button>
                                                <button type="submit" name="verification_status" value="verified" class="flex-1 py-1 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 font-bold rounded-lg text-[10px] uppercase">Setuju</button>
                                            </div>
                                        </form>
                                    @elseif($doc->rejection_reason)
                                        <p class="text-[10px] text-red-500 font-semibold bg-red-50 p-2 rounded-lg">Alasan Tolak: {{ $doc->rejection_reason }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 text-center py-4">Belum ada lampiran dokumen digital yang diunggah.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pop-up Modal untuk Gambar -->
        <div x-show="modalOpen" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" @click="modalOpen = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" x-transition.scale class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <div class="bg-white p-4 flex justify-between items-center border-b border-slate-100">
                        <h3 class="text-lg leading-6 font-bold text-slate-900" x-text="modalTitle"></h3>
                        <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-500">
                            <span class="sr-only">Tutup</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="bg-slate-100 p-4 flex justify-center max-h-[80vh] overflow-auto">
                        <img :src="modalImgSrc" alt="Document Preview" class="max-w-full h-auto rounded shadow-sm">
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
