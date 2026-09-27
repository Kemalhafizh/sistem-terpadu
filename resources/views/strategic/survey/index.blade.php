@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Survei Kepuasan (IKM)" subtitle="Indeks Kepuasan Masyarakat sekolah: siswa, orang tua, guru dan mitra.">
        <x-slot name="actions">
            <a href="{{ route('strategic.survey.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">+ Survei Baru</a>
        </x-slot>
    </x-page-header>
    @include('strategic._nav')

    <x-form.card class="!p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500">
                <tr><th class="text-left px-6 py-3">Judul</th><th class="text-left px-6 py-3">Jenis</th><th class="text-left px-6 py-3">Periode</th><th class="text-left px-6 py-3">Status</th><th class="text-right px-6 py-3">Responden</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($surveys as $survey)
                    <tr>
                        <td class="px-6 py-3 font-bold text-gray-900 dark:text-white">{{ $survey->title }}</td>
                        <td class="px-6 py-3">{{ \App\Http\Controllers\Strategic\SatisfactionSurveyController::TYPES[$survey->survey_type] ?? $survey->survey_type }}</td>
                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $survey->start_date->format('d M Y') }} – {{ $survey->end_date->format('d M Y') }}</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded-lg text-xs font-bold {{ $survey->isOpen() ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $survey->isOpen() ? 'Dibuka' : ucfirst($survey->status) }}</span></td>
                        <td class="px-6 py-3 text-right">{{ $survey->respondents_count }}</td>
                        <td class="px-6 py-3 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ route('strategic.survey.show', $survey->id) }}" class="font-bold text-indigo-600 hover:underline">Detail</a>
                            <a href="{{ route('strategic.survey.results', $survey->id) }}" class="font-bold text-emerald-600 hover:underline">Hasil</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada survei.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-form.card>
</div>
@endsection
