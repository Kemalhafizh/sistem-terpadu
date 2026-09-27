@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ objectiveForm: {{ $errors->hasAny(['perspective', 'objective', 'weight']) ? 'true' : 'false' }}, kpiFor: {{ old('kpi_objective_id', 'null') }} }">
    <x-page-header :title="$plan->title" :subtitle="'Periode '.$plan->start_year.'–'.$plan->end_year.' · Status '.ucfirst($plan->status)">
        <x-slot name="actions">
            <button type="button" @click="objectiveForm = ! objectiveForm" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">+ Sasaran Strategis</button>
        </x-slot>
    </x-page-header>
    @include('strategic._nav')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.card title="Visi"><p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $plan->vision }}</p></x-form.card>
        <x-form.card title="Misi"><p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $plan->mission }}</p></x-form.card>
    </div>

    <x-form.card title="Tambah sasaran strategis" x-show="objectiveForm" x-cloak>
        <form method="POST" action="{{ route('strategic.plan.objectives.store', $plan->id) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <x-form.select name="perspective" label="Perspektif BSC" :options="$perspectives" required />
            <x-form.input name="code" label="Kode" placeholder="S1" required />
            <div class="md:col-span-2"><x-form.input name="objective" label="Sasaran" required /></div>
            <div class="md:col-span-3"><x-form.textarea name="description" label="Deskripsi" rows="2" /></div>
            <x-form.input name="weight" label="Bobot (%)" type="number" min="0" max="100" value="0" required />
            <div class="md:col-span-4 flex justify-end"><button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Simpan sasaran</button></div>
        </form>
    </x-form.card>

    @foreach ($perspectives as $key => $label)
        @php($objectives = $plan->objectives->where('perspective', $key))
        <x-form.card :title="$label">
            @forelse ($objectives as $objective)
                <div class="border border-gray-100 dark:border-gray-700 rounded-2xl p-4 mb-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-black text-gray-900 dark:text-white">{{ $objective->code }} · {{ $objective->objective }}</p>
                            @if ($objective->description)<p class="text-sm text-gray-500 mt-1">{{ $objective->description }}</p>@endif
                        </div>
                        <span class="text-xs font-bold text-gray-500 shrink-0">Bobot {{ $objective->weight }}%</span>
                    </div>

                    @if ($objective->kpis->isNotEmpty())
                        <table class="w-full text-sm mt-3">
                            <thead class="text-xs uppercase text-gray-500"><tr><th class="text-left py-1">KPI</th><th class="text-right">Baseline</th><th class="text-right">Target</th><th class="text-right">Capaian</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($objective->kpis as $kpi)
                                    @php($met = $kpi->polarity === 'lower_is_better' ? $kpi->current_value <= $kpi->target_value : $kpi->current_value >= $kpi->target_value)
                                    <tr class="border-t border-gray-100 dark:border-gray-700">
                                        <td class="py-2 text-gray-800 dark:text-gray-200">{{ $kpi->code }} · {{ $kpi->indicator }}</td>
                                        <td class="text-right">{{ (float) $kpi->baseline_value }} {{ $kpi->unit }}</td>
                                        <td class="text-right">{{ (float) $kpi->target_value }} {{ $kpi->unit }}</td>
                                        <td class="text-right font-bold {{ $met ? 'text-emerald-600' : 'text-amber-600' }}">{{ (float) $kpi->current_value }} {{ $kpi->unit }}</td>
                                        <td class="text-right"><a href="{{ route('strategic.kpi.report', $kpi->id) }}" class="text-xs font-bold text-indigo-600 hover:underline">Laporan</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <button type="button" class="mt-3 text-xs font-bold text-indigo-600 hover:underline" @click="kpiFor = kpiFor === {{ $objective->id }} ? null : {{ $objective->id }}">+ Tambah KPI</button>
                    <form x-show="kpiFor === {{ $objective->id }}" x-cloak method="POST" action="{{ route('strategic.objectives.kpis.store', $objective->id) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-3">
                        @csrf
                        <input type="hidden" name="kpi_objective_id" value="{{ $objective->id }}">
                        <x-form.input name="code" label="Kode KPI" :id="'kpi-code-'.$objective->id" placeholder="K1.1" required />
                        <div class="md:col-span-2"><x-form.input name="indicator" label="Indikator" :id="'kpi-indicator-'.$objective->id" required /></div>
                        <x-form.input name="unit" label="Satuan" :id="'kpi-unit-'.$objective->id" placeholder="%" required />
                        <x-form.input name="baseline_value" label="Baseline" type="number" step="any" :id="'kpi-baseline-'.$objective->id" required />
                        <x-form.input name="target_value" label="Target" type="number" step="any" :id="'kpi-target-'.$objective->id" required />
                        <x-form.select name="polarity" label="Arah" :id="'kpi-polarity-'.$objective->id" :options="['higher_is_better' => 'Makin tinggi makin baik', 'lower_is_better' => 'Makin rendah makin baik']" />
                        <x-form.select name="frequency" label="Frekuensi ukur" :id="'kpi-frequency-'.$objective->id" :options="$frequencies" value="semester" />
                        <div class="md:col-span-3"><x-form.input name="data_source" label="Sumber data" :id="'kpi-source-'.$objective->id" /></div>
                        <div class="flex items-end justify-end"><button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Simpan KPI</button></div>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada sasaran pada perspektif ini.</p>
            @endforelse
        </x-form.card>
    @endforeach
</div>
@endsection
