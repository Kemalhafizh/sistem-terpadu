<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-800 leading-tight">
            {{ __('Lokasi & Ruangan Sekolah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Form Tambah -->
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 h-fit">
                <h3 class="text-lg font-black text-gray-800 mb-6 uppercase tracking-tighter">Tambah Ruangan</h3>
                <form action="{{ route('rooms.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-black text-gray-700 uppercase mb-2">Nama Ruangan</label>
                        <input type="text" name="name" required class="w-full rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500" placeholder="Contoh: Lab Komputer 1">
                    </div>
                    <div>
                        <label class="block text-sm font-black text-gray-700 uppercase mb-2">Lokasi (Gedung/Lantai)</label>
                        <input type="text" name="location" class="w-full rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500" placeholder="Contoh: Gedung B, Lantai 2">
                    </div>
                    <button type="submit" class="w-full py-3 bg-primary-600 text-white rounded-xl font-black text-sm hover:bg-primary-700 transition-all shadow-lg shadow-primary-100">Simpan Ruangan</button>
                </form>
            </div>

            <!-- List Ruangan -->
            <div class="md:col-span-2 bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase">Nama Ruangan</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase">Lokasi</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase text-center">Total Aset</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($rooms ?? [] as $room)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-black text-gray-900">{{ $room->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-500">
                                    {{ $room->location ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold">{{ $room->assets_count }} Item</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" onsubmit="return confirm('Hapus ruangan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600 font-bold text-xs uppercase tracking-widest">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
