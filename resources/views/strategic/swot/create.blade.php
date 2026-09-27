@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-5xl">
    <x-page-header title="Buat Analisis SWOT" subtitle="Tulis satu faktor per baris pada setiap kuadran." />

    <form method="POST" action="{{ route('strategic.swot.store') }}" class="space-y-6">
        @csrf
        <x-form.card>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2"><x-form.input name="title" label="Judul" required :placeholder="'Analisis SWOT Sekolah '.date('Y')" /></div>
                <x-form.input name="analysis_year" label="Tahun" type="number" :value="date('Y')" min="2000" max="2100" required />
                <div class="md:col-span-3"><x-form.select name="strategic_plan_id" label="Terkait RENSTRA (opsional)" :options="$plans->mapWithKeys(fn ($p) => [$p->id => $p->title])" placeholder="— Tidak terkait —" /></div>
            </div>
        </x-form.card>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($quadrants as $key => $label)
                <x-form.card>
                    <x-form.textarea :name="'items['.$key.']'" :label="$label" rows="6" />
                </x-form.card>
            @endforeach
        </div>

        <x-form.card>
            <x-form.textarea name="conclusion" label="Kesimpulan / arah strategi" rows="4" />
        </x-form.card>

        <div class="flex justify-end gap-3">
            <a href="{{ route('strategic.swot.index') }}" class="py-2.5 px-5 text-sm font-bold text-gray-600 dark:text-gray-300">Batal</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Simpan analisis</button>
        </div>
    </form>
</div>
@endsection
