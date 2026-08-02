@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/></svg>
                Dapodik/EMIS Auto-Export & Validation Engine
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sinkronisasi Data Kemdikbudristek & Kemenag Otomatis SMA ISA 1 Semarang</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Riwayat Ekspor XML Dapodik/EMIS</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Tahun Ajaran</th>
                    <th class="py-3 px-4">Semester</th>
                    <th class="py-3 px-4">Jumlah Record</th>
                    <th class="py-3 px-4">Status Validasi</th>
                    <th class="py-3 px-4 text-right">Tanggal Ekspor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($exports as $ex)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $ex->academic_year }}</td>
                    <td class="py-3 px-4 uppercase text-xs font-semibold">{{ $ex->semester }}</td>
                    <td class="py-3 px-4 font-mono font-bold text-indigo-600">{{ number_format($ex->records_processed) }} Item</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-emerald-100 text-emerald-700">
                            {{ strtoupper($ex->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right text-xs font-mono">{{ $ex->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada riwayat ekspor Dapodik.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
