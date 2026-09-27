@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ copied: false }">
    <x-page-header :title="$survey->title" :subtitle="(\App\Http\Controllers\Strategic\SatisfactionSurveyController::TYPES[$survey->survey_type] ?? $survey->survey_type).' · '.$survey->start_date->format('d M Y').' – '.$survey->end_date->format('d M Y')">
        <x-slot name="actions">
            <a href="{{ route('strategic.survey.results', $survey->id) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Lihat hasil ({{ $respondents }})</a>
        </x-slot>
    </x-page-header>
    @include('strategic._nav')

    <x-form.card title="Tautan pengisian">
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
            Bagikan tautan ini kepada responden (harus login).
            Status: <span class="font-bold">{{ $survey->isOpen() ? 'dibuka' : ($survey->status === 'draft' ? 'draf — belum dibuka' : 'di luar periode / ditutup') }}</span>.
            {{ $survey->is_anonymous ? 'Survei anonim.' : '' }}
        </p>
        <div class="flex gap-2">
            <input type="text" readonly value="{{ route('strategic.survey.respond', $survey->id) }}" class="flex-1 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm" x-ref="link">
            <button type="button" @click="navigator.clipboard.writeText($refs.link.value); copied = true; setTimeout(() => copied = false, 1500)" class="bg-gray-100 dark:bg-gray-700 rounded-xl px-4 text-sm font-bold" x-text="copied ? 'Tersalin' : 'Salin'"></button>
        </div>
    </x-form.card>

    @if ($survey->description)
        <x-form.card title="Pengantar"><p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $survey->description }}</p></x-form.card>
    @endif

    @foreach ($survey->dimensions as $dimension)
        <x-form.card :title="$dimension->dimension_name">
            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700 dark:text-gray-300">
                @foreach ($dimension->questions as $question)
                    <li>{{ $question->question_text }} <span class="text-xs text-gray-500">({{ $scales[$question->scale_type] ?? $question->scale_type }})</span></li>
                @endforeach
            </ol>
        </x-form.card>
    @endforeach
</div>
@endsection
