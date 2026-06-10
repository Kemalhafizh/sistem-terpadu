<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Laporan Keterlambatan Pengembalian Buku (Overdue)') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('library.circulation.index') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl font-bold text-xs hover:bg-gray-50 transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Sirkulasi
                </a>
                <a href="{{ route('library.statistics') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl font-bold text-xs hover:bg-gray-50 transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Statistik Perpus
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Success/Error Toast Notifications -->
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 rounded-2xl bg-red-50 border border-red-100 text-red-800 text-xs font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Overdue Table -->
            <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-4">
                <div class="flex justify-between items-center border-b border-gray-50 pb-4">
                    <h3 class="text-base font-black text-gray-800 uppercase tracking-wider">
                        Daftar Peminjaman Melewati Jatuh Tempo
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="pb-3 pl-4">SISWA & KELAS</th>
                                <th class="pb-3">JUDUL BUKU</th>
                                <th class="pb-3">TANGGAL PINJAM</th>
                                <th class="pb-3">BATAS WAKTU</th>
                                <th class="pb-3 text-center">TERLAMBAT (HARI)</th>
                                <th class="pb-3 text-center">AKUMULASI DENDA</th>
                                <th class="pb-3 text-center pr-4">AKSI PENGINGAT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($overdueLoans as $loan)
                                <tr class="text-xs text-gray-700">
                                    <!-- Siswa -->
                                    <td class="py-4 pl-4">
                                        <a href="{{ route('library.reading-history.detail', $loan->student) }}" class="font-black text-gray-900 hover:text-emerald-600 uppercase transition-all block">
                                            {{ $loan->student->user->name }}
                                        </a>
                                        <span class="text-[9px] font-bold text-gray-400 block mt-1 uppercase">NIS: {{ $loan->student->nis }} &bull; {{ $loan->student->classroom->name ?? 'Tanpa Kelas' }}</span>
                                    </td>
                                    
                                    <!-- Buku -->
                                    <td class="py-4 font-bold text-gray-600 uppercase">{{ $loan->book->title }}</td>
                                    
                                    <!-- Tanggal Pinjam -->
                                    <td class="py-4 font-bold text-gray-400">{{ $loan->borrowed_at->format('d M Y') }}</td>
                                    
                                    <!-- Batas Waktu -->
                                    <td class="py-4 font-bold text-gray-400">{{ $loan->due_at->format('d M Y') }}</td>
                                    
                                    <!-- Hari Terlambat -->
                                    <td class="py-4 text-center font-black text-red-600">
                                        {{ $loan->daysOverdue() }} hari
                                    </td>
                                    
                                    <!-- Denda -->
                                    <td class="py-4 text-center font-black text-sm text-red-600">
                                        Rp {{ number_format($loan->calculateFine(), 0, ',', '.') }}
                                    </td>
                                    
                                    <!-- Aksi WhatsApp -->
                                    <td class="py-4 text-center pr-4">
                                        <form action="{{ route('library.loans.remind', $loan) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3.5 py-2 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-xl font-black text-[10px] hover:bg-emerald-600 hover:text-white transition-all inline-flex items-center gap-1.5 uppercase shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                                KIRIM WA
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">Hebat! Tidak ada transaksi peminjaman buku yang terlambat saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
