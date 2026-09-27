@extends('layouts.app')
@section('content')
<div class="space-y-6">
    <x-page-header :title="$group->name" :subtitle="($types[$group->type] ?? $group->type).' · Koordinator '.($group->coordinator?->name ?? '-')">
        <x-slot name="actions">
            <a href="{{ route('knowledge.plc.index') }}" class="text-sm font-bold text-indigo-600 hover:underline">&larr; Semua komunitas</a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            @if ($group->description)
                <x-form.card title="Tentang komunitas"><p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $group->description }}</p></x-form.card>
            @endif
            <x-form.card title="Diskusi terbaru">
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($group->discussions as $discussion)
                        <li class="py-3">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $discussion->title }}</p>
                            <p class="text-xs text-gray-500">{{ $discussion->created_at?->diffForHumans() }} · {{ $discussion->reply_count }} balasan</p>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-gray-500">Belum ada diskusi.</li>
                    @endforelse
                </ul>
            </x-form.card>
        </div>
        <x-form.card title="Anggota ({{ $group->members->count() }})">
            <ul class="space-y-2 text-sm">
                @foreach ($group->members as $member)
                    <li class="flex justify-between"><span class="text-gray-800 dark:text-gray-200">{{ $member->user?->name ?? '-' }}</span><span class="text-xs text-gray-500">{{ $member->role === 'coordinator' ? 'Koordinator' : 'Anggota' }}</span></li>
                @endforeach
            </ul>
        </x-form.card>
    </div>
</div>
@endsection
