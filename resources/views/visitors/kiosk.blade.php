@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 p-8 text-center">
        <svg class="w-16 h-16 text-indigo-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-2">Kios Mandiri Pengunjung</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-8">Selamat datang di {{ school_name() }}</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="{{ route('visitors.pre_register') }}" class="block p-6 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors">
                <h3 class="text-xl font-bold text-indigo-900 dark:text-indigo-300 mb-2">Registrasi Baru</h3>
                <p class="text-sm text-indigo-700 dark:text-indigo-400">Daftar sebagai pengunjung baru</p>
            </a>
            <div class="block p-6 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                <h3 class="text-xl font-bold text-emerald-900 dark:text-emerald-300 mb-2">Check-In Terdaftar</h3>
                <p class="text-sm text-emerald-700 dark:text-emerald-400">Pindai kode QR atau masukkan nomor badge</p>
            </div>
        </div>
    </div>
</div>
@endsection
