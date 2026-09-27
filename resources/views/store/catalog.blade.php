@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            Katalog Koperasi {{ school_short_name() }}
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Pemesanan seragam dan atribut sekolah</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($items ?? [] as $item)
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                        {{ ucfirst($item->category) }}
                    </span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ $item->item_name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Stok: {{ $item->current_stock }} | SKU: {{ $item->sku }}</p>
                <button class="w-full flex justify-center py-2 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Tambah ke Keranjang
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
