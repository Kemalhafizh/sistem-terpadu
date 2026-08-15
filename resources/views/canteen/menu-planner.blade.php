@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"></path></svg>
            Perencanaan Menu Kantin SMA Islam Sultan Agung 1
        </h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            @forelse($plans ?? [] as $plan)
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 p-6 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan->menu->meal_name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ \Carbon\Carbon::parse($plan->serve_date)->format('d M Y') }} • {{ ucfirst($plan->meal_time) }}
                    </p>
                </div>
                <div class="text-right">
                    <span class="block text-xl font-black text-rose-600">{{ $plan->menu->calories }} kcal</span>
                    <span class="text-xs text-gray-500">Kuota: {{ $plan->preparation_quota }} porsi</span>
                </div>
            </div>
            @empty
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 p-8 text-center text-gray-500">
                Belum ada menu yang direncanakan.
            </div>
            @endforelse
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Rencanakan Menu Baru</h3>
            <form action="{{ route('canteen.store_menu') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                    <input type="date" name="serve_date" required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-rose-500 focus:ring-rose-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Menu</label>
                    <select name="canteen_menu_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-rose-500 focus:ring-rose-500">
                        @foreach($menus ?? [] as $menu)
                            <option value="{{ $menu->id }}">{{ $menu->meal_name }} ({{ $menu->calories }} kcal)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Waktu</label>
                    <select name="meal_time" required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-rose-500 focus:ring-rose-500">
                        <option value="breakfast">Sarapan</option>
                        <option value="lunch">Makan Siang</option>
                        <option value="snack">Cemilan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kuota Porsi</label>
                    <input type="number" name="preparation_quota" value="50" required class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-rose-500 focus:ring-rose-500">
                </div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-rose-600 hover:bg-rose-700">
                    Simpan Rencana
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
