@extends('layouts.app')

@section('content')
@php($grade = fn ($i) => $i === null ? '-' : ($i >= 88.31 ? 'A (Sangat Baik)' : ($i >= 76.61 ? 'B (Baik)' : ($i >= 65 ? 'C (Kurang Baik)' : 'D (Tidak Baik)'))))
<div class="space-y-6">
    <x-page-header :title="'Hasil: '.$survey->title" subtitle="Indeks 0–100 = rata-rata skor dibagi skor maksimum skala. Mutu mengikuti kategori IKM (Permenpan RB 14/2017).">
        <x-slot name="actions">
            <a href="{{ route('strategic.survey.show', $survey->id) }}" class="text-sm font-bold text-indigo-600 hover:underline">&larr; Detail survei</a>
        </x-slot>
    </x-page-header>
    @include('strategic._nav')

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-form.card><p class="text-xs font-bold uppercase text-gray-500">Responden</p><p class="text-3xl font-black text-gray-900 dark:text-white">{{ $respondents }}</p></x-form.card>
        <x-form.card><p class="text-xs font-bold uppercase text-gray-500">Indeks kepuasan</p><p class="text-3xl font-black text-indigo-600">{{ $overallIndex ?? '-' }}</p></x-form.card>
        <x-form.card><p class="text-xs font-bold uppercase text-gray-500">Mutu layanan</p><p class="text-xl font-black text-gray-900 dark:text-white">{{ $grade($overallIndex) }}</p></x-form.card>
    </div>

    @foreach ($dimensions as $row)
        <x-form.card :title="$row['dimension']->dimension_name.' — indeks '.($row['index'] ?? '-')">
            <div class="space-y-5">
                @foreach ($row['questions'] as $q)
                    <div>
                        <div class="flex justify-between gap-4">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $q['question']->question_text }}</p>
                            <p class="text-xs text-gray-500 shrink-0">{{ $q['count'] }} jawaban @if ($q['average'] !== null) · rata-rata {{ $q['average'] }} · indeks {{ $q['index'] }} @endif</p>
                        </div>
                        @if ($q['index'] !== null)
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-700 mt-2"><div class="h-2 rounded-full bg-indigo-500" style="width: {{ $q['index'] }}%"></div></div>
                            <div class="flex flex-wrap gap-3 mt-2 text-xs text-gray-500">
                                @foreach ($q['distribution'] as $score => $count)
                                    <span>{{ $q['question']->scale_type === 'yes_no' ? ($score ? 'Ya' : 'Tidak') : 'Skor '.$score }}: <span class="font-bold">{{ $count }}</span></span>
                                @endforeach
                            </div>
                        @elseif ($q['texts']->isNotEmpty())
                            <ul class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-300 list-disc list-inside">
                                @foreach ($q['texts'] as $text)
                                    <li>{{ $text }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-form.card>
    @endforeach
</div>
@endsection
