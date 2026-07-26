@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                Continuing Professional Development (CPD/PKB) Tracker
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pelacakan Kredit Pengembangan Keprofesian Berkelanjutan Guru Permendikbud SMA ISA 1 Semarang</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('hc.cpd.team_overview') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md">
                Verifikasi Kredit Tim (Manager/Kepsek)
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Target Progress Card -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-3">
        <div class="flex justify-between items-center text-xs font-bold">
            <span class="uppercase text-gray-500">Target Kredit PKB/CPD Tahun Ajaran 2026/2027</span>
            <span class="text-emerald-600">{{ $target->achieved_credits ?? 0 }} / {{ $target->target_credits ?? 12 }} Kredit Terkumpul</span>
        </div>
        <div class="w-full bg-gray-100 dark:bg-gray-700 h-3 rounded-full overflow-hidden">
            <div class="bg-emerald-500 h-3 rounded-full" style="width: {{ min(100, (($target->achieved_credits ?? 0) / ($target->target_credits ?? 12)) * 100) }}%"></div>
        </div>
    </div>

    <!-- Form Log Activity -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Catat Kegiatan Pengembangan Diri / Workshop / Publikasi Baru</h3>
        <form action="{{ route('hc.cpd.log_activity') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kategori PKB</label>
                <select name="cpd_category_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }} (Bobot x{{ $cat->credit_weight }})</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Pelatihan / Karya Ilmiah</label>
                <input type="text" name="title" placeholder="Workshop Nasional Implemetasi Kurikulum Merdeka & AI dalam Pembelajaran" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Durasi (Jam Pelatihan - JP)</label>
                <input type="number" name="hours" value="32" min="1" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kredit yang Diusulkan</label>
                <input type="number" step="0.5" name="credits_earned" value="4.0" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi & Output Karya</label>
                <textarea name="description" rows="2" placeholder="Pelatihan diselenggarakan oleh Kemendikbudristek secara daring..." required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold"></textarea>
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md">+ Catat Kegiatan PKB</button>
            </div>
        </form>
    </div>

    <!-- History Table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Riwayat Kegiatan PKB/CPD Saya</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Judul Kegiatan</th>
                    <th class="py-3 px-4">Kategori</th>
                    <th class="py-3 px-4">Beban JP</th>
                    <th class="py-3 px-4">Kredit</th>
                    <th class="py-3 px-4">Status Verifikasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($activities as $act)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $act->title }}</td>
                    <td class="py-3 px-4 text-xs font-semibold">{{ $act->category->name }}</td>
                    <td class="py-3 px-4 font-semibold">{{ $act->hours }} JP</td>
                    <td class="py-3 px-4 font-black text-emerald-600">+{{ $act->credits_earned }} Kredit</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase
                            {{ $act->status === 'verified' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $act->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada aktivitas PKB yang dicatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
