@extends('layouts.public')

@section('seo_meta')
    <title>Prestasi Sekolah - SMA Islam Sultan Agung 1 Semarang</title>
    <meta name="description" content="Daftar prestasi dan pencapaian gemilang siswa-siswi serta civitas akademika SMA Islam Sultan Agung 1 Semarang di berbagai bidang kompetisi.">
@endsection

@section('content')
<div class="w-full px-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs font-bold text-slate-400 mb-10 animate-fade-in-up">
        <a href="{{ route('home') }}" class="hover:text-primary-600 transition-colors">Beranda</a>
        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <span class="text-slate-600">Prestasi</span>
    </nav>

    {{-- Hero Header --}}
    <div class="text-center max-w-3xl mx-auto mb-20 animate-fade-in-up">
        <div class="inline-flex items-center px-5 py-2 rounded-full bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200/60 mb-6">
            <svg class="w-4 h-4 text-amber-500 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
            <span class="text-[10px] font-black text-amber-700 uppercase tracking-[0.2em]">Hall of Fame</span>
        </div>
        <h2 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-6">Prestasi & Pencapaian</h2>
        <p class="text-slate-500 text-lg font-medium leading-relaxed">Catatan gemilang perjalanan siswa-siswi dan civitas akademika SMA Islam Sultan Agung 1 Semarang dalam menorehkan prestasi di berbagai bidang.</p>
    </div>

    @if(isset($achievements) && $achievements->count() > 0)
        {{-- Timeline Layout --}}
        <div class="relative max-w-4xl mx-auto">

            {{-- Gradient Timeline Line --}}
            <div class="absolute left-6 md:left-1/2 md:-translate-x-px top-0 bottom-0 w-0.5">
                <div class="w-full h-full bg-gradient-to-b from-primary-400 via-emerald-400 to-teal-500 rounded-full"></div>
            </div>

            @foreach($achievements as $index => $achievement)
                @php $isLeft = $index % 2 === 0; @endphp
                <div class="relative flex items-start mb-12 animate-fade-in-up" style="animation-delay: {{ $index * 150 }}ms;">

                    {{-- Timeline Dot --}}
                    <div class="absolute left-6 md:left-1/2 -translate-x-1/2 z-10">
                        <div class="w-5 h-5 rounded-full bg-gradient-to-br from-primary-400 to-emerald-500 border-4 border-white shadow-lg shadow-primary-300/40"></div>
                    </div>

                    {{-- Card — Desktop: alternates left/right, Mobile: always right of line --}}
                    <div class="ml-16 md:ml-0 md:w-[calc(50%-2.5rem)] {{ $isLeft ? 'md:mr-auto md:pr-0' : 'md:ml-auto md:pl-0' }}">
                        <div class="glass rounded-[2rem] overflow-hidden shadow-sm hover:shadow-xl border border-slate-200/60 transition-all duration-300 group hover:-translate-y-1">

                            {{-- Cover Image --}}
                            @if($achievement->cover_image)
                                <div class="relative h-48 w-full overflow-hidden">
                                    <img src="{{ Storage::url($achievement->cover_image) }}" alt="{{ $achievement->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent"></div>
                                </div>
                            @else
                                <div class="relative h-32 w-full bg-gradient-to-br from-primary-400 via-emerald-400 to-teal-500 flex items-center justify-center overflow-hidden">
                                    <svg class="w-12 h-12 text-white/40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                                    <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                                </div>
                            @endif

                            {{-- Card Body --}}
                            <div class="p-6 md:p-8">
                                {{-- Date --}}
                                <div class="flex items-center space-x-2 mb-3">
                                    <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                        {{ $achievement->published_at ? $achievement->published_at->format('d M Y') : '-' }}
                                    </span>
                                </div>

                                {{-- Title --}}
                                <h3 class="text-lg font-black text-slate-900 mb-3 leading-snug group-hover:text-primary-600 transition-colors">
                                    {{ $achievement->title }}
                                </h3>

                                {{-- Excerpt --}}
                                @if($achievement->excerpt)
                                    <p class="text-slate-500 text-sm leading-relaxed font-medium line-clamp-3">{{ $achievement->excerpt }}</p>
                                @endif

                                {{-- Read More --}}
                                @if($achievement->slug)
                                    <div class="mt-5 pt-4 border-t border-slate-100">
                                        <a href="{{ route('public.post', $achievement->slug) }}" class="inline-flex items-center text-primary-600 hover:text-primary-700 font-bold text-xs uppercase tracking-wider transition-colors">
                                            Selengkapnya
                                            <svg class="w-3.5 h-3.5 ml-1.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Timeline End Cap --}}
            <div class="absolute left-6 md:left-1/2 -translate-x-1/2 -bottom-3 z-10">
                <div class="w-3 h-3 rounded-full bg-gradient-to-br from-teal-400 to-emerald-500 shadow-md"></div>
            </div>
        </div>

    @else
        {{-- Empty State --}}
        <div class="max-w-2xl mx-auto animate-fade-in-up">
            <div class="glass rounded-[2.5rem] p-16 text-center border border-slate-200/80">
                <div class="w-20 h-20 mx-auto mb-6 rounded-[1.5rem] bg-gradient-to-br from-amber-50 to-yellow-50 border border-amber-200/40 flex items-center justify-center">
                    <svg class="w-10 h-10 text-amber-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                </div>
                <p class="text-slate-600 font-bold text-lg mb-2">Belum ada prestasi yang dipublikasikan.</p>
                <p class="text-slate-400 font-medium text-sm">Halaman ini akan diperbarui saat ada pencapaian baru yang diraih.</p>
            </div>
        </div>
    @endif

</div>
@endsection
