@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">{{ school_name() }} - Buat Dana Kas Kecil</h1>
    @include('treasury.partials.alerts')
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('treasury.petty-cash.funds.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nama Dana</label>
                <input type="text" name="fund_name" value="{{ old('fund_name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Pemegang Dana</label>
                <select name="custodian_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($custodians as $user)
                        <option value="{{ $user->id }}" @selected((string) old('custodian_id') === (string) $user->id)>{{ $user->name }} ({{ $user->role }})</option>
                    @endforeach
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Plafon Imprest (Rp)</label>
                <input type="number" step="0.01" min="1" name="imprest_amount" value="{{ old('imprest_amount') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
            </div>
            <p class="mt-4 text-xs text-gray-500">Dana dibuat dengan saldo Rp 0. Saldo terisi setelah pengajuan pengisian ulang disetujui, dan tidak dapat melebihi plafon imprest.</p>
            <div class="mt-6"><button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Simpan</button></div>
        </form>
    </div>
</div>
@endsection
