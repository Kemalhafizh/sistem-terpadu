@extends('layouts.public')

@section('seo_meta')
    <title>Tentang Kami - {{ school_name() }}</title>
    <meta name="description" content="Mengenal lebih dekat {{ school_name() }} — sejarah, visi misi, dan komitmen kami dalam membentuk generasi berilmu, beriman, dan berakhlak mulia.">
@endsection

@section('content')
<div class="w-full px-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs font-bold uppercase tracking-widest mb-12 animate-fade-in-up">
        <a href="{{ route('home') }}" class="text-slate-400 hover:text-primary-600 transition-colors">Beranda</a>
        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
        <span class="text-primary-600">Tentang Kami</span>
    </nav>

    {{-- HERO SECTION --}}
    <section class="relative mb-24 animate-fade-in-up">
        <div class="relative glass rounded-[2.5rem] overflow-hidden border border-slate-200/80 shadow-xl">
            {{-- Decorative gradient overlay --}}
            <div class="absolute inset-0 bg-gradient-to-br from-primary-600/10 via-transparent to-emerald-500/10 pointer-events-none"></div>

            <div class="relative px-8 py-16 md:px-16 md:py-24 text-center">
                {{-- School icon --}}
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-[1.5rem] bg-gradient-to-br from-primary-500 to-emerald-500 shadow-xl shadow-primary-500/25 mb-8 animate-float">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                </div>

                <p class="text-primary-600 font-black tracking-[0.25em] uppercase text-[10px] mb-4">{{ school_name() }}</p>
                <h1 class="text-4xl md:text-6xl font-black text-slate-900 tracking-tight mb-6 leading-tight">
                    Membentuk Generasi<br>
                    <span class="text-primary-600 dark:text-primary-400">Berilmu, Beriman</span><br>
                    & Berakhlak Mulia
                </h1>
                <p class="text-slate-500 text-lg md:text-xl font-medium max-w-2xl mx-auto leading-relaxed">
                    Sejak tahun 1963, kami berkomitmen mencetak lulusan yang unggul dalam ilmu pengetahuan, kokoh dalam keimanan, dan mulia dalam akhlak.
                </p>
            </div>
        </div>
    </section>

    {{-- SEJARAH SINGKAT --}}
    <section class="mb-24 animate-fade-in-up" style="animation-delay: .1s">
        <div class="grid lg:grid-cols-5 gap-12 items-start">
            {{-- Section label --}}
            <div class="lg:col-span-2 lg:sticky lg:top-32">
                <p class="text-primary-600 font-black tracking-[0.25em] uppercase text-[10px] mb-4 flex items-center">
                    <span class="w-8 h-px bg-primary-600 mr-3"></span>Sejarah Singkat
                </p>
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight mb-4">Perjalanan Panjang Penuh Dedikasi</h2>
                <div class="w-16 h-1.5 bg-gradient-to-r from-primary-500 to-emerald-500 rounded-full"></div>
            </div>

            {{-- Content --}}
            <div class="lg:col-span-3 space-y-6">
                <div class="glass rounded-[2rem] p-8 md:p-10 border border-slate-200/80 shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <p class="text-slate-600 text-base leading-relaxed font-medium mb-6">
                        {{ school_name() }} didirikan pada tahun 1963 oleh {{ school_foundation_name() }} sebagai bagian dari upaya besar mencerdaskan kehidupan bangsa yang berlandaskan nilai-nilai Islam. Berawal dari beberapa ruang kelas sederhana di kawasan Kaligawe, sekolah ini terus tumbuh menjadi lembaga pendidikan Islam terkemuka di Kota Semarang.
                    </p>
                    <p class="text-slate-600 text-base leading-relaxed font-medium mb-6">
                        Seiring perjalanan waktu, {{ school_short_name() }} terus berinovasi dalam metode pembelajaran, mengembangkan kurikulum yang memadukan pendidikan umum berkualitas tinggi dengan penanaman akidah dan akhlak mulia. Berbagai prestasi akademik maupun non-akademik telah diraih oleh para siswa di tingkat regional, nasional, hingga internasional.
                    </p>
                    <p class="text-slate-600 text-base leading-relaxed font-medium">
                        Kini, dengan fasilitas modern dan tenaga pendidik profesional, {{ school_name() }} terus berkomitmen untuk menjadi sekolah Islam unggulan yang menghasilkan lulusan berkarakter, berdaya saing global, dan bermanfaat bagi umat.
                    </p>
                </div>

                {{-- Stats row --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="glass rounded-[1.5rem] p-6 text-center border border-slate-200/80 hover:shadow-md transition-shadow">
                        <p class="text-3xl md:text-4xl font-black text-primary-600 dark:text-primary-400">60+</p>
                        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1">Tahun Berdiri</p>
                    </div>
                    <div class="glass rounded-[1.5rem] p-6 text-center border border-slate-200/80 hover:shadow-md transition-shadow">
                        <p class="text-3xl md:text-4xl font-black text-primary-600 dark:text-primary-400">1000+</p>
                        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1">Siswa Aktif</p>
                    </div>
                    <div class="glass rounded-[1.5rem] p-6 text-center border border-slate-200/80 hover:shadow-md transition-shadow">
                        <p class="text-3xl md:text-4xl font-black text-primary-600 dark:text-primary-400">100+</p>
                        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1">Tenaga Pendidik</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- VISI & MISI --}}
    <section class="mb-24 animate-fade-in-up" style="animation-delay: .2s">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <p class="text-primary-600 font-black tracking-[0.25em] uppercase text-[10px] mb-4">Arah & Tujuan</p>
            <h2 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight mb-4">Visi & Misi Sekolah</h2>
            <div class="w-16 h-1.5 bg-gradient-to-r from-primary-500 to-emerald-500 rounded-full mx-auto"></div>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            {{-- Visi --}}
            <div class="glass rounded-[2rem] p-8 md:p-10 border border-slate-200/80 shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-primary-500/10 to-transparent rounded-bl-full pointer-events-none group-hover:from-primary-500/20 transition-all duration-500"></div>

                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 shadow-lg shadow-primary-500/20 mb-6">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </div>

                <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-4">Visi</h3>
                <p class="text-slate-600 text-base leading-relaxed font-medium">
                    Terwujudnya sekolah Islam unggulan yang menghasilkan lulusan berilmu amaliah, beramal ilmiah, berkarakter Islami, berdaya saing global, dan berwawasan lingkungan.
                </p>
            </div>

            {{-- Misi --}}
            <div class="glass rounded-[2rem] p-8 md:p-10 border border-slate-200/80 shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-emerald-500/10 to-transparent rounded-bl-full pointer-events-none group-hover:from-emerald-500/20 transition-all duration-500"></div>

                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/20 mb-6">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>

                <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-4">Misi</h3>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mt-0.5 mr-3">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        </span>
                        <span class="text-slate-600 text-sm leading-relaxed font-medium">Menyelenggarakan pendidikan Islam yang berkualitas dan berdaya saing tinggi.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mt-0.5 mr-3">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        </span>
                        <span class="text-slate-600 text-sm leading-relaxed font-medium">Membudayakan nilai-nilai Islam dalam seluruh aspek kehidupan warga sekolah.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mt-0.5 mr-3">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        </span>
                        <span class="text-slate-600 text-sm leading-relaxed font-medium">Mengembangkan potensi siswa secara optimal melalui kegiatan akademik dan non-akademik.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mt-0.5 mr-3">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        </span>
                        <span class="text-slate-600 text-sm leading-relaxed font-medium">Menumbuhkan kesadaran cinta lingkungan dan kepedulian sosial.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mt-0.5 mr-3">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        </span>
                        <span class="text-slate-600 text-sm leading-relaxed font-medium">Membangun kemitraan global dengan lembaga pendidikan dalam dan luar negeri.</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- SAMBUTAN KEPALA SEKOLAH --}}
    <section class="mb-12 animate-fade-in-up" style="animation-delay: .3s">
        <div class="glass rounded-[2.5rem] p-8 md:p-14 border border-slate-200/80 shadow-sm relative overflow-hidden">
            {{-- Decorative SVG pattern --}}
            <div class="absolute top-0 right-0 opacity-[0.04] pointer-events-none">
                <svg width="400" height="400" viewBox="0 0 400 400" fill="none"><circle cx="200" cy="200" r="200" stroke="currentColor" stroke-width="0.5" class="text-primary-600"/><circle cx="200" cy="200" r="160" stroke="currentColor" stroke-width="0.5" class="text-primary-600"/><circle cx="200" cy="200" r="120" stroke="currentColor" stroke-width="0.5" class="text-primary-600"/><circle cx="200" cy="200" r="80" stroke="currentColor" stroke-width="0.5" class="text-primary-600"/><circle cx="200" cy="200" r="40" stroke="currentColor" stroke-width="0.5" class="text-primary-600"/></svg>
            </div>
            {{-- Decorative quote SVG --}}
            <div class="absolute -top-2 -left-2 opacity-[0.06] pointer-events-none">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="currentColor" class="text-primary-600"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
            </div>

            <div class="relative grid md:grid-cols-5 gap-10 items-center">
                {{-- Avatar placeholder --}}
                <div class="md:col-span-2 flex justify-center">
                    <div class="relative">
                        <div class="w-52 h-52 md:w-60 md:h-60 rounded-[2rem] bg-gradient-to-br from-primary-100 to-emerald-100 border-2 border-white shadow-xl flex items-center justify-center">
                            <svg class="w-24 h-24 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        {{-- Decorative floating dot --}}
                        <div class="absolute -bottom-3 -right-3 w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-emerald-500 shadow-lg flex items-center justify-center animate-float">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="md:col-span-3">
                    <p class="text-primary-600 font-black tracking-[0.25em] uppercase text-[10px] mb-4 flex items-center">
                        <span class="w-8 h-px bg-primary-600 mr-3"></span>Sambutan Kepala Sekolah
                    </p>
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight mb-6">
                        Selamat Datang di<br>{{ school_short_name() }}
                    </h2>
                    <div class="space-y-4">
                        <p class="text-slate-600 text-base leading-relaxed font-medium">
                            <em class="text-slate-800 font-bold">Assalamu'alaikum Warahmatullahi Wabarakatuh,</em>
                        </p>
                        <p class="text-slate-600 text-base leading-relaxed font-medium">
                            Puji syukur kehadirat Allah SWT atas segala rahmat dan karunia-Nya. Kami menyambut dengan hangat seluruh pengunjung website resmi {{ school_name() }}. Sekolah kami senantiasa berkomitmen untuk memberikan pendidikan terbaik yang memadukan keunggulan akademik dengan pembinaan karakter Islami.
                        </p>
                        <p class="text-slate-600 text-base leading-relaxed font-medium">
                            Kami percaya bahwa setiap siswa memiliki potensi luar biasa yang perlu dikembangkan secara holistik. Melalui program pembelajaran yang inovatif, bimbingan yang penuh kasih sayang, dan lingkungan yang kondusif, kami berupaya mewujudkan generasi muda yang siap menghadapi tantangan masa depan.
                        </p>
                    </div>
                    <div class="mt-8 pt-6 border-t border-slate-200/60">
                        <p class="font-black text-slate-900 text-base">Kepala Sekolah</p>
                        <p class="text-slate-500 text-sm font-bold">{{ school_name() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
