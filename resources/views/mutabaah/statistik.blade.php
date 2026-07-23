<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Analisis & Statistik Mutaba\'ah') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Visualisasi Kualitas Ibadah Kelas: {{ $classroom->name }}
                </p>
            </div>
            @if($classroom)
                <a href="{{ route('mutabaah.rekap-kelas', ['classroom' => $classroom->id, 'week_start' => $weekStart]) }}" 
                   class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold uppercase tracking-wider border border-gray-200 shadow-sm transition-all flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Rekap Kelas
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Class & Period Filters --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <form action="{{ route('mutabaah.statistik') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                    @if(count($classrooms) > 1)
                        <div class="w-full sm:w-48">
                            <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">Pilih Kelas</label>
                            <select name="classroom_id" onchange="this.form.submit()" 
                                    class="w-full border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl text-xs font-bold text-gray-700">
                                @foreach($classrooms as $c)
                                    <option value="{{ $c->id }}" {{ $classroom->id === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                    @endif
                    <div class="w-full sm:w-48">
                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">Mulai Minggu</label>
                        <input type="date" name="week_start" value="{{ $weekStart }}" onchange="this.form.submit()" 
                               class="w-full border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl text-xs font-bold text-gray-700">
                    </div>
                </form>
                
                <div class="flex items-center space-x-2 shrink-0">
                    @php
                        $carbonWeek = \Carbon\Carbon::parse($weekStart);
                        $prevWeek = $carbonWeek->copy()->subWeek()->format('Y-m-d');
                        $nextWeek = $carbonWeek->copy()->addWeek()->format('Y-m-d');
                    @endphp
                    <a href="{{ route('mutabaah.statistik', ['classroom_id' => $classroom->id, 'week_start' => $prevWeek]) }}" 
                       class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <a href="{{ route('mutabaah.statistik', ['classroom_id' => $classroom->id, 'week_start' => $nextWeek]) }}" 
                       class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            {{-- Main stats grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Left Panel: Charts --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Grade distribution chart --}}
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider mb-4">Distribusi Grade Pekan Ini</h3>
                        <div class="h-64 relative flex items-center justify-center">
                            @if($summaries->count() > 0)
                                <canvas id="gradeChart" class="max-w-[250px] max-h-[250px]"></canvas>
                            @else
                                <div class="text-center text-gray-400">
                                    <span class="text-3xl block mb-2"></span>
                                    <span>Belum ada data untuk direkap pekan ini.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Average score vs peers --}}
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider mb-4">Grafik Nilai Rata-rata Siswa</h3>
                        <div class="h-72 relative">
                            @if($summaries->count() > 0)
                                <canvas id="scoresBarChart"></canvas>
                            @else
                                <div class="text-center text-gray-400 py-12">
                                    <span>Belum ada data nilai pekan ini.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Right Panel: Leaderboard & Stats Summary --}}
                <div class="space-y-6">
                    
                    {{-- Quick Stat Summary Card --}}
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 space-y-4">
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider border-b border-gray-100 pb-3">Ringkasan Pekan Ini</h3>
                        <div class="space-y-3.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-gray-400 uppercase tracking-wider">Rata-rata Kelas</span>
                                <span class="text-gray-800 text-sm font-black">{{ round($avgClassScore, 1) }} / 100</span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-gray-400 uppercase tracking-wider">Total Siswa Terdata</span>
                                <span class="text-gray-800 text-sm font-black">{{ $summaries->count() }} / {{ count($classroom->students ?? []) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-gray-400 uppercase tracking-wider">Tingkat Ketuntasan (&ge;70)</span>
                                @php
                                    $tuntasCount = $summaries->where('total_score', '>=', 70)->count();
                                    $tuntasPercent = $summaries->count() > 0 ? round(($tuntasCount / $summaries->count()) * 100, 1) : 0;
                                @endphp
                                <span class="text-emerald-600 text-sm font-black">{{ $tuntasPercent }}%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Leaderboard (Istiqomah Top Performers) --}}
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100">
                            <h3 class="text-xs font-black text-gray-500 uppercase tracking-[0.2em]">Top 5 Siswa Istiqomah</h3>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($topPerformers as $index => $summary)
                                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50/30 transition-colors">
                                    <div class="flex items-center space-x-3">
                                        {{-- Rank Badge --}}
                                        <div class="w-6 h-6 rounded-full font-black text-xs flex items-center justify-center 
                                            {{ $index === 0 ? 'bg-amber-100 text-amber-800' : ($index === 1 ? 'bg-slate-100 text-slate-800' : ($index === 2 ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-650')) }}">
                                            {{ $index + 1 }}
                                        </div>
                                        <div>
                                            <div class="text-xs font-black text-gray-800">{{ $summary->student->user->name }}</div>
                                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Grade {{ $summary->grade }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-black text-primary-600">{{ $summary->total_score }} Poin</span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-xs font-bold text-gray-400 italic">
                                    Belum ada data prestasi.
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if($summaries->count() > 0)
                // 1. Doughnut Chart (Grade Distribution)
                const gradeCtx = document.getElementById('gradeChart').getContext('2d');
                new Chart(gradeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['A (Istimewa)', 'B (Baik)', 'C (Cukup)', 'D (Pembinaan)'],
                        datasets: [{
                            data: [
                                {{ $gradeDistribution->get('A', 0) }},
                                {{ $gradeDistribution->get('B', 0) }},
                                {{ $gradeDistribution->get('C', 0) }},
                                {{ $gradeDistribution->get('D', 0) }}
                            ],
                            backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#ef4444'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    boxWidth: 12
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });

                // 2. Bar Chart (Scores comparison)
                const scoresCtx = document.getElementById('scoresBarChart').getContext('2d');
                new Chart(scoresCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($summaries->map(fn($s) => substr($s->student->user->name, 0, 12) . '...')->toArray()) !!},
                        datasets: [{
                            label: 'Rata-rata Skor',
                            data: {!! json_encode($summaries->pluck('total_score')->toArray()) !!},
                            backgroundColor: '#3b82f6',
                            borderRadius: 8,
                            barThickness: 16
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                min: 0,
                                max: 100,
                                grid: {
                                    color: '#f3f4f6'
                                },
                                ticks: {
                                    font: {
                                        weight: 'bold'
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        weight: 'bold',
                                        size: 9
                                    }
                                }
                            }
                        }
                    }
                });
            @endif
        });
    </script>
    @endpush
</x-app-layout>
