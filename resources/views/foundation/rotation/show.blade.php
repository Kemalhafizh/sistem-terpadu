@extends('layouts.app')
@section('content')
<div class="space-y-6 max-w-3xl">
    <x-page-header :title="'Rotasi: '.($rotation->user?->name ?? '-')" :subtitle="($types[$rotation->rotation_type] ?? $rotation->rotation_type).' · '.($statuses[$rotation->status] ?? $rotation->status)">
        <x-slot name="actions">
            <a href="{{ route('foundation.rotation.index') }}" class="text-sm font-bold text-indigo-600 hover:underline">&larr; Semua rotasi</a>
        </x-slot>
    </x-page-header>

    <x-form.card>
        <div class="flex items-center justify-between gap-4 text-center">
            <div class="flex-1 rounded-2xl bg-gray-50 dark:bg-gray-900/50 p-4"><p class="text-xs font-bold uppercase text-gray-500">Dari</p><p class="font-black text-gray-900 dark:text-white">{{ $rotation->fromUnit?->unit_name ?? '-' }}</p></div>
            <span class="text-2xl text-gray-400">→</span>
            <div class="flex-1 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 p-4"><p class="text-xs font-bold uppercase text-indigo-600">Ke</p><p class="font-black text-gray-900 dark:text-white">{{ $rotation->toUnit?->unit_name ?? '-' }}</p></div>
        </div>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mt-6">
            <div><dt class="text-xs font-bold uppercase text-gray-500">Tanggal efektif</dt><dd>{{ $rotation->effective_date->format('d M Y') }}</dd></div>
            <div><dt class="text-xs font-bold uppercase text-gray-500">Berakhir</dt><dd>{{ $rotation->end_date?->format('d M Y') ?? '-' }}</dd></div>
            <div><dt class="text-xs font-bold uppercase text-gray-500">Disetujui oleh</dt><dd>{{ $rotation->approver?->name ?? '-' }}</dd></div>
            <div><dt class="text-xs font-bold uppercase text-gray-500">Diajukan</dt><dd>{{ $rotation->created_at?->format('d M Y') }}</dd></div>
        </dl>
        <div class="mt-6"><p class="text-xs font-bold uppercase text-gray-500">Alasan</p><p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line mt-1">{{ $rotation->reason }}</p></div>
    </x-form.card>
</div>
@endsection
