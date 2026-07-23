<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Inventaris Aset & Sarpras') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('asset-categories.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs hover:bg-gray-200 transition-all">Kelola Kategori</a>
                <a href="{{ route('rooms.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs hover:bg-gray-200 transition-all">Daftar Ruangan</a>
                <a href="{{ route('assets.create') }}" class="px-6 py-2 bg-primary-600 text-white rounded-xl font-black text-xs shadow-lg shadow-primary-200 hover:bg-primary-700 transition-all">+ Register Aset Baru</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filter Section -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <form action="{{ route('assets.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Cari Nama/Kode</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="w-full rounded-xl border-gray-100 text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Ketik pencarian...">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Kategori</label>
                        <select name="category" class="w-full rounded-xl border-gray-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Semua Kategori</option>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Lokasi Ruangan</label>
                        <select name="room" class="w-full rounded-xl border-gray-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Semua Ruangan</option>
                            @foreach($rooms ?? [] as $room)
                                <option value="{{ $room->id }}" {{ request('room') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full py-2 bg-gray-900 text-white rounded-xl font-bold text-sm hover:bg-gray-800 transition-all">Filter Data</button>
                    </div>
                </form>
            </div>

            <!-- Asset Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Kode & Nama Aset</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Kategori</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Lokasi</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Kondisi</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($assets ?? [] as $asset)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-5">
                                    <div class="font-black text-gray-900">{{ $asset->name }}</div>
                                    <div class="text-[10px] font-bold text-primary-600 font-mono tracking-tighter">{{ $asset->asset_code }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase">{{ $asset?->category?->name }}</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-sm font-bold text-gray-700">{{ $asset?->room?->name }}</div>
                                    <div class="text-[10px] text-gray-400 font-medium">{{ $asset->room->location }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    @if($asset->condition == 'good')
                                        <span class="flex items-center text-emerald-600 text-xs font-bold">
                                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span> Baik
                                        </span>
                                    @elseif($asset->condition == 'damaged')
                                        <span class="flex items-center text-amber-600 text-xs font-bold">
                                            <span class="w-2 h-2 bg-amber-500 rounded-full mr-2"></span> Rusak Ringan
                                        </span>
                                    @else
                                        <span class="flex items-center text-red-600 text-xs font-bold">
                                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span> Rusak Berat
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-xs font-bold text-gray-500">{{ ucfirst($asset->status) }}</span>
                                </td>
                                <td class="px-6 py-5 text-right space-x-2">
                                    <a href="{{ route('assets.show', $asset->uuid) }}" class="p-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-primary-50 hover:text-primary-600 transition-all inline-block">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                    </a>
                                    <form action="{{ route('assets.destroy', $asset->uuid) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus data aset ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 bg-gray-100 text-gray-400 rounded-lg hover:bg-red-50 hover:text-red-600 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic text-sm">Belum ada data aset yang terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($assets->hasPages())
                    <div class="px-6 py-4 border-t border-gray-50">
                        {{ $assets->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
