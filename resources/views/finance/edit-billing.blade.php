<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Edit Tagihan Pembayaran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 bg-primary-50 border-b border-primary-100">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-primary-600 rounded-2xl flex items-center justify-center text-white mr-4 shadow-lg shadow-primary-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-gray-800">{{ $billing?->student?->name }}</h3>
                            <p class="text-sm text-primary-600 font-bold uppercase tracking-widest">{{ $billing?->category?->name }} {{ $billing->month ? '- ' . $billing->month : '' }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('finance.billings.update', $billing) }}" method="POST" class="p-10 space-y-8">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Amount -->
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Nominal Tagihan (Rp)</label>
                            <input type="number" name="amount" value="{{ old('amount', $billing->amount) }}" required class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-lg font-black text-gray-800 focus:ring-primary-500 focus:bg-white transition-all">
                            <p class="mt-2 text-xs text-gray-400 italic">*Sudah terbayar: Rp {{ number_format($billing->total_paid, 0, ',', '.') }}</p>
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Jatuh Tempo</label>
                            <input type="date" name="due_date" value="{{ old('due_date', $billing->due_date) }}" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 font-bold text-gray-700 focus:ring-primary-500 focus:bg-white transition-all">
                        </div>

                        <!-- Month -->
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Bulan Tagihan</label>
                            <select name="month" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 font-bold text-gray-700 focus:ring-primary-500 focus:bg-white transition-all">
                                <option value="">N/A</option>
                                @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] ?? [] as $m)
                                    <option value="{{ $m }}" {{ (old('month', $billing->month) == $m) ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="pt-10 flex items-center justify-end space-x-4 border-t border-gray-50">
                        <a href="{{ route('finance.billings.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-all">Batal & Kembali</a>
                        <button type="submit" class="px-12 py-4 bg-primary-600 text-white rounded-2xl font-black text-sm shadow-2xl shadow-primary-200 hover:bg-primary-700 hover:-translate-y-1 transition-all">
                            SIMPAN PERUBAHAN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
