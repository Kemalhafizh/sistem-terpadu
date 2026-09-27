@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">
    <x-page-header :title="$survey->title" :subtitle="$survey->is_anonymous ? 'Survei anonim: jawaban Anda hanya ditampilkan sebagai rekap.' : null" />

    @if ($survey->description)
        <x-form.card><p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $survey->description }}</p></x-form.card>
    @endif

    @if ($alreadyAnswered)
        <x-form.card><p class="text-sm font-bold text-emerald-700 dark:text-emerald-400">Terima kasih, Anda sudah mengisi survei ini.</p></x-form.card>
    @elseif (! $survey->isOpen())
        <x-form.card><p class="text-sm font-bold text-amber-700 dark:text-amber-400">Survei ini sedang tidak menerima tanggapan (periode {{ $survey->start_date->format('d M Y') }} – {{ $survey->end_date->format('d M Y') }}).</p></x-form.card>
    @else
        <form method="POST" action="{{ route('strategic.survey.storeResponse', $survey->id) }}" class="space-y-6">
            @csrf
            @foreach ($survey->dimensions as $dimension)
                <x-form.card :title="$dimension->dimension_name">
                    <div class="space-y-6">
                        @foreach ($dimension->questions as $question)
                            @php($field = 'answers.'.$question->id)
                            <fieldset>
                                <legend class="text-sm font-bold text-gray-900 dark:text-white mb-2">{{ $question->question_text }}</legend>
                                @if (in_array($question->scale_type, ['likert_5', 'likert_7'], true))
                                    @php($max = $question->scale_type === 'likert_5' ? 5 : 7)
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs text-gray-500">Sangat tidak puas</span>
                                        @for ($i = 1; $i <= $max; $i++)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $i }}" class="peer sr-only" @checked((string) old($field) === (string) $i) required>
                                                <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-300 dark:border-gray-600 text-sm font-bold peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600">{{ $i }}</span>
                                            </label>
                                        @endfor
                                        <span class="text-xs text-gray-500">Sangat puas</span>
                                    </div>
                                @elseif ($question->scale_type === 'yes_no')
                                    <div class="flex gap-4 text-sm">
                                        <label class="inline-flex items-center gap-2"><input type="radio" name="answers[{{ $question->id }}]" value="1" @checked(old($field) === '1') required> Ya</label>
                                        <label class="inline-flex items-center gap-2"><input type="radio" name="answers[{{ $question->id }}]" value="0" @checked(old($field) === '0')> Tidak</label>
                                    </div>
                                @else
                                    <textarea name="answers[{{ $question->id }}]" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm">{{ old($field) }}</textarea>
                                @endif
                                <x-input-error :messages="$errors->get($field)" class="mt-1" />
                            </fieldset>
                        @endforeach
                    </div>
                </x-form.card>
            @endforeach

            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-6 text-sm font-bold">Kirim tanggapan</button>
            </div>
        </form>
    @endif
</div>
@endsection
