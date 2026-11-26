<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('hrd.payrolls.index', ['month' => $payroll->month, 'year' => $payroll->year]) }}" class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-xl transition-all shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-black text-3xl text-gray-900 tracking-tight">Detail Payroll Karyawan</h2>
                    <p class="text-sm text-gray-500 font-medium mt-1">{{ __('Tinjau rincian kehadiran dan sesuaikan tunjangan atau potongan lain.') }}</p>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                @if(in_array($payroll->status, ['approved', 'paid']))
                    <a href="{{ route('my-hrd.payslips.pdf', $payroll->id) }}" class="inline-flex items-center px-5 py-3 bg-gray-900 hover:bg-black text-white rounded-2xl font-bold text-sm shadow-md transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        UNDUH PDF SLIP
                    </a>
                @endif
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

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Side: Forms and Logs (2 cols wide) -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Employee Details Card -->
                    <div class="bg-white border border-gray-100 rounded-[2rem] p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-primary-50 rounded-[1.25rem] flex items-center justify-center font-black text-2xl text-primary-600 shadow-inner">
                                {{ substr($payroll->employee->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900">{{ $payroll->employee->name }}</h3>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">{{ $payroll->employee->employee_number }}</p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-gray-100 text-gray-600">
                                        {{ $payroll->employee->position }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-primary-50 text-primary-700">
                                        {{ str_replace('_', ' ', $payroll->employee->employment_status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-left md:text-right border-t md:border-t-0 pt-4 md:pt-0 w-full md:w-auto">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Periode Gaji</p>
                            <p class="text-lg font-black text-gray-900 mt-0.5">{{ $payroll->month_name }} {{ $payroll->year }}</p>
                        </div>
                    </div>

                    <!-- Attendance Summary Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <!-- Working Days -->
                        <div class="bg-white border border-gray-100 rounded-[1.5rem] p-4 text-center shadow-sm">
                            <span class="block text-2xl font-black text-gray-900 font-mono">{{ $payroll->working_days ?? '–' }}</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-1 block">Hari Kerja</span>
                        </div>
                        <!-- Present Days -->
                        <div class="bg-white border border-gray-100 rounded-[1.5rem] p-4 text-center shadow-sm">
                            <span class="block text-2xl font-black text-emerald-600 font-mono">{{ $payroll->present_days ?? '–' }}</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-1 block">Hadir</span>
                        </div>
                        <!-- Late Days -->
                        <div class="bg-white border border-gray-100 rounded-[1.5rem] p-4 text-center shadow-sm">
                            <span class="block text-2xl font-black text-amber-500 font-mono">{{ $payroll->late_days ?? '–' }}</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-1 block">Terlambat</span>
                        </div>
                        <!-- Leave Days -->
                        <div class="bg-white border border-gray-100 rounded-[1.5rem] p-4 text-center shadow-sm">
                            <span class="block text-2xl font-black text-blue-500 font-mono">{{ $payroll->leave_days ?? '–' }}</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-1 block">Izin/Cuti</span>
                        </div>
                        <!-- Absent Days -->
                        <div class="bg-white border border-gray-100 rounded-[1.5rem] p-4 text-center shadow-sm col-span-2 md:col-span-1">
                            <span class="block text-2xl font-black text-red-500 font-mono">{{ $payroll->absent_days ?? '–' }}</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-1 block">{{ __('Tidak Hadir') }}</span>
                        </div>
                    </div>

                    <!-- Dynamic Adjustments Form -->
                    <div class="bg-white rounded-[2rem] border border-gray-100 p-6 shadow-sm">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-gray-900">Penyesuaian Slip Gaji</h3>
                                <p class="text-xs text-gray-400 font-semibold mt-0.5">Sesuaikan tunjangan manual, potongan tambahan, dan tambahkan catatan slip gaji.</p>
                            </div>
                        </div>

                        <form action="{{ route('hrd.payrolls.update', $payroll->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Allowances Input -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Tunjangan Tambahan (Rp)</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-sm font-bold text-gray-400">Rp</span>
                                        <input type="number" name="allowances" value="{{ old('allowances', (int)$payroll->allowances) }}" 
                                            class="w-full pl-11 pr-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-primary-500 text-sm font-semibold text-gray-900 font-mono disabled:opacity-60" 
                                            placeholder="0" min="0" {{ $payroll->status === 'paid' ? 'disabled' : '' }}>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-medium mt-1.5 block">Tunjangan jabatan, bonus performa, lemburan, dll.</span>
                                </div>

                                <!-- Deductions Input -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">{{ __('Potongan Lain (Rp)') }}</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-sm font-bold text-gray-400">Rp</span>
                                        <input type="number" name="other_deductions" value="{{ old('other_deductions', (int)$payroll->other_deductions) }}" 
                                            class="w-full pl-11 pr-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-primary-500 text-sm font-semibold text-gray-900 font-mono disabled:opacity-60" 
                                            placeholder="0" min="0" {{ $payroll->status === 'paid' ? 'disabled' : '' }}>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-medium mt-1.5 block">{{ __('Kasbon, BPJS, dll. Potongan kehadiran dihitung terpisah dan tetap dipertahankan saat hitung ulang.') }}</span>
                                </div>

                                <!-- Notes Area -->
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Catatan Intern / Slip Gaji</label>
                                    <textarea name="notes" rows="3" 
                                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-primary-500 text-sm font-semibold text-gray-900 disabled:opacity-60" 
                                        placeholder="Tuliskan keterangan penyesuaian di sini..." {{ $payroll->status === 'paid' ? 'disabled' : '' }}>{{ old('notes', $payroll->notes) }}</textarea>
                                </div>
                            </div>

                            @if($payroll->status !== 'paid')
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="px-6 py-3.5 bg-primary-600 text-white font-black text-sm rounded-xl hover:bg-primary-700 transition-colors shadow-md flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                        SIMPAN PENYESUAIAN
                                    </button>
                                </div>
                            @else
                                <div class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-100 text-center text-xs font-bold text-gray-500">
                                    Slip gaji ini telah ditandai LUNAS (PAID) dan tidak dapat disunting kembali.
                                </div>
                            @endif
                        </form>
                    </div>

                    <!-- Attendance Logs Review -->
                    <div class="bg-white rounded-[2rem] border border-gray-100 overflow-hidden shadow-sm">
                        <div class="p-6 border-b border-gray-50">
                            <h3 class="text-lg font-black text-gray-900">{{ __('Log Presensi') }}</h3>
                            <p class="text-sm text-gray-500 font-medium mt-1">{{ __('Presensi harian pegawai pada periode ini, dari mesin sidik jari atau dicatat TU.') }}</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">
                                        <th class="p-4 pl-6">{{ __('Tanggal') }}</th>
                                        <th class="p-4">{{ __('Status') }}</th>
                                        <th class="p-4">{{ __('Jam masuk') }}</th>
                                        <th class="p-4">{{ __('Jam pulang') }}</th>
                                        <th class="p-4 pr-6">{{ __('Sumber') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($attendances as $att)
                                        <tr class="hover:bg-gray-50/50 transition-colors text-sm font-medium text-gray-700">
                                            <td class="p-4 pl-6">
                                                <p class="font-bold text-gray-950 font-mono text-xs">{{ $att->date->format('d-m-Y') }}</p>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ $att->date->isoFormat('dddd') }}</p>
                                            </td>
                                            <td class="p-4">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider
                                                    @if($att->status == 'hadir') bg-emerald-50 text-emerald-700 border border-emerald-100
                                                    @elseif($att->status == 'terlambat') bg-amber-50 text-amber-700 border border-amber-100
                                                    @elseif(in_array($att->status, ['izin', 'sakit', 'cuti'])) bg-blue-50 text-blue-700 border border-blue-100
                                                    @else bg-red-50 text-red-700 border border-red-100 @endif">
                                                    {{ $att->status }}
                                                </span>
                                            </td>
                                            <td class="p-4 font-mono text-xs">{{ $att->check_in_time ?? '–' }}</td>
                                            <td class="p-4 font-mono text-xs">{{ $att->check_out_time ?? '–' }}</td>
                                            <td class="p-4 pr-6 text-xs">{{ $att->source === 'fingerprint' ? __('Mesin sidik jari') : __('Dicatat manual') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-8 text-center text-xs text-gray-400 font-semibold">
                                                {{ __('Belum ada data presensi untuk pegawai ini pada periode ini.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Right Side: Payroll Summary Sheet (1 col wide) -->
                <div class="space-y-8">
                    
                    <!-- Payslip Summary Box -->
                    <div class="bg-gray-900 rounded-[2.5rem] text-white p-8 shadow-xl relative overflow-hidden">
                        
                        <div class="relative space-y-6">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black uppercase tracking-widest text-primary-400">IKHTISAR PAYROLL</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                    @if($payroll->status == 'paid') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                    @elseif($payroll->status == 'approved') bg-blue-500/20 text-blue-400 border border-blue-500/30
                                    @else bg-amber-500/20 text-amber-400 border border-amber-500/30 @endif">
                                    {{ $payroll->status }}
                                </span>
                            </div>

                            <div class="border-t border-b border-gray-800/80 py-4 divide-y divide-gray-900">
                                <!-- Gaji Pokok -->
                                <div class="flex justify-between py-3">
                                    <span class="text-xs font-semibold text-gray-400">Gaji Pokok Karyawan</span>
                                    <span class="text-sm font-bold font-mono">{{ $payroll->base_salary_formatted }}</span>
                                </div>
                                <!-- Tunjangan Tambahan -->
                                <div class="flex justify-between py-3">
                                    <span class="text-xs font-semibold text-gray-400">Total Tunjangan (+)</span>
                                    <span class="text-sm font-bold text-emerald-400 font-mono">+{{ $payroll->allowances_formatted }}</span>
                                </div>
                                <!-- Potongan kehadiran (dihitung) dan potongan lain (diisi TU) -->
                                <div class="flex justify-between py-3">
                                    <span class="text-xs font-semibold text-gray-400">{{ __('Potongan Kehadiran (-)') }}</span>
                                    <span class="text-sm font-bold text-red-400 font-mono">-{{ $payroll->attendance_deductions_formatted }}</span>
                                </div>
                                <div class="flex justify-between py-3">
                                    <span class="text-xs font-semibold text-gray-400">{{ __('Potongan Lain (-)') }}</span>
                                    <span class="text-sm font-bold text-red-400 font-mono">-{{ $payroll->other_deductions_formatted }}</span>
                                </div>
                                @unless($payroll->hasAttendanceData())
                                    <p class="py-3 text-[11px] text-amber-300 font-semibold">{{ __('Belum ada data presensi saat slip dihitung, jadi tidak ada potongan kehadiran.') }}</p>
                                @endunless
                            </div>

                            <!-- Net Salary -->
                            <div class="pt-2 text-center">
                                <span class="text-xs font-black uppercase tracking-widest text-gray-400 block mb-2">Total Gaji Bersih (Net)</span>
                                <h2 class="text-3xl font-black text-white font-mono tracking-tight">{{ $payroll->net_salary_formatted }}</h2>
                            </div>

                            <!-- Meta info payment -->
                            @if($payroll->status === 'paid' && $payroll->payment_date)
                                <div class="p-4 bg-gray-800 rounded-2xl border border-gray-800 text-center">
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">DIBAYARKAN PADA TANGGAL</p>
                                    <p class="text-sm font-black text-gray-100 font-mono mt-1">{{ $payroll->payment_date->format('d F Y') }}</p>
                                </div>
                            @endif

                            <!-- Quick Action Buttons -->
                            <div class="space-y-3 pt-4">
                                @if($payroll->status === 'draft')
                                    <form action="{{ route('hrd.payrolls.approve', $payroll) }}" method="POST" onsubmit="return confirm('{{ __('Terbitkan slip gaji ini ke pegawai?') }}')">
                                        @csrf
                                        <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-black tracking-widest uppercase transition-colors shadow-md flex items-center justify-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                            TERBITKAN SLIP GAJI
                                        </button>
                                    </form>
                                @endif

                                @if($payroll->status === 'approved')
                                    <form action="{{ route('hrd.payrolls.pay', $payroll) }}" method="POST" onsubmit="return confirm('{{ __('Tandai slip gaji ini sudah dibayar?') }}')">
                                        @csrf
                                        <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black tracking-widest uppercase transition-colors shadow-md flex items-center justify-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                            PROSES PEMBAYARAN GAJI
                                        </button>
                                    </form>
                                @endif

                                @if(in_array($payroll->status, ['approved', 'paid']))
                                    <a href="{{ route('my-hrd.payslips.pdf', $payroll->id) }}" class="w-full py-4 bg-gray-800 hover:bg-gray-800 text-gray-200 border border-gray-800 rounded-2xl text-xs font-black tracking-widest uppercase transition-all flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        CETAK SLIP (PDF)
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
