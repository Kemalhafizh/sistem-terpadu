@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-page-header :title="'Rotasi Staf '.school_name()" subtitle="Mutasi, penugasan sementara, pertukaran dan promosi antarunit.">
        <x-slot name="actions">
            <a href="{{ route('foundation.rotation.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">+ Usulan Rotasi</a>
        </x-slot>
    </x-page-header>

    <x-form.card class="!p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500">
                <tr><th class="text-left px-6 py-3">Staf</th><th class="text-left px-6 py-3">Jenis</th><th class="text-left px-6 py-3">Dari → Ke</th><th class="text-left px-6 py-3">Efektif</th><th class="text-left px-6 py-3">Status</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($rotations as $rotation)
                    <tr>
                        <td class="px-6 py-3 font-bold text-gray-900 dark:text-white">{{ $rotation->user?->name ?? '-' }}</td>
                        <td class="px-6 py-3">{{ $types[$rotation->rotation_type] ?? $rotation->rotation_type }}</td>
                        <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $rotation->fromUnit?->unit_name ?? '-' }} → {{ $rotation->toUnit?->unit_name ?? '-' }}</td>
                        <td class="px-6 py-3">{{ $rotation->effective_date->format('d M Y') }}</td>
                        <td class="px-6 py-3">{{ $statuses[$rotation->status] ?? $rotation->status }}</td>
                        <td class="px-6 py-3 text-right"><a href="{{ route('foundation.rotation.show', $rotation->id) }}" class="font-bold text-indigo-600 hover:underline">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada rotasi staf.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-form.card>
</div>
@endsection
