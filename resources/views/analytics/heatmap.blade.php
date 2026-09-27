<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-end">
            <div>
                <a href="{{ route('analytics.index') }}" class="text-sm font-bold text-gray-500 hover:text-primary-600 flex items-center mb-1 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Dashboard Utama
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center" aria-hidden="true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </span>
                    {{ __('Heatmap Matriks Kehadiran') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Persentase siswa tidak hadir (sakit, izin, alpha) per kelas dari presensi harian wali kelas.</p>
            </div>
            <nav class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-800 p-1" aria-label="Rentang hari">
                @foreach ([7, 14, 30] as $option)
                    <a href="{{ route('analytics.heatmap', ['days' => $option]) }}"
                       @if ($option === $days) aria-current="page" @endif
                       class="px-4 py-2 rounded-lg text-sm font-bold transition-colors {{ $option === $days ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200' }}">
                        {{ $option }} hari sekolah
                    </a>
                @endforeach
            </nav>
        </div>
    </x-slot>

    @php
        $summary = $heatmap['summary'];
        $toneOf = fn (?float $rate) => match (true) {
            $rate === null => 'text-gray-400',
            $rate <= 5 => 'text-emerald-600 dark:text-emerald-400',
            $rate <= 15 => 'text-amber-600 dark:text-amber-400',
            default => 'text-rose-600 dark:text-rose-400',
        };
        $dateLabels = collect($heatmap['dates'])
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('D d/m'))
            ->all();
        $ranked = collect($heatmap['rows'])->filter(fn ($r) => $r['average'] !== null)->sortByDesc('average')->values();
    @endphp

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            @if ($heatmap['dates'] === [])
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-10 text-center border border-gray-100 dark:border-gray-700">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">Belum ada presensi harian yang tercatat</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Heatmap akan terisi otomatis setelah wali kelas mengisi presensi harian.</p>
                    <a href="{{ route('attendances.index') }}" class="inline-flex mt-5 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold">Buka Presensi</a>
                </div>
            @else
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hari sekolah</p>
                        <p class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $summary['school_days'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $dateLabels[0] }} – {{ end($dateLabels) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rata-rata tidak hadir</p>
                        <p class="text-3xl font-black mt-1 {{ $toneOf($summary['average_rate']) }}">{{ $summary['average_rate'] !== null ? number_format($summary['average_rate'], 1, ',', '.').'%' : '—' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">seluruh kelas</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Perlu perhatian</p>
                        @if ($summary['worst'])
                            <p class="text-xl font-black text-gray-900 dark:text-white mt-1 truncate">{{ $summary['worst']['name'] }}</p>
                            <p class="text-xs mt-1 font-bold {{ $toneOf($summary['worst']['rate']) }}">{{ number_format($summary['worst']['rate'], 1, ',', '.') }}% tidak hadir</p>
                        @else
                            <p class="text-xl font-black text-gray-400 mt-1">—</p>
                        @endif
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Belum diabsen</p>
                        <p class="text-3xl font-black mt-1 {{ $summary['missing_cells'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $summary['missing_cells'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">kelas × hari tanpa presensi</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                        <h3 class="text-lg font-black text-gray-900 dark:text-white">Heatmap kelas × hari</h3>
                        <ul class="flex flex-wrap gap-3 text-xs font-bold text-gray-600 dark:text-gray-300" aria-label="Keterangan warna">
                            <li class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-500"></span>≤ 5%</li>
                            <li class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-amber-500"></span>5–15%</li>
                            <li class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-rose-500"></span>&gt; 15%</li>
                            <li class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-300 dark:bg-gray-600"></span>Belum diabsen</li>
                        </ul>
                    </div>
                    <div class="overflow-x-auto">
                        <div id="attendanceHeatmapChart" style="min-width: {{ max(560, count($heatmap['dates']) * 56) }}px" role="img"
                             aria-label="Heatmap persentase ketidakhadiran per kelas selama {{ $summary['school_days'] }} hari sekolah terakhir. Rinciannya ada di tabel di bawah."></div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <h3 class="px-6 pt-6 text-lg font-black text-gray-900 dark:text-white">Peringkat kelas</h3>
                    <p class="px-6 text-sm text-gray-500 dark:text-gray-400">Diurutkan dari persentase tidak hadir tertinggi.</p>
                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Kelas</th>
                                    <th scope="col" class="px-6 py-3 text-right">Tidak hadir</th>
                                    <th scope="col" class="px-6 py-3 text-right">Catatan presensi</th>
                                    <th scope="col" class="px-6 py-3 text-right">Hari tanpa presensi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($ranked as $row)
                                    @php($recorded = collect($row['cells'])->where('total', '>', 0))
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                                        <th scope="row" class="px-6 py-3 font-bold text-gray-900 dark:text-white text-left">{{ $row['name'] }}</th>
                                        <td class="px-6 py-3 text-right font-bold {{ $toneOf($row['average']) }}">{{ number_format($row['average'], 1, ',', '.') }}%</td>
                                        <td class="px-6 py-3 text-right text-gray-600 dark:text-gray-300">{{ $recorded->sum('absent') }} / {{ $recorded->sum('total') }}</td>
                                        <td class="px-6 py-3 text-right text-gray-600 dark:text-gray-300">{{ count($row['cells']) - $recorded->count() }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-6 text-center text-gray-500">Belum ada kelas dengan presensi pada rentang ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($heatmap['dates'] !== [])
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const heatmap = @json($heatmap);
                const labels = @json($dateLabels);
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? '#cbd5e1' : '#475569';
                const NO_DATA = -1;

                // ApexCharts draws the first series at the bottom; reverse so the list reads top-down.
                const series = heatmap.rows.slice().reverse().map(function (row) {
                    return {
                        name: row.name,
                        data: row.cells.map(function (cell, i) {
                            return { x: labels[i], y: cell.rate === null ? NO_DATA : cell.rate, absent: cell.absent, total: cell.total };
                        })
                    };
                });

                const options = {
                    chart: { type: 'heatmap', height: Math.max(260, heatmap.rows.length * 34 + 80), toolbar: { show: false }, animations: { enabled: false }, fontFamily: 'inherit' },
                    series: series,
                    legend: { show: false },
                    xaxis: { labels: { style: { colors: textColor, fontSize: '12px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: { style: { colors: textColor, fontSize: '12px', fontWeight: 600 } } },
                    stroke: { width: 2, colors: [isDark ? '#1f2937' : '#ffffff'] },
                    plotOptions: {
                        heatmap: {
                            radius: 6,
                            enableShades: false,
                            colorScale: {
                                ranges: [
                                    { from: -1, to: -1, name: 'Belum diabsen', color: isDark ? '#4b5563' : '#d1d5db' },
                                    { from: 0, to: 5, name: '≤ 5%', color: '#10b981' },
                                    { from: 5.01, to: 15, name: '5–15%', color: '#f59e0b' },
                                    { from: 15.01, to: 100, name: '> 15%', color: '#f43f5e' }
                                ]
                            }
                        }
                    },
                    dataLabels: { enabled: false },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        custom: function ({ seriesIndex, dataPointIndex, w }) {
                            const point = w.config.series[seriesIndex].data[dataPointIndex];
                            const name = w.config.series[seriesIndex].name;
                            const body = point.y === NO_DATA
                                ? 'Belum ada presensi harian'
                                : point.y.toLocaleString('id-ID') + '% tidak hadir (' + point.absent + ' dari ' + point.total + ' siswa)';
                            const box = document.createElement('div');
                            box.className = 'px-3 py-2 text-xs';
                            const title = document.createElement('div');
                            title.className = 'font-bold';
                            title.textContent = name + ' • ' + point.x;
                            const detail = document.createElement('div');
                            detail.textContent = body;
                            box.append(title, detail);
                            return box.outerHTML;
                        }
                    }
                };
                new ApexCharts(document.querySelector('#attendanceHeatmapChart'), options).render();
            });
        </script>
    @endif
</x-app-layout>
