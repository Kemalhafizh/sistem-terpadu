<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Proses Seleksi & Kelulusan - ' . $wave->name) }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8">

            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('spmb.admin.dashboard') }}" class="px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-all">&larr; Kembali ke Dashboard</a>
                
                <form action="{{ route('spmb.admin.selection.run', $wave->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center gap-1.5">
                         Jalankan Perangkingan & Umumkan Kelulusan
                    </button>
                </form>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="font-black text-slate-800 text-sm">Preview Peringkat Pendaftar</h3>
                    <span class="text-xs text-slate-400 font-semibold font-inter">Urut berdasarkan Skor Akhir Tertinggi</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-150 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                <th class="p-4">Rank</th>
                                <th class="p-4">No. Pendaftaran</th>
                                <th class="p-4">Nama Lengkap</th>
                                <th class="p-4">Jalur Seleksi</th>
                                <th class="p-4 text-center">Rapor Avg</th>
                                <th class="p-4 text-center">Nilai Ujian</th>
                                <th class="p-4 text-center">Skor Akhir</th>
                                <th class="p-4">Status Kelulusan</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y divide-slate-100">
                            @forelse($applicants as $app)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4">
                                        @if($app->rank)
                                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full font-black {{ $app->rank <= ($app->spmbTrack->quota ?? 30) ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">#{{ $app->rank }}</span>
                                        @else
                                            <span class="text-slate-350">-</span>
                                        @endif
                                    </td>
                                    <td class="p-4 font-bold text-slate-500">{{ $app->registration_number }}</td>
                                    <td class="p-4 font-black text-slate-900">{{ $app->full_name }}</td>
                                    <td class="p-4 font-semibold text-slate-700">{{ $app->spmbTrack->name ?? 'Reguler' }}</td>
                                    <td class="p-4 text-center font-inter font-semibold text-slate-600">
                                        @php
                                            $raporAvg = 0.0;
                                            if ($app->rapor_scores && is_array($app->rapor_scores)) {
                                                $scores = array_filter($app->rapor_scores, 'is_numeric');
                                                if (count($scores) > 0) {
                                                    $raporAvg = array_sum($scores) / count($scores);
                                                }
                                            }
                                        @endphp
                                        {{ round($raporAvg, 2) }}
                                    </td>
                                    <td class="p-4 text-center font-inter font-semibold text-slate-600">{{ $app->entrance_test_score ?: '-' }}</td>
                                    <td class="p-4 text-center font-inter font-black text-slate-800">{{ $app->total_score ?: '-' }}</td>
                                    <td class="p-4">
                                        @if($app->status === 'accepted')
                                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-lg border border-emerald-150">DITERIMA</span>
                                        @elseif($app->status === 'rejected')
                                            <span class="px-2.5 py-1 bg-red-50 text-red-700 text-[10px] font-black rounded-lg border border-red-150">TIDAK LOLOS</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-[10px] font-black rounded-lg border border-amber-150">PENDING</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-slate-400">Tidak ada pendaftar di gelombang ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
