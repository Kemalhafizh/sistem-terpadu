@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-page-header :title="'Akreditasi '.school_name()" subtitle="Status akreditasi setiap unit dan masa berlakunya.">
        <x-slot name="actions">
            <a href="{{ route('foundation.accreditation.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">+ Data Akreditasi</a>
        </x-slot>
    </x-page-header>

    <x-form.card class="!p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-500">
                <tr><th class="text-left px-6 py-3">Unit</th><th class="text-left px-6 py-3">Lembaga</th><th class="text-left px-6 py-3">Peringkat</th><th class="text-left px-6 py-3">Berlaku sampai</th><th class="text-left px-6 py-3">Status</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($records as $record)
                    @php($soon = $record->valid_until->isFuture() && $record->valid_until->lte(now()->addMonths(6)))
                    <tr>
                        <td class="px-6 py-3 font-bold text-gray-900 dark:text-white">{{ $record->unit?->unit_name ?? '-' }}</td>
                        <td class="px-6 py-3">{{ $record->accreditation_body }}</td>
                        <td class="px-6 py-3 font-black">{{ $record->accreditation_grade }}</td>
                        <td class="px-6 py-3 {{ $record->valid_until->isPast() ? 'text-rose-600 font-bold' : ($soon ? 'text-amber-600 font-bold' : '') }}">{{ $record->valid_until->format('d M Y') }}</td>
                        <td class="px-6 py-3">{{ $statuses[$record->status] ?? $record->status }}</td>
                        <td class="px-6 py-3 text-right"><a href="{{ route('foundation.accreditation.show', $record->id) }}" class="font-bold text-indigo-600 hover:underline">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada data akreditasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-form.card>
</div>
@endsection
