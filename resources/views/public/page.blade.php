@extends('layouts.public')

@section('seo_meta')
    <title>{{ $page->title }} - SMA Islam Sultan Agung 1 Semarang</title>
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-6">
    <!-- Breadcrumb -->
    <nav class="flex mb-8 text-xs font-semibold uppercase tracking-wider text-slate-400" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="hover:text-primary-600">Beranda</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 mx-1 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 md:ml-2 text-slate-500">{{ $page->title }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <header class="mb-12 animate-fade-in-up">
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight mb-4">{{ $page->title }}</h1>
        <div class="h-1.5 w-24 bg-gradient-to-r from-primary-600 to-emerald-500 rounded-full"></div>
    </header>

    <!-- Main Content Container with Glassmorphism Card -->
    <div class="glass rounded-[2rem] p-8 md:p-12 shadow-sm border border-slate-200/80 animate-fade-in-up delay-100">
        <div class="prose prose-slate max-w-none prose-headings:font-black prose-headings:tracking-tight prose-a:text-primary-600 hover:prose-a:text-primary-700 prose-img:rounded-3xl leading-relaxed text-slate-600 font-medium text-base">
            {!! $page->content !!}
        </div>
    </div>
</div>
@endsection
