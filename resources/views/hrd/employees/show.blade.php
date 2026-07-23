<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('hrd.employees.index') }}" class="mr-4 text-gray-400 hover:text-primary-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-gray-900 tracking-tight">Profil Pegawai</h2>
                    <p class="text-sm text-gray-500 font-medium">Informasi detail, absensi, dan cuti.</p>
                </div>
            </div>
            <div>
                <a href="{{ route('hrd.employees.edit', $employee) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-amber-600 active:bg-amber-700 transition shadow-lg shadow-amber-500/30">
                    Edit Data
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Kolom Kiri: Profil Card -->
                <div class="md:col-span-1">
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 text-center sticky top-8">
                        <div class="w-32 h-32 mx-auto bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-black text-5xl mb-6 shadow-inner">
                            {{ substr($employee?->user?->name, 0, 1) }}
                        </div>
                        <h3 class="font-black text-2xl text-gray-900 mb-1">{{ $employee?->user?->name }}</h3>
                        <p class="text-primary-600 font-bold mb-4">{{ $employee->position }}</p>
                        
                        <div class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-widest mb-6
                            @if($employee->employment_status == 'gty') bg-emerald-100 text-emerald-800
                            @elseif($employee->employment_status == 'staf_tetap') bg-blue-100 text-blue-800
                            @elseif($employee->employment_status == 'gtt') bg-amber-100 text-amber-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ str_replace('_', ' ', $employee->employment_status) }}
                        </div>

                        <div class="text-left space-y-4 border-t border-gray-100 pt-6">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">NIY</p>
                                <p class="font-bold text-gray-900">{{ $employee->employee_number }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Email System</p>
                                <p class="font-bold text-gray-900">{{ $employee?->user?->email }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Mulai Bergabung</p>
                                <p class="font-bold text-gray-900">{{ optional($employee->hire_date)->format('d F Y') ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Rekening Bank</p>
                                <p class="font-bold text-gray-900">{{ $employee->bank_name ?? '-' }} <br> <span class="text-gray-500 font-medium text-sm">{{ $employee->bank_account ?? '' }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Histori -->
                <div class="md:col-span-2 space-y-8">
                    
                    <!-- Absensi Terakhir -->
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="font-black text-lg text-gray-900">Histori Absensi (10 Terakhir)</h3>
                            <!-- Could add a link to full attendance here -->
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-400 uppercase tracking-widest bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 font-black">Tanggal</th>
                                        <th class="px-6 py-4 font-black">Masuk</th>
                                        <th class="px-6 py-4 font-black">Keluar</th>
                                        <th class="px-6 py-4 font-black">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($employee->attendances ?? [] as $attendance)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-6 py-4 font-bold text-gray-900">{{ $attendance->date?->format('d M Y') }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ $attendance->check_in_time ?? '-' }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ $attendance->check_out_time ?? '-' }}</td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider
                                                    @if($attendance->status == 'hadir') bg-emerald-100 text-emerald-700
                                                    @elseif($attendance->status == 'terlambat') bg-amber-100 text-amber-700
                                                    @elseif($attendance->status == 'alfa') bg-red-100 text-red-700
                                                    @else bg-blue-100 text-blue-700 @endif">
                                                    {{ $attendance->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 font-medium">Belum ada histori absensi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Riwayat Cuti -->
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="font-black text-lg text-gray-900">Riwayat Pengajuan Cuti / Izin</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            @forelse($employee->leaveRequests ?? [] as $leave)
                                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 flex justify-between items-center">
                                    <div>
                                        <div class="flex items-center mb-1">
                                            <span class="font-bold text-gray-900 mr-2">{{ strtoupper(str_replace('_', ' ', $leave->type)) }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                                @if($leave->status == 'approved') bg-emerald-100 text-emerald-700
                                                @elseif($leave->status == 'pending') bg-amber-100 text-amber-700
                                                @else bg-red-100 text-red-700 @endif">
                                                {{ $leave->status }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500">{{ $leave->start_date?->format('d M') }} - {{ $leave->end_date?->format('d M Y') }}</p>
                                        <p class="text-sm font-medium text-gray-700 mt-2">{{ $leave->reason }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Diajukan Pada</p>
                                        <p class="text-xs font-bold text-gray-900">{{ $leave->created_at?->format('d M Y') }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-400 font-medium py-4">Belum ada riwayat pengajuan cuti/izin.</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
