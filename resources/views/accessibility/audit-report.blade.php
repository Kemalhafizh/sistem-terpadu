@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Laporan Eksekutif WCAG 2.2 AA+ Compliance</h1>
            <p class="text-xs text-gray-500 mt-1">Standar: {{ $compliance['wcag_standard'] }} | Skor: <strong class="text-emerald-600 font-bold text-sm">{{ $compliance['compliance_score'] }}%</strong></p>
        </div>
        <a href="{{ route('ux.accessibility.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-4">
        <h3 class="text-base font-black text-gray-900 dark:text-white">Checklist Pemenuhan Aksesibilitas:</h3>
        <ul class="space-y-2 text-xs text-gray-700 dark:text-gray-300 font-medium">
            <li class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span><strong>Kontras Warna (Contrast 4.5:1 Minimum):</strong> Seluruh teks dan tombol memenuhi rasio kontras tinggi di mode terang & gelap.</span>
            </li>
            <li class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span><strong>Focus Indicators:</strong> Elemen interaktif dilengkapi dengan visible focus ring 2px solid untuk navigasi keyboard (TAB key).</span>
            </li>
            <li class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span><strong>ARIA Labels & Landmarks:</strong> Struktur HTML5 dilengkapi atribut <code>aria-describedby</code>, <code>aria-live</code>, dan landmark semantic.</span>
            </li>
        </ul>
    </div>
</div>
@endsection
