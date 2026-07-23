@extends('layouts.public')

@section('seo_meta')
    <title>{{ $post->title }} - SMA Islam Sultan Agung 1 Semarang</title>
    @if($post->excerpt)
        <meta name="description" content="{{ $post->excerpt }}">
    @endif
@endsection

@section('content')
<div class="w-full px-6">
    <!-- Breadcrumb -->
    <nav class="flex mb-8 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="hover:text-primary-600">Beranda</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 mx-1 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ route('public.posts') }}" class="ml-1 md:ml-2 hover:text-primary-600">Berita</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 mx-1 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 md:ml-2 text-slate-500 line-clamp-1">{{ $post->title }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
        <!-- Main Content Area -->
        <article class="lg:col-span-2 space-y-8 animate-fade-in-up">
            <!-- Header -->
            <header class="space-y-4">
                <span class="px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-xs font-black uppercase tracking-wider">{{ $post->category }}</span>
                <h1 class="text-3xl md:text-5xl font-black text-slate-900 leading-tight">{{ $post->title }}</h1>
                
                <div class="flex items-center space-x-6 text-sm text-slate-400 font-bold border-b border-slate-200/80 pb-6">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-xs">
                            {{ strtoupper(substr($post->author->name ?? 'H', 0, 2)) }}
                        </div>
                        <span class="text-slate-700">{{ $post->author->name ?? 'Humas Sekolah' }}</span>
                    </div>
                    <span>•</span>
                    <span>{{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}</span>
                    <span>•</span>
                    <span class="flex items-center"><svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> {{ $post->view_count }} Kali Dibaca</span>
                </div>
            </header>

            <!-- Cover Image -->
            <div class="w-full h-[400px] rounded-[2.5rem] overflow-hidden shadow-md">
                @if($post->cover_image)
                    <img src="{{ asset('storage/' . $post->cover_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-primary-400 to-indigo-600 flex items-center justify-center text-white text-lg font-black">
                        {{ $post->title }}
                    </div>
                @endif
            </div>

            <!-- Body -->
            <div class="glass rounded-[2rem] p-8 md:p-12 shadow-sm border border-slate-200/80">
                <div class="prose prose-slate max-w-none prose-headings:font-black prose-headings:tracking-tight prose-a:text-primary-600 hover:prose-a:text-primary-700 prose-img:rounded-3xl leading-relaxed text-slate-600 font-medium text-base">
                    {!! $post->body !!}
                </div>
            </div>

            <!-- Sharing Buttons -->
            <div class="flex items-center space-x-4 border-t border-slate-200 pt-8">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Bagikan:</span>
                <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . request()->url()) }}" target="_blank" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-500/10">WhatsApp</a>
                <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(request()->url()) }}" target="_blank" class="px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-sky-500/10">Twitter</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-blue-600/10">Facebook</a>
            </div>
        </article>

        <!-- Sidebar (Related Posts) -->
        <aside class="space-y-8 animate-fade-in-up delay-100">
            <div class="glass rounded-[2rem] p-8 shadow-sm border border-slate-200/80">
                <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center">
                    <span class="w-2.5 h-6 bg-primary-600 rounded-full mr-3 inline-block"></span>
                    Rekomendasi Berita
                </h3>
                <div class="space-y-6">
                    @if(isset($relatedPosts) && $relatedPosts->count() > 0)
                        @foreach($relatedPosts as $related)
                            <div class="group flex space-x-4 items-start cursor-pointer">
                                <div class="w-20 h-20 rounded-2xl overflow-hidden shrink-0 bg-slate-100 border border-slate-100">
                                    @if($related->cover_image)
                                        <img src="{{ asset('storage/' . $related->cover_image) }}" alt="{{ $related->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-tr from-slate-200 to-slate-300 flex items-center justify-center text-[10px] font-black text-slate-500">
                                            SA
                                        </div>
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[9px] font-black text-primary-600 uppercase tracking-widest">{{ $related->category }}</span>
                                    <h4 class="text-sm font-bold text-slate-800 leading-snug group-hover:text-primary-600 transition-colors line-clamp-2">
                                        <a href="{{ route('public.post', $related->slug) }}">{{ $related->title }}</a>
                                    </h4>
                                    <p class="text-[10px] text-slate-400 font-bold">{{ $related->published_at ? $related->published_at->format('d M Y') : $related->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-slate-400 font-semibold text-xs">Belum ada rekomendasi berita terkait.</p>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
