<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Analisis Mutaba\'ah Pribadi') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Progress Grafik Ibadah Harian: {{ $student->user->name }}
                </p>
            </div>
            <a href="{{ route('mutabaah.index', ['student_id' => $student->id]) }}" 
               class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold uppercase tracking-wider border border-gray-200 shadow-sm transition-all flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Jurnal
            </a>
        </div>
    </x-slot>

    @php
        $avgScore = $scoreTrend->avg('score') ?? 0;
        $maxScore = $scoreTrend->max('score') ?? 0;
        $minScore = $scoreTrend->min('score') ?? 0;
    @endphp

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Summary Metrics --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm text-center">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block leading-none">Rata-rata Skor</span>
                    <span class="text-2xl font-black text-emerald-600 mt-2 block leading-none">{{ round($avgScore, 1) }}</span>
                </div>
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm text-center">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block leading-none">Skor Tertinggi</span>
                    <span class="text-2xl font-black text-primary-650 mt-2 block leading-none">{{ $maxScore }}</span>
                </div>
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm text-center">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block leading-none">Skor Terendah</span>
                    <span class="text-2xl font-black text-rose-600 mt-2 block leading-none">{{ $minScore }}</span>
                </div>
            </div>

            {{-- Line Chart Card --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"></span>
                    Tren Grafik Skor Harian (15 Input Terakhir)
                </h3>
                <div class="h-80 relative">
                    @if($scoreTrend->count() > 0)
                        <canvas id="trendChart"></canvas>
                    @else
                        <div class="text-center text-gray-400 py-16">
                            <span class="text-3xl block mb-2"></span>
                            <span>Belum ada data jurnal ibadah yang cukup untuk membuat grafik tren.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Spiritual Encouragement Message --}}
            <div class="bg-emerald-50/50 border border-emerald-100 rounded-3xl p-6 shadow-sm">
                <h4 class="text-sm font-black text-emerald-950 flex items-center gap-1.5">
                    <span></span> Pesan Motivasi Ruhiyah
                </h4>
                @if($avgScore >= 85)
                    <p class="text-xs text-emerald-800 mt-2 leading-relaxed font-bold">
                        Masya Allah! Istiqomah ibadah Anda berada di level istimewa. Pertahankan amalan berjamaah Anda dan jadilah teladan kebaikan di lingkungan {{ school_name() }}. Semoga menjadi pemberat amal kebaikan kelak. Amin.
                    </p>
                @elseif($avgScore >= 70)
                    <p class="text-xs text-emerald-800 mt-2 leading-relaxed font-bold">
                        Alhamdulillah, grafik ibadah harian Anda terbilang baik. Cobalah untuk lebih merutinkan amalan sunnah seperti dhuha dan tadarus setiap hari untuk meraih pahala dan keberkahan ekstra.
                    </p>
                @else
                    <p class="text-xs text-rose-800 mt-2 leading-relaxed font-bold">
                        Semangat! Terus tingkatkan kedisiplinan beribadah harian Anda. Mulailah dengan selalu menjaga shalat 5 waktu secara berjamaah. Jangan lupa untuk mengisi jurnal mutaba'ah setiap hari.
                    </p>
                @endif
            </div>

        </div>
    </div>

    @push('scripts')
    <x-vendor.script lib="chartjs" />
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if($scoreTrend->count() > 0)
                const ctx = document.getElementById('trendChart').getContext('2d');
                
                const dates = {!! json_encode($scoreTrend->map(fn($t) => $t->date->translatedFormat('d M'))->toArray()) !!};
                const scores = {!! json_encode($scoreTrend->pluck('score')->toArray()) !!};
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [{
                            label: 'Skor Mutabaah',
                            data: scores,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#10b981',
                            pointHoverRadius: 6,
                            pointRadius: 4
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
                                        size: 10
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
