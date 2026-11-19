<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen SPMB (Panitia)') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <!-- KANBAN BOARD -->
            <div class="flex flex-col md:flex-row gap-6 overflow-x-auto pb-4">
                
                <!-- Kolom 1: Pendaftar Baru (Pending) -->
                <div class="flex-1 min-w-[300px] bg-gray-50 rounded-2xl border border-gray-200 flex flex-col h-[70vh]">
                    <div class="p-4 border-b border-gray-200 bg-white rounded-t-2xl flex justify-between items-center shadow-sm z-10 relative">
                        <h3 class="font-bold text-gray-700 flex items-center">
                            <span class="w-3 h-3 bg-amber-400 rounded-full mr-2"></span> Baru Masuk
                        </h3>
                        <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-lg">{{ $pending->count() }}</span>
                    </div>
                    <div class="p-4 overflow-y-auto flex-1 space-y-4">
                        @forelse($pending ?? [] as $reg)
                            <a href="{{ route('spmb.admin.show', $reg) }}" class="block bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-primary-300 hover:shadow-md transition-all cursor-pointer">
                                <div class="text-xs font-bold text-gray-400 mb-1">{{ $reg->registration_number }}</div>
                                <div class="font-black text-gray-900 mb-2">{{ $reg->full_name }}</div>
                                <div class="text-xs text-gray-500 mb-3 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    {{ $reg->previous_school }}
                                </div>
                                <div class="text-[10px] text-gray-400 text-right">{{ $reg->created_at?->diffForHumans() }}</div>
                            </a>
                        @empty
                            <div class="text-center text-sm text-gray-400 mt-10">Tidak ada pendaftar baru.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Kolom 2: Sedang Dicek (Reviewing) -->
                <div class="flex-1 min-w-[300px] bg-gray-50 rounded-2xl border border-gray-200 flex flex-col h-[70vh]">
                    <div class="p-4 border-b border-gray-200 bg-white rounded-t-2xl flex justify-between items-center shadow-sm z-10 relative">
                        <h3 class="font-bold text-gray-700 flex items-center">
                            <span class="w-3 h-3 bg-blue-400 rounded-full mr-2"></span> Sedang Dicek
                        </h3>
                        <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-lg">{{ $reviewing->count() }}</span>
                    </div>
                    <div class="p-4 overflow-y-auto flex-1 space-y-4">
                        @forelse($reviewing ?? [] as $reg)
                            <a href="{{ route('spmb.admin.show', $reg) }}" class="block bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-primary-300 hover:shadow-md transition-all cursor-pointer">
                                <div class="text-xs font-bold text-gray-400 mb-1">{{ $reg->registration_number }}</div>
                                <div class="font-black text-gray-900 mb-2">{{ $reg->full_name }}</div>
                                <div class="text-xs text-gray-500 mb-3 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    {{ $reg->previous_school }}
                                </div>
                            </a>
                        @empty
                            <div class="text-center text-sm text-gray-400 mt-10">Kosong.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Kolom 3: Diterima (Accepted) -->
                <div class="flex-1 min-w-[300px] bg-emerald-50 rounded-2xl border border-emerald-200 flex flex-col h-[70vh]">
                    <div class="p-4 border-b border-emerald-200 bg-white rounded-t-2xl flex justify-between items-center shadow-sm z-10 relative">
                        <h3 class="font-bold text-emerald-800 flex items-center">
                            <span class="w-3 h-3 bg-emerald-500 rounded-full mr-2"></span> Diterima / Aktif
                        </h3>
                        <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-2 py-1 rounded-lg">{{ $accepted->count() }}</span>
                    </div>
                    <div class="p-4 overflow-y-auto flex-1 space-y-4">
                        @forelse($accepted ?? [] as $reg)
                            <a href="{{ route('spmb.admin.show', $reg) }}" class="block bg-white p-4 rounded-xl shadow-sm border border-emerald-100 hover:border-emerald-300 hover:shadow-md transition-all cursor-pointer">
                                <div class="text-xs font-bold text-emerald-500 mb-1">{{ $reg->registration_number }}</div>
                                <div class="font-black text-gray-900 mb-2">{{ $reg->full_name }}</div>
                                @if($reg->user_id)
                                    <div class="mt-2 flex space-x-2">
                                        <span class="inline-block bg-emerald-100 text-emerald-700 text-[9px] font-bold px-2 py-1 rounded uppercase tracking-wider">
                                            Akun Siswa Aktif
                                        </span>
                                        <span class="inline-block bg-blue-100 text-blue-700 text-[9px] font-bold px-2 py-1 rounded uppercase tracking-wider">
                                            Tagihan Terbit
                                        </span>
                                    </div>
                                @endif
                            </a>
                        @empty
                            <div class="text-center text-sm text-emerald-400 mt-10">Belum ada yang diterima.</div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
