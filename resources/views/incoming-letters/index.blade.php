<x-app-layout>
    <x-slot name="title">Surat Masuk &amp; Disposisi</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    Agenda Surat Masuk &amp; Disposisi
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Pencatatan surat masuk sekolah, disposisi Kepala Sekolah, tindak lanjut staf, dan pengarsipan
                </p>
            </div>
            @if($canRegister)
                <a href="{{ route('incoming-letters.create') }}" class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl py-2.5 px-5 text-xs font-bold shadow-lg shadow-indigo-500/20 transition-all">
                    Catat Surat Masuk
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white/80 dark:bg-gray-800/80 rounded-2xl p-4 border border-gray-200/60 dark:border-gray-700/50 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Surat</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white/80 dark:bg-gray-800/80 rounded-2xl p-4 border border-amber-200/60 dark:border-amber-800/40 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Diterima</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $stats['diterima'] }}</p>
            </div>
            <div class="bg-white/80 dark:bg-gray-800/80 rounded-2xl p-4 border border-indigo-200/60 dark:border-indigo-800/40 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Didisposisikan</p>
                <p class="text-2xl font-black text-indigo-600 mt-1">{{ $stats['didisposisikan'] }}</p>
            </div>
            <div class="bg-white/80 dark:bg-gray-800/80 rounded-2xl p-4 border border-cyan-200/60 dark:border-cyan-800/40 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-cyan-600">Ditindaklanjuti</p>
                <p class="text-2xl font-black text-cyan-600 mt-1">{{ $stats['ditindaklanjuti'] }}</p>
            </div>
            <div class="bg-white/80 dark:bg-gray-800/80 rounded-2xl p-4 border border-emerald-200/60 dark:border-emerald-800/40 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Selesai</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['selesai'] }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('incoming-letters.index') }}" class="bg-white/80 dark:bg-gray-800/80 rounded-2xl p-4 border border-gray-200/60 dark:border-gray-700/50 flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no agenda, no surat asal, pengirim, atau perihal..." class="flex-1 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm">
            <select name="status" class="rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm">
                <option value="">Semua Status</option>
                <option value="diterima" @selected(request('status') === 'diterima')>Diterima</option>
                <option value="didisposisikan" @selected(request('status') === 'didisposisikan')>Didisposisikan</option>
                <option value="ditindaklanjuti" @selected(request('status') === 'ditindaklanjuti')>Ditindaklanjuti</option>
                <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
            </select>
            <button type="submit" class="px-5 py-2 rounded-xl bg-gray-900 dark:bg-indigo-600 text-white text-xs font-bold">Filter</button>
        </form>

        <div class="bg-white/80 dark:bg-gray-800/80 rounded-3xl border border-gray-200/60 dark:border-gray-700/50 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200/70 dark:border-gray-700 text-[11px] font-black uppercase tracking-wider text-gray-500">
                            <th class="py-3.5 px-5">No. Agenda</th>
                            <th class="py-3.5 px-5">Pengirim &amp; Nomor Asal</th>
                            <th class="py-3.5 px-5">Perihal</th>
                            <th class="py-3.5 px-5">Tgl Terima</th>
                            <th class="py-3.5 px-5">Disposisi</th>
                            <th class="py-3.5 px-5">Status</th>
                            <th class="py-3.5 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @forelse($letters as $letter)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-900/40 transition">
                                <td class="py-3.5 px-5 font-bold text-gray-900 dark:text-white">{{ $letter->agenda_number }}</td>
                                <td class="py-3.5 px-5">
                                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $letter->sender }}</div>
                                    <div class="text-xs text-gray-400">{{ $letter->reference_number }}</div>
                                </td>
                                <td class="py-3.5 px-5 text-gray-700 dark:text-gray-300">{{ $letter->subject }}</td>
                                <td class="py-3.5 px-5 text-xs text-gray-500">{{ $letter->received_date?->translatedFormat('d M Y') }}</td>
                                <td class="py-3.5 px-5 text-xs text-gray-600 dark:text-gray-300">
                                    {{ $letter->dispositions->pluck('toUser.name')->filter()->implode(', ') ?: 'Belum didisposisikan' }}
                                </td>
                                <td class="py-3.5 px-5">
                                    @php
                                        $badgeClass = match ($letter->status) {
                                            'diterima' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'didisposisikan' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'ditindaklanjuti' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                            default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-lg border text-[11px] font-bold {{ $badgeClass }}">
                                        {{ $letter->statusLabel() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <a href="{{ route('incoming-letters.show', $letter) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 px-5 text-center text-gray-400 text-sm">Belum ada data surat masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($letters->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700/50">
                    {{ $letters->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
