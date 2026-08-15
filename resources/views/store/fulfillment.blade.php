@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            Pemenuhan Pesanan Koperasi
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($orders ?? [] as $order)
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 p-6">
            <div class="flex justify-between items-center mb-4">
                <span class="font-mono text-sm font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</span>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                    {{ ucfirst($order->fulfillment_status) }}
                </span>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Total: Rp {{ number_format($order->total_amount, 0, ',', '.') }}
            </div>
            <button class="w-full py-2 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                Tandai Selesai
            </button>
        </div>
        @empty
        <div class="col-span-2 text-center text-gray-500 py-8">
            Tidak ada pesanan yang perlu dipenuhi.
        </div>
        @endforelse
    </div>
</div>
@endsection
