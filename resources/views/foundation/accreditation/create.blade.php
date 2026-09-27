@extends('layouts.app')
@section('content')
<div class="space-y-6 max-w-3xl">
    <x-page-header title="Tambah Data Akreditasi" />

    <x-form.card>
        @if ($units->isEmpty())
            <p class="text-sm text-gray-600 dark:text-gray-300">Belum ada unit yayasan. Tambahkan unit terlebih dahulu.</p>
        @else
            <form method="POST" action="{{ route('foundation.accreditation.store') }}" class="space-y-5">
                @csrf
                <x-form.select name="foundation_unit_id" label="Unit" :options="$units->mapWithKeys(fn ($u) => [$u->id => $u->unit_name.' ('.$u->unit_code.')'])" placeholder="Pilih unit" required />
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2"><x-form.input name="accreditation_body" label="Lembaga akreditasi" value="BAN-PDM" required /></div>
                    <x-form.input name="accreditation_grade" label="Peringkat" placeholder="A / Unggul" required />
                    <x-form.input name="accreditation_score" label="Nilai (0–100)" type="number" step="0.01" min="0" max="100" />
                    <x-form.input name="valid_from" label="Berlaku mulai" type="date" required />
                    <x-form.input name="valid_until" label="Berlaku sampai" type="date" required />
                    <div class="md:col-span-2"><x-form.input name="certificate_number" label="Nomor sertifikat" /></div>
                    <x-form.select name="status" label="Status" :options="$statuses" value="active" />
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('foundation.accreditation.index') }}" class="py-2.5 px-5 text-sm font-bold text-gray-600 dark:text-gray-300">Batal</a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Simpan</button>
                </div>
            </form>
        @endif
    </x-form.card>
</div>
@endsection
