<x-app-layout>
    <x-slot name="title">Hasil Pemilu OSIS</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    📈 Hasil Real-Count E-Voting
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Agenda: {{ $election->title }} | Total Suara Masuk: {{ $totalVotes }} Suara
                </p>
            </div>
            <a href="{{ route('osis.elections') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅️ Kembali
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Visual Donut Chart ApexCharts -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl lg:col-span-1 flex flex-col justify-between">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-4">📈 Distribusi Persentase Suara</h3>
            
            <div id="voteDistributionChart" class="w-full h-64"></div>
        </div>

        <!-- Rincian Suara per Kandidat -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl lg:col-span-2 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm">📋 Rekapitulasi Perolehan Suara Calon</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-55 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">No Urut</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Calon</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Total Perolehan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @forelse($election->candidates as $cand)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-all">
                                <td class="px-6 py-4 font-black text-gray-800 dark:text-white text-center w-20">
                                    {{ $cand->candidate_number }}
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-800 dark:text-white">
                                    {{ $cand->student->name }}
                                </td>
                                <td class="px-6 py-4 text-right font-black text-indigo-600 dark:text-indigo-400">
                                    {{ $cand->vote_count }} Suara
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-12 text-gray-400 italic">
                                    Belum ada kandidat paslon terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Script ApexCharts untuk Pie/Donut Chart -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const candidates = @json($election->candidates);
            const seriesData = candidates.map(c => parseInt(c.vote_count));
            const labelsData = candidates.map(c => 'No ' + c.candidate_number + ' - ' + c.student.name);

            const options = {
                chart: {
                    type: 'donut',
                    height: 280,
                    fontFamily: 'Inter, sans-serif'
                },
                series: seriesData.length > 0 ? seriesData : [100],
                labels: labelsData.length > 0 ? labelsData : ['Belum ada suara'],
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                legend: {
                    position: 'bottom',
                    fontSize: '11px'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val.toFixed(1) + "%"
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector("#voteDistributionChart"), options);
            chart.render();
        });
    </script>
</x-app-layout>
