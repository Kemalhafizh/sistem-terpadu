<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('elearning.class.progress', $elearningClass->uuid) }}" class="text-gray-400 hover:text-emerald-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                Detail Kemajuan: {{ $student->user?->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in">

            <!-- Back Link -->
            <div>
                <a href="{{ route('elearning.class.progress', $elearningClass->uuid) }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span>Kembali ke Progress Kelas</span>
                </a>
            </div>

            <!-- Student Profile Summary -->
            <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 sm:p-8 border border-gray-100 dark:border-slate-700 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 rounded-2xl flex items-center justify-center font-black text-lg uppercase shrink-0">
                        {{ substr($student->user?->name ?? 'S', 0, 1) }}
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base sm:text-lg font-extrabold text-gray-900 dark:text-white leading-tight">{{ $student->user?->name }}</h3>
                        <p class="text-xs text-gray-400 dark:text-slate-500 font-bold">
                            NIS: {{ $student->nis }} <span class="mx-1.5">|</span> Kelas Fisik: {{ $student->classroom?->name }}
                        </p>
                    </div>
                </div>

                <!-- Progress completion circle/bar -->
                <div class="space-y-2 max-w-xs w-full">
                    <div class="flex justify-between text-xs font-bold text-gray-700 dark:text-slate-300">
                        <span>Ketuntasan Belajar</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">{{ $progressData['completion_percentage'] }}%</span>
                    </div>
                    <div class="w-full h-2 bg-gray-100 dark:bg-slate-900 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $progressData['completion_percentage'] }}%"></div>
                    </div>
                    <div class="flex justify-between text-[9px] text-gray-400 font-bold uppercase tracking-wider">
                        <span>{{ $progressData['read_materials'] }} / {{ $progressData['total_materials'] }} Materi</span>
                        <span>{{ $progressData['submitted_assignments'] }} / {{ $progressData['total_assignments'] }} Tugas</span>
                    </div>
                </div>
            </div>

            <!-- Progress Checklist details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Materials Checklist Column -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm space-y-4">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <span>Checklist Materi Pembelajaran</span>
                    </h4>
                    
                    @if(count($progressData['materials']) === 0)
                        <div class="text-center py-6 text-gray-400 text-xs font-bold">
                            Belum ada materi dibagikan.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($progressData['materials'] as $item)
                                @php
                                    $m = $item['material'];
                                @endphp
                                <div class="p-4 bg-gray-50/50 dark:bg-slate-900/40 rounded-2xl border border-gray-100 dark:border-slate-800/80 flex justify-between items-center gap-4">
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold text-gray-900 dark:text-white leading-tight line-clamp-1">{{ $m->title }}</div>
                                        <div class="text-[9px] text-gray-400 font-bold">
                                            @if($item['is_viewed'])
                                                Dibaca: {{ \Carbon\Carbon::parse($item['viewed_at'])->format('d M Y, H:i') }}
                                            @else
                                                Belum dibaca / dibuka
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        @if($item['is_viewed'])
                                            <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-lg text-[9px] font-black uppercase tracking-wider border border-emerald-500/20">Selesai</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-lg text-[9px] font-black uppercase tracking-wider border border-amber-500/20">Belum</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Assignments Checklist Column -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm space-y-4">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <span>Checklist Tugas & Ujian</span>
                    </h4>

                    @if(count($progressData['assignments']) === 0)
                        <div class="text-center py-6 text-gray-400 text-xs font-bold">
                            Belum ada tugas/kuis diterbitkan.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($progressData['assignments'] as $item)
                                @php
                                    $a = $item['assignment'];
                                @endphp
                                <div class="p-4 bg-gray-50/50 dark:bg-slate-900/40 rounded-2xl border border-gray-100 dark:border-slate-800/80 space-y-3">
                                    <div class="flex justify-between items-start gap-4">
                                        <div class="space-y-1">
                                            <div class="text-xs font-bold text-gray-900 dark:text-white leading-tight line-clamp-1">{{ $a->title }}</div>
                                            <div class="text-[9px] text-gray-400 font-bold">
                                                Deadline: {{ \Carbon\Carbon::parse($a->due_at)->format('d M Y, H:i') }}
                                            </div>
                                        </div>
                                        <div>
                                            @if($item['is_submitted'])
                                                <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-lg text-[9px] font-black uppercase tracking-wider border border-emerald-500/20">Diserahkan</span>
                                            @else
                                                <span class="px-2.5 py-1 bg-red-500/10 text-red-600 dark:text-red-400 rounded-lg text-[9px] font-black uppercase tracking-wider border border-red-500/20">Belum</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if($item['is_submitted'])
                                        <div class="pt-3 border-t border-gray-200/50 dark:border-slate-800 text-[10px] space-y-1 font-semibold text-slate-500 dark:text-slate-400">
                                            <div class="flex justify-between">
                                                <span>Tanggal Kumpul:</span>
                                                <span class="text-slate-800 dark:text-white font-bold">{{ \Carbon\Carbon::parse($item['submitted_at'])->format('d M Y, H:i') }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Nilai:</span>
                                                @if(!is_null($item['score']))
                                                    <span class="text-emerald-600 dark:text-emerald-400 font-extrabold text-xs">{{ $item['score'] }}</span>
                                                @else
                                                    <span class="text-slate-400 font-bold">Belum Dinilai</span>
                                                @endif
                                            </div>
                                            @if($item['feedback'])
                                                <div class="mt-1.5 p-2 bg-white dark:bg-slate-950 rounded-lg border border-gray-100 dark:border-slate-800 text-[9px]">
                                                    <span class="font-bold text-slate-700 dark:text-slate-300">Catatan Guru:</span> {{ $item['feedback'] }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
