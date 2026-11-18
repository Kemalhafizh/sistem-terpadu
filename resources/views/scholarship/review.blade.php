@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Seleksi & Penyaluran: {{ $program->name }}</h1>
            <p class="text-xs text-gray-500 mt-1">Total Kuota: {{ $summary['total_quota'] }} Siswa | Sisa Kuota: {{ $summary['quota_remaining'] }} | Total Dicairkan: <strong class="text-emerald-600">Rp {{ number_format($summary['total_disbursed_amount'], 0, ',', '.') }}</strong></p>
        </div>
        <a href="{{ route('financial.scholarships.programs') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Daftar Pemohon Beasiswa</h3>

        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Siswa</th>
                    <th class="py-3 px-4">Nilai Rapor/IPK</th>
                    <th class="py-3 px-4">Penghasilan Orang Tua</th>
                    <th class="py-3 px-4">Status Seleksi</th>
                    <th class="py-3 px-4 text-right">Aksi Review & Pencairan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($program->applications as $app)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $app->student->name }}</td>
                    <td class="py-3 px-4 font-semibold text-indigo-600">{{ $app->gpa_score ?? '-' }}</td>
                    <td class="py-3 px-4 font-medium">Rp {{ number_format($app->family_income ?? 0, 0, ',', '.') }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase
                            {{ $app->status === 'disbursed' ? 'bg-emerald-100 text-emerald-700' : ($app->status === 'approved' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ $app->status }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right space-x-1">
                        @if($app->status === 'pending' || $app->status === 'shortlisted')
                        <form action="{{ route('financial.scholarships.review_action', $app->id) }}" method="POST" class="inline-block">
                            @csrf
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold">Setujui Beasiswa</button>
                        </form>
                        <form action="{{ route('financial.scholarships.review_action', $app->id) }}" method="POST" class="inline-block">
                            @csrf
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold">Tolak</button>
                        </form>
                        @elseif($app->status === 'approved')
                        <a href="{{ route('financial.scholarships.disburse_form', $app->id) }}" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold inline-block">Cairkan Dana &rarr;</a>
                        @elseif($app->status === 'disbursed')
                        <span class="text-xs font-bold text-emerald-600">Terbuka (Ref: {{ $app->disbursement->reference_number ?? 'DISB-OK' }})</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada berkas pendaftaran beasiswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
