<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Rekap Mutaba\'ah Mingguan') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Riwayat Mingguan: {{ $student->user->name }}
                </p>
            </div>
            <a href="{{ route('mutabaah.index', ['student_id' => $student->id]) }}" 
               class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold uppercase tracking-wider border border-gray-200 shadow-sm transition-all flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Jurnal
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-5 bg-gray-50/50 border-b border-gray-100">
                    <h3 class="text-xs font-black text-gray-500 uppercase tracking-[0.2em]">Daftar Rekap Mingguan</h3>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($summaries as $summary)
                        @php
                            $gradeColors = [
                                'A' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'B' => 'bg-amber-50 text-amber-700 border-amber-100',
                                'C' => 'bg-orange-50 text-orange-700 border-orange-100',
                                'D' => 'bg-rose-50 text-rose-700 border-rose-100'
                            ];
                            $gradeColor = $gradeColors[$summary->grade] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                            
                            $gradeLabels = [
                                'A' => 'Istimewa',
                                'B' => 'Baik',
                                'C' => 'Cukup',
                                'D' => 'Perlu Pembinaan'
                            ];
                        @endphp
                        <div class="p-6 md:p-8 hover:bg-gray-50/40 transition-colors">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                                
                                {{-- Week & General Score info --}}
                                <div class="flex items-start space-x-4">
                                    <div class="px-4 py-3 rounded-2xl bg-primary-50 text-primary-700 border border-primary-100 text-center min-w-[80px]">
                                        <span class="text-[9px] font-bold uppercase tracking-wider block leading-none">Rata Skor</span>
                                        <span class="text-2xl font-black mt-1 block leading-none">{{ $summary->total_score }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-gray-800 tracking-tight">
                                            Minggu: {{ $summary->week_start->translatedFormat('d M') }} - {{ $summary->week_end->translatedFormat('d M Y') }}
                                        </h4>
                                        <div class="flex items-center gap-2 mt-1.5">
                                            <span class="px-2.5 py-0.5 rounded-lg border text-[10px] font-bold uppercase tracking-wider {{ $gradeColor }}">
                                                Grade {{ $summary->grade }}: {{ $gradeLabels[$summary->grade] ?? '-' }}
                                            </span>
                                            @if($summary->parent_acknowledged)
                                                <span class="px-2 py-0.5 rounded-lg bg-emerald-100 border border-emerald-200 text-emerald-800 text-[9px] font-black uppercase tracking-wider flex items-center gap-0.5">
                                                     Ditandatangani Ortu
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-lg bg-gray-100 border border-gray-200 text-gray-500 text-[9px] font-black uppercase tracking-wider">
                                                    Belum Ditandatangani
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Specific Counts (Prayers, Tadarus, etc) --}}
                                <div class="grid grid-cols-3 gap-4 bg-gray-50/50 p-4 rounded-2xl border border-gray-100 max-w-md w-full lg:w-auto">
                                    <div class="text-center">
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block leading-none">Full Shalat Wajib</span>
                                        <span class="text-sm font-black text-gray-700 mt-1 block">{{ $summary->sholat_5_waktu_count }} Hari</span>
                                    </div>
                                    <div class="text-center border-x border-gray-200/60 px-3">
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block leading-none">Shalat Berjamaah</span>
                                        <span class="text-sm font-black text-gray-700 mt-1 block">{{ $summary->sholat_berjamaah_count }} Kali</span>
                                    </div>
                                    <div class="text-center">
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block leading-none">Total Tadarus</span>
                                        <span class="text-sm font-black text-gray-700 mt-1 block">{{ $summary->tadarus_total_pages }} Hal</span>
                                    </div>
                                </div>

                                {{-- Action / Signature Area --}}
                                <div class="flex items-center justify-end gap-3 lg:self-center">
                                    @if(auth()->user()->role === 'parent' && $student->parent_id === auth()->user()->id && !$summary->parent_acknowledged)
                                        <form action="{{ route('mutabaah.parent-acknowledge', $summary->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-emerald-900/10 hover:scale-[1.02] transition-all">
                                                Tandatangani Laporan
                                            </button>
                                        </form>
                                    @endif
                                </div>

                            </div>

                            {{-- Wali Kelas Comments --}}
                            <div class="mt-4 p-4 bg-indigo-50/20 border border-indigo-100/30 rounded-2xl">
                                <span class="text-[9px] font-black text-indigo-500 uppercase tracking-wider block">Catatan Wali Kelas</span>
                                @if($summary->wali_kelas_comment)
                                    <p class="text-xs font-bold text-gray-700 mt-1">
                                        "{{ $summary->wali_kelas_comment }}"
                                    </p>
                                @else
                                    <p class="text-xs font-bold text-gray-400 mt-1 italic">
                                        Belum ada komentar dari wali kelas.
                                    </p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400 mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <p class="text-sm font-bold">Belum ada rangkuman rekap mingguan yang dibuat.</p>
                        </div>
                    @endforelse
                </div>

                @if($summaries->hasPages())
                    <div class="px-8 py-4 bg-gray-50/50 border-t border-gray-100">
                        {{ $summaries->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
