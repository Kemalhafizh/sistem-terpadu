<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">Keuangan Saya</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-emerald-600 text-white p-4 rounded-2xl shadow-lg flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Summary Card -->
                <div class="lg:col-span-1">
                    <div class="bg-gradient-to-br from-primary-800 to-primary-600 rounded-[2.5rem] p-8 shadow-xl text-white">
                        <p class="text-[11px] font-black uppercase tracking-widest opacity-80 mb-6">Total Tagihan Belum Lunas</p>
                        @php $total = $billings->where('status', '!=', 'paid')->sum(function($b){ return $b->remaining_balance; }); @endphp
                        <p class="text-4xl font-black tracking-tighter mb-8">Rp {{ number_format($total, 0, ',', '.') }}</p>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-sm">
                                <span class="opacity-70">Jumlah Tagihan</span>
                                <span class="font-bold">{{ $billings->where('status', '!=', 'paid')->count() }} Item</span>
                            </div>
                            <div class="pt-4 border-t border-white/20">
                                <p class="text-[10px] italic opacity-60">*Segera lakukan pembayaran untuk menghindari kendala administrasi sekolah.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billings List -->
                <div class="lg:col-span-2 space-y-4">
                    <h3 class="font-black text-gray-800 uppercase text-xs tracking-widest px-2">Daftar Tagihan & Iuran</h3>
                    
                    @forelse($billings ?? [] as $billing)
                        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-md transition-all">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 {{ $billing->status == 'paid' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-50 text-red-500' }} rounded-2xl flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-gray-800">{{ $billing?->category?->name }} {{ $billing->month ? '- ' . $billing->month : '' }}</h4>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter">Status: {{ strtoupper($billing->status) }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-gray-800 mb-2">Rp {{ number_format($billing->remaining_balance, 0, ',', '.') }}</p>
                                @if($billing->status != 'paid')
                                    <a href="{{ route('finance.student.checkout', $billing->id) }}" class="inline-block px-4 py-2 bg-primary-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-primary-900/10 hover:bg-primary-700 transition-all">Bayar Sekarang</a>
                                @else
                                    <span class="inline-block px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-xs font-bold border border-emerald-100 uppercase tracking-widest">Lunas</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="bg-gray-50 rounded-[2.5rem] p-20 text-center border-2 border-dashed border-gray-200">
                            <p class="text-gray-400 italic font-bold">Tidak ada tagihan aktif.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
