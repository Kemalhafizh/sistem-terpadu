@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Rencana Kerja Sekolah (RKS)" subtitle="Program tahunan turunan RENSTRA beserta anggaran dan realisasinya.">
        <x-slot name="actions">
            <a href="{{ route('strategic.workplan.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">+ RKS Baru</a>
        </x-slot>
    </x-page-header>
    @include('strategic._nav')

    <x-form.card class="!p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500">
                <tr><th class="text-left px-6 py-3">Judul</th><th class="text-left px-6 py-3">Tahun</th><th class="text-left px-6 py-3">RENSTRA</th><th class="text-right px-6 py-3">Anggaran</th><th class="text-right px-6 py-3">Program</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($workplans as $workplan)
                    <tr>
                        <td class="px-6 py-3 font-bold text-gray-900 dark:text-white">{{ $workplan->title }}</td>
                        <td class="px-6 py-3">{{ $workplan->fiscal_year }}</td>
                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $workplan->plan?->title ?? '-' }}</td>
                        <td class="px-6 py-3 text-right">Rp {{ number_format((float) $workplan->total_budget, 0, ',', '.') }}</td>
                        <td class="px-6 py-3 text-right">{{ $workplan->programs->count() }}</td>
                        <td class="px-6 py-3 text-right"><a href="{{ route('strategic.workplan.show', $workplan->id) }}" class="font-bold text-indigo-600 hover:underline">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada RKS.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-form.card>
</div>
@endsection
