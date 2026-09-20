@props([
    'headers' => [],
])

<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
        <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
            <tr>
                @foreach ($headers as $h)
                    <th class="px-5 py-3.5">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            {{ $slot }}
        </tbody>
    </table>
</div>
