@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm max-w-2xl mx-auto">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-2 flex items-center gap-2">
            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            Laporan Anti-Perundungan
        </h1>
        <p class="text-sm text-gray-500 mb-6">Identitas Anda dijamin kerahasiaannya. Silakan laporkan kejadian dengan detail.</p>
        
        <form action="{{ route('crisis.bullying.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Tipe Perundungan</label>
                <select name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    <option value="verbal">Verbal</option>
                    <option value="fisik">Fisik</option>
                    <option value="cyber">Cyber</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Tingkat Keparahan</label>
                <select name="severity" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    <option value="ringan">Ringan</option>
                    <option value="sedang">Sedang</option>
                    <option value="berat">Berat</option>
                    <option value="sangat_berat">Sangat Berat</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Deskripsi Kejadian</label>
                <textarea name="description" rows="5" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required></textarea>
            </div>
            <input type="hidden" name="is_anonymous" value="1">
            <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700">Kirim Laporan Rahasia</button>
        </form>
    </div>
</div>
@endsection