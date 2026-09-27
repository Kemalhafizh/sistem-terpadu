@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-3xl">
    <x-page-header title="Buat Rencana Strategis (RENSTRA)" subtitle="Visi, misi dan periode rencana. Sasaran dan KPI ditambahkan setelah RENSTRA disimpan." />

    <x-form.card>
        <form method="POST" action="{{ route('strategic.plan.store') }}" class="space-y-5">
            @csrf
            <x-form.input name="title" label="Judul" required placeholder="RENSTRA 2026–2030" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form.input name="start_year" label="Tahun mulai" type="number" :value="date('Y')" min="2000" max="2100" required />
                <x-form.input name="end_year" label="Tahun selesai" type="number" :value="date('Y') + 4" min="2000" max="2100" required />
                <x-form.select name="status" label="Status" :options="['draft' => 'Draf', 'active' => 'Aktif']" value="draft" />
            </div>
            <x-form.textarea name="vision" label="Visi" rows="3" required />
            <x-form.textarea name="mission" label="Misi" rows="5" required hint="Satu misi per baris." />
            <div class="flex justify-end gap-3">
                <a href="{{ route('strategic.plan.index') }}" class="py-2.5 px-5 text-sm font-bold text-gray-600 dark:text-gray-300">Batal</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Simpan</button>
            </div>
        </form>
    </x-form.card>
</div>
@endsection
