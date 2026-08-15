@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            Manajemen Inventaris Koperasi
        </h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50">
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">SKU</th>
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Nama Barang</th>
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Kategori</th>
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Stok</th>
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Harga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                    @forelse($items ?? [] as $item)
                        <tr>
                            <td class="px-6 py-4 font-mono">{{ $item->sku }}</td>
                            <td class="px-6 py-4">{{ $item->item_name }}</td>
                            <td class="px-6 py-4">{{ ucfirst($item->category) }}</td>
                            <td class="px-6 py-4">
                                <span class="{{ $item->current_stock < 10 ? 'text-rose-600 dark:text-rose-400 font-bold' : '' }}">
                                    {{ $item->current_stock }}
                                </span>
                            </td>
                            <td class="px-6 py-4">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center">Tidak ada barang di inventaris.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
