<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Riwayat Baca & Peminjaman Buku') }}
            </h2>
            <div class="flex items-center gap-2">
                @if(auth()->user()->role !== 'student')
                    <a href="{{ route('library.circulation.index') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl font-bold text-xs hover:bg-gray-50 transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kembali ke Sirkulasi
                    </a>
                @else
                    <a href="{{ route('library.index') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl font-bold text-xs hover:bg-gray-50 transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kembali ke Katalog
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Ringkasan Anggota -->
            <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm flex flex-col md:flex-row gap-8 items-center">
                <!-- Avatar & Identitas -->
                <div class="flex items-center gap-4 border-r border-gray-100 pr-8 md:w-1/3">
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center font-black text-xl shadow-inner">
                        {{ substr($student->user->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-base font-black text-gray-900 uppercase leading-none">{{ $student->user->name }}</h3>
                        <p class="text-[10px] font-bold text-gray-400 mt-2 uppercase tracking-wider">
                            NIS &bull; <span class="text-gray-700 font-black">{{ $student->nis }}</span>
                        </p>
                        <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-wider">
                            Kelas &bull; <span class="text-gray-700 font-bold">{{ $student->classroom->name ?? '-' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Metrics Grid -->
                <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-6 w-full">
                    <!-- Total Peminjaman -->
                    <div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Total Peminjaman</span>
                        <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ $readingStats['total_borrowed'] }} <span class="text-xs font-bold text-gray-400">buku</span></span>
                    </div>

                    <!-- Sebaran Fisik/Digital -->
                    <div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Fisik & Digital</span>
                        <span class="text-xs font-bold text-gray-700 mt-2 block">
                            Fisik: <span class="font-black text-emerald-600">{{ $readingStats['physical_count'] }}</span> &bull; Digital: <span class="font-black text-blue-600">{{ $readingStats['digital_count'] }}</span>
                        </span>
                    </div>

                    <!-- Kategori Terfavorit -->
                    <div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Kategori Terfavorit</span>
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-lg font-black text-[9px] mt-2 inline-block border border-emerald-100 uppercase tracking-wider truncate max-w-[150px]">{{ $readingStats['favorite_category'] }}</span>
                    </div>

                    <!-- Denda Dibayar -->
                    <div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Denda Terbayar</span>
                        <span class="text-lg font-black text-gray-900 mt-1 block">Rp {{ number_format($readingStats['total_fine_paid'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Buku Aktif Sedang Dipinjam -->
            <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-4">
                <div class="flex justify-between items-center border-b border-gray-50 pb-4">
                    <h3 class="text-base font-black text-gray-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                        Buku yang Sedang Dipinjam saat Ini
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="pb-3 pl-4">JUDUL BUKU</th>
                                <th class="pb-3">KATEGORI</th>
                                <th class="pb-3">TANGGAL PINJAM</th>
                                <th class="pb-3">JATUH TEMPO</th>
                                <th class="pb-3">STATUS & KETERANGAN</th>
                                <th class="pb-3 text-right pr-4">NOMINAL DENDA</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($readingStats['active_loans'] as $loan)
                                <tr class="text-xs text-gray-700">
                                    <td class="py-4 pl-4 font-black text-gray-900 uppercase">{{ $loan->book->title }}</td>
                                    <td class="py-4 uppercase"><span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md font-bold text-[8px] border border-gray-200">{{ $loan->book->category->name }}</span></td>
                                    <td class="py-4 font-bold text-gray-400">{{ $loan->borrowed_at->format('d M Y') }}</td>
                                    <td class="py-4 font-bold text-gray-400">{{ $loan->due_at->format('d M Y') }}</td>
                                    <td class="py-4">
                                        @if($loan->due_at->isPast())
                                            <span class="px-2.5 py-1 bg-red-50 text-red-600 rounded-lg font-black text-[9px] border border-red-100 uppercase tracking-wider">Terlambat {{ $loan->daysOverdue() }} Hari</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg font-black text-[9px] border border-indigo-100 uppercase tracking-wider">Sedang Dipinjam</span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-right pr-4 font-black text-sm {{ $loan->due_at->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                                        Rp {{ number_format($loan->calculateFine(), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">Tidak ada buku fisik yang sedang dipinjam saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Riwayat Lengkap Selesai -->
            <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-4">
                <h3 class="text-base font-black text-gray-800 uppercase tracking-wider">Daftar Riwayat Peminjaman Selesai</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="pb-3 pl-4">JUDUL BUKU</th>
                                <th class="pb-3">KATEGORI</th>
                                <th class="pb-3">TANGGAL PINJAM</th>
                                <th class="pb-3">TANGGAL KEMBALI</th>
                                <th class="pb-3">DENDA DIKENAKAN</th>
                                <th class="pb-3 text-right pr-4">STATUS DENDA</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($readingStats['history_loans'] as $loan)
                                <tr class="text-xs text-gray-700">
                                    <td class="py-4 pl-4 font-black text-gray-900 uppercase">{{ $loan->book->title }}</td>
                                    <td class="py-4 uppercase"><span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md font-bold text-[8px] border border-gray-200">{{ $loan->book->category->name }}</span></td>
                                    <td class="py-4 font-bold text-gray-400">{{ $loan->borrowed_at->format('d M Y') }}</td>
                                    <td class="py-4 font-bold text-gray-400">{{ $loan->returned_at->format('d M Y') }}</td>
                                    <td class="py-4 font-black text-gray-900">
                                        Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 text-right pr-4">
                                        @if($loan->fine_amount > 0)
                                            @if($loan->fine_paid_at)
                                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-lg font-black text-[9px] border border-emerald-100 uppercase tracking-wider">Lunas &bull; {{ $loan->fine_paid_at->format('d-m-Y') }}</span>
                                            @else
                                                <span class="px-2.5 py-1 bg-red-50 text-red-600 rounded-lg font-black text-[9px] border border-red-100 uppercase tracking-wider">Belum Lunas</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">Belum ada riwayat peminjaman buku yang selesai.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
