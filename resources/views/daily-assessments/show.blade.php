<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
                {{ __('Detail Penilaian Harian') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('daily-assessments.pdf', $assessment->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl font-bold text-xs text-gray-700 uppercase tracking-widest bg-white hover:bg-gray-50 shadow-sm transition">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Unduh PDF
                </a>
                <a href="{{ route('daily-assessments.input-scores', $assessment->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-800 transition shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Semua Nilai
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Main Split Layout: Info Details & Statistics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Info Card -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-700 text-lg border-b border-gray-100 pb-2">Informasi Penilaian</h3>
                    
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-gray-400 block text-xs font-semibold uppercase tracking-wider">Judul Penilaian</span>
                            <span class="font-bold text-gray-800">{{ $assessment->title }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs font-semibold uppercase tracking-wider">Mata Pelajaran</span>
                            <span class="font-bold text-gray-800">{{ $assessment->subject->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs font-semibold uppercase tracking-wider">Kelas</span>
                            <span class="font-bold text-gray-800">{{ $assessment->classroom->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs font-semibold uppercase tracking-wider">Tipe / Tanggal</span>
                            <span class="font-bold text-gray-800 capitalize">{{ str_replace('_', ' ', $assessment->type) }} — {{ $assessment->assessment_date->isoFormat('D MMMM YYYY') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-xs font-semibold uppercase tracking-wider">Nilai KKM (Passing Grade)</span>
                            <span class="font-bold text-primary-600 text-base">{{ number_format($kkm, 0) }}</span>
                        </div>
                        @if($assessment->description)
                            <div>
                                <span class="text-gray-400 block text-xs font-semibold uppercase tracking-wider">Deskripsi / Materi</span>
                                <span class="text-gray-600 block leading-relaxed">{{ $assessment->description }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Stats Cards & Chart -->
                <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-6">
                    <div class="flex-grow space-y-4 justify-between flex flex-col">
                        <h3 class="font-bold text-gray-700 text-lg border-b border-gray-100 pb-2">Metrik Kelulusan Kelas</h3>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-gray-50 p-4 rounded-xl text-center">
                                <span class="text-xs text-gray-400 block font-semibold">Rata-rata</span>
                                <span class="text-2xl font-black text-gray-800">{{ $average }}</span>
                            </div>
                            <div class="bg-emerald-50 p-4 rounded-xl text-center border border-emerald-100">
                                <span class="text-xs text-emerald-600 block font-semibold">Lulus KKM</span>
                                <span class="text-2xl font-black text-emerald-700">{{ $passedCount }}</span>
                            </div>
                            <div class="bg-red-50 p-4 rounded-xl text-center border border-red-100">
                                <span class="text-xs text-red-600 block font-semibold">Remedial</span>
                                <span class="text-2xl font-black text-red-700">{{ $remedialCount }}</span>
                            </div>
                        </div>

                        <div class="text-xs text-gray-400 bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <b>Info Kurikulum:</b> Siswa yang belum mencapai nilai KKM ({{ number_format($kkm, 0) }}) didaftarkan secara otomatis pada antrean program remedial.
                        </div>
                    </div>

                    <div class="w-full md:w-64 flex flex-col items-center justify-center border-t md:border-t-0 md:border-l border-gray-100 pt-4 md:pt-0 md:pl-6">
                        <div id="pass-ratio-chart" class="w-full"></div>
                    </div>
                </div>
            </div>

            <!-- Student Scores Table -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-bold text-gray-700">Daftar Hasil Belajar Siswa</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider w-16">No</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Siswa</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-28">Nilai Awal</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-40">Status Remedial</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-32">Nilai Perbaikan</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-primary-600 uppercase tracking-wider w-32">Nilai Akhir</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Catatan</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($scores as $index => $score)
                                @php
                                    $finalScore = $score->final_score;
                                    $passed = $score->is_passed;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium text-center bg-gray-50 border-r border-gray-100">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $score->student->name }}</div>
                                        <div class="text-xs text-gray-400">NIS: {{ $score->student->nis }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold {{ $score->score !== null && $score->score < $kkm ? 'text-red-600' : 'text-gray-700' }}">
                                        {{ $score->score !== null ? number_format($score->score, 0) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($score->remedial_status === 'not_needed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                Lulus Awal
                                            </span>
                                        @elseif($score->remedial_status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100">
                                                Pending Remedial
                                            </span>
                                        @elseif($score->remedial_status === 'completed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                                Remedial Selesai
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-700">
                                        {{ $score->remedial_score !== null ? number_format($score->remedial_score, 0) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($finalScore !== null)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black {{ $passed ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                                {{ number_format($finalScore, 0) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 font-bold">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $score->notes }}">
                                        {{ $score->notes ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        @if($score->remedial_status === 'pending')
                                            <a href="{{ route('daily-assessments.input-remedial', $score->id) }}" class="inline-flex items-center px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold rounded-xl hover:bg-amber-100 transition duration-150">
                                                Beri Remedial
                                            </a>
                                        @else
                                            <span class="text-gray-300 text-xs font-semibold">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">
                                        Tidak ada siswa terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- ApexCharts JS -->
    <x-vendor.script lib="apexcharts" />
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passed = {{ $passedCount }};
            const remedial = {{ $remedialCount }};
            
            const options = {
                chart: {
                    type: 'donut',
                    height: 200
                },
                series: [passed, remedial],
                labels: ['Lulus KKM', 'Butuh Remedial'],
                colors: ['#10b981', '#ef4444'],
                legend: {
                    show: false
                },
                dataLabels: {
                    enabled: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Rasio',
                                    formatter: function (w) {
                                        const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        if (sum === 0) return '0%';
                                        return Math.round((passed / sum) * 100) + '%';
                                    }
                                }
                            }
                        }
                    }
                }
            };

            if (passed > 0 || remedial > 0) {
                const chart = new ApexCharts(document.querySelector("#pass-ratio-chart"), options);
                chart.render();
            } else {
                document.querySelector("#pass-ratio-chart").innerHTML = '<div class="text-sm text-gray-400 font-bold">Belum ada data nilai</div>';
            }
        });
    </script>
</x-app-layout>
