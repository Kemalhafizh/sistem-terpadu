@props([
    'title' => 'Belum Ada Data',
    'description' => 'Data akan muncul di sini saat aktivitas telah dimulai.',
])

<div class="flex flex-col items-center justify-center p-8 text-center">
    <div class="w-12 h-12 mb-3 text-slate-400 dark:text-slate-500">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
        </svg>
    </div>
    <h3 class="text-base font-bold text-slate-700 dark:text-slate-200">{{ $title }}</h3>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm">{{ $description }}</p>
    @if (!$slot->isEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
