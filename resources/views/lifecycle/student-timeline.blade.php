@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Vertical Lifecycle Timeline: {{ $student->name }}</h1>
            <p class="text-xs text-gray-500 mt-1">Status Saat Ini: <strong class="uppercase text-emerald-600 text-sm font-black">{{ $timelineData['current_status'] }}</strong></p>
        </div>
        <a href="{{ route('growth.lifecycle.retention') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Dashboard Retensi</a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Add Lifecycle Event Form -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Update Status Lifecycle Siswa</h3>
        <form action="{{ route('growth.lifecycle.store_event', $student->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe Event Lifecycle</label>
                <select name="event_type" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    <option value="enrolled">Enrolled (Terdaftar Aktif)</option>
                    <option value="promoted">Promoted (Kenaikan Kelas)</option>
                    <option value="retained">Retained (Tinggal Kelas)</option>
                    <option value="graduated">Graduated (Kelulusan)</option>
                    <option value="transferred_out">Transferred Out (Pindah Sekolah)</option>
                    <option value="dropped_out">Dropped Out (Putus Sekolah)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status Tujuan (To Status)</label>
                <input type="text" name="to_status" value="Active Student" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Catatan / Alasan Perubahan</label>
                <input type="text" name="notes" placeholder="Lulus ujian sekolah & dinyatakan naik ke kelas XI" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md">+ Record Lifecycle Event</button>
            </div>
        </form>
    </div>

    <!-- Timeline UI -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-6">
        <h3 class="text-base font-black text-gray-900 dark:text-white">Jejak Histori Lifecycle Events</h3>

        <div class="relative border-l-2 border-emerald-500 ml-4 space-y-6 pl-6">
            @forelse($timelineData['events'] as $ev)
            <div class="relative">
                <div class="absolute -left-[31px] top-1 w-4 h-4 bg-emerald-500 rounded-full border-4 border-white dark:border-gray-800"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-lg uppercase bg-emerald-100 text-emerald-700">
                            {{ strtoupper($ev->event_type) }}
                        </span>
                        <h4 class="text-base font-black text-gray-900 dark:text-white mt-1">Status: {{ $ev->to_status }}</h4>
                        <p class="text-xs text-gray-500">{{ $ev->notes ?? 'Diproses oleh ' . $ev->triggerUser->name }}</p>
                    </div>
                    <span class="text-xs font-bold text-gray-400">{{ $ev->created_at->format('d M Y H:i') }}</span>
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-400">Belum ada histori event lifecycle.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
