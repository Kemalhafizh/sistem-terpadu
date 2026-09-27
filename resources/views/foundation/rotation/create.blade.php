@extends('layouts.app')
@section('content')
<div class="space-y-6 max-w-3xl">
    <x-page-header title="Usulan Rotasi Staf" subtitle="Usulan baru berstatus 'Diusulkan' sampai disetujui." />

    <x-form.card>
        @if ($units->count() < 2)
            <p class="text-sm text-gray-600 dark:text-gray-300">Rotasi membutuhkan minimal dua unit yayasan.</p>
        @else
            @php($unitOptions = $units->mapWithKeys(fn ($u) => [$u->id => $u->unit_name]))
            <form method="POST" action="{{ route('foundation.rotation.store') }}" class="space-y-5">
                @csrf
                <x-form.select name="user_id" label="Staf" :options="$staff->mapWithKeys(fn ($s) => [$s->id => $s->name.' ('.$s->role.')'])" placeholder="Pilih staf" required />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.select name="from_unit_id" label="Dari unit" :options="$unitOptions" placeholder="Pilih unit asal" required />
                    <x-form.select name="to_unit_id" label="Ke unit" :options="$unitOptions" placeholder="Pilih unit tujuan" required />
                    <x-form.select name="rotation_type" label="Jenis rotasi" :options="$types" required />
                    <x-form.input name="effective_date" label="Tanggal efektif" type="date" required />
                    <x-form.input name="end_date" label="Tanggal berakhir (penugasan sementara)" type="date" />
                </div>
                <x-form.textarea name="reason" label="Alasan" rows="3" required />
                <div class="flex justify-end gap-3">
                    <a href="{{ route('foundation.rotation.index') }}" class="py-2.5 px-5 text-sm font-bold text-gray-600 dark:text-gray-300">Batal</a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Simpan usulan</button>
                </div>
            </form>
        @endif
    </x-form.card>
</div>
@endsection
