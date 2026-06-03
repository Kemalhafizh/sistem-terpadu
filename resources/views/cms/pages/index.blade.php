<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kelola Halaman Statis') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('cms.dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-250 text-gray-700 text-sm font-semibold rounded-xl transition-all flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>Dashboard CMS</span>
                </a>
                <a href="{{ route('cms.pages.create') }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow transition-all flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Tambah Halaman</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white shadow-sm border border-gray-150 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Judul / Slug</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Meta Deskripsi</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Navigasi</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Urutan</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($pages as $page)
                                <tr class="hover:bg-gray-50 transition-all">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $page->title }}</div>
                                        <div class="text-xs text-gray-400 font-mono mt-0.5">/page/{{ $page->slug }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                        {{ $page->meta_description ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($page->show_in_nav)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                                Tampil di Nav
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500">
                                                Sembunyi
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($page->is_published)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700">
                                                Draf
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-gray-800">
                                        {{ $page->order }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="{{ route('public.page', $page->slug) }}" target="_blank" class="p-1.5 bg-gray-50 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all" title="Pratinjau">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                            <a href="{{ route('cms.pages.edit', $page->id) }}" class="p-1.5 bg-blue-50 text-blue-600 hover:text-blue-900 hover:bg-blue-100 rounded-lg transition-all" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            <form action="{{ route('cms.pages.destroy', $page->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus halaman ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 bg-red-50 text-red-600 hover:text-red-900 hover:bg-red-100 rounded-lg transition-all" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm font-medium text-gray-400">
                                        Belum ada Halaman Statis yang dibuat. Klik tombol di atas untuk membuat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
