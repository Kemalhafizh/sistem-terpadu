@extends('layouts.public')

@section('seo_meta')
    <title>Berita & Prestasi Terkini - SMA Islam Sultan Agung 1 Semarang</title>
    <meta name="description" content="Kumpulan warta berita, artikel, kegiatan, dan prestasi terbaru dari SMA Islam Sultan Agung 1 Semarang.">
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-6">
    <!-- Header Section -->
    <div class="text-center max-w-3xl mx-auto mb-16 animate-fade-in-up">
        <h1 class="text-primary-600 font-black tracking-[0.2em] uppercase text-xs mb-4">Warta Sultan Agung</h1>
        <h2 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-6">Berita & Kegiatan Terkini</h2>
        <p class="text-slate-500 text-lg font-medium">Ikuti kabar terbaru, pengumuman resmi, artikel pendidikan, serta ukiran prestasi dari keluarga besar SMA Islam Sultan Agung 1 Semarang.</p>
    </div>

    <!-- Filter & Search Panel -->
    <div class="glass rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-200/80 mb-12 animate-fade-in-up delay-100">
        <form action="{{ route('public.posts') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <!-- Search field -->
            <div class="md:col-span-2 space-y-2">
                <label for="search" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Cari Berita</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik kata kunci..." class="w-full pl-12 pr-4 py-3 bg-white/80 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:border-primary-500 transition-colors">
                    <div class="absolute left-4 top-3.5 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Category field -->
            <div class="space-y-2">
                <label for="category" class="text-xs font-bold text-slate-500 uppercase tracking-widest">Kategori</label>
                <select name="category" id="category" class="w-full px-4 py-3 bg-white/80 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:border-primary-500 transition-colors">
                    <option value="">Semua Kategori</option>
                    <option value="berita" {{ request('category') === 'berita' ? 'selected' : '' }}>Berita Sekolah</option>
                    <option value="prestasi" {{ request('category') === 'prestasi' ? 'selected' : '' }}>Prestasi Unggul</option>
                    <option value="kegiatan" {{ request('category') === 'kegiatan' ? 'selected' : '' }}>Kegiatan Siswa</option>
                    <option value="artikel" {{ request('category') === 'artikel' ? 'selected' : '' }}>Artikel Edukatif</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-primary-600/20 transition-all">Saring</button>
                @if(request()->filled('search') || request()->filled('category'))
                    <a href="{{ route('public.posts') }}" class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-all flex items-center justify-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Posts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 animate-fade-in-up delay-200">
        @if(isset($posts) && $posts->count() > 0)
            @foreach($posts as $post)
                <article class="glass rounded-[2rem] overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group border border-slate-200/60">
                    <div class="relative h-56 w-full overflow-hidden">
                        @if($post->cover_image)
                            <img src="{{ asset('storage/' . $post->cover_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-primary-400 to-indigo-600 flex items-center justify-center text-white">
                                <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @endif
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-[10px] font-black text-primary-700 uppercase tracking-wider shadow-sm">{{ $post->category }}</span>
                        </div>
                    </div>
                    <div class="p-8 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center space-x-3 text-xs text-slate-400 mb-3 font-semibold">
                                <span>{{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}</span>
                                <span>•</span>
                                <span>{{ $post->view_count }} Views</span>
                            </div>
                            <h4 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-primary-600 transition-colors leading-snug">
                                <a href="{{ route('public.post', $post->slug) }}">{{ $post->title }}</a>
                            </h4>
                            <p class="text-slate-500 text-sm leading-relaxed mb-6 font-medium">
                                {{ $post->excerpt ?? Str::limit(strip_tags($post->body), 100) }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-100 pt-6">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-xs text-slate-700">
                                    {{ strtoupper(substr($post->author->name ?? 'A', 0, 2)) }}
                                </div>
                                <span class="text-xs font-bold text-slate-700">{{ $post->author->name ?? 'Humas' }}</span>
                            </div>
                            <a href="{{ route('public.post', $post->slug) }}" class="text-primary-600 hover:text-primary-700 font-bold text-xs uppercase tracking-wider inline-flex items-center">
                                Selengkapnya &rarr;
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        @else
            <div class="col-span-3 glass rounded-2xl p-16 text-center border border-slate-200">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-slate-500 font-bold text-lg mb-2">Pencarian Tidak Ditemukan</p>
                <p class="text-slate-400 font-semibold text-sm">Cobalah mencari kata kunci atau kategori lain yang relevan.</p>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    <div class="mt-16 flex justify-center">
        {{ $posts->links() }}
    </div>
</div>
@endsection
