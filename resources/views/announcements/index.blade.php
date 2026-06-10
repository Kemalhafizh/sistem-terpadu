<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                <span class="w-8 h-8 bg-primary-100 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 rounded-lg flex items-center justify-center mr-3">
                    📢
                </span>
                {{ __('Manajemen Pengumuman (Mading Digital)') }}
            </h2>
            <a href="{{ route('announcements.create') }}" class="inline-flex items-center px-5 py-2.5 bg-primary-600 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-wider hover:bg-primary-700 shadow-md shadow-primary-600/20 transition-all active:scale-95">
                + Buat Pengumuman Baru
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400 px-5 py-4 rounded-2xl relative shadow-sm" role="alert">
                    <span class="block sm:inline font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-3xl border border-gray-100 dark:border-gray-700">
                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-750">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Poster</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Kategori & Judul</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Target Penerima</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Pembaca</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Status / Expired</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($announcements ?? [] as $announcement)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                    <!-- Poster -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($announcement->image_path)
                                            <img src="{{ Storage::url($announcement->image_path) }}" alt="Poster" class="h-14 w-14 object-cover rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                        @else
                                            <div class="h-14 w-14 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-650">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        @endif
                                    </td>
                                    <!-- Kategori & Judul -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <!-- Pinned indicator -->
                                            @if($announcement->is_pinned)
                                                <span class="px-2 py-0.5 rounded bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 text-[9px] font-black uppercase tracking-wider flex items-center gap-0.5 shadow-sm border border-amber-200/20">
                                                    📌 PINNED
                                                </span>
                                            @endif
                                            <!-- Category Badge -->
                                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{ 
                                                $announcement->category === 'akademik' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : (
                                                $announcement->category === 'keuangan' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : (
                                                $announcement->category === 'kegiatan' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300' : (
                                                $announcement->category === 'keislaman' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')))
                                            }}">
                                                📁 {{ $announcement->category ?? 'umum' }}
                                            </span>
                                            <!-- Priority level -->
                                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{
                                                $announcement->priority === 'urgent' ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 animate-pulse' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                            }}">
                                                {{ $announcement->priority }}
                                            </span>
                                        </div>
                                        <div class="text-sm font-bold text-gray-900 dark:text-white leading-tight">
                                            {{ $announcement->title }}
                                        </div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 line-clamp-1 w-64">
                                            {{ $announcement->content }}
                                        </div>
                                    </td>
                                    <!-- Target Penerima -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-100/30">
                                            👥 {{ $announcement->target_role ? ucfirst($announcement->target_role) : 'Semua Orang' }}
                                        </span>
                                        @if($announcement->targetClassroom)
                                            <div class="mt-1">
                                                <span class="px-2.5 py-1 inline-flex text-[10px] leading-5 font-bold rounded-full bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-400 border border-purple-100/30">
                                                    🏫 {{ $announcement->targetClassroom->name }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <!-- Pembaca -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-black text-gray-800 dark:text-gray-200">
                                            👁️ {{ $announcement->read_count }}
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">
                                            Total Pembaca Unik
                                        </div>
                                    </td>
                                    <!-- Status / Expired -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($announcement->expires_at && $announcement->expires_at->isPast())
                                            <span class="px-2.5 py-1 inline-flex text-xs font-black rounded-full bg-red-100 dark:bg-red-950/40 text-red-800 dark:text-red-400">
                                                Expired
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 inline-flex text-xs font-black rounded-full bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-400">
                                                Tayang
                                            </span>
                                            @if($announcement->expires_at)
                                                <div class="text-[10px] text-gray-450 dark:text-gray-500 mt-1">s.d {{ $announcement->expires_at->format('d M Y') }}</div>
                                            @endif
                                        @endif
                                    </td>
                                    <!-- Aksi -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-black tracking-wider space-y-1">
                                        <div class="flex items-center justify-end gap-2 flex-wrap">
                                            <!-- Show Button -->
                                            <a href="{{ route('announcements.show', $announcement) }}" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors shadow-sm">
                                                Lihat
                                            </a>
                                            
                                            <!-- Pin Toggle Form -->
                                            <form action="{{ route('announcements.pin', $announcement) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 {{ $announcement->is_pinned ? 'bg-amber-500 text-white hover:bg-amber-600' : 'bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 hover:bg-amber-200' }} rounded-lg transition-colors shadow-sm">
                                                    {{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}
                                                </button>
                                            </form>

                                            <!-- Read Receipts Log -->
                                            <a href="{{ route('announcements.receipts', $announcement) }}" class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors shadow-sm">
                                                Penerima
                                            </a>

                                            <!-- Edit -->
                                            <a href="{{ route('announcements.edit', $announcement) }}" class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-450 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/55 transition-colors shadow-sm">
                                                Edit
                                            </a>

                                            <!-- Delete -->
                                            <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengumuman ini beserta lampirannya?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-450 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/55 transition-colors shadow-sm">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-550 dark:text-gray-500 text-sm">
                                        Belum ada pengumuman yang disebarkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($announcements->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
                        {{ $announcements->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
