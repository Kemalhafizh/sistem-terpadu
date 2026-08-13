@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">Buat Pesan Darurat</h1>
        <form action="{{ route('crisis.broadcast.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Judul</label>
                <input type="text" name="title" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Pesan</label>
                <textarea name="message" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Prioritas</label>
                <select name="priority" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    <option value="warning">Warning</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <input type="hidden" name="target_groups[]" value="all">
            <input type="hidden" name="channels[]" value="sms">
            
            <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg font-bold hover:bg-amber-700">Kirim Pesan</button>
        </form>
    </div>
</div>
@endsection