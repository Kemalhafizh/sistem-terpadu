<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('uks.visits.index') }}" class="hover:text-teal-600 transition-colors">
                    <span class="w-8 h-8 bg-teal-100 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </span>
                </a>
                <div>
                    <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                        {{ __('Detail Catatan Medis UKS') }}
                    </h2>
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">ID Kunjungan: {{ $visit->uuid }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('uks.student-history', $visit->student->id) }}" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                    <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Riwayat Siswa
                </a>

                <form action="{{ route('uks.visits.destroy', $visit->uuid) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus catatan kunjungan ini? Stok obat yang diberikan akan dikembalikan otomatis.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-950/20 dark:hover:bg-red-900/30 rounded-xl text-xs font-black uppercase tracking-wider transition-all border border-red-200 dark:border-red-900/30 shadow-sm">
                        <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Batalkan Kunjungan
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- COLUMN 1 & 2: Clinical Details (2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- General Medical Case Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-6">
                        <div class="flex justify-between items-center flex-wrap gap-4">
                            <h4 class="text-base font-black text-gray-900 dark:text-white flex items-center">
                                <span class="w-2 h-5 bg-teal-600 rounded-full mr-3"></span>
                                Catatan Kasus Klinis
                            </h4>

                            <div class="flex items-center gap-2">
                                <!-- Severity Badge -->
                                @if($visit->severity === 'berat')
                                    <span class="px-3 py-1.5 bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/30 rounded-xl text-xs font-black uppercase tracking-wider">Berat</span>
                                @elseif($visit->severity === 'sedang')
                                    <span class="px-3 py-1.5 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/30 rounded-xl text-xs font-black uppercase tracking-wider">Sedang</span>
                                @else
                                    <span class="px-3 py-1.5 bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border border-teal-200 dark:border-teal-900/30 rounded-xl text-xs font-black uppercase tracking-wider">Ringan</span>
                                @endif

                                <!-- Disposition Badge -->
                                @php
                                    $dispLabels = [
                                        'kembali_kelas' => ['bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/30', 'Kembali Kelas'],
                                        'istirahat_uks' => ['bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border-teal-200 dark:border-teal-900/30', 'Istirahat UKS'],
                                        'pulang' => ['bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-900/30', 'Dipulangkan'],
                                        'rujuk_rs' => ['bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-900/30', 'Dirujuk ke RS']
                                    ];
                                    $disp = $dispLabels[$visit->disposition] ?? ['bg-gray-50 text-gray-700', 'Istirahat'];
                                @endphp
                                <span class="px-3 py-1.5 {{ $disp[0] }} border rounded-xl text-xs font-black uppercase tracking-wider">{{ $disp[1] }}</span>
                            </div>
                        </div>

                        <!-- Case Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <!-- Complaint Section -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Keluhan Utama</span>
                                <p class="text-base font-extrabold text-teal-600 dark:text-teal-400">"{{ $visit->complaint }}"</p>
                            </div>

                            <!-- Diagnosis Section -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Diagnosis Sementara</span>
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $visit->diagnosis ?: 'Tidak Ada Diagnosis Spesifik' }}</p>
                            </div>

                            <!-- Treatment Section -->
                            <div class="space-y-1 md:col-span-2">
                                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Tindakan Medis / Penanganan</span>
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 leading-relaxed bg-gray-50 dark:bg-gray-900 rounded-2xl p-4 border border-gray-100 dark:border-gray-800">
                                    {{ $visit->treatment ?: 'Tidak ada catatan tindakan penanganan.' }}
                                </p>
                            </div>

                            <!-- Handler Info -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Ditangani Oleh (Petugas)</span>
                                <p class="text-sm font-extrabold text-gray-900 dark:text-gray-100">{{ $visit->handler->name ?? '-' }}</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide">Pendidik / Tenaga Kependidikan</p>
                            </div>

                            <!-- Timing Info -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Durasi Penanganan</span>
                                <p class="text-sm font-extrabold text-gray-900 dark:text-gray-100">
                                    {{ $visit->visit_time->translatedFormat('d M Y, H:i') }} WIB
                                </p>
                                @if($visit->leave_time)
                                    <p class="text-xs text-gray-400 font-bold">
                                        s/d {{ $visit->leave_time->translatedFormat('d M Y, H:i') }} WIB 
                                        ({{ $visit->leave_time->diffInMinutes($visit->visit_time) }} Menit)
                                    </p>
                                @else
                                    <p class="text-xs text-amber-500 font-extrabold flex items-center mt-0.5">
                                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-1.5 animate-pulse"></span>
                                        Sedang Berada di UKS
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Notes Section -->
                        @if($visit->notes)
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-700 space-y-1">
                                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Catatan Tambahan Petugas</span>
                                <p class="text-xs text-gray-600 dark:text-gray-400 italic">"{{ $visit->notes }}"</p>
                            </div>
                        @endif
                    </div>

                    <!-- Medicines Given Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-6">
                        <h4 class="text-base font-black text-gray-900 dark:text-white flex items-center">
                            <span class="w-2 h-5 bg-emerald-600 rounded-full mr-3"></span>
                            Obat-Obatan Yang Diberikan
                        </h4>

                        @if($visit->medicines->count() > 0)
                            <div class="overflow-hidden border border-gray-100 dark:border-gray-700 rounded-2xl">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-900/60 border-b border-gray-100 dark:border-gray-700">
                                            <th class="p-4 text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Nama Obat</th>
                                            <th class="p-4 text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Kategori</th>
                                            <th class="p-4 text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 text-center">Kuantitas</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($visit->medicines as $medicine)
                                            <tr>
                                                <td class="p-4 text-sm font-extrabold text-gray-900 dark:text-white">
                                                    {{ $medicine->name }}
                                                </td>
                                                <td class="p-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    {{ $medicine->category }}
                                                </td>
                                                <td class="p-4 text-sm font-extrabold text-gray-900 dark:text-white text-center">
                                                    {{ $medicine->pivot->quantity }} Pcs
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-6 text-center opacity-50 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800">
                                <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400">Tidak ada pemberian obat-obatan terdaftar.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- COLUMN 3: Student Clinical Card & Parents WA Status (1/3) -->
                <div class="space-y-6">
                    <!-- Student Demographics Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-6">
                        <div class="flex flex-col items-center text-center pb-4 border-b border-gray-100 dark:border-gray-700">
                            <div class="w-20 h-20 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-3xl flex items-center justify-center font-extrabold text-3xl mb-4 shadow-inner">
                                {{ substr($visit->student->user->name ?? '-', 0, 1) }}
                            </div>
                            <h5 class="text-base font-black text-gray-900 dark:text-white">{{ $visit->student->user->name ?? '-' }}</h5>
                            <p class="text-xs text-teal-600 dark:text-teal-400 font-bold uppercase tracking-wider mt-1">{{ $visit->student->classroom->name ?? '-' }}</p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold tracking-widest mt-0.5">NIS: {{ $visit->student->nis ?? '-' }}</p>
                        </div>

                        <div class="space-y-4">
                            <!-- Blood Type -->
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gol. Darah</span>
                                <span class="px-3 py-1 bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 rounded-xl text-xs font-black">
                                    {{ $visit->student->blood_type ?: 'Tidak Diketahui' }}
                                </span>
                            </div>

                            <!-- Allergies -->
                            <div class="space-y-1">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Riwayat Alergi</span>
                                <div class="p-3 bg-red-50/50 dark:bg-red-950/10 border border-red-100/50 dark:border-red-950/30 rounded-xl text-xs text-red-700 dark:text-red-400 font-bold leading-relaxed">
                                    {{ $visit->student->allergies ?: 'Tidak ada riwayat alergi terdaftar.' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- WhatsApp Parent Alert Status Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-6">
                        <h4 class="text-base font-black text-gray-900 dark:text-white flex items-center">
                            <span class="w-2 h-5 bg-teal-700 rounded-full mr-3"></span>
                            Notifikasi Orang Tua
                        </h4>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                @if($visit->parent_notified)
                                    <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Terkirim</h5>
                                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold mt-0.5">WhatsApp Alert sukses disalurkan.</p>
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg-gray-100 dark:bg-gray-900 text-gray-400 dark:text-gray-500 rounded-xl flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="text-xs font-black text-gray-600 dark:text-gray-400 uppercase tracking-widest">Tidak Dikirim</h5>
                                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold mt-0.5">Kasus ringan / normal.</p>
                                    </div>
                                @endif
                            </div>

                            @if($visit->student->parent)
                                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 text-xs space-y-2 font-semibold text-gray-600 dark:text-gray-400">
                                    <div class="flex justify-between">
                                        <span>Orang Tua/Wali</span>
                                        <span class="font-extrabold text-gray-800 dark:text-gray-200">{{ $visit->student->parent->name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>No. Telepon WA</span>
                                        <span class="font-extrabold text-gray-800 dark:text-gray-200">{{ $visit->student->parent->phone_number ?: '-' }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="p-3 bg-amber-50/50 dark:bg-amber-950/10 border border-amber-100/50 dark:border-amber-900/30 rounded-xl text-[10px] text-amber-600 dark:text-amber-500 font-bold leading-relaxed uppercase tracking-wider text-center">
                                    Wali murid tidak terdaftar di sistem. Pemberitahuan WA darurat dinonaktifkan.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
