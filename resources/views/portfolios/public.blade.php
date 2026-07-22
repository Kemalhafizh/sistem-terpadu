@extends('layouts.public')

@section('seo_meta')
    <title>e-Portfolio {{ $student->name }} — SMA Islam Sultan Agung 1</title>
    <meta name="description" content="Lembar portofolio digital prestasi resmi milik {{ $student->name }}, siswa aktif SMA Islam Sultan Agung 1 Semarang. Terverifikasi oleh sistem sekolah.">
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-6 space-y-12">
    
    {{-- Glassmorphic Student Profile Showcase --}}
    <div class="glass rounded-3xl p-8 flex flex-col md:flex-row items-center gap-8 shadow-xl animate-fade-in-up">
        {{-- Avatar/Initials --}}
        <div class="w-24 h-24 bg-gradient-to-tr from-primary-500 to-indigo-600 rounded-3xl flex items-center justify-center text-4xl font-black text-white shadow-lg animate-float">
            {{ strtoupper(substr($student->name, 0, 2)) }}
        </div>

        {{-- Profile Info --}}
        <div class="flex-grow text-center md:text-left space-y-2">
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                <span class="px-3 py-1 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 rounded-xl text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                    <span></span> Terverifikasi Sekolah
                </span>
                <span class="px-3 py-1 bg-primary-500/10 text-primary-700 dark:text-primary-400 border border-primary-500/20 rounded-xl text-[10px] font-black uppercase tracking-wider">
                    Siswa Aktif
                </span>
            </div>

            <h2 class="text-2xl md:text-3xl font-black text-slate-900 leading-tight">
                {{ $student->name }}
            </h2>

            <p class="text-sm font-semibold text-slate-500">
                NISN: {{ $student->nisn }} • Kelas: {{ $student->classroom->name ?? 'Wali Kelas' }} • SMA Islam Sultan Agung 1 Semarang
            </p>
        </div>
    </div>

    {{-- Grid Layout for Achievements & Badges --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Left Column: Glowing Badges Shelf --}}
        <div class="space-y-6 lg:col-span-1">
            <div class="glass rounded-3xl p-6 shadow-lg space-y-4">
                <span class="text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest block">Lencana Kehormatan</span>
                <h3 class="text-base font-black text-slate-900 mt-1">Sertifikasi & Lencana Emas</h3>
                
                <div class="space-y-4 pt-2">
                    @forelse($badges as $badge)
                        <div class="p-4 bg-white/40 rounded-2xl border border-slate-200/50 hover:shadow-md transition duration-300 flex items-start space-x-3"
                             style="border-left: 4px solid {{ $badge->color }};">
                            <span class="text-3xl shrink-0 pt-0.5">{{ $badge->icon }}</span>
                            <div class="space-y-1">
                                <span class="block text-sm font-black text-slate-900 leading-tight">{{ $badge->name }}</span>
                                <span class="block text-[8px] font-bold text-slate-400 uppercase">Dianugerahi: {{ \Carbon\Carbon::parse($badge->pivot->earned_at)->format('d M Y') }}</span>
                                @if($badge->pivot->notes)
                                    <p class="text-[10px] text-slate-500 leading-relaxed font-semibold italic bg-white/50 p-2 rounded-xl mt-1.5 border border-slate-100">
                                        "{{ $badge->pivot->notes }}"
                                    </p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-6 bg-white/20 border-2 border-dashed border-slate-200 rounded-2xl text-center">
                            <p class="text-xs font-semibold text-slate-400 leading-relaxed">
                                Belum ada lencana kehormatan resmi yang disematkan untuk siswa saat ini.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Column: Timeline of Verified Public Achievements --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="glass rounded-3xl p-6 md:p-8 shadow-lg">
                <span class="text-[9px] font-black text-primary-600 uppercase tracking-widest block mb-6">Peta Jalan & Bukti Pencapaian</span>
                
                <div class="relative pl-6 border-l-2 border-slate-200 space-y-8">
                    @forelse($portfolioItems as $item)
                        <div class="relative">
                            {{-- Dot Node --}}
                            <div class="absolute -left-[35px] w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-black shadow-md ring-4 ring-emerald-50">
                                
                            </div>

                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 border border-indigo-200 text-indigo-600 text-[8px] font-black uppercase tracking-wider">
                                        {{ $item->category }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-bold">
                                        {{ $item->date ? $item->date->format('d M Y') : 'Tanpa Tanggal' }}
                                    </span>
                                </div>

                                <h3 class="text-base font-black text-slate-900 leading-tight">
                                    {{ $item->title }}
                                </h3>

                                @if($item->description)
                                    <p class="text-xs text-slate-600 leading-relaxed font-medium">
                                        {{ $item->description }}
                                    </p>
                                @endif

                                {{-- External Link or Certificate Download buttons --}}
                                @if($item->file_path || $item->external_link)
                                    <div class="flex items-center space-x-2 pt-1.5">
                                        @if($item->file_path)
                                            <a href="{{ Storage::url($item->file_path) }}" target="_blank"
                                               class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-[9px] font-black uppercase tracking-wider transition active:scale-95 flex items-center space-x-1 shadow">
                                                <span> Buka Sertifikat</span>
                                            </a>
                                        @endif

                                        @if($item->external_link)
                                            <a href="{{ $item->external_link }}" target="_blank"
                                               class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 rounded-lg text-[9px] font-black uppercase tracking-wider transition active:scale-95 flex items-center space-x-1 shadow">
                                                <span> Sumber Karya ↗</span>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-4 border border-slate-100 mx-auto text-2xl">
                                
                            </div>
                            <h4 class="text-sm font-black text-slate-700">Portofolio Masih Kosong</h4>
                            <p class="text-xs text-slate-400 max-w-xs mx-auto leading-relaxed mt-1">
                                Siswa belum mengatur visibilitas prestasi publik atau belum ada dokumen prestasi yang disetujui pihak sekolah.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
