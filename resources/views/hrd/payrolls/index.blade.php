<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-black text-3xl text-gray-900 tracking-tight">Manajemen Payroll</h2>
                <p class="text-sm text-gray-500 font-medium mt-1">Kelola penggajian bulanan, tunjangan manual, pemotongan absensi, dan penerbitan slip gaji pegawai.</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('hrd.payrolls.create') }}" class="inline-flex items-center px-5 py-3 bg-gray-900 hover:bg-black text-white rounded-2xl font-bold text-sm shadow-md transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    GENERATE PAYROLL
                </a>
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

            <!-- Statistik Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Expenditure -->
                <div class="bg-white border border-gray-100 rounded-[2rem] p-6 flex items-center shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Estimasi Pengeluaran</p>
                        <h4 class="text-lg font-black text-gray-900 font-mono mt-0.5">Rp {{ number_format($stats['total_expenditure'], 0, ',', '.') }}</h4>
                    </div>
                </div>

                <!-- Draft Count -->
                <div class="bg-white border border-gray-100 rounded-[2rem] p-6 flex items-center shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Belum Terbit (Draft)</p>
                        <h4 class="text-2xl font-black text-gray-900 mt-0.5">{{ $stats['draft_count'] }} <span class="text-xs text-gray-400 font-medium">pegawai</span></h4>
                    </div>
                </div>

                <!-- Approved Count -->
                <div class="bg-white border border-gray-100 rounded-[2rem] p-6 flex items-center shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Disetujui (Approved)</p>
                        <h4 class="text-2xl font-black text-gray-900 mt-0.5">{{ $stats['approved_count'] }} <span class="text-xs text-gray-400 font-medium">pegawai</span></h4>
                    </div>
                </div>

                <!-- Paid Count -->
                <div class="bg-white border border-gray-100 rounded-[2rem] p-6 flex items-center shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Telah Dibayar (Paid)</p>
                        <h4 class="text-2xl font-black text-gray-900 mt-0.5">{{ $stats['paid_count'] }} <span class="text-xs text-gray-400 font-medium">pegawai</span></h4>
                    </div>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="bg-white rounded-[2rem] border border-gray-100 p-6 shadow-sm">
                <form action="{{ route('hrd.payrolls.index') }}" method="GET" class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div class="flex flex-wrap md:flex-nowrap gap-4 items-end flex-grow">
                        <!-- Filter Bulan -->
                        <div class="w-full md:w-44">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5">Bulan</label>
                            <select name="month" class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 focus:ring-primary-500 font-bold text-sm">
                                @foreach(['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $key => $name)
                                    <option value="{{ $key }}" {{ $month == $key ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Tahun -->
                        <div class="w-full md:w-32">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5">Tahun</label>
                            <select name="year" class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 focus:ring-primary-500 font-bold text-sm">
                                @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Cari Nama -->
                        <div class="w-full md:w-64">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5">Nama Pegawai</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pegawai..." class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 focus:ring-primary-500 text-sm font-semibold">
                        </div>

                        <div>
                            <button type="submit" class="px-5 py-3 bg-primary-600 text-white font-black text-sm rounded-xl hover:bg-primary-700 transition-colors shadow-md flex items-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                FILTER
                            </button>
                        </div>
                    </div>

                    <!-- Pemicu Aksi Bulk Massal -->
                    @if($payrolls->count() > 0)
                        <div class="flex gap-2">
                            @if($stats['draft_count'] > 0)
                                <form action="{{ route('hrd.payrolls.approve-bulk') }}" method="POST" onsubmit="return confirm('Terbitkan semua slip gaji DRAFT bulan ini? Karyawan akan langsung dapat melihat slip mereka.')">
                                    @csrf
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <button type="submit" class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black shadow-md flex items-center transition-colors">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                        TERBITKAN SEMUA
                                    </button>
                                </form>
                            @endif

                            @if($stats['approved_count'] > 0)
                                <form action="{{ route('hrd.payrolls.pay-bulk') }}" method="POST" onsubmit="return confirm('Tandai semua slip gaji APPROVED bulan ini sebagai TELAH DIBAYAR?')">
                                    @csrf
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <button type="submit" class="px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-md flex items-center transition-colors">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        BAYAR MASSAL
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </form>
            </div>

            <!-- Data Table Payroll -->
            <div class="bg-white rounded-[2rem] border border-gray-100 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">
                                <th class="p-4 pl-6">Pegawai</th>
                                <th class="p-4">Gaji Pokok</th>
                                <th class="p-4">Tunjangan</th>
                                <th class="p-4">Potongan Kehadiran</th>
                                <th class="p-4">Gaji Bersih (Net)</th>
                                <th class="p-4">Rasio Masuk</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($payrolls as $pay)
                                <tr class="hover:bg-gray-50/50 transition-colors text-sm font-medium text-gray-700">
                                    <td class="p-4 pl-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 bg-gray-100 rounded-xl flex items-center justify-center font-bold text-gray-700">
                                                {{ substr($pay->employee->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900">{{ $pay->employee->name }}</p>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ $pay->employee->employee_number }} | {{ $pay->employee->position }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 font-mono text-xs">{{ $pay->base_salary_formatted }}</td>
                                    <td class="p-4 font-mono text-xs text-emerald-600">+{{ $pay->allowances_formatted }}</td>
                                    <td class="p-4 font-mono text-xs text-red-500">-{{ $pay->deductions_formatted }}</td>
                                    <td class="p-4 font-bold text-gray-900 font-mono text-xs">{{ $pay->net_salary_formatted }}</td>
                                    <td class="p-4 text-xs font-semibold text-gray-500">
                                        {{ $pay->present_days }} / {{ $pay->working_days }} hari
                                        <span class="block text-[10px] text-red-400 font-bold">
                                            (Terlambat: {{ $pay->late_days }}, Alfa: {{ $pay->absent_days }})
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider
                                            @if($pay->status == 'paid') bg-emerald-50 text-emerald-700 border border-emerald-100
                                            @elseif($pay->status == 'approved') bg-blue-50 text-blue-700 border border-blue-100
                                            @else bg-amber-50 text-amber-700 border border-amber-100 @endif">
                                            {{ $pay->status }}
                                        </span>
                                    </td>
                                    <td class="p-4 pr-6 text-right">
                                        <div class="inline-flex items-center space-x-2">
                                            <!-- Detail / Edit -->
                                            <a href="{{ route('hrd.payrolls.show', $pay->id) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-colors">
                                                Detail
                                            </a>
                                            <!-- PDF Slip (hanya jika approved/paid) -->
                                            @if(in_array($pay->status, ['approved', 'paid']))
                                                <a href="{{ route('my-hrd.payslips.pdf', $pay->id) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-bold hover:bg-black transition-colors">
                                                    PDF
                                                </a>
                                            @endif
                                            <!-- Delete -->
                                            @if($pay->status !== 'paid')
                                                <form action="{{ route('hrd.payrolls.destroy', $pay->id) }}" method="POST" onsubmit="return confirm('Hapus data payroll pegawai ini?')" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-2 py-1.5 text-red-500 hover:text-red-700 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-12 text-center text-sm text-gray-400 font-semibold">
                                        Belum ada data payroll untuk periode {{ $month }}-{{ $year }}.<br>
                                        Silakan klik tombol <strong>GENERATE PAYROLL</strong> di kanan atas untuk menghitung otomatis.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginate Link -->
                @if($payrolls->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50/20">
                        {{ $payrolls->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
