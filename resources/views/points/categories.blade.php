<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Master Kategori Pelanggaran & Prestasi') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-primary-600 text-white p-4 rounded-2xl shadow-lg shadow-primary-900/20 flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Add New Category Form -->
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
                <h3 class="text-lg font-black text-gray-800 mb-6">Tambah Kategori Baru</h3>
                <form action="{{ route('point-categories.store') }}" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Nama Kategori</label>
                        <input type="text" name="name" required placeholder="Contoh: Bolos, Terlambat, Juara Lomba" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm font-medium focus:ring-primary-500 focus:bg-white transition-all">
                    </div>
                    <div class="w-full md:w-48">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Tipe</label>
                        <select name="type" required class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm font-medium focus:ring-primary-500">
                            <option value="penalty">Pelanggaran (+Poin)</option>
                            <option value="reward">Prestasi (-Poin)</option>
                        </select>
                    </div>
                    <div class="w-full md:w-36">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Jumlah Poin</label>
                        <input type="number" name="default_points" min="1" required placeholder="10" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 text-sm font-medium focus:ring-primary-500">
                    </div>
                    <button type="submit" class="px-8 py-4 bg-primary-600 text-white rounded-2xl font-black text-sm shadow-lg shadow-primary-900/10 hover:bg-primary-700 transition-all whitespace-nowrap">
                        + TAMBAH
                    </button>
                </form>
            </div>

            <!-- Categories Table -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pelanggaran -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 bg-red-50 border-b border-red-100">
                        <h3 class="font-black text-red-700 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                            Pelanggaran (Tambah Poin)
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @forelse($categories->where('type', 'penalty') as $cat)
                            <div class="flex items-center justify-between p-5 hover:bg-gray-50 transition-colors">
                                <div>
                                    <span class="font-bold text-gray-800">{{ $cat->name }}</span>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black {{ $cat->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400' }}">
                                        {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-lg font-black text-red-500">+{{ abs($cat->default_points) }}</span>
                                    <form action="{{ route('point-categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-400 text-sm font-medium">Belum ada kategori pelanggaran.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Prestasi -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 bg-emerald-50 border-b border-emerald-100">
                        <h3 class="font-black text-emerald-700 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                            Prestasi (Kurangi Poin)
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @forelse($categories->where('type', 'reward') as $cat)
                            <div class="flex items-center justify-between p-5 hover:bg-gray-50 transition-colors">
                                <div>
                                    <span class="font-bold text-gray-800">{{ $cat->name }}</span>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black {{ $cat->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400' }}">
                                        {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-lg font-black text-emerald-500">{{ $cat->default_points }}</span>
                                    <form action="{{ route('point-categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-400 text-sm font-medium">Belum ada kategori prestasi.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
