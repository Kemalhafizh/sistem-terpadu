@extends('layouts.app')

@section('content')
@php($tone = ['strength' => 'border-emerald-200 bg-emerald-50/60 dark:bg-emerald-900/10', 'weakness' => 'border-amber-200 bg-amber-50/60 dark:bg-amber-900/10', 'opportunity' => 'border-sky-200 bg-sky-50/60 dark:bg-sky-900/10', 'threat' => 'border-rose-200 bg-rose-50/60 dark:bg-rose-900/10'])
<div class="space-y-6">
    <x-page-header :title="$swot->title" :subtitle="'Analisis tahun '.$swot->analysis_year">
        <x-slot name="actions">
            <a href="{{ route('strategic.swot.index') }}" class="text-sm font-bold text-indigo-600 hover:underline">&larr; Semua analisis</a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($quadrants as $key => $label)
            <div class="rounded-3xl border p-6 {{ $tone[$key] }}">
                <h2 class="text-sm font-black uppercase tracking-wider text-gray-900 dark:text-white mb-3">{{ $label }}</h2>
                <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300 list-disc list-inside">
                    @forelse ($swot->items->where('quadrant', $key) as $item)
                        <li>{{ $item->description }} @if ($item->strategy_response)<span class="block text-xs text-gray-500 ml-5">Strategi: {{ $item->strategy_response }}</span>@endif</li>
                    @empty
                        <li class="list-none text-gray-500">Belum ada faktor.</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>

    @if ($swot->conclusion)
        <x-form.card title="Kesimpulan / arah strategi"><p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $swot->conclusion }}</p></x-form.card>
    @endif
</div>
@endsection
