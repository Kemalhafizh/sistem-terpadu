<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Pengelolaan Konten (CMS) & Landing Page') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Access Quick Buttons -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('cms.pages.index') }}" class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-primary-500 hover:shadow-md transition-all flex flex-col items-center justify-center text-center space-y-2 group">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-primary-600 group-hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <span class="text-sm font-bold text-gray-800">Halaman Statis</span>
                </a>
                <a href="{{ route('cms.posts.index') }}" class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-primary-500 hover:shadow-md transition-all flex flex-col items-center justify-center text-center space-y-2 group">
                    <div class="p-3 bg-purple-50 text-purple-600 rounded-lg group-hover:bg-primary-600 group-hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 4a2 2 0 00-2-2v3m2-3V9m0 0a2 2 0 10-4 0v3M3 19v0M3 14v0M3 9v0"></path></svg>
                    </div>
                    <span class="text-sm font-bold text-gray-800">Berita & Artikel</span>
                </a>
                <a href="{{ route('cms.galleries.index') }}" class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-primary-500 hover:shadow-md transition-all flex flex-col items-center justify-center text-center space-y-2 group">
                    <div class="p-3 bg-teal-50 text-teal-600 rounded-lg group-hover:bg-primary-600 group-hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="text-sm font-bold text-gray-800">Galeri Foto</span>
                </a>
                <a href="{{ route('cms.testimonials.index') }}" class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-primary-500 hover:shadow-md transition-all flex flex-col items-center justify-center text-center space-y-2 group">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-lg group-hover:bg-primary-600 group-hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <span class="text-sm font-bold text-gray-800">Testimoni</span>
                </a>
            </div>

            <!-- Statistical Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Stat Card 1 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Artikel</p>
                        <h3 class="text-3xl font-black text-gray-900 mt-2">{{ $stats['total_posts'] }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 4a2 2 0 00-2-2v3m2-3V9m0 0a2 2 0 10-4 0v3M3 19v0M3 14v0M3 9v0"></path></svg>
                    </div>
                </div>

                <!-- Stat Card 2 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Kunjungan Berita</p>
                        <h3 class="text-3xl font-black text-gray-900 mt-2">{{ $stats['total_views'] }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </div>
                </div>

                <!-- Stat Card 3 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Galeri Aktif</p>
                        <h3 class="text-3xl font-black text-gray-900 mt-2">{{ $stats['active_galleries'] }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                </div>

                <!-- Stat Card 4 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Halaman Publik</p>
                        <h3 class="text-3xl font-black text-gray-900 mt-2">{{ $stats['published_pages'] }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Popular Posts Table -->
                <div class="bg-white shadow-sm border border-gray-150 sm:rounded-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-base font-bold text-gray-800">Artikel Terpopuler</h3>
                    </div>
                    <div class="p-0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Judul</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Views</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Kategori</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($popularPosts as $pop)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 truncate max-w-xs">{{ $pop->title }}</td>
                                        <td class="px-6 py-4 text-sm font-bold text-blue-600">{{ $pop->view_count }}</td>
                                        <td class="px-6 py-4 text-xs font-bold uppercase text-gray-500">{{ $pop->category }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-sm font-medium text-gray-400">Belum ada data artikel.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Posts Table -->
                <div class="bg-white shadow-sm border border-gray-150 sm:rounded-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-base font-bold text-gray-800">Artikel Terbaru</h3>
                    </div>
                    <div class="p-0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Judul</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($recentPosts as $rec)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 truncate max-w-xs">{{ $rec->title }}</td>
                                        <td class="px-6 py-4 text-xs font-bold">
                                            @if($rec->is_published)
                                                <span class="px-2 py-1 bg-emerald-100 text-emerald-800 rounded-full">Published</span>
                                            @else
                                                <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full">Draft</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $rec->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-sm font-medium text-gray-400">Belum ada data artikel.</td>
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
