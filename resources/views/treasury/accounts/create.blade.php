@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">{{ school_name() }} - Tambah Rekening Bank</h1>
    @include('treasury.partials.alerts')
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('treasury.accounts.store') }}" method="POST">
            @include('treasury.accounts._form')
        </form>
    </div>
</div>
@endsection
