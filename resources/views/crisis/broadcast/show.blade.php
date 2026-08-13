@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">Detail Broadcast</h1>
        <div class="text-gray-600 dark:text-gray-300">
            <p><strong>Judul:</strong> {{ $broadcast->title }}</p>
            <p><strong>Pesan:</strong> {{ $broadcast->message }}</p>
            <p><strong>Total Penerima:</strong> {{ $stats['total'] }}</p>
        </div>
    </div>
</div>
@endsection