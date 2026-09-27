@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Rencana Strategis (RENSTRA) & Balanced Scorecard" :subtitle="'Tata Kelola Perencanaan Strategis '.school_name()" />
    @include('strategic._nav')

    @if ($activePlan)
        <x-form.card>
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">RENSTRA aktif {{ $activePlan->start_year }}–{{ $activePlan->end_year }}</p>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white mt-1">{{ $activePlan->title }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2"><span class="font-bold">Visi:</span> {{ $activePlan->vision }}</p>
                </div>
                <a href="{{ route('strategic.plan.show', $activePlan->id) }}" class="shrink-0 text-sm font-bold text-indigo-600 hover:underline">Lihat detail &rarr;</a>
            </div>
        </x-form.card>

        @php($kpis = $activePlan->objectives->flatMap->kpis)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-form.card><p class="text-xs font-bold uppercase text-gray-500">Sasaran strategis</p><p class="text-3xl font-black text-gray-900 dark:text-white">{{ $activePlan->objectives->count() }}</p></x-form.card>
            <x-form.card><p class="text-xs font-bold uppercase text-gray-500">Indikator kinerja (KPI)</p><p class="text-3xl font-black text-gray-900 dark:text-white">{{ $kpis->count() }}</p></x-form.card>
            <x-form.card>
                <p class="text-xs font-bold uppercase text-gray-500">KPI mencapai target</p>
                <p class="text-3xl font-black text-emerald-600">{{ $kpis->filter(fn ($k) => $k->polarity === 'lower_is_better' ? $k->current_value <= $k->target_value : $k->current_value >= $k->target_value)->count() }}</p>
            </x-form.card>
        </div>
    @else
        <x-form.card>
            <p class="text-sm text-gray-600 dark:text-gray-300">Belum ada RENSTRA berstatus aktif.</p>
            <a href="{{ route('strategic.plan.create') }}" class="inline-flex mt-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Buat RENSTRA</a>
        </x-form.card>
    @endif
</div>
@endsection
