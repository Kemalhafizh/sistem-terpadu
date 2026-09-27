@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Analisis SWOT" subtitle="Pemetaan kekuatan, kelemahan, peluang dan ancaman sebagai dasar penyusunan strategi.">
        <x-slot name="actions">
            <a href="{{ route('strategic.swot.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">+ Analisis Baru</a>
        </x-slot>
    </x-page-header>
    @include('strategic._nav')

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse ($analyses as $analysis)
            <a href="{{ route('strategic.swot.show', $analysis->id) }}" class="block bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 hover:border-indigo-300">
                <p class="text-xs font-bold uppercase text-indigo-600">Tahun {{ $analysis->analysis_year }}</p>
                <p class="font-black text-gray-900 dark:text-white mt-1">{{ $analysis->title }}</p>
                <div class="grid grid-cols-4 gap-2 mt-4 text-center text-xs">
                    @foreach (['strength' => 'S', 'weakness' => 'W', 'opportunity' => 'O', 'threat' => 'T'] as $quadrant => $letter)
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-900/50 py-2"><span class="font-black">{{ $letter }}</span> {{ $analysis->items->where('quadrant', $quadrant)->count() }}</div>
                    @endforeach
                </div>
            </a>
        @empty
            <x-form.card class="md:col-span-2 xl:col-span-3"><p class="text-sm text-gray-500 text-center">Belum ada analisis SWOT.</p></x-form.card>
        @endforelse
    </div>
</div>
@endsection
