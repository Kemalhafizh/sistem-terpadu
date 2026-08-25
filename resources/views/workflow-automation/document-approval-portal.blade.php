<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Portal Persetujuan Dokumen (Approval Portal)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola antrean persetujuan dokumen berantai, pengajuan RKAS, dan surat keputusan resmi.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Pending Approvals Inbox -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Menunggu Persetujuan Anda
                </h3>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300">
                    {{ $pendingApprovals->count() }} Dokumen
                </span>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($pendingApprovals as $req)
                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase text-slate-400">{{ $req->chain->blueprint->name ?? 'Proses' }}</span>
                            <h4 class="font-bold text-slate-900 dark:text-white text-base">Pengajuan dari {{ $req->chain->requester->name ?? 'Pemohon' }}</h4>
                            <p class="text-xs text-slate-500">Tahap: {{ $req->node_key }} &bull; Diajukan: {{ $req->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors">
                                Setujui
                            </button>
                            <button class="px-3.5 py-2 rounded-xl bg-rose-50 text-rose-700 text-xs font-bold hover:bg-rose-100 transition-colors">
                                Tolak
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400 py-4 text-center">Tidak ada dokumen yang memerlukan persetujuan saat ini.</p>
                @endforelse
            </div>
        </div>

        <!-- Historical Chains Table -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Riwayat Pengajuan Seluruh Alur Kerja</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold">
                            <th class="p-3">Alur Proses</th>
                            <th class="p-3">Pemohon</th>
                            <th class="p-3">Tahap Saat Ini</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Waktu Mulai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($allChains as $c)
                            <tr>
                                <td class="p-3 font-bold text-slate-800 dark:text-white">{{ $c->blueprint->name ?? 'Workflow' }}</td>
                                <td class="p-3 text-slate-600 dark:text-slate-300">{{ $c->requester->name ?? 'Pemohon' }}</td>
                                <td class="p-3 font-mono text-slate-500">{{ $c->current_node_key }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $c->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($c->status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $c->status }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-400">{{ $c->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
