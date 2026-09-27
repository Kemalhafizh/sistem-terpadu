@extends('layouts.app')

@section('content')
@php
    $initialQuestions = old('questions', [
        ['dimension' => 'Layanan Akademik', 'text' => '', 'scale' => 'likert_5'],
    ]);
@endphp
<div class="space-y-6 max-w-4xl" x-data="{ questions: @js(array_values($initialQuestions)) }">
    <x-page-header title="Buat Survei Kepuasan" subtitle="Pertanyaan dikelompokkan per dimensi sesuai nama dimensi yang diisi." />

    <form method="POST" action="{{ route('strategic.survey.store') }}" class="space-y-6">
        @csrf
        <x-form.card title="Informasi survei">
            <div class="space-y-5">
                <x-form.input name="title" label="Judul" required placeholder="Survei Kepuasan Orang Tua Semester Ganjil" />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.select name="survey_type" label="Jenis survei" :options="$types" required />
                    <x-form.select name="status" label="Status" :options="['draft' => 'Draf (belum dibuka)', 'active' => 'Aktif (dibuka)']" value="draft" />
                    <x-form.input name="start_date" label="Mulai" type="date" :value="date('Y-m-d')" required />
                    <x-form.input name="end_date" label="Selesai" type="date" :value="date('Y-m-d', strtotime('+14 days'))" required />
                </div>
                <x-form.textarea name="description" label="Pengantar untuk responden" rows="3" />
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="hidden" name="is_anonymous" value="0">
                    <input type="checkbox" name="is_anonymous" value="1" class="rounded border-gray-300 text-indigo-600" @checked(old('is_anonymous', true))>
                    Anonim (hasil hanya ditampilkan sebagai agregat)
                </label>
            </div>
        </x-form.card>

        <x-form.card title="Pertanyaan">
            <x-input-error :messages="$errors->get('questions')" class="mb-3" />
            <template x-for="(question, index) in questions" :key="index">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Dimensi</label>
                        <input type="text" x-model="question.dimension" :name="`questions[${index}][dimension]`" required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm">
                    </div>
                    <div class="md:col-span-6">
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1" x-text="`Pertanyaan ${index + 1}`"></label>
                        <input type="text" x-model="question.text" :name="`questions[${index}][text]`" required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Skala</label>
                        <select x-model="question.scale" :name="`questions[${index}][scale]`" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm">
                            @foreach ($scales as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-1 md:pt-6">
                        <button type="button" @click="questions.splice(index, 1)" x-show="questions.length > 1" class="text-xs font-bold text-rose-600 hover:underline">Hapus</button>
                    </div>
                </div>
            </template>
            @foreach ($errors->getMessages() as $key => $messages)
                @if (str_starts_with($key, 'questions.'))
                    <p class="text-sm text-red-600">Pertanyaan {{ (int) explode('.', $key)[1] + 1 }}: {{ $messages[0] }}</p>
                @endif
            @endforeach
            <button type="button" @click="questions.push({ dimension: questions.at(-1)?.dimension ?? '', text: '', scale: 'likert_5' })" class="text-sm font-bold text-indigo-600 hover:underline">+ Tambah pertanyaan</button>
        </x-form.card>

        <div class="flex justify-end gap-3">
            <a href="{{ route('strategic.survey.index') }}" class="py-2.5 px-5 text-sm font-bold text-gray-600 dark:text-gray-300">Batal</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Simpan survei</button>
        </div>
    </form>
</div>
@endsection
