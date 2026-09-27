@extends('layouts.public')

@section('seo_meta')
    <title>Virtual Tour - {{ school_name() }}</title>
    <meta name="description" content="Jelajahi kampus {{ school_name() }} secara virtual. Lihat fasilitas gedung utama, area olahraga, masjid dan taman sekolah.">
@endsection

@section('content')
<div class="w-full px-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs font-bold text-slate-400 mb-10 animate-fade-in-up">
        <a href="{{ route('home') }}" class="hover:text-primary-600 transition-colors">Beranda</a>
        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <span class="text-slate-600">Virtual Tour</span>
    </nav>

    {{-- Hero Header --}}
    <div class="text-center max-w-3xl mx-auto mb-16 animate-fade-in-up">
        <div class="inline-flex items-center px-5 py-2 rounded-full bg-gradient-to-r from-indigo-50 to-violet-50 border border-indigo-200/60 mb-6">
            <svg class="w-4 h-4 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            <span class="text-[10px] font-black text-indigo-700 uppercase tracking-[0.2em]">Tur 360°</span>
        </div>
        <h2 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-6">Virtual Tour Sekolah</h2>
        <p class="text-slate-500 text-lg font-medium leading-relaxed">Jelajahi kampus kami secara virtual dan rasakan pengalaman menjadi bagian dari keluarga besar {{ school_name() }}.</p>
    </div>

    {{-- 360° Viewer Placeholder --}}
    <div class="glass rounded-[2.5rem] overflow-hidden border border-slate-200/60 shadow-sm mb-16 animate-fade-in-up" style="animation-delay: 150ms;">
        <div class="relative w-full" style="min-height: 28rem;">
            {{-- Gradient Background --}}
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 via-violet-500 to-purple-600 overflow-hidden">
                {{-- Decorative Elements --}}
                <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-white/10 rounded-full blur-3xl animate-blob"></div>
                <div class="absolute bottom-1/4 right-1/4 w-48 h-48 bg-indigo-300/20 rounded-full blur-2xl animate-blob animation-delay-2000"></div>
                <div class="absolute top-1/2 right-1/3 w-32 h-32 bg-violet-300/15 rounded-full blur-2xl animate-blob animation-delay-4000"></div>

                {{-- Grid pattern overlay --}}
                <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 30px 30px;"></div>
            </div>

            {{-- Center Content --}}
            <div class="relative flex flex-col items-center justify-center text-center px-8 py-20 md:py-24 z-10">
                {{-- VR/360° Icon --}}
                <div class="w-24 h-24 rounded-[1.5rem] bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center mb-8 animate-float shadow-2xl shadow-violet-900/30">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        {{-- 360° panoramic icon --}}
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>

                <h3 class="text-white font-black text-2xl md:text-3xl mb-4 tracking-tight">Segera Hadir</h3>
                <p class="text-white/80 font-medium text-base md:text-lg max-w-lg leading-relaxed mb-8">
                    Konten Virtual Tour akan segera tersedia. Silakan kunjungi sekolah kami untuk pengalaman langsung.
                </p>

                {{-- CTA Button --}}
                <a href="{{ route('home') }}#kontak" class="inline-flex items-center px-8 py-4 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white font-bold text-xs uppercase tracking-widest rounded-2xl border border-white/25 transition-all duration-300 hover:-translate-y-0.5 shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Kunjungi Sekolah
                </a>
            </div>
        </div>
    </div>

    {{-- Highlight Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">

        {{-- Card 1: Gedung Utama --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/60 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group animate-fade-in-up" style="animation-delay: 300ms;">
            <div class="w-16 h-16 rounded-[1.25rem] bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center mb-6 shadow-lg shadow-blue-500/20 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 22h20M3 22V6l9-4 9 4v16M9 22v-5a3 3 0 016 0v5M7 10h.01M12 10h.01M17 10h.01M7 14h.01M12 14h.01M17 14h.01"></path>
                </svg>
            </div>
            <span class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] mb-2 block">Zona 01</span>
            <h3 class="text-xl font-black text-slate-900 mb-3 tracking-tight">Gedung Utama</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">Ruang kelas modern, laboratorium lengkap, perpustakaan digital, dan ruang multimedia yang menunjang kegiatan belajar mengajar.</p>
        </div>

        {{-- Card 2: Area Olahraga --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/60 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group animate-fade-in-up" style="animation-delay: 450ms;">
            <div class="w-16 h-16 rounded-[1.25rem] bg-gradient-to-br from-emerald-400 to-green-500 flex items-center justify-center mb-6 shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em] mb-2 block">Zona 02</span>
            <h3 class="text-xl font-black text-slate-900 mb-3 tracking-tight">Area Olahraga</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">Lapangan olahraga terbuka, hall indoor serbaguna, serta fasilitas pendukung aktivitas fisik dan ekstrakurikuler siswa.</p>
        </div>

        {{-- Card 3: Masjid & Taman --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/60 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group animate-fade-in-up" style="animation-delay: 600ms;">
            <div class="w-16 h-16 rounded-[1.25rem] bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center mb-6 shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-1.2 0-2.4.6-3 1.5C8.4 5.4 7.2 6 6 6c-2.2 0-4 1.8-4 4v8a2 2 0 002 2h16a2 2 0 002-2v-8c0-2.2-1.8-4-4-4-1.2 0-2.4-.6-3-1.5-.6-.9-1.8-1.5-3-1.5zM12 3v4M8 20v-3a4 4 0 018 0v3"></path>
                </svg>
            </div>
            <span class="text-[10px] font-black text-amber-600 uppercase tracking-[0.2em] mb-2 block">Zona 03</span>
            <h3 class="text-xl font-black text-slate-900 mb-3 tracking-tight">Masjid & Taman</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">Masjid sekolah sebagai pusat kegiatan keagamaan, dilengkapi taman asri yang menjadi ruang refleksi dan relaksasi warga sekolah.</p>
        </div>

    </div>

</div>
@endsection
