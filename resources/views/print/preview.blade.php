@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center no-print">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Print-Optimized Engine Preview</h1>
            <p class="text-xs text-gray-500 mt-1">Format Kertas: <strong>{{ $layout['paper_size'] }} ({{ strtoupper($layout['orientation']) }})</strong> | Watermark: {{ $layout['watermark'] }}</p>
        </div>
        <button onclick="window.print()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- Printable Paper Preview Sandbox -->
    <div class="bg-white p-8 rounded-3xl border border-gray-200 shadow-xl space-y-6 max-w-3xl mx-auto">
        <!-- Print Header Kop Surat -->
        <div class="print-header text-center border-b-2 border-gray-900 pb-4">
            <h1 class="text-xl font-black text-gray-900 uppercase">YAYASAN BADAN WAKAF SULTAN AGUNG</h1>
            <h2 class="text-lg font-extrabold text-gray-800 uppercase">SMA ISLAM SULTAN AGUNG 1 SEMARANG</h2>
            <p class="text-xs text-gray-600 font-semibold">Jl. Mataram No. 657, Semarang | Telp: (024) 8312137 | Website: sultanagung1.sch.id</p>
        </div>

        <div class="text-center space-y-1">
            <h3 class="text-base font-black text-gray-900 uppercase tracking-wider underline">{{ $layout['title'] }}</h3>
            <p class="text-xs text-gray-500 font-mono">Nomor Surat / Dokumen: 450/SMA-ISA1/RAPOR/2026</p>
        </div>

        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="bg-gray-100 font-bold uppercase border-b">
                    <th class="py-2 px-3 border">No</th>
                    <th class="py-2 px-3 border">Mata Pelajaran</th>
                    <th class="py-2 px-3 border">Nilai Akhir (Nilai)</th>
                    <th class="py-2 px-3 border">Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 font-medium">
                <tr>
                    <td class="py-2 px-3 border font-bold">1</td>
                    <td class="py-2 px-3 border font-bold">Pendidikan Agama Islam</td>
                    <td class="py-2 px-3 border font-black text-emerald-600">92 (A)</td>
                    <td class="py-2 px-3 border">Sangat baik dalam pemahaman Fiqih & Tahfidz Al-Qur'an Juz 30.</td>
                </tr>
                <tr>
                    <td class="py-2 px-3 border font-bold">2</td>
                    <td class="py-2 px-3 border font-bold">Matematika Tingkat Lanjut</td>
                    <td class="py-2 px-3 border font-black text-emerald-600">88 (B+)</td>
                    <td class="py-2 px-3 border">Terampil dalam analisis sifat-sifat eksponen & kalkulus dasar.</td>
                </tr>
            </tbody>
        </table>

        <div class="pt-8 flex justify-between items-end text-xs">
            <div class="text-center space-y-12">
                <p>Orang Tua / Wali Murid</p>
                <p class="font-bold underline">(.......................................)</p>
            </div>
            <div class="text-center space-y-12">
                <p>Semarang, 26 Juli 2026<br>Wali Kelas X MIPA 1</p>
                <p class="font-bold underline">NIP. 19850615 201001 1 002</p>
            </div>
        </div>
    </div>
</div>
@endsection
