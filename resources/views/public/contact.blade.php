@extends('layouts.public')

@section('seo_meta')
    <title>Hubungi Kami - SMA Islam Sultan Agung 1 Semarang</title>
    <meta name="description" content="Hubungi SMA Islam Sultan Agung 1 Semarang. Alamat, nomor telepon, email, dan formulir kontak untuk informasi lebih lanjut.">
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs font-bold uppercase tracking-widest mb-12 animate-fade-in-up">
        <a href="{{ route('home') }}" class="text-slate-400 hover:text-primary-600 transition-colors">Beranda</a>
        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
        <span class="text-primary-600">Hubungi Kami</span>
    </nav>

    {{-- Header --}}
    <div class="text-center max-w-3xl mx-auto mb-16 animate-fade-in-up">
        <p class="text-primary-600 font-black tracking-[0.25em] uppercase text-[10px] mb-4">Kontak & Lokasi</p>
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-6">Hubungi Kami</h1>
        <p class="text-slate-500 text-lg font-medium">Kami siap membantu Anda. Silakan hubungi kami melalui informasi di bawah ini atau kirimkan pesan melalui formulir kontak.</p>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         CONTACT INFO CARDS
    ═══════════════════════════════════════════════════════ --}}
    <div class="grid md:grid-cols-3 gap-6 mb-16 animate-fade-in-up" style="animation-delay: .1s">
        {{-- Alamat --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 shadow-lg shadow-primary-500/20 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-900 mb-2">Alamat</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">Jl. Mataram No.657, Wonodri<br>Semarang, Jawa Tengah 50242</p>
        </div>

        {{-- Telepon --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/20 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-900 mb-2">Telepon</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">(024) 6580019</p>
        </div>

        {{-- Email --}}
        <div class="glass rounded-[2rem] p-8 border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-teal-500 to-teal-600 shadow-lg shadow-teal-500/20 mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-900 mb-2">Email</h3>
            <p class="text-slate-500 text-sm font-medium leading-relaxed">info@sultanagung1.sch.id</p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         GOOGLE MAPS & CONTACT FORM
    ═══════════════════════════════════════════════════════ --}}
    <div class="grid lg:grid-cols-5 gap-8 mb-12">

        {{-- Google Maps --}}
        <div class="lg:col-span-3 animate-fade-in-up" style="animation-delay: .2s">
            <div class="glass rounded-[2rem] overflow-hidden border border-slate-200/80 shadow-sm h-full">
                <div class="p-6 pb-4">
                    <p class="text-primary-600 font-black tracking-[0.25em] uppercase text-[10px] mb-2 flex items-center">
                        <span class="w-8 h-px bg-primary-600 mr-3"></span>Lokasi Kami
                    </p>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Peta Lokasi Sekolah</h2>
                </div>
                <div class="px-6 pb-6">
                    <div class="rounded-[1.5rem] overflow-hidden border border-slate-200/60">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.123!2d110.456!3d-6.789"
                            width="100%"
                            height="380"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            class="w-full"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Form --}}
        <div class="lg:col-span-2 animate-fade-in-up" style="animation-delay: .3s">
            <div class="glass rounded-[2rem] p-8 border border-slate-200/80 shadow-sm relative overflow-hidden h-full">
                {{-- Decorative gradient --}}
                <div class="absolute -top-20 -right-20 w-40 h-40 bg-primary-400/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative">
                    <p class="text-primary-600 font-black tracking-[0.25em] uppercase text-[10px] mb-2 flex items-center">
                        <span class="w-8 h-px bg-primary-600 mr-3"></span>Formulir Kontak
                    </p>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight mb-6">Kirim Pesan</h2>

                    <form action="#" method="POST" class="space-y-5">
                        @csrf

                        {{-- Nama --}}
                        <div>
                            <label for="nama" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Nama Lengkap</label>
                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                required
                                placeholder="Masukkan nama Anda"
                                class="w-full px-5 py-3.5 rounded-xl bg-white/70 border border-slate-200 text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                            >
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Alamat Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                required
                                placeholder="nama@email.com"
                                class="w-full px-5 py-3.5 rounded-xl bg-white/70 border border-slate-200 text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                            >
                        </div>

                        {{-- Subjek --}}
                        <div>
                            <label for="subjek" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Subjek</label>
                            <input
                                type="text"
                                id="subjek"
                                name="subjek"
                                required
                                placeholder="Subjek pesan"
                                class="w-full px-5 py-3.5 rounded-xl bg-white/70 border border-slate-200 text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all"
                            >
                        </div>

                        {{-- Pesan --}}
                        <div>
                            <label for="pesan" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Pesan</label>
                            <textarea
                                id="pesan"
                                name="pesan"
                                rows="4"
                                required
                                placeholder="Tulis pesan Anda di sini..."
                                class="w-full px-5 py-3.5 rounded-xl bg-white/70 border border-slate-200 text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 transition-all resize-none"
                            ></textarea>
                        </div>

                        {{-- Submit Button --}}
                        <button
                            type="submit"
                            class="w-full px-8 py-4 bg-gradient-to-r from-primary-600 to-emerald-500 text-white rounded-xl font-black text-xs uppercase tracking-[0.15em] shadow-xl shadow-primary-600/25 hover:shadow-2xl hover:shadow-primary-600/30 hover:-translate-y-0.5 transition-all duration-300 active:scale-[0.98] flex items-center justify-center space-x-2"
                        >
                            <span>Kirim Pesan</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
