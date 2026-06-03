<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('spmb.admin.index') }}" class="mr-4 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Pendaftar: <span class="font-black text-primary-600">{{ $registration->registration_number }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ modalOpen: false, modalImgSrc: '', modalTitle: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Informasi Status -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 font-semibold mb-1">Status Pendaftaran Saat Ini:</p>
                    @if($registration->status == 'pending')
                        <span class="px-4 py-2 bg-amber-100 text-amber-800 font-bold rounded-lg text-sm uppercase tracking-wider">Baru Masuk</span>
                    @elseif($registration->status == 'reviewing')
                        <span class="px-4 py-2 bg-blue-100 text-blue-800 font-bold rounded-lg text-sm uppercase tracking-wider">Sedang Dicek</span>
                    @elseif($registration->status == 'accepted')
                        <span class="px-4 py-2 bg-emerald-100 text-emerald-800 font-bold rounded-lg text-sm uppercase tracking-wider">Diterima</span>
                    @else
                        <span class="px-4 py-2 bg-red-100 text-red-800 font-bold rounded-lg text-sm uppercase tracking-wider">Ditolak</span>
                    @endif
                </div>

                <div class="flex space-x-3">
                    @if($registration->status == 'pending' || $registration->status == 'reviewing')
                        <form action="{{ route('spmb.admin.status', $registration->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="reviewing">
                            <button type="submit" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold rounded-lg transition-colors border border-blue-200 shadow-sm text-sm">
                                Tandai Sedang Dicek
                            </button>
                        </form>
                        <form action="{{ route('spmb.admin.status', $registration->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENOLAK pendaftar ini?');">
                            @csrf
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 font-bold rounded-lg transition-colors border border-red-200 shadow-sm text-sm">
                                Tolak
                            </button>
                        </form>
                        <form action="{{ route('spmb.admin.status', $registration->id) }}" method="POST" onsubmit="return confirm('Sihir Konversi akan dijalankan! Apakah Anda yakin menerima anak ini dan membuatkan akun Siswa untuknya secara otomatis?');">
                            @csrf
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit" class="px-6 py-2 bg-emerald-500 text-white hover:bg-emerald-600 font-black rounded-lg transition-all shadow-md transform hover:-translate-y-0.5 text-sm uppercase tracking-widest flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                Terima Siswa (Konversi)
                            </button>
                        </form>
                    @elseif($registration->status == 'accepted')
                        <div class="px-6 py-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm font-bold flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Siswa Ini Telah Dikonversi Menjadi Akun Resmi.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Detail Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Biodata -->
                <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800">Biodata Calon Siswa</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                            <div class="sm:col-span-1">
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Lengkap</dt>
                                <dd class="mt-1 text-base font-black text-gray-900">{{ $registration->full_name }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">NISN</dt>
                                <dd class="mt-1 text-base font-bold text-gray-900">{{ $registration->nisn ?: '-' }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Asal Sekolah</dt>
                                <dd class="mt-1 text-sm font-bold text-gray-900">{{ $registration->previous_school }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor WhatsApp Wali</dt>
                                <dd class="mt-1 text-sm font-bold text-gray-900 flex items-center">
                                    {{ $registration->parent_phone }}
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $registration->parent_phone) }}" target="_blank" class="ml-2 text-green-500 hover:text-green-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.183-.573c.978.582 1.894.882 3.145.882 3.182 0 5.769-2.586 5.769-5.766 0-3.181-2.587-5.766-5.766-5.766m3.352 8.016c-.171.498-.823.906-1.157.94-.356.036-.921.144-2.884-.664-2.37-1.196-3.882-3.626-4.001-3.785-.119-.158-.956-1.272-.956-2.428s.607-1.722.822-1.954c.214-.232.467-.291.621-.291.155 0 .31.002.449.008.148.006.347-.058.544.417.202.486.671 1.636.732 1.761.061.125.102.271.025.426-.076.155-.116.252-.232.387-.116.136-.242.292-.347.404-.116.126-.241.264-.105.498.136.234.606 1.001 1.298 1.619.894.798 1.649 1.042 1.884 1.157.234.116.37.098.508-.061.137-.158.591-.689.749-.926.158-.236.315-.197.528-.116.213.08 1.346.634 1.576.749.231.116.385.174.441.272.056.098.056.571-.115 1.069z"></path></svg>
                                    </a>
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Mendaftar</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $registration->created_at?->format('d M Y, H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Berkas -->
                <div class="md:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800">Berkas Lampiran</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            
                            <!-- Kartu Keluarga -->
                            <div class="border border-gray-200 rounded-lg p-3 flex justify-between items-center hover:bg-gray-50 transition-colors">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">Kartu Keluarga (KK)</p>
                                        <p class="text-[10px] text-gray-500">{{ $registration->document_kk ? 'Telah Diunggah' : 'Tidak Ada' }}</p>
                                    </div>
                                </div>
                                @if($registration->document_kk)
                                    @if(Str::endsWith(strtolower($registration->document_kk), ['.jpg', '.jpeg', '.png']))
                                        <button @click="modalOpen = true; modalImgSrc = '{{ Storage::url($registration->document_kk) }}'; modalTitle = 'Kartu Keluarga'" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-md">Lihat</button>
                                    @else
                                        <a href="{{ Storage::url($registration->document_kk) }}" target="_blank" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-md">Buka PDF</a>
                                    @endif
                                @endif
                            </div>

                            <!-- Ijazah/SKL -->
                            <div class="border border-gray-200 rounded-lg p-3 flex justify-between items-center hover:bg-gray-50 transition-colors">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-amber-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">Ijazah / SKL</p>
                                        <p class="text-[10px] text-gray-500">{{ $registration->document_certificate ? 'Telah Diunggah' : 'Tidak Ada' }}</p>
                                    </div>
                                </div>
                                @if($registration->document_certificate)
                                    @if(Str::endsWith(strtolower($registration->document_certificate), ['.jpg', '.jpeg', '.png']))
                                        <button @click="modalOpen = true; modalImgSrc = '{{ Storage::url($registration->document_certificate) }}'; modalTitle = 'Ijazah / SKL'" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-md">Lihat</button>
                                    @else
                                        <a href="{{ Storage::url($registration->document_certificate) }}" target="_blank" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-md">Buka PDF</a>
                                    @endif
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pop-up Modal untuk Gambar -->
        <div x-show="modalOpen" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="modalOpen = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" x-transition.scale class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <div class="bg-white p-4 flex justify-between items-center border-b border-gray-100">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" x-text="modalTitle"></h3>
                        <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Tutup</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="bg-gray-100 p-4 flex justify-center max-h-[80vh] overflow-auto">
                        <img :src="modalImgSrc" alt="Document Preview" class="max-w-full h-auto rounded shadow-sm">
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
