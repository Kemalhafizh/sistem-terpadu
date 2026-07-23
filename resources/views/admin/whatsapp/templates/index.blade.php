<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    {{ __('WhatsApp Message Templates') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kelola template pesan dinamis dengan variabel penampung untuk pengiriman otomatis maupun massal.</p>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.whatsapp.templates.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-500/20 hover:-translate-y-0.5">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Template Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/30 rounded-2xl text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/30 rounded-2xl text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Navigation Tabs -->
            <div class="flex flex-wrap gap-2 bg-gray-100/50 dark:bg-gray-900/50 p-1.5 rounded-2xl border border-gray-100 dark:border-gray-800 max-w-max">
                <a href="{{ route('admin.whatsapp.dashboard') }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request()->routeIs('admin.whatsapp.dashboard') ? 'bg-teal-600 text-white shadow-sm shadow-teal-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-800' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.whatsapp.templates.index') }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request()->routeIs('admin.whatsapp.templates.*') ? 'bg-teal-600 text-white shadow-sm shadow-teal-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-800' }}">
                    Templates
                </a>
                <a href="{{ route('admin.whatsapp.broadcasts.index') }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request()->routeIs('admin.whatsapp.broadcasts.*') ? 'bg-teal-600 text-white shadow-sm shadow-teal-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-800' }}">
                    Broadcasts
                </a>
                <a href="{{ route('admin.whatsapp.automations.index') }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request()->routeIs('admin.whatsapp.automations.*') ? 'bg-teal-600 text-white shadow-sm shadow-teal-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-800' }}">
                    Automations
                </a>
                <a href="{{ route('admin.whatsapp.index') }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request()->routeIs('admin.whatsapp.index') ? 'bg-teal-600 text-white shadow-sm shadow-teal-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-800' }}">
                    Settings & Simulator
                </a>
                <a href="{{ route('admin.whatsapp.logs') }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request()->routeIs('admin.whatsapp.logs') ? 'bg-teal-600 text-white shadow-sm shadow-teal-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-800' }}">
                    Logs
                </a>
            </div>

            <!-- Templates List Card Panel -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Daftar Template Pesan</h3>
                        <p class="text-[10px] text-gray-400 font-bold mt-0.5">Menyimpan format pesan instan terstruktur.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30 text-gray-400 uppercase tracking-widest font-black">
                                <th class="py-4 px-6">Nama & Kategori</th>
                                <th class="py-4 px-6">Isi Template</th>
                                <th class="py-4 px-6">Variabel Placeholders</th>
                                <th class="py-4 px-6">Status Aktif</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($templates as $template)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/10 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900 dark:text-white text-sm">
                                            {{ $template->name }}
                                        </div>
                                        <div class="mt-1">
                                            @if($template->category === 'keuangan')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900/40">Keuangan</span>
                                            @elseif($template->category === 'akademik')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900/40">Akademik</span>
                                            @elseif($template->category === 'keislaman')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/40">Keislaman</span>
                                            @elseif($template->category === 'kegiatan')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">Kegiatan</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">Umum</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 max-w-xs font-medium text-gray-600 dark:text-gray-300">
                                        <div class="line-clamp-2" title="{{ $template->content }}">
                                            {{ $template->content }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 max-w-xs">
                                        <div class="flex flex-wrap gap-1">
                                            @php
                                                $placeholdersList = is_string($template->placeholders) 
                                                    ? json_decode($template->placeholders, true) 
                                                    : $template->placeholders;
                                            @endphp
                                            @if(!empty($placeholdersList) && is_array($placeholdersList))
                                                @foreach($placeholdersList as $placeholder)
                                                    <span class="inline-block px-1.5 py-0.5 rounded bg-teal-50 dark:bg-teal-950/50 text-teal-700 dark:text-teal-400 font-mono text-[9px] font-bold">
                                                        {{ '{'.$placeholder.'}' }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-gray-400 italic text-[10px]">Tanpa Variabel</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <!-- Interactive toggler submitting updateTemplate dynamically -->
                                        <form action="{{ route('admin.whatsapp.templates.update', $template->id) }}" method="POST" id="toggle-form-{{ $template->id }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" value="{{ $template->name }}">
                                            <input type="hidden" name="category" value="{{ $template->category }}">
                                            <input type="hidden" name="content" value="{{ $template->content }}">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" onchange="document.getElementById('toggle-form-{{ $template->id }}').submit();" {{ $template->is_active ? 'checked' : '' }}>
                                                <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-teal-600"></div>
                                            </label>
                                        </form>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Edit Button -->
                                            <a href="{{ route('admin.whatsapp.templates.edit', $template->id) }}" class="p-2 rounded-xl bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-950/20 transition-all" title="Edit Template">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </a>

                                            <!-- Delete Button -->
                                            <form action="{{ route('admin.whatsapp.templates.delete', $template->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini? Penggunaan otomasi dengan template ini akan terdampak.');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-xl bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all" title="Hapus Template">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-16 text-center text-gray-400 dark:text-gray-500 italic">
                                        Belum ada template pesan yang ditambahkan. Silakan klik tombol "Template Baru" untuk membuatnya.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($templates->hasPages())
                    <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        {{ $templates->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
