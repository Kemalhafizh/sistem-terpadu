@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-page-header title="Daftar Rencana Strategis (RENSTRA)">
        <x-slot name="actions">
            <a href="{{ route('strategic.plan.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">+ RENSTRA Baru</a>
        </x-slot>
    </x-page-header>
    @include('strategic._nav')

    <x-form.card class="!p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500">
                <tr><th class="text-left px-6 py-3">Judul</th><th class="text-left px-6 py-3">Periode</th><th class="text-left px-6 py-3">Sasaran</th><th class="text-left px-6 py-3">Status</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($plans as $plan)
                    <tr>
                        <td class="px-6 py-3 font-bold text-gray-900 dark:text-white">{{ $plan->title }}</td>
                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $plan->start_year }}–{{ $plan->end_year }}</td>
                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $plan->objectives_count }}</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded-lg text-xs font-bold {{ $plan->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($plan->status) }}</span></td>
                        <td class="px-6 py-3 text-right"><a href="{{ route('strategic.plan.show', $plan->id) }}" class="font-bold text-indigo-600 hover:underline">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">Belum ada RENSTRA.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-form.card>
</div>
@endsection
