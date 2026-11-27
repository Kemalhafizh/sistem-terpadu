<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-slate-900 dark:text-slate-100 leading-tight">
                    {{ __('Rapor Projek Penguatan Profil Pelajar Pancasila (P5)') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ $student->user?->name ?? '-' }} · NIS {{ $student->nis ?? '-' }} · {{ $report['classroom']?->name ?? '-' }} (Fase {{ $report['phase'] ?? '-' }})
                </p>
            </div>
            <a href="{{ route('p5.reports.pdf', $student) }}" target="_blank" class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">
                {{ __('Unduh PDF Rapor P5') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ __('Nama Peserta Didik') }}</span>
                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $student->user?->name ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ __('NIS / NISN') }}</span>
                <span class="font-semibold text-slate-900 dark:text-slate-100 tabular-nums">{{ $student->nis ?? '-' }} / {{ $student->nisn ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ __('Kelas / Fase') }}</span>
                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $report['classroom']?->name ?? '-' }} / Fase {{ $report['phase'] ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ __('Tahun Ajaran') }}</span>
                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $report['school_year']?->name ?? '-' }}</span>
            </div>
        </div>

        @forelse ($report['projects'] as $proj)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 space-y-4">
                <div>
                    <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $proj['theme_label'] }}</span>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $proj['title'] }}</h3>
                    @if ($proj['description'])
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">{{ $proj['description'] }}</p>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400">
                                <th class="py-2.5 pe-4">{{ __('Dimensi & Elemen') }}</th>
                                <th class="py-2.5 px-4">{{ __('Capaian Akhir Fase') }}</th>
                                <th class="py-2.5 ps-4 text-end">{{ __('Predikat') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach ($proj['dimensions'] as $dim)
                                <tr>
                                    <td class="py-3 pe-4">
                                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $dim['dimension_name'] }}</div>
                                        <div class="text-xs text-slate-500">{{ $dim['element'] }}{{ $dim['sub_element'] ? ' — '.$dim['sub_element'] : '' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-400">
                                        {{ $dim['target_achievement'] }}
                                    </td>
                                    <td class="py-3 ps-4 text-end whitespace-nowrap">
                                        @if ($dim['predicate'])
                                            <span class="px-2.5 py-1 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs font-semibold">
                                                {{ $dim['predicate'] }} — {{ $dim['predicate_label'] }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">{{ __('Belum dinilai') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800 rounded-lg p-4">
                    <span class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Catatan Proses') }}</span>
                    <p class="text-sm text-slate-700 dark:text-slate-300">
                        {{ $proj['process_notes'] ?? __('Belum ada catatan proses projek.') }}
                    </p>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                {{ __('Belum ada data Projek P5 untuk peserta didik ini pada tahun ajaran terkait.') }}
            </div>
        @endforelse
    </div>
</x-app-layout>
