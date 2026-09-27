@extends('layouts.app')
@section('content')
<div class="space-y-6 max-w-3xl">
    <x-page-header :title="'Akreditasi '.($record->unit?->unit_name ?? '-')" :subtitle="$record->accreditation_body">
        <x-slot name="actions">
            <a href="{{ route('foundation.accreditation.index') }}" class="text-sm font-bold text-indigo-600 hover:underline">&larr; Semua akreditasi</a>
        </x-slot>
    </x-page-header>

    <x-form.card>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-xs font-bold uppercase text-gray-500">Peringkat</dt><dd class="text-2xl font-black text-gray-900 dark:text-white">{{ $record->accreditation_grade }}</dd></div>
            <div><dt class="text-xs font-bold uppercase text-gray-500">Nilai</dt><dd class="text-2xl font-black text-gray-900 dark:text-white">{{ $record->accreditation_score !== null ? (float) $record->accreditation_score : '-' }}</dd></div>
            <div><dt class="text-xs font-bold uppercase text-gray-500">Masa berlaku</dt><dd>{{ $record->valid_from->format('d M Y') }} – {{ $record->valid_until->format('d M Y') }}</dd></div>
            <div><dt class="text-xs font-bold uppercase text-gray-500">Status</dt><dd>{{ $statuses[$record->status] ?? $record->status }}{{ $record->valid_until->isPast() ? ' (masa berlaku habis)' : '' }}</dd></div>
            <div><dt class="text-xs font-bold uppercase text-gray-500">Nomor sertifikat</dt><dd>{{ $record->certificate_number ?? '-' }}</dd></div>
            <div><dt class="text-xs font-bold uppercase text-gray-500">Unit</dt><dd>{{ $record->unit?->unit_name }} {{ $record->unit ? '('.$record->unit->unit_code.')' : '' }}</dd></div>
        </dl>
    </x-form.card>
</div>
@endsection
