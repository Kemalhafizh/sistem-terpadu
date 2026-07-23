@extends('layouts.public')

@section('seo_meta')
    <title>Fasilitas Sekolah - SMA Islam Sultan Agung 1 Semarang</title>
    <meta name="description" content="Sarana dan prasarana modern SMA Islam Sultan Agung 1 Semarang — laboratorium, perpustakaan, masjid, lapangan olahraga, dan fasilitas penunjang lainnya.">
@endsection

@section('content')
<div class="w-full px-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs font-bold uppercase tracking-widest mb-12 animate-fade-in-up">
        <a href="{{ route('home') }}" class="text-slate-400 hover:text-primary-600 transition-colors">Beranda</a>
        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
        <span class="text-primary-600">Fasilitas</span>
    </nav>

    {{-- Header --}}
    <div class="text-center max-w-3xl mx-auto mb-16 animate-fade-in-up">
        <p class="text-primary-600 font-black tracking-[0.25em] uppercase text-[10px] mb-4">Sarana & Prasarana</p>
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-6">Fasilitas Sekolah</h1>
        <p class="text-slate-500 text-lg font-medium">Kami menyediakan berbagai fasilitas modern dan lengkap untuk menunjang proses belajar mengajar yang optimal dan nyaman.</p>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         FACILITIES GRID
    ═══════════════════════════════════════════════════════ --}}
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12 animate-fade-in-up" style="animation-delay: .1s">

        {{-- 1. Laboratorium IPA --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/80 shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-300 group">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/20 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-900 tracking-tight mb-2">Laboratorium IPA</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">Laboratorium sains lengkap untuk praktikum Fisika, Kimia, dan Biologi dengan peralatan modern dan standar keselamatan tinggi.</p>
        </div>

        {{-- 2. Laboratorium Komputer --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/80 shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-300 group">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/20 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-900 tracking-tight mb-2">Laboratorium Komputer</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">Ruang komputer dengan perangkat terkini dan koneksi internet berkecepatan tinggi untuk pembelajaran TIK dan ujian berbasis komputer.</p>
        </div>

        {{-- 3. Perpustakaan --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/80 shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-300 group">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/20 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-900 tracking-tight mb-2">Perpustakaan</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">Koleksi ribuan buku referensi, fiksi, dan jurnal ilmiah dengan ruang baca yang nyaman serta sistem katalog digital terintegrasi.</p>
        </div>

        {{-- 4. Masjid Sekolah --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/80 shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-300 group">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-emerald-600 shadow-lg shadow-primary-500/20 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3L2 12h3v8h14v-8h3L12 3z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v5m0 0a2 2 0 100 4 2 2 0 000-4z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-900 tracking-tight mb-2">Masjid Sekolah</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">Masjid yang luas dan representatif untuk kegiatan ibadah harian, sholat berjamaah, kajian keislaman, dan perayaan hari besar Islam.</p>
        </div>

        {{-- 5. Lapangan Olahraga --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/80 shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-300 group">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-green-500 to-lime-600 shadow-lg shadow-green-500/20 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-900 tracking-tight mb-2">Lapangan Olahraga</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">Lapangan serbaguna untuk basket, futsal, voli, dan upacara. Dilengkapi tribun penonton dan pencahayaan yang memadai.</p>
        </div>

        {{-- 6. Ruang Multimedia --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/80 shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-300 group">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 shadow-lg shadow-rose-500/20 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-900 tracking-tight mb-2">Ruang Multimedia</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">Ruang presentasi dengan proyektor, sound system, dan fasilitas audio visual untuk mendukung pembelajaran interaktif dan kreatif.</p>
        </div>

        {{-- 7. Ruang BK --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/80 shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-300 group">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-500 to-sky-600 shadow-lg shadow-cyan-500/20 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-900 tracking-tight mb-2">Ruang BK</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">Ruang Bimbingan Konseling yang privat dan nyaman untuk konsultasi akademik, karir, serta pengembangan potensi diri siswa.</p>
        </div>

        {{-- 8. Unit Kesehatan Sekolah (UKS) --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/80 shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-300 group">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg shadow-red-500/20 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-900 tracking-tight mb-2">Unit Kesehatan Sekolah (UKS)</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">Ruang kesehatan dengan peralatan P3K lengkap, tempat istirahat, dan tenaga kesehatan terlatih untuk penanganan pertama siswa.</p>
        </div>

    </div>

    {{-- Bottom CTA --}}
    <div class="text-center animate-fade-in-up mb-12" style="animation-delay: .2s">
        <div class="glass rounded-[2rem] p-10 md:p-14 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-600/5 via-transparent to-emerald-500/5 pointer-events-none"></div>
            <div class="relative">
                <p class="text-primary-600 font-black tracking-[0.25em] uppercase text-[10px] mb-4">Kunjungi Kami</p>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight mb-4">Ingin Melihat Fasilitas Secara Langsung?</h2>
                <p class="text-slate-500 text-base font-medium max-w-xl mx-auto mb-8">Jadwalkan kunjungan ke sekolah kami untuk melihat langsung seluruh fasilitas yang tersedia dan merasakan atmosfer belajar yang nyaman.</p>
                <a href="{{ route('home') }}" class="inline-flex items-center space-x-2 px-8 py-4 bg-gradient-to-r from-primary-600 to-emerald-500 text-white rounded-xl font-black text-xs uppercase tracking-[0.15em] shadow-xl shadow-primary-600/25 hover:shadow-2xl hover:shadow-primary-600/30 hover:-translate-y-0.5 transition-all duration-300 active:scale-[0.98]">
                    <span>Hubungi Kami</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
