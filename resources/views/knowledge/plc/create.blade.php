@extends('layouts.app')
@section('content')
<div class="space-y-6 max-w-3xl">
    <x-page-header title="Buat Komunitas Praktisi" subtitle="Anda otomatis menjadi koordinator komunitas ini." />

    <x-form.card>
        <form method="POST" action="{{ route('knowledge.plc.store') }}" class="space-y-5">
            @csrf
            <x-form.input name="name" label="Nama komunitas" placeholder="MGMP Matematika" required />
            <x-form.select name="type" label="Jenis" :options="$types" required />
            <x-form.textarea name="description" label="Deskripsi & tujuan" rows="4" />
            <div class="flex justify-end gap-3">
                <a href="{{ route('knowledge.plc.index') }}" class="py-2.5 px-5 text-sm font-bold text-gray-600 dark:text-gray-300">Batal</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Simpan</button>
            </div>
        </form>
    </x-form.card>
</div>
@endsection
