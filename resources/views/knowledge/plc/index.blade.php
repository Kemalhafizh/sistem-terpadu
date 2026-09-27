@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-page-header :title="school_name().' - Komunitas Praktisi'" subtitle="Professional Learning Community: forum guru berbagi praktik baik.">
        <x-slot name="actions">
            <a href="{{ route('knowledge.plc.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">+ Komunitas Baru</a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse ($groups as $group)
            <a href="{{ route('knowledge.plc.show', $group->id) }}" class="block bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 hover:border-indigo-300">
                <p class="text-xs font-bold uppercase text-indigo-600">{{ $types[$group->type] ?? $group->type }}</p>
                <p class="font-black text-gray-900 dark:text-white mt-1">{{ $group->name }}</p>
                @if ($group->description)<p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $group->description }}</p>@endif
                <p class="text-xs text-gray-500 mt-4">Koordinator {{ $group->coordinator?->name ?? '-' }} · {{ $group->members_count }} anggota · {{ $group->discussions_count }} diskusi</p>
            </a>
        @empty
            <x-form.card class="md:col-span-2 xl:col-span-3"><p class="text-sm text-gray-500 text-center">Belum ada komunitas praktisi.</p></x-form.card>
        @endforelse
    </div>
</div>
@endsection
