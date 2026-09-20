@props([
    'type' => 'success',
    'message' => '',
])

@php
    $styles = match ($type) {
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950/50 dark:border-emerald-800 dark:text-emerald-300',
        'error' => 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/50 dark:border-rose-800 dark:text-rose-300',
        'warning' => 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-950/50 dark:border-amber-800 dark:text-amber-300',
        default => 'bg-slate-50 border-slate-200 text-slate-800 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200',
    };
@endphp

<div x-data="{ show: true }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="flex items-center justify-between rounded-xl border p-4 shadow-md {{ $styles }}">
    <div class="flex items-center space-x-2 text-sm font-medium">
        <span>{{ $slot->isEmpty() ? $message : $slot }}</span>
    </div>
    <button @click="show = false" class="text-xs opacity-60 hover:opacity-100 ml-4 font-bold">&times;</button>
</div>
