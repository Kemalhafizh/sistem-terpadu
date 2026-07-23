@extends('layouts.public')

@section('seo_meta')
    <title>Galeri Dokumentasi & Potret Kegiatan - SMA Islam Sultan Agung 1 Semarang</title>
    <meta name="description" content="Dokumentasi foto kegiatan belajar mengajar, sarana prasarana, fasilitas penunjang, dan prestasi di SMA Islam Sultan Agung 1 Semarang.">
@endsection

@section('content')
<div class="w-full px-6">
    <!-- Header Section -->
    <div class="text-center max-w-3xl mx-auto mb-16 animate-fade-in-up">
        <h1 class="text-primary-600 font-black tracking-[0.2em] uppercase text-xs mb-4">Galeri Dokumentasi</h1>
        <h2 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-6">Potret Sarana & Kegiatan</h2>
        <p class="text-slate-500 text-lg font-medium">Jelajahi potret-potret sarana prasarana modern, fasilitas unggul, dinamika kegiatan belajar mengajar, serta kebersamaan civitas akademika kami.</p>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex flex-wrap justify-center gap-3 mb-12 animate-fade-in-up delay-100">
        <a href="{{ route('public.gallery') }}" class="px-6 py-3 rounded-full font-bold text-xs uppercase tracking-widest transition-all shadow-sm {{ !request('category') ? 'bg-primary-600 text-white shadow-primary-600/20' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200' }}">Semua Foto</a>
        @if(isset($categories) && $categories->count() > 0)
            @foreach($categories as $category)
                <a href="{{ route('public.gallery', ['category' => $category]) }}" class="px-6 py-3 rounded-full font-bold text-xs uppercase tracking-widest transition-all shadow-sm {{ request('category') === $category ? 'bg-primary-600 text-white shadow-primary-600/20' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200' }}">{{ $category }}</a>
            @endforeach
        @endif
    </div>

    <!-- Gallery Grid (Masonry Grid style) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 animate-fade-in-up delay-200">
        @if(isset($galleries) && $galleries->count() > 0)
            @foreach($galleries as $gallery)
                <div class="group relative h-72 rounded-[2rem] overflow-hidden shadow-sm border border-slate-200/80 cursor-pointer glass">
                    <img src="{{ asset('storage/' . $gallery->image_path) }}" alt="{{ $gallery->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-6 flex flex-col justify-end">
                        <span class="text-[9px] font-black text-primary-400 uppercase tracking-widest mb-1">{{ $gallery->category }}</span>
                        <h4 class="text-white font-bold text-base mb-1 leading-snug">{{ $gallery->title }}</h4>
                        @if($gallery->caption)
                            <p class="text-slate-300 text-xs line-clamp-2">{{ $gallery->caption }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-span-4 glass rounded-[2.5rem] p-16 text-center border border-slate-200">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <p class="text-slate-500 font-bold text-lg mb-2">Dokumentasi Foto Belum Tersedia</p>
                <p class="text-slate-400 font-semibold text-sm">Humas sekolah belum mengunggah foto dokumentasi dalam kategori ini.</p>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    <div class="mt-16 flex justify-center">
        {{ $galleries->links() }}
    </div>
</div>
@endsection
