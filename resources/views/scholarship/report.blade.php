@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Laporan Eksekutif Penyaluran Beasiswa & Bantuan</h1>
            <p class="text-xs text-gray-500 mt-1">Rekapitulasi realisasi penyaluran dana beasiswa PIP, BOS, & Yayasan YBWSA</p>
        </div>
        <a href="{{ route('financial.scholarships.programs') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Program Beasiswa</th>
                    <th class="py-3 px-4">Sumber Dana</th>
                    <th class="py-3 px-4">Total Penerima</th>
                    <th class="py-3 px-4">Nominal / Siswa</th>
                    <th class="py-3 px-4">Total Dana Tersalurkan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($programs as $p)
                @php
                    $disbursedCount = $p->applications->where('status', 'disbursed')->count();
                    $totalDisbursed = $disbursedCount * $p->amount_per_student;
                @endphp
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $p->name }}</td>
                    <td class="py-3 px-4 font-semibold uppercase text-xs text-indigo-600">{{ $p->source }}</td>
                    <td class="py-3 px-4 font-bold">{{ $disbursedCount }} Siswa</td>
                    <td class="py-3 px-4">Rp {{ number_format($p->amount_per_student, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 font-black text-emerald-600">Rp {{ number_format($totalDisbursed, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada data penyaluran beasiswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
