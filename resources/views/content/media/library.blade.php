<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    Centralized Digital Asset Management (DAM)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Repositori tersentralisasi pengelolaan file media, dokumen resmi, foto kegiatan, dan kwitansi digital institusi.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-mono text-xs font-bold rounded-xl border border-indigo-200 dark:border-indigo-800">
                    Kapasitas Terpakai: {{ $stats['formatted_total_size'] }} ({{ $stats['total_count'] }} Aset)
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- DAM Control Toolbar & Storage Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-mono font-bold uppercase text-gray-400">Total Ukuran Repositori</span>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white mt-0.5">{{ $stats['formatted_total_size'] }}</h4>
                    </div>
                    <div class="p-3 bg-indigo-500/10 text-indigo-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v1H5zM5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-mono font-bold uppercase text-gray-400">Gambar & Fotografi</span>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white mt-0.5">{{ $stats['images_count'] }} File</h4>
                    </div>
                    <div class="p-3 bg-emerald-500/10 text-emerald-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-mono font-bold uppercase text-gray-400">Dokumen PDF & PDF/A</span>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white mt-0.5">{{ $stats['documents_count'] }} File</h4>
                    </div>
                    <div class="p-3 bg-amber-500/10 text-amber-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-mono font-bold uppercase text-gray-400">Media Video & Audio</span>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white mt-0.5">{{ $stats['video_count'] }} File</h4>
                    </div>
                    <div class="p-3 bg-purple-500/10 text-purple-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Upload File & Create Folder Bar -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Create Folder -->
                <form action="{{ route('media.create_folder') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ optional($currentFolder)->id }}">
                    <label class="block text-xs font-bold text-gray-500 uppercase">Buat Subfolder Baru</label>
                    <div class="flex gap-2">
                        <input type="text" name="name" required placeholder="Dokumen Kurikulum 2026" class="flex-1 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-xs px-4 py-2.5">
                        <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all">
                            Buat Folder
                        </button>
                    </div>
                </form>

                <!-- Upload File -->
                <form action="{{ route('media.upload') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ optional($currentFolder)->id }}">
                    <label class="block text-xs font-bold text-gray-500 uppercase">Register / Unggah Aset Media Baru</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="original_filename" required placeholder="Foto_Upacara_Bendera.jpg" class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-xs px-3 py-2">
                        <select name="mime_type" class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-xs px-3 py-2">
                            <option value="image/jpeg">image/jpeg</option>
                            <option value="image/png">image/png</option>
                            <option value="application/pdf">application/pdf</option>
                            <option value="video/mp4">video/mp4</option>
                        </select>
                        <input type="number" name="file_size_bytes" value="2048500" required placeholder="Ukuran (Bytes)" class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-xs px-3 py-2">
                        <input type="text" name="tags" placeholder="Tag: foto, upacara, 2026" class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-xs px-3 py-2">
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                        Unggah Aset Ke DAM
                    </button>
                </form>
            </div>

            <!-- DAM Library Grid (Folders & Files) -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden p-6 space-y-6">
                <!-- Breadcrumbs & Search -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-100 dark:border-gray-700 pb-4">
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-600 dark:text-gray-300">
                        <a href="{{ route('media.index') }}" class="hover:text-indigo-600">Root Directory</a>
                        @if($currentFolder)
                            <span>/</span>
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $currentFolder->name }}</span>
                        @endif
                    </div>

                    <form action="{{ route('media.index') }}" method="GET" class="flex gap-2">
                        <input type="hidden" name="folder_id" value="{{ optional($currentFolder)->id }}">
                        <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Cari nama aset / tag..." class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-xs px-4 py-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold">Cari</button>
                    </form>
                </div>

                <!-- Folders List Grid -->
                @if($folders->isNotEmpty())
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold uppercase font-mono text-gray-400">Folders</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            @foreach($folders as $f)
                                <a href="{{ route('media.index', ['folder_id' => $f->id]) }}" class="p-4 bg-gray-50 dark:bg-gray-900 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-2xl border border-gray-200 dark:border-gray-700 transition-all flex items-center gap-3">
                                    <svg class="w-6 h-6 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                    </svg>
                                    <span class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $f->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Files Table -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold uppercase font-mono text-gray-400">Media Assets</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-3">Nama File Aset</th>
                                    <th class="px-6 py-3">MIME Type</th>
                                    <th class="px-6 py-3">Ukuran File</th>
                                    <th class="px-6 py-3">Tagging Metadata</th>
                                    <th class="px-6 py-3">Pengunggah</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                                @forelse($assets as $asset)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                            <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $asset->original_filename }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-[11px] text-gray-500">{{ $asset->mime_type }}</td>
                                        <td class="px-6 py-4 font-mono text-[11px] text-indigo-600 dark:text-indigo-400 font-bold">{{ $asset->formatted_size }}</td>
                                        <td class="px-6 py-4">
                                            @if($asset->tags)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($asset->tags as $t)
                                                        <span class="px-2 py-0.5 bg-slate-100 dark:bg-gray-700 font-mono text-[10px] rounded text-slate-700 dark:text-gray-300">#{{ $t }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-[11px]">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">{{ optional($asset->uploader)->name }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('media.destroy', $asset->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus aset media ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 bg-rose-500/10 text-rose-600 hover:bg-rose-500/20 rounded-lg text-xs font-bold transition-all">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                            Tidak ada aset media terdaftar dalam folder ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
