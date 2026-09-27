@extends('layouts.app')

@section('content')
@php
    $met = $kpi->polarity === 'lower_is_better' ? $kpi->current_value <= $kpi->target_value : $kpi->current_value >= $kpi->target_value;
    $values = $kpi->measurements->pluck('value')->map(fn ($v) => (float) $v);
    $scaleMax = max($values->max() ?? 0, (float) $kpi->target_value, (float) $kpi->baseline_value, 1);
@endphp
<div class="space-y-6">
    <x-page-header :title="$kpi->code.' · '.$kpi->indicator" :subtitle="($kpi->objective?->plan?->title ?? '').' · Sasaran '.($kpi->objective?->code ?? '-')">
        <x-slot name="actions">
            @if ($kpi->objective)
                <a href="{{ route('strategic.plan.show', $kpi->objective->strategic_plan_id) }}" class="text-sm font-bold text-indigo-600 hover:underline">&larr; Kembali ke RENSTRA</a>
            @endif
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-form.card><p class="text-xs font-bold uppercase text-gray-500">Baseline</p><p class="text-2xl font-black text-gray-900 dark:text-white">{{ (float) $kpi->baseline_value }} <span class="text-sm">{{ $kpi->unit }}</span></p></x-form.card>
        <x-form.card><p class="text-xs font-bold uppercase text-gray-500">Target</p><p class="text-2xl font-black text-gray-900 dark:text-white">{{ (float) $kpi->target_value }} <span class="text-sm">{{ $kpi->unit }}</span></p></x-form.card>
        <x-form.card><p class="text-xs font-bold uppercase text-gray-500">Capaian terkini</p><p class="text-2xl font-black {{ $met ? 'text-emerald-600' : 'text-amber-600' }}">{{ (float) $kpi->current_value }} <span class="text-sm">{{ $kpi->unit }}</span></p></x-form.card>
        <x-form.card><p class="text-xs font-bold uppercase text-gray-500">Status</p><p class="text-lg font-black {{ $met ? 'text-emerald-600' : 'text-amber-600' }}">{{ $met ? 'Tercapai' : 'Belum tercapai' }}</p><p class="text-xs text-gray-500">{{ $kpi->polarity === 'lower_is_better' ? 'Makin rendah makin baik' : 'Makin tinggi makin baik' }}</p></x-form.card>
    </div>

    <x-form.card title="Tren pengukuran">
        @if ($kpi->measurements->isEmpty())
            <p class="text-sm text-gray-500">Belum ada pengukuran.</p>
        @else
            <div class="flex items-end gap-2 h-40 border-b border-gray-200 dark:border-gray-700">
                @foreach ($kpi->measurements as $m)
                    <div class="flex-1 flex flex-col items-center justify-end h-full" title="{{ $m->measurement_date->format('d M Y') }}: {{ (float) $m->value }} {{ $kpi->unit }}">
                        <span class="text-[10px] font-bold text-gray-600 dark:text-gray-300">{{ (float) $m->value }}</span>
                        <div class="w-full max-w-10 rounded-t-lg bg-indigo-500" style="height: {{ max(2, (float) $m->value / $scaleMax * 100) }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="flex gap-2 mt-1">
                @foreach ($kpi->measurements as $m)
                    <span class="flex-1 text-center text-[10px] text-gray-500">{{ $m->measurement_date->format('M y') }}</span>
                @endforeach
            </div>
        @endif
    </x-form.card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-form.card title="Riwayat pengukuran" class="lg:col-span-2 !p-0 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500"><tr><th class="text-left px-6 py-3">Tanggal</th><th class="text-right px-6 py-3">Nilai</th><th class="text-left px-6 py-3">Bukti / Analisis</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($kpi->measurements->sortByDesc('measurement_date') as $m)
                        <tr>
                            <td class="px-6 py-3">{{ $m->measurement_date->format('d M Y') }}</td>
                            <td class="px-6 py-3 text-right font-bold">{{ (float) $m->value }} {{ $kpi->unit }}</td>
                            <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $m->evidence }} @if ($m->analysis)<span class="block text-xs text-gray-500">{{ $m->analysis }}</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">Belum ada pengukuran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-form.card>

        <x-form.card title="Catat pengukuran">
            <form method="POST" action="{{ route('strategic.kpi.measure', $kpi->id) }}" class="space-y-4">
                @csrf
                <x-form.input name="measurement_date" label="Tanggal" type="date" :value="date('Y-m-d')" required />
                <x-form.input name="value" :label="'Nilai ('.$kpi->unit.')'" type="number" step="any" required />
                <x-form.textarea name="evidence" label="Bukti" rows="2" />
                <x-form.textarea name="analysis" label="Analisis" rows="2" />
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Simpan</button>
            </form>
        </x-form.card>
    </div>
</div>
@endsection
