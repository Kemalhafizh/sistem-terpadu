@extends('layouts.public')

@section('seo_meta')
    <title>Direktori Guru & Staff - SMA Islam Sultan Agung 1 Semarang</title>
    <meta name="description" content="Direktori lengkap guru pengajar, tenaga kependidikan, dan staff SMA Islam Sultan Agung 1 Semarang.">
@endsection

@section('content')
<div class="w-full px-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs font-bold text-slate-400 mb-10 animate-fade-in-up">
        <a href="{{ route('home') }}" class="hover:text-primary-600 transition-colors">Beranda</a>
        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <span class="text-slate-600">Direktori Guru & Staff</span>
    </nav>

    {{-- Hero Header --}}
    <div class="text-center max-w-3xl mx-auto mb-16 animate-fade-in-up">
        <div class="inline-flex items-center px-5 py-2 rounded-full bg-gradient-to-r from-primary-50 to-emerald-50 border border-primary-200/60 mb-6">
            <svg class="w-4 h-4 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span class="text-[10px] font-black text-primary-700 uppercase tracking-[0.2em]">Tim Kami</span>
        </div>
        <h2 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-6">Direktori Guru & Tenaga Kependidikan</h2>
        <p class="text-slate-500 text-lg font-medium leading-relaxed">Para pendidik dan tenaga kependidikan yang berdedikasi dalam membentuk generasi unggul di SMA Islam Sultan Agung 1 Semarang.</p>
    </div>

    @php
        $roleLabels = [
            'guru' => 'Guru Pengajar',
            'staf_tu' => 'Staf Tata Usaha',
            'bk' => 'Guru BK',
            'waka_kesiswaan' => 'Waka Kesiswaan',
            'waka_kurikulum' => 'Waka Kurikulum',
            'waka_sarpras' => 'Waka Sarpras',
            'waka_humas' => 'Waka Humas',
            'kepala_sekolah' => 'Kepala Sekolah',
        ];

        $roleColors = [
            'kepala_sekolah' => 'from-amber-500 to-yellow-500 text-white',
            'waka_kesiswaan' => 'from-primary-500 to-emerald-500 text-white',
            'waka_kurikulum' => 'from-blue-500 to-indigo-500 text-white',
            'waka_sarpras' => 'from-violet-500 to-purple-500 text-white',
            'waka_humas' => 'from-rose-500 to-pink-500 text-white',
            'bk' => 'from-cyan-500 to-teal-500 text-white',
            'guru' => 'from-slate-100 to-slate-200 text-slate-700',
            'staf_tu' => 'from-slate-100 to-slate-200 text-slate-700',
        ];

        $avatarGradients = [
            'from-primary-400 to-emerald-500',
            'from-blue-400 to-indigo-500',
            'from-violet-400 to-purple-500',
            'from-rose-400 to-pink-500',
            'from-amber-400 to-orange-500',
            'from-cyan-400 to-teal-500',
            'from-emerald-400 to-green-500',
            'from-fuchsia-400 to-pink-500',
        ];
    @endphp

    {{-- Staff Grid --}}
    @if(isset($staff) && $staff->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8">
            @foreach($staff as $index => $member)
                @php
                    $gradient = $avatarGradients[$index % count($avatarGradients)];
                    $roleKey = $member->role ?? 'guru';
                    $roleLabel = $roleLabels[$roleKey] ?? ucfirst(str_replace('_', ' ', $roleKey));
                    $roleColor = $roleColors[$roleKey] ?? 'from-slate-100 to-slate-200 text-slate-700';
                @endphp
                <div class="glass rounded-[2rem] p-8 border border-slate-200/60 shadow-sm hover:shadow-xl hover:scale-105 transition-all duration-300 text-center group animate-fade-in-up" style="animation-delay: {{ ($index % 12) * 80 }}ms;">

                    {{-- Avatar --}}
                    <div class="relative mx-auto mb-6 w-24 h-24">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br {{ $gradient }} flex items-center justify-center ring-4 ring-white shadow-lg group-hover:ring-primary-100 transition-all">
                            <span class="text-white font-black text-2xl tracking-tight select-none">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </span>
                        </div>
                        {{-- Online-style accent dot --}}
                        <div class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-white shadow flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"></path><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                        </div>
                    </div>

                    {{-- Name --}}
                    <h3 class="font-black text-slate-900 text-base mb-2 leading-snug">{{ $member->name }}</h3>

                    {{-- Role Badge --}}
                    <span class="inline-block px-4 py-1.5 rounded-full bg-gradient-to-r {{ $roleColor }} text-[10px] font-black uppercase tracking-widest shadow-sm">
                        {{ $roleLabel }}
                    </span>

                    {{-- Email (subtle) --}}
                    @if($member->email)
                        <p class="mt-4 text-slate-400 text-xs font-semibold truncate">{{ $member->email }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="max-w-2xl mx-auto animate-fade-in-up">
            <div class="glass rounded-[2.5rem] p-16 text-center border border-slate-200/80">
                <div class="w-20 h-20 mx-auto mb-6 rounded-[1.5rem] bg-gradient-to-br from-primary-50 to-emerald-50 border border-primary-200/40 flex items-center justify-center">
                    <svg class="w-10 h-10 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <p class="text-slate-600 font-bold text-lg mb-2">Direktori belum tersedia.</p>
                <p class="text-slate-400 font-medium text-sm">Data guru dan tenaga kependidikan sedang dalam proses pengunggahan.</p>
            </div>
        </div>
    @endif

</div>
@endsection
