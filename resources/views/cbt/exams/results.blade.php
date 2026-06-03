<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">Hasil Ujian: {{ $exam->title }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $exam->subject?->name }} — {{ $exam->classroom?->name ?? 'Semua Kelas' }}</p>
            </div>
            <a href="{{ route('cbt.exams.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border border-slate-200 dark:border-slate-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">

        {{-- Stats KPI --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Rata-rata Nilai</p>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white mt-1">{{ $stats['average'] }}</h3>
                <p class="text-[10px] text-slate-400 mt-1">dari {{ $stats['total_students'] }} peserta</p>
            </div>
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nilai Tertinggi</p>
                <h3 class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['highest'] }}</h3>
            </div>
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nilai Terendah</p>
                <h3 class="text-3xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ $stats['lowest'] }}</h3>
            </div>
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tingkat Kelulusan</p>
                <h3 class="text-3xl font-black mt-1 {{ $stats['pass_rate'] >= 70 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">{{ $stats['pass_rate'] }}%</h3>
                <p class="text-[10px] text-slate-400 mt-1">{{ $stats['passed_count'] }} lulus (KKM {{ $exam->passing_score }})</p>
            </div>
        </div>

        {{-- Score Distribution Visual --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm">
            <h4 class="text-sm font-black text-slate-800 dark:text-white mb-4">Distribusi Nilai</h4>
            @php
                $ranges = ['0-20' => 0, '21-40' => 0, '41-60' => 0, '61-80' => 0, '81-100' => 0];
                $allAttempts = $attempts instanceof \Illuminate\Pagination\LengthAwarePaginator ? $attempts->items() : $attempts;
                foreach($allAttempts as $a) {
                    $s = $a->score ?? 0;
                    if ($s <= 20) $ranges['0-20']++;
                    elseif ($s <= 40) $ranges['21-40']++;
                    elseif ($s <= 60) $ranges['41-60']++;
                    elseif ($s <= 80) $ranges['61-80']++;
                    else $ranges['81-100']++;
                }
                $maxCount = max(array_values($ranges)) ?: 1;
                $barColors = ['0-20' => 'bg-red-500', '21-40' => 'bg-orange-500', '41-60' => 'bg-amber-500', '61-80' => 'bg-blue-500', '81-100' => 'bg-emerald-500'];
            @endphp
            <div class="space-y-3">
                @foreach($ranges as $range => $count)
                    <div class="flex items-center gap-3">
                        <span class="w-16 text-xs font-bold text-slate-500 dark:text-slate-400 text-right shrink-0">{{ $range }}</span>
                        <div class="flex-1 h-6 bg-slate-100 dark:bg-slate-800 rounded-lg overflow-hidden">
                            <div class="{{ $barColors[$range] }} h-full rounded-lg transition-all duration-500 flex items-center justify-end pr-2" style="width: {{ ($count / $maxCount) * 100 }}%; min-width: {{ $count > 0 ? '2rem' : '0' }}">
                                @if($count > 0)<span class="text-[10px] font-black text-white">{{ $count }}</span>@endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Results Table --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50/60 dark:bg-slate-950/40 border-b border-gray-100 dark:border-slate-800/80 text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <th class="px-6 py-4">#</th>
                            <th class="px-6 py-4">Nama Siswa</th>
                            <th class="px-6 py-4">NIS</th>
                            <th class="px-6 py-4 text-center">Nilai</th>
                            <th class="px-6 py-4 text-center">Benar</th>
                            <th class="px-6 py-4 text-center">Salah</th>
                            <th class="px-6 py-4 text-center">Kosong</th>
                            <th class="px-6 py-4 text-center">Pelanggaran</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Waktu Submit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800/60 text-xs font-semibold text-gray-700 dark:text-slate-300">
                        @forelse($attempts as $i => $a)
                            <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-950/10 transition-colors">
                                <td class="px-6 py-4 text-slate-400">{{ $attempts->firstItem() + $i }}</td>
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $a->student?->user?->name ?? 'Siswa' }}</td>
                                <td class="px-6 py-4">{{ $a->student?->nis ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-black text-lg {{ ($a->score ?? 0) >= $exam->passing_score ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $a->score ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 text-center text-emerald-600 font-bold">{{ $a->correct_count }}</td>
                                <td class="px-6 py-4 text-center text-rose-600 font-bold">{{ $a->wrong_count }}</td>
                                <td class="px-6 py-4 text-center text-slate-400 font-bold">{{ $a->unanswered_count }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($a->violation_count > 0)
                                        <span class="px-2 py-1 rounded-lg text-[10px] font-black border {{ $a->violation_count >= $exam->max_violations ? 'bg-red-500/15 text-red-600 border-red-500/30 animate-pulse' : 'bg-amber-500/10 text-amber-600 border-amber-500/20' }}">{{ $a->violation_count }}x</span>
                                    @else
                                        <span class="text-emerald-500 text-[10px] font-bold">Bersih</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $sColor = match($a->status) {
                                            'graded' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                                            'submitted' => 'bg-blue-500/10 text-blue-600 border-blue-500/20',
                                            'blocked_violation' => 'bg-red-500/15 text-red-600 border-red-500/30',
                                            'timeout' => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                                            'in_progress' => 'bg-purple-500/10 text-purple-600 border-purple-500/20',
                                            default => 'bg-slate-500/10 text-slate-600 border-slate-500/20'
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $sColor }}">{{ str_replace('_', ' ', $a->status) }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-400">{{ $a->submitted_at?->format('H:i, d M') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center text-slate-400 italic">
                                    <span class="font-bold">Belum ada siswa yang mengerjakan ujian ini.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($attempts->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-800">{{ $attempts->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
