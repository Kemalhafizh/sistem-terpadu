<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('uks.dashboard') }}" class="hover:text-teal-600 transition-colors">
                    <span class="w-8 h-8 bg-teal-100 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </span>
                </a>
                <div>
                    <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                        {{ __('Log Restock Obat') }}
                    </h2>
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">SMA ISLAM SULTAN AGUNG 1 • PENERIMAAN & PEMBELIAN OBAT</p>
                </div>
            </div>

            <button type="button" @click="$dispatch('open-modal', 'add-restock-modal')" class="inline-flex items-center px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/10">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Stok Obat
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alert Session Messages -->
            @if(session('success'))
                <div class="rounded-2xl p-4 bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-900/50 flex items-center text-teal-800 dark:text-teal-300">
                    <svg class="w-5 h-5 mr-3 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Main Table Card -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 flex flex-col">
                <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center mb-6">
                    <span class="w-2 h-6 bg-teal-600 rounded-full mr-3"></span>
                    Riwayat Penambahan Inventaris Obat UKS
                </h4>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700/50 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                <th class="pb-3">Tanggal / Waktu</th>
                                <th class="pb-3">Nama Obat</th>
                                <th class="pb-3 text-center">Jumlah Ditambahkan</th>
                                <th class="pb-3">Petugas Pelapor</th>
                                <th class="pb-3 text-right">Catatan / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-55/30 dark:divide-gray-700/30">
                            @forelse($logs as $log)
                                <tr class="group hover:bg-gray-50/55 dark:hover:bg-gray-900/10 transition-all">
                                    <td class="py-4 text-xs font-semibold text-gray-600 dark:text-gray-400">
                                        {{ $log->created_at->translatedFormat('d M Y • H:i') }} WIB
                                    </td>
                                    <td class="py-4 text-sm font-extrabold text-gray-900 dark:text-white">
                                        {{ $log->medicine->name ?? 'N/A' }}
                                    </td>
                                    <td class="py-4 text-center text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                        +{{ $log->quantity }} Pcs
                                    </td>
                                    <td class="py-4 text-xs font-semibold text-gray-650 dark:text-gray-400">
                                        {{ $log->restocker->name ?? '-' }}
                                    </td>
                                    <td class="py-4 text-right text-xs text-gray-400 dark:text-gray-500 font-semibold">
                                        {{ $log->notes ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 dark:text-gray-600 italic">Belum ada riwayat restock terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $logs->links() }}
                </div>
            </div>

        </div>
    </div>

    <!-- Modal: Tambah Restock -->
    <x-modal name="add-restock-modal" focusable>
        <form method="POST" action="{{ route('uks.medicines.restock.store') }}" class="p-6">
            @csrf
            <h3 class="text-lg font-black text-gray-900 dark:text-white mb-4">Input Restock Obat UKS</h3>
            
            <div class="space-y-4">
                <!-- Medicine Selection -->
                <div class="space-y-1">
                    <x-input-label for="uks_medicine_id" value="Pilih Obat *" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500" />
                    <select name="uks_medicine_id" id="uks_medicine_id" required
                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                        <option value="">-- Pilih Obat --</option>
                        @foreach($medicines as $med)
                            <option value="{{ $med->id }}">{{ $med->name }} (Stok saat ini: {{ $med->stock }} {{ $med->category }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Quantity -->
                <div class="space-y-1">
                    <x-input-label for="quantity" value="Jumlah Ditambahkan *" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500" />
                    <x-text-input name="quantity" id="quantity" type="number" min="1" placeholder="Contoh: 50" required class="w-full" />
                </div>

                <!-- Notes -->
                <div class="space-y-1">
                    <x-input-label for="notes" value="Keterangan / Catatan" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500" />
                    <x-text-input name="notes" id="notes" type="text" placeholder="Contoh: Pembelian rutin apotek, Donasi komite sekolah" class="w-full" />
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="$dispatch('close-modal', 'add-restock-modal')" class="px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-black uppercase hover:bg-gray-50 dark:hover:bg-gray-750 transition-all text-gray-700 dark:text-gray-300">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase transition-all shadow-md shadow-teal-600/10">
                    Simpan Restock
                </button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
