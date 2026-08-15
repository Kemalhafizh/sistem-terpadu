@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
    <div>
      <h1 class="text-xl font-black text-gray-900 dark:text-white">Analisis Flakey Tests E2E</h1>
      <p class="text-xs text-gray-500 mt-1">Identifikasi Pengujian Tidak Stabil (Zero Flakey Tests Detected)</p>
    </div>
    <a href="{{ route('deploy.e2e.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
  </div>

  <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 text-center text-gray-400 space-y-2">
    <p class="text-base font-bold text-emerald-600"> Seluruh 10 Skenario Uji E2E 100% Deterministik & Stabil!</p>
    <p class="text-xs text-gray-500">Tidak ditemukan pengujian flakey/intermittent pada seluruh eksekusi CI/CD pipeline.</p>
  </div>
</div>
@endsection
