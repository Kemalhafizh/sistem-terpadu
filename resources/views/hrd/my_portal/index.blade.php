<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-black text-3xl text-gray-900 tracking-tight">Portal Kepegawaian</h2>
                <p class="text-sm text-gray-500 font-medium mt-1">Presensi berbasis GPS, pengajuan izin cuti, dan histori slip gaji bulanan Anda.</p>
            </div>
            <div class="mt-4 md:mt-0 bg-primary-50 border border-primary-100 rounded-2xl px-6 py-3 flex items-center">
                <div class="w-2.5 h-2.5 bg-primary-500 rounded-full mr-2.5 animate-pulse"></div>
                <span class="text-xs font-black text-primary-800 uppercase tracking-widest">{{ $employee->employee_number }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full sm:px-6 lg:px-8 space-y-8">
            
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-xl flex items-center shadow-sm">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl flex items-center shadow-sm">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Kiri: Presensi Hari Ini (dari mesin sidik jari) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 text-center sticky top-8 space-y-6">
                        <div>
                            <div class="w-20 h-20 mx-auto bg-gray-900 text-white rounded-3xl flex items-center justify-center font-black text-3xl mb-4 shadow-md">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <h3 class="font-black text-xl text-gray-900 mb-0.5">{{ auth()->user()->name }}</h3>
                            <p class="text-xs font-bold text-primary-600 uppercase tracking-widest">{{ $employee->position }}</p>
                        </div>

                        <div class="border-t border-gray-100 pt-6 space-y-4">
                            <p class="text-sm font-bold text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
                            {{-- FASE 119: staff sign in on the fingerprint machine; TU imports its log. --}}
                            @if($todayAttendance)
                                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 space-y-1">
                                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ __('Presensi hari ini') }}</p>
                                    <p class="text-sm font-bold text-gray-800">{{ __('Jam masuk') }}: {{ $todayAttendance->check_in_time ?? '–' }} | {{ __('Jam pulang') }}: {{ $todayAttendance->check_out_time ?? '–' }}</p>
                                </div>
                            @else
                                <p class="text-xs text-gray-500">{{ __('Belum ada data presensi hari ini. Presensi pegawai dicatat mesin sidik jari dan masuk setelah Tata Usaha mengimpor log mesin.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Kanan: Form Pengajuan Cuti, Histori & Slip Gaji -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Form Pengajuan Cuti -->
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-black text-lg text-gray-900">Pengajuan Izin / Cuti</h3>
                            <p class="text-xs text-gray-500 mt-1">Kirimkan permohonan ketidakhadiran secara terencana di sini.</p>
                        </div>
                        <div class="p-6">
                            <form action="{{ route('my-hrd.leaves') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Jenis Ketidakhadiran</label>
                                        <select name="type" required class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 focus:ring-primary-500 font-medium text-sm">
                                            <option value="sakit">Sakit</option>
                                            <option value="izin">Izin Keperluan Pribadi</option>
                                            <option value="cuti_tahunan">Cuti Tahunan</option>
                                            <option value="cuti_melahirkan">Cuti Melahirkan</option>
                                            <option value="umroh_haji">Umroh / Haji</option>
                                            <option value="dinas_luar">Dinas Luar</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Lampiran Bukti (Opsional)</label>
                                        <input type="file" name="attachment" accept=".pdf,.jpg,.png" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 border border-gray-200 rounded-xl">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Tanggal Mulai</label>
                                        <input type="date" name="start_date" required min="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 focus:ring-primary-500 font-medium text-sm text-gray-600">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Tanggal Berakhir</label>
                                        <input type="date" name="end_date" required min="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 focus:ring-primary-500 font-medium text-sm text-gray-600">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Alasan Detail</label>
                                        <textarea name="reason" rows="2" required placeholder="Jelaskan secara singkat alasan ketidakhadiran Anda..." class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 focus:ring-primary-500 font-medium text-sm"></textarea>
                                    </div>
                                </div>
                                <div class="pt-2 flex justify-end">
                                    <button type="submit" class="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold text-sm shadow-md hover:bg-black transition-colors">AJUKAN SEKARANG</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Riwayat Singkat (Absensi & Cuti) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- List Absensi -->
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 bg-gray-50/20">
                                <h3 class="font-black text-gray-900 text-base">Histori Absen (5 Hari Terakhir)</h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @forelse($recentAttendances ?? [] as $att)
                                    <div class="p-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm">{{ $att->date?->translatedFormat('d M Y') }}</p>
                                            <p class="text-xs text-gray-400 font-medium">
                                                Masuk: {{ $att->check_in_time ?? '--:--' }} 
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider
                                            @if($att->status == 'hadir') bg-emerald-50 text-emerald-700 border border-emerald-100
                                            @elseif($att->status == 'terlambat') bg-amber-50 text-amber-700 border border-amber-100
                                            @elseif($att->status == 'alfa') bg-red-50 text-red-700 border border-red-100
                                            @else bg-blue-50 text-blue-700 border border-blue-100 @endif">
                                            {{ $att->status }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="p-4 text-center text-sm text-gray-400 font-medium">Belum ada data kehadiran.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- List Cuti -->
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 bg-gray-50/20">
                                <h3 class="font-black text-gray-900 text-base">Pengajuan Cuti Saya</h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @forelse($recentLeaves ?? [] as $leave)
                                    <div class="p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex justify-between items-center mb-1">
                                            <p class="font-bold text-gray-900 text-sm">{{ strtoupper(str_replace('_', ' ', $leave->type)) }}</p>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                                                @if($leave->status == 'approved') bg-emerald-100 text-emerald-700
                                                @elseif($leave->status == 'pending') bg-amber-100 text-amber-700
                                                @else bg-red-100 text-red-700 @endif">
                                                {{ $leave->status }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-gray-400 font-semibold">{{ $leave->start_date?->format('d M') }} - {{ $leave->end_date?->format('d M Y') }}</p>
                                    </div>
                                @empty
                                    <p class="p-4 text-center text-sm text-gray-400 font-medium">Belum ada pengajuan izin/cuti.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Slip Gaji Bulanan Karyawan -->
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-black text-lg text-gray-900">Histori Slip Gaji Saya</h3>
                            <p class="text-xs text-gray-500 mt-1">Unduh slip gaji resmi bulanan yang telah diterbitkan oleh bendahara sekolah.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">
                                        <th class="p-4 pl-6">Periode</th>
                                        <th class="p-4">Gaji Pokok</th>
                                        <th class="p-4">Tunjangan</th>
                                        <th class="p-4">Potongan</th>
                                        <th class="p-4">Gaji Bersih</th>
                                        <th class="p-4">Status</th>
                                        <th class="p-4 pr-6 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($payslips ?? [] as $pay)
                                        <tr class="hover:bg-gray-50/50 transition-colors text-sm font-medium text-gray-700">
                                            <td class="p-4 pl-6 font-bold text-gray-900">
                                                {{ $pay->month_name }} {{ $pay->year }}
                                            </td>
                                            <td class="p-4 font-mono text-xs">{{ $pay->base_salary_formatted }}</td>
                                            <td class="p-4 font-mono text-xs text-emerald-600">+{{ $pay->allowances_formatted }}</td>
                                            <td class="p-4 font-mono text-xs text-red-500">-{{ $pay->deductions_formatted }}</td>
                                            <td class="p-4 font-bold text-gray-900 font-mono text-xs">{{ $pay->net_salary_formatted }}</td>
                                            <td class="p-4">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider
                                                    @if($pay->status == 'paid') bg-emerald-50 text-emerald-700 border border-emerald-100
                                                    @else bg-blue-50 text-blue-700 border border-blue-100 @endif">
                                                    {{ $pay->status }}
                                                </span>
                                            </td>
                                            <td class="p-4 pr-6 text-right">
                                                <a href="{{ route('my-hrd.payslips.pdf', $pay->id) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-bold hover:bg-black transition-colors">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                    PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="p-6 text-center text-sm text-gray-400 font-medium">Belum ada slip gaji bulanan yang diterbitkan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

</x-app-layout>
