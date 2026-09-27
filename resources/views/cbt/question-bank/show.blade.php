<x-app-layout>
    <x-slot name="title">Detail Soal</x-slot>

    @php
        $typeLabel = ['multiple_choice' => 'Pilihan Ganda', 'true_false' => 'Benar / Salah', 'essay' => 'Esai'][$questionBank->type] ?? $questionBank->type;
        $difficultyLabel = ['easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Sulit'][$questionBank->difficulty] ?? $questionBank->difficulty;
    @endphp
    <div class="max-w-4xl space-y-6">
        <x-page-header title="Detail Soal" :subtitle="($questionBank->subject?->name ?? '-').' · Kelas '.$questionBank->grade_level.' · '.$typeLabel.' · '.$difficultyLabel">
            <x-slot name="actions">
                <a href="{{ route('cbt.question-banks.edit', $questionBank) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Edit</a>
                <a href="{{ route('cbt.question-banks.index') }}" class="text-sm font-bold text-gray-600 dark:text-gray-300">Kembali</a>
            </x-slot>
        </x-page-header>

        <x-form.card title="Pertanyaan">
            <div class="text-sm text-gray-900 dark:text-white leading-relaxed">{!! nl2br(e($questionBank->question_text)) !!}</div>
            @if ($questionBank->question_image)
                <img src="{{ Storage::url($questionBank->question_image) }}" alt="Gambar soal" class="mt-4 max-h-72 rounded-xl border border-gray-200 dark:border-gray-700">
            @endif
        </x-form.card>

        <x-form.card title="Jawaban">
            @if ($questionBank->type === 'multiple_choice')
                <ul class="space-y-2">
                    @foreach ($questionBank->options ?? [] as $key => $option)
                        @php($correct = (string) $key === (string) $questionBank->correct_answer)
                        <li @class(['flex gap-3 items-start rounded-xl border p-3 text-sm', 'border-emerald-300 bg-emerald-50 dark:bg-emerald-900/20' => $correct, 'border-gray-200 dark:border-gray-700' => ! $correct])>
                            <span class="font-black {{ $correct ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-500' }}">{{ $key }}.</span>
                            <span class="text-gray-800 dark:text-gray-200">{{ $option }}</span>
                            @if ($correct)<span class="ml-auto text-xs font-bold text-emerald-700 dark:text-emerald-400">Kunci</span>@endif
                        </li>
                    @endforeach
                </ul>
            @elseif ($questionBank->type === 'true_false')
                <p class="text-sm">Kunci: <span class="font-black text-emerald-700 dark:text-emerald-400">{{ $questionBank->correct_answer === 'true' ? 'Benar' : 'Salah' }}</span></p>
            @else
                <p class="text-xs font-bold uppercase text-gray-500 mb-1">Rubrik / jawaban acuan</p>
                <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $questionBank->correct_answer ?: '—' }}</p>
            @endif
        </x-form.card>

        @if ($questionBank->explanation)
            <x-form.card title="Pembahasan">
                <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{!! nl2br(e($questionBank->explanation)) !!}</div>
            </x-form.card>
        @endif

        <p class="text-xs text-gray-500">
            @if ($questionBank->competency_code) Kompetensi {{ $questionBank->competency_code }} · @endif
            Dibuat oleh {{ $questionBank->creator?->name ?? '-' }} · {{ $questionBank->created_at?->format('d M Y') }}
        </p>
    </div>
</x-app-layout>
