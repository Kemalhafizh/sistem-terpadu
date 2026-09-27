@php
    $treasuryLinks = [
        'treasury.accounts.index' => ['Rekening Bank', 'treasury.accounts.*'],
        'treasury.position.dashboard' => ['Posisi Kas', 'treasury.position.*'],
        'treasury.reconciliation.index' => ['Rekonsiliasi', 'treasury.reconciliation.*'],
        'treasury.petty-cash.index' => ['Kas Kecil', 'treasury.petty-cash.*'],
        'treasury.vendor.index' => ['Pembayaran Vendor', 'treasury.vendor.*'],
    ];
@endphp
<nav class="mb-6 flex flex-wrap gap-2 text-sm">
    @foreach($treasuryLinks as $route => [$label, $pattern])
        <a href="{{ route($route) }}"
           class="px-3 py-1.5 rounded-full font-bold {{ request()->routeIs($pattern) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200' }}">{{ $label }}</a>
    @endforeach
</nav>
