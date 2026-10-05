@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 space-y-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                {{ __('Pelacak Sampah') }}
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-bold">{{ school_name() }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 p-4 text-sm font-bold text-emerald-700 dark:text-emerald-300">{{ session('success') }}</div>
    @endif

    @if ($canRecord)
        <form method="POST" action="{{ route('waste.store') }}" class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            @csrf
            <label class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ __('Tanggal') }}
                <input type="date" name="log_date" value="{{ old('log_date', now()->toDateString()) }}" class="mt-1 w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:border-gray-700" required>
            </label>
            <label class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ __('Kategori') }}
                <input type="text" name="waste_category" value="{{ old('waste_category') }}" class="mt-1 w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:border-gray-700" required>
            </label>
            <label class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ __('Berat (kg)') }}
                <input type="number" step="0.01" min="0.01" name="weight_kg" value="{{ old('weight_kg') }}" class="mt-1 w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:border-gray-700" required>
            </label>
            <label class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ __('Penanganan') }}
                <select name="disposal_method" class="mt-1 w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:border-gray-700" required>
                    @foreach ($methods as $method)
                        <option value="{{ $method }}" @selected(old('disposal_method') === $method)>{{ $method }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-black text-white hover:bg-emerald-700">{{ __('Catat') }}</button>
            @if ($errors->any())
                <p class="md:col-span-5 text-sm font-bold text-red-600">{{ $errors->first() }}</p>
            @endif
        </form>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        @forelse ($logs as $log)
            @if ($loop->first)
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500"><th class="py-2">{{ __('Tanggal') }}</th><th>{{ __('Kategori') }}</th><th>{{ __('Berat (kg)') }}</th><th>{{ __('Penanganan') }}</th><th>{{ __('Dicatat oleh') }}</th></tr></thead>
                    <tbody>
            @endif
                        <tr class="border-t border-gray-100 dark:border-gray-700 text-gray-800 dark:text-gray-200">
                            <td class="py-2">{{ $log->log_date->format('d M Y') }}</td>
                            <td>{{ $log->waste_category }}</td>
                            <td>{{ number_format((float) $log->weight_kg, 2, ',', '.') }}</td>
                            <td>{{ $log->disposal_method }}</td>
                            <td>{{ $log->recorder?->user?->name ?? '–' }}</td>
                        </tr>
            @if ($loop->last)
                    </tbody>
                </table>
            @endif
        @empty
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Belum ada log sampah.') }}</p>
        @endforelse
    </div>
</div>
@endsection
