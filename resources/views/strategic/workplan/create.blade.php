@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-3xl">
    <x-page-header title="Buat Rencana Kerja Sekolah (RKS)" subtitle="RKS tahunan selalu merupakan turunan dari satu RENSTRA." />

    <x-form.card>
        @if ($plans->isEmpty())
            <p class="text-sm text-gray-600 dark:text-gray-300">Buat RENSTRA terlebih dahulu sebelum menyusun RKS.</p>
            <a href="{{ route('strategic.plan.create') }}" class="inline-flex mt-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Buat RENSTRA</a>
        @else
            <form method="POST" action="{{ route('strategic.workplan.store') }}" class="space-y-5">
                @csrf
                <x-form.select name="strategic_plan_id" label="RENSTRA induk" :options="$plans->mapWithKeys(fn ($p) => [$p->id => $p->title.' ('.$p->start_year.'–'.$p->end_year.')'])" placeholder="Pilih RENSTRA" required />
                <x-form.input name="title" label="Judul RKS" required :placeholder="'RKS Tahun '.date('Y')" />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="fiscal_year" label="Tahun anggaran" type="number" :value="date('Y')" min="2000" max="2100" required />
                    <x-form.input name="total_budget" label="Total anggaran (Rp)" type="number" min="0" step="1000" required />
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('strategic.workplan.index') }}" class="py-2.5 px-5 text-sm font-bold text-gray-600 dark:text-gray-300">Batal</a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Simpan</button>
                </div>
            </form>
        @endif
    </x-form.card>
</div>
@endsection
