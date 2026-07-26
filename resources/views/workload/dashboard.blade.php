@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Teacher Workload Balancing & Capacity Dashboard
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Analisis Pemerataan Jam Mengajar & Tugas Tambahan Guru SMA ISA 1 Semarang</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('hc.workload.capacity_report') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">
                Laporan Kapasitas Beban
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Form Calculate Workload -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Hitung Kalkulasi Beban Kerja Guru</h3>
        <form action="{{ route('hc.workload.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Guru</label>
                <select name="employee_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jam Mengajar Mingguan (JP)</label>
                <input type="number" name="teaching_hours_per_week" value="24" min="1" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">+ Kalkulasi Workload Guru</button>
            </div>
        </form>
    </div>

    <!-- Workload Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($workloads as $w)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-3">
            <div class="flex justify-between items-center">
                <span class="px-2.5 py-1 text-xs font-black rounded-lg uppercase
                    {{ $w->status === 'balanced' ? 'bg-emerald-100 text-emerald-700' : ($w->status === 'over' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                    STATUS: {{ strtoupper($w->status) }}
                </span>
                <span class="text-xs font-bold text-gray-400">Score: {{ $w->total_workload_score }} Poin</span>
            </div>
            <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $w->employee->name }}</h3>
            <p class="text-xs text-gray-500">Beban Mengajar Tatap Muka: <strong class="text-indigo-600 font-bold text-sm">{{ $w->teaching_hours_per_week }} JP / Minggu</strong></p>
            <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ min(100, ($w->total_workload_score / 40) * 100) }}%"></div>
            </div>
        </div>
        @empty
        <div class="col-span-2 py-12 text-center text-gray-400">Belum ada data beban kerja guru yang dikalkulasi.</div>
        @endforelse
    </div>
</div>
@endsection
