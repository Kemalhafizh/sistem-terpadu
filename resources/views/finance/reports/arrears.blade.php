<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Laporan Tunggakan per Kelas') }}
            </h2>
            <div class="flex items-center space-x-3">
                <form action="{{ route('finance.reports.arrears') }}" method="GET" class="flex items-center space-x-2">
                    @if(request('classroom_id'))
                        <input type="hidden" name="classroom_id" value="{{ request('classroom_id') }}">
                    @endif
                    <select name="year_id" onchange="this.form.submit()" class="rounded-xl border-gray-100 text-xs font-bold focus:border-primary-500 focus:ring-primary-500">
                        @foreach($years ?? [] as $year)
                            <option value="{{ $year->id }}" {{ $yearId == $year->id ? 'selected' : '' }}>
                                T.A {{ $year->year }} - {{ ucfirst($year->semester) }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <div class="flex gap-2">
                    <a href="{{ route('finance.reports.export', ['type' => 'arrears', 'format' => 'excel', 'year_id' => $yearId]) }}" class="px-4 py-2 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-xl font-bold text-xs hover:bg-emerald-600 hover:text-white transition-all flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Ekspor Excel
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Back to Executive Report Link -->
            <div class="flex justify-between items-center">
                <a href="{{ route('finance.reports.executive') }}" class="text-xs font-bold text-gray-500 hover:text-gray-900 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Dashboard Eksekutif
                </a>
            </div>

            <!-- Arrears Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Tagihan Dirilis</p>
                    <h3 class="text-2xl font-black text-gray-900">Rp {{ number_format($classrooms->sum('total_bill'), 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-gray-400 mt-2 font-bold italic">Semua jenis iuran siswa aktif</p>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Dana Terbayar</p>
                    <h3 class="text-2xl font-black text-emerald-600">Rp {{ number_format($classrooms->sum('total_paid'), 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-emerald-400 mt-2 font-bold italic">Rasio Lunas: {{ $classrooms->sum('total_bill') > 0 ? number_format(($classrooms->sum('total_paid') / $classrooms->sum('total_bill')) * 100, 1) : 0 }}%</p>
                </div>
                <div class="bg-red-50 p-6 rounded-[2rem] shadow-sm border border-red-100">
                    <p class="text-[10px] font-black text-red-500 uppercase tracking-widest mb-1">Total Sisa Tunggakan</p>
                    <h3 class="text-2xl font-black text-red-600">Rp {{ number_format($classrooms->sum('outstanding'), 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-red-400 mt-2 font-bold italic">Menanti penyelesaian pembayaran</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Class List and Summary -->
                <div class="lg:col-span-1 bg-white rounded-[2.5rem] p-6 shadow-sm border border-gray-100 space-y-6">
                    <h4 class="text-base font-black text-gray-800 uppercase tracking-wider flex items-center">
                        <span class="w-2 h-5 bg-primary-600 rounded-full mr-2.5"></span> Daftar Tunggakan Kelas
                    </h4>
                    <div class="space-y-3">
                        @foreach($classrooms as $class)
                            <a href="{{ route('finance.reports.arrears', ['classroom_id' => $class['id'], 'year_id' => $yearId]) }}" 
                               class="block p-4 rounded-2xl border transition-all 
                               {{ $selectedClassroom && $selectedClassroom->id == $class['id'] ? 'border-primary-500 bg-primary-50/30' : 'border-gray-50 bg-gray-50/50 hover:bg-gray-50' }}">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-black text-gray-900 uppercase">{{ $class['name'] }}</span>
                                    <span class="text-xs font-black text-red-600">Rp {{ number_format($class['outstanding'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                                    <span>{{ $class['outstanding_students_count'] }} Siswa Menunggak</span>
                                    <span>Lunas: {{ $class['total_bill'] > 0 ? number_format(($class['total_paid'] / $class['total_bill']) * 100, 0) : 0 }}%</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Classroom Details -->
                <div class="lg:col-span-2 bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 space-y-6">
                    @if($selectedClassroom)
                        <div class="flex justify-between items-center border-b border-gray-50 pb-4">
                            <div>
                                <h4 class="text-lg font-black text-gray-800 uppercase tracking-wider">
                                    Rincian Kelas: {{ $selectedClassroom->name }}
                                </h4>
                                <p class="text-xs text-gray-400 font-bold mt-1 uppercase">Daftar siswa yang memiliki sisa tunggakan semester ini</p>
                            </div>
                            <span class="px-3.5 py-1 bg-red-50 text-red-600 rounded-xl font-bold text-[10px] border border-red-100 uppercase tracking-wider">
                                {{ $studentArrears->count() }} Siswa
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        <th class="pb-3 pl-4">SISWA & NIS</th>
                                        <th class="pb-3 text-center">JUMLAH TAGIHAN</th>
                                        <th class="pb-3 text-center">TOTAL TUNGGAKAN</th>
                                        <th class="pb-3 text-right pr-4">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse($studentArrears as $arrear)
                                        <tr class="text-xs text-gray-700 hover:bg-gray-50/50 transition-all">
                                            <td class="py-4 pl-4">
                                                <div class="font-black text-gray-900 uppercase">{{ $arrear['name'] }}</div>
                                                <div class="text-[9px] font-bold text-gray-400 uppercase mt-0.5">NIS: {{ $arrear['nis'] }}</div>
                                            </td>
                                            <td class="py-4 text-center font-bold text-gray-500">
                                                {{ $arrear['unpaid_billings_count'] }} iuran belum lunas
                                            </td>
                                            <td class="py-4 text-center font-black text-red-600">
                                                Rp {{ number_format($arrear['outstanding'], 0, ',', '.') }}
                                            </td>
                                            <td class="py-4 text-right pr-4 space-x-2">
                                                <!-- Dropdown or Accordion for individual bills -->
                                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                                    <button @click="open = !open" class="px-3 py-1.5 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-xl font-bold text-[10px] uppercase transition-all">
                                                        Rincian
                                                    </button>
                                                    
                                                    <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-72 rounded-2xl shadow-xl bg-white border border-gray-100 z-50 p-4 space-y-3" style="display: none;">
                                                        <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 pb-2">Rincian Tunggakan</h5>
                                                        <div class="max-h-48 overflow-y-auto space-y-2">
                                                            @foreach($arrear['billings'] as $bill)
                                                                <div class="flex justify-between items-center text-[10px]">
                                                                    <div class="text-gray-700 font-bold uppercase">
                                                                        {{ $bill->category->name }}
                                                                        @if($bill->month)
                                                                            <span class="text-gray-400">({{ $bill->month }})</span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="font-black text-red-600">Rp {{ number_format($bill->remaining_balance, 0, ',', '.') }}</span>
                                                                        <a href="{{ route('finance.billings.invoice', $bill->id) }}" title="Unduh Surat Tagihan" class="text-primary-600 hover:text-primary-800">
                                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                                <a href="{{ route('finance.billings.index', ['search' => $arrear['nis']]) }}" class="px-3 py-1.5 bg-primary-600 text-white hover:bg-primary-700 rounded-xl font-bold text-[10px] uppercase transition-all">
                                                    Bayar
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-8 text-center text-gray-400 italic">Luar biasa! Tidak ada siswa dengan tunggakan di kelas ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-20 text-center space-y-4">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto text-gray-300">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                            </div>
                            <h5 class="text-sm font-black text-gray-800 uppercase tracking-wider">Pilih Kelas</h5>
                            <p class="text-xs text-gray-400 max-w-sm mx-auto">Silakan pilih kelas di panel sebelah kiri untuk menampilkan rincian dan daftar siswa yang menunggak.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
