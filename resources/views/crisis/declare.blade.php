@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">Deklarasi Darurat</h1>
        <form action="{{ route('crisis.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Judul Insiden</label>
                <input type="text" name="title" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Tipe</label>
                <select name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    <option value="gempa_bumi">Gempa Bumi</option>
                    <option value="kebakaran">Kebakaran</option>
                    <option value="banjir">Banjir</option>
                    <option value="pandemi">Pandemi</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Tingkat Keparahan</label>
                <select name="severity" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    <option value="level_1_minor">Minor</option>
                    <option value="level_2_moderate">Moderate</option>
                    <option value="level_3_major">Major</option>
                    <option value="level_4_critical">Critical</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Deskripsi</label>
                <textarea name="description" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required></textarea>
            </div>
            <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-lg font-bold hover:bg-rose-700">Simpan & Deklarasikan</button>
        </form>
    </div>
</div>
@endsection