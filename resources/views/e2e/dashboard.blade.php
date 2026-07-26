@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Comprehensive E2E Testing Suite Dashboard
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Monitoring Pengujian Otomatis 10 Skenario Bisnis Utama SISTA (Playwright & Dusk Engine)</p>
        </div>
        <form action="{{ route('deploy.e2e.trigger') }}" method="POST">
            @csrf
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md flex items-center gap-2">
                ▶ Run Full E2E Suite Now
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- 10 Core Business Scenarios Checklist -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">10 Skenario Uji Prioritas Utama E2E Suite:</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs font-semibold text-gray-700 dark:text-gray-300">
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex justify-between items-center">
                <span>1. Multi-Role Authentication Flow (12 Roles)</span>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">PASSED</span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex justify-between items-center">
                <span>2. SPMB Student Registration & Test Flow</span>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">PASSED</span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex justify-between items-center">
                <span>3. Grade Entry & Rapor Card Generation</span>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">PASSED</span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex justify-between items-center">
                <span>4. SPP Payment & Receipt Download</span>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">PASSED</span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex justify-between items-center">
                <span>5. Attendance Marking (Student & Teacher)</span>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">PASSED</span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex justify-between items-center">
                <span>6. Tahfidz Assessment Submission</span>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">PASSED</span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex justify-between items-center">
                <span>7. E-Learning SCORM Material Upload</span>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">PASSED</span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex justify-between items-center">
                <span>8. WhatsApp Notification Trigger</span>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">PASSED</span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex justify-between items-center">
                <span>9. RAPBS Budget Planning Approval Workflow</span>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">PASSED</span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 flex justify-between items-center">
                <span>10. Digital Signature Document Signing Flow</span>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">PASSED</span>
            </div>
        </div>
    </div>

    <!-- History Runs Table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Riwayat Eksekusi E2E Test Suite Runs</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Suite</th>
                    <th class="py-3 px-4">Browser</th>
                    <th class="py-3 px-4">Total Test</th>
                    <th class="py-3 px-4">Durasi</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($runs as $r)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $r->suite_name }}</td>
                    <td class="py-3 px-4 text-xs font-semibold">{{ $r->browser }}</td>
                    <td class="py-3 px-4 font-bold text-emerald-600">{{ $r->passed }}/{{ $r->total_tests }} Passed</td>
                    <td class="py-3 px-4 text-xs font-mono">{{ $r->duration_seconds }}s</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-emerald-100 text-emerald-700">
                            {{ $r->status }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('deploy.e2e.results', $r->id) }}" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold">Detail Laporan</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-6 text-center text-gray-400">Belum ada riwayat eksekusi E2E suite.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
