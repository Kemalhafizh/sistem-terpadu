@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Counselor Risk Heatmap (Guru BK / Konselor)</h1>
            <p class="text-xs text-gray-500 mt-1">Analitik deteksi dini risiko kesehatan mental siswa SMA ISA 1 Semarang</p>
        </div>
        <a href="{{ route('growth.wellbeing.screenings') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <!-- Risk Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Risiko Rendah (Low)</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $heatmap['by_risk']['low'] }} Siswa</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Risiko Sedang (Moderate)</span>
            <p class="text-2xl font-black text-amber-500 mt-1">{{ $heatmap['by_risk']['moderate'] }} Siswa</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Risiko Tinggi (High)</span>
            <p class="text-2xl font-black text-orange-600 mt-1">{{ $heatmap['by_risk']['high'] }} Siswa</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Kritis (Critical Followup)</span>
            <p class="text-2xl font-black text-rose-600 mt-1">{{ $heatmap['by_risk']['critical'] }} Siswa</p>
        </div>
    </div>

    <!-- Heatmap Table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Hasil Skrining & Tindak Lanjut Konseling</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Siswa</th>
                    <th class="py-3 px-4">Judul Skrining</th>
                    <th class="py-3 px-4">Skor Total</th>
                    <th class="py-3 px-4">Tingkat Risiko</th>
                    <th class="py-3 px-4">Tindak Lanjut BK</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($heatmap['responses'] as $resp)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $resp->student->name }}</td>
                    <td class="py-3 px-4 text-xs font-semibold">{{ $resp->screening->title }}</td>
                    <td class="py-3 px-4 font-bold">{{ $resp->total_score }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase
                            {{ $resp->risk_level === 'critical' ? 'bg-rose-100 text-rose-700' : ($resp->risk_level === 'high' ? 'bg-orange-100 text-orange-700' : 'bg-emerald-100 text-emerald-700') }}">
                            {{ $resp->risk_level }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-xs">
                        @if($resp->followup_required)
                        <span class="font-bold text-rose-600">Butuh Konseling Intensif</span>
                        @else
                        <span class="text-gray-400">Normal</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada respon skrining yang tercatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
