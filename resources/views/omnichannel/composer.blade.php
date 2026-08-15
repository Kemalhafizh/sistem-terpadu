@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
            Tulis Pesan - SMA Islam Sultan Agung 1 Semarang
        </h1>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
        <form>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Penerima</label>
                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm text-gray-600 dark:text-gray-300" placeholder="Semua Orang Tua Kelas 10">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Saluran Komunikasi</label>
                <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm text-gray-600 dark:text-gray-300">
                    <option>Email & WhatsApp</option>
                    <option>Hanya Email</option>
                    <option>Hanya WhatsApp</option>
                    <option>SMS</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Pesan</label>
                <textarea rows="6" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm text-gray-600 dark:text-gray-300"></textarea>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Kirim Pesan</button>
        </form>
    </div>
</div>
@endsection
