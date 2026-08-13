@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">Tambah Protokol Darurat</h1>
        <form action="{{ route('crisis.protocols.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Nama Protokol</label>
                <input type="text" name="name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Tipe Krisis</label>
                <select name="crisis_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    <option value="gempa_bumi">Gempa Bumi</option>
                    <option value="kebakaran">Kebakaran</option>
                </select>
            </div>
            <!-- Dummy inputs for JSON fields -->
            <input type="hidden" name="steps[]" value="Langkah 1">
            <input type="hidden" name="emergency_contacts[]" value="112">
            <input type="hidden" name="assembly_points[]" value="Lapangan Utama">
            
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700">Simpan</button>
        </form>
    </div>
</div>
@endsection