@extends('layouts.app')

@section('content')
@php
    $allocated = $workplan->programs->sum('budget_allocation');
    $realized = $workplan->programs->sum('realization');
    $rupiah = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
    $statusClass = ['planned' => 'bg-gray-100 text-gray-600', 'in_progress' => 'bg-amber-50 text-amber-700', 'completed' => 'bg-emerald-50 text-emerald-700', 'cancelled' => 'bg-rose-50 text-rose-700'];
@endphp
<div class="space-y-6">
    <x-page-header :title="$workplan->title" :subtitle="'Tahun anggaran '.$workplan->fiscal_year.' · RENSTRA: '.($workplan->plan?->title ?? '-')">
        <x-slot name="actions">
            <a href="{{ route('strategic.workplan.index') }}" class="text-sm font-bold text-indigo-600 hover:underline">&larr; Semua RKS</a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-form.card><p class="text-xs font-bold uppercase text-gray-500">Total anggaran</p><p class="text-2xl font-black text-gray-900 dark:text-white">{{ $rupiah($workplan->total_budget) }}</p></x-form.card>
        <x-form.card><p class="text-xs font-bold uppercase text-gray-500">Dialokasikan ke program</p><p class="text-2xl font-black text-gray-900 dark:text-white">{{ $rupiah($allocated) }}</p></x-form.card>
        <x-form.card>
            <p class="text-xs font-bold uppercase text-gray-500">Realisasi</p>
            <p class="text-2xl font-black text-emerald-600">{{ $rupiah($realized) }}</p>
            <p class="text-xs text-gray-500">{{ $allocated > 0 ? round($realized / $allocated * 100, 1) : 0 }}% dari alokasi</p>
        </x-form.card>
    </div>

    @forelse ($workplan->programs as $program)
        <x-form.card>
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2">
                <div>
                    <p class="font-black text-gray-900 dark:text-white">{{ $program->program_code }} · {{ $program->program_name }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ str_replace('_', ' ', ucfirst($program->category)) }} · {{ strtoupper(str_replace('_', ' ', $program->quarter)) }}</p>
                    @if ($program->description)<p class="text-sm text-gray-600 dark:text-gray-300 mt-2">{{ $program->description }}</p>@endif
                </div>
                <div class="text-right shrink-0">
                    <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $statusClass[$program->status] ?? 'bg-gray-100 text-gray-600' }}">{{ str_replace('_', ' ', $program->status) }}</span>
                    <p class="text-xs text-gray-500 mt-2">{{ $rupiah($program->realization) }} / {{ $rupiah($program->budget_allocation) }}</p>
                </div>
            </div>
            <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-700 mt-3"><div class="h-2 rounded-full bg-indigo-500" style="width: {{ min(100, (int) $program->progress_percentage) }}%"></div></div>

            @if ($program->activities->isNotEmpty())
                <table class="w-full text-sm mt-4">
                    <thead class="text-xs uppercase text-gray-500"><tr><th class="text-left py-1">Kegiatan</th><th class="text-left">Jadwal</th><th class="text-right">Anggaran</th><th class="text-right">Realisasi</th><th class="text-right">Status</th></tr></thead>
                    <tbody>
                        @foreach ($program->activities as $activity)
                            <tr class="border-t border-gray-100 dark:border-gray-700">
                                <td class="py-2">{{ $activity->activity_name }}<span class="block text-xs text-gray-500">{{ $activity->output_target }}</span></td>
                                <td class="text-xs text-gray-500">{{ $activity->start_date ? \Illuminate\Support\Carbon::parse($activity->start_date)->format('d M') : '-' }} – {{ $activity->end_date ? \Illuminate\Support\Carbon::parse($activity->end_date)->format('d M Y') : '-' }}</td>
                                <td class="text-right">{{ $rupiah($activity->budget) }}</td>
                                <td class="text-right">{{ $rupiah($activity->realization) }}</td>
                                <td class="text-right text-xs">{{ str_replace('_', ' ', $activity->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-form.card>
    @empty
        <x-form.card><p class="text-sm text-gray-500">Belum ada program pada RKS ini.</p></x-form.card>
    @endforelse
</div>
@endsection
