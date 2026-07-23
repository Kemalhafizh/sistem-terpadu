<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            title="Manajemen Pengumuman (Mading Digital)" 
            subtitle="Publikasikan pengumuman sekolah, jadwal kegiatan, dan edaran untuk siswa, guru, maupun wali murid."
        >
            <x-slot name="actions">
                <a href="{{ route('announcements.create') }}" class="inline-flex items-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-wider transition-all shadow-xs">
                    + Buat Pengumuman Baru
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8">
            <x-data-table 
                :headers="[
                    ['label' => 'Poster', 'width' => 'w-20'],
                    ['label' => 'Kategori & Judul'],
                    ['label' => 'Target Penerima'],
                    ['label' => 'Pembaca'],
                    ['label' => 'Status / Expired'],
                    ['label' => 'Aksi', 'align' => 'right']
                ]"
                :items="$announcements"
                emptyIcon="document"
                emptyMessage="Belum ada pengumuman"
                emptySubMessage="Publikasikan pengumuman pertama Anda untuk mengabarkan berita sekolah terbaru."
                emptyActionUrl="{{ route('announcements.create') }}"
                emptyActionText="Buat Pengumuman"
            >
                @foreach ($announcements as $announcement)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-750/30 transition-colors">
                        <!-- Poster -->
                        <td class="py-4 px-6 whitespace-nowrap">
                            @if($announcement->image_path)
                                <img src="{{ Storage::url($announcement->image_path) }}" alt="Poster" class="h-12 w-12 object-cover rounded-xl border border-gray-200 dark:border-slate-700 shadow-xs">
                            @else
                                <div class="h-12 w-12 rounded-xl bg-gray-100 dark:bg-slate-900 flex items-center justify-center text-gray-400 dark:text-slate-650 border border-gray-150 dark:border-slate-700/50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                        </td>

                        <!-- Kategori & Judul -->
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                <!-- Pinned indicator -->
                                @if($announcement->is_pinned)
                                    <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/45 dark:text-amber-300 text-[9px] font-extrabold uppercase tracking-wider border border-amber-250 dark:border-amber-900/30 shadow-xs">
                                         PINNED
                                    </span>
                                @endif

                                <!-- Category Badge -->
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider border {{ 
                                    $announcement->category === 'akademik' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border-blue-200 dark:border-blue-900/40' : (
                                    $announcement->category === 'keuangan' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-250 dark:border-emerald-900/40' : (
                                    $announcement->category === 'kegiatan' ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border-purple-250 dark:border-purple-900/40' : (
                                    $announcement->category === 'keislaman' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-250 dark:border-amber-900/40' : 'bg-slate-50 text-slate-755 dark:bg-slate-800/80 dark:text-slate-300 border-slate-200 dark:border-slate-700/60')))
                                }}">
                                    {{ $announcement->category ?? 'umum' }}
                                </span>

                                <!-- Priority level -->
                                @if($announcement->priority === 'urgent')
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-red-50 text-red-700 dark:bg-red-950/45 dark:text-red-300 border border-red-250 dark:border-red-900/40 uppercase tracking-wider animate-pulse">
                                         URGENT
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm font-bold text-gray-900 dark:text-white leading-tight">
                                {{ $announcement->title }}
                            </div>
                            <div class="text-xs text-gray-400 dark:text-slate-400 mt-0.5 line-clamp-1 max-w-xs md:max-w-md">
                                {{ $announcement->content }}
                            </div>
                        </td>

                        <!-- Target Penerima -->
                        <td class="py-4 px-6 whitespace-nowrap">
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-750 dark:text-blue-400 border border-blue-200 dark:border-blue-900/40">
                                 {{ $announcement->target_role ? ucfirst($announcement->target_role) : 'Semua Orang' }}
                            </span>
                            @if($announcement->targetClassroom)
                                <div class="mt-1.5">
                                    <span class="px-2.5 py-1 inline-flex text-[10px] leading-5 font-bold rounded-full bg-purple-50 dark:bg-purple-950/40 text-purple-750 dark:text-purple-400 border border-purple-200 dark:border-purple-900/40">
                                         {{ $announcement->targetClassroom->name }}
                                    </span>
                                </div>
                            @endif
                        </td>

                        <!-- Pembaca -->
                        <td class="py-4 px-6 whitespace-nowrap">
                            <div class="text-sm font-black text-gray-800 dark:text-slate-200">
                                 {{ $announcement->read_count }}
                            </div>
                            <div class="text-[10px] text-gray-400 dark:text-slate-400 font-bold uppercase tracking-wider mt-0.5">
                                Pembaca Unik
                            </div>
                        </td>

                        <!-- Status / Expired -->
                        <td class="py-4 px-6 whitespace-nowrap">
                            @if($announcement->expires_at && $announcement->expires_at->isPast())
                                <x-status-badge status="expired" />
                            @else
                                <x-status-badge status="aktif" />
                                @if($announcement->expires_at)
                                    <div class="text-[10px] text-gray-400 dark:text-slate-500 mt-1">s.d {{ $announcement->expires_at->format('d M Y') }}</div>
                                @endif
                            @endif
                        </td>

                        <!-- Aksi -->
                        <td class="py-4 px-6 whitespace-nowrap text-right text-xs font-bold tracking-wider">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('announcements.show', $announcement) }}" class="px-2.5 py-1.5 bg-gray-100 dark:bg-slate-700 text-gray-750 dark:text-slate-300 rounded-lg hover:bg-gray-250 dark:hover:bg-slate-655 transition-colors shadow-xs">
                                    Lihat
                                </a>
                                
                                <form action="{{ route('announcements.pin', $announcement) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 {{ $announcement->is_pinned ? 'bg-amber-500 text-gray-950 hover:bg-amber-600' : 'bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 hover:bg-amber-200' }} rounded-lg transition-colors shadow-xs font-bold">
                                        {{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}
                                    </button>
                                </form>

                                <a href="{{ route('announcements.receipts', $announcement) }}" class="px-2.5 py-1.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors shadow-xs">
                                    Penerima
                                </a>

                                <a href="{{ route('announcements.edit', $announcement) }}" class="px-2.5 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-450 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors shadow-xs">
                                    Edit
                                </a>

                                <button type="button" x-data x-on:click="$dispatch('open-modal', 'confirm-delete-{{ $announcement->id }}')" class="px-2.5 py-1.5 bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors shadow-xs">
                                    Hapus
                                </button>
                                <x-confirm-modal 
                                    name="confirm-delete-{{ $announcement->id }}" 
                                    title="Hapus Pengumuman?" 
                                    type="danger" 
                                    :action="route('announcements.destroy', $announcement->id)" 
                                    method="DELETE"
                                    submitText="Ya, Hapus"
                                >
                                    Apakah Anda yakin ingin menghapus pengumuman <strong>{{ $announcement->title }}</strong> beserta semua lampirannya secara permanen?
                                </x-confirm-modal>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-app-layout>
