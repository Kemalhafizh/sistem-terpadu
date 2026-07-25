<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </span>
                    Incident Response & Management Workflow
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pelaporan insiden operasional, keamanan IT, keselamatan fisik, dan alur penanganan darurat GRC.</p>
            </div>
            <a href="{{ route('risk.register') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Register Risiko ERM
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Report Incident Form Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Laporkan Kejadian Insiden Baru</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Picu respon insiden darurat untuk ditangani oleh Tim GRC dan Manajer Operasional.</p>
                </div>

                <form action="{{ route('risk.create_incident') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Insiden</label>
                        <input type="text" name="title" required value="Ganggungan Jaringan Listrik & UPS Lab Komputer" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tingkat Keparahan (Severity)</label>
                        <select name="severity" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold">
                            <option value="low">Low (Kecil)</option>
                            <option value="medium" selected>Medium (Sedang)</option>
                            <option value="high">High (Tinggi)</option>
                            <option value="critical">Critical (Kritis / Darurat)</option>
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi Detail Kronologi Kejadian</label>
                        <input type="text" name="description" required value="Terjadi pemadaman listrik mendadak di gedung CBT saat pelaksanaan ujian simulasi." class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                        <input type="hidden" name="category" value="Safety & Infrastructure">
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-rose-600/30">
                            Laporkan Insiden Ke GRC
                        </button>
                    </div>
                </form>
            </div>

            <!-- Incidents Queue Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Antrean Penanganan Insiden Terlaporkan</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Log tindak lanjut resolusi dan catatan investigasi insiden.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">ID & Judul Insiden</th>
                                <th class="px-6 py-4">Keparahan</th>
                                <th class="px-6 py-4">Pelapor</th>
                                <th class="px-6 py-4">Status Penanganan</th>
                                <th class="px-6 py-4">Catatan Resolusi</th>
                                <th class="px-6 py-4 text-right">Otorisasi Resolusi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($incidents as $inc)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold block text-[11px]">#INC-{{ $inc->id }}</span>
                                        <span class="font-bold text-gray-900 dark:text-white block">{{ $inc->title }}</span>
                                        <span class="text-[11px] text-gray-500 block truncate max-w-xs">{{ $inc->description }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-mono">
                                        @if($inc->severity === 'critical')
                                            <span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 font-bold rounded uppercase">CRITICAL</span>
                                        @elseif($inc->severity === 'high')
                                            <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 font-bold rounded uppercase">HIGH</span>
                                        @else
                                            <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 font-bold rounded uppercase">{{ $inc->severity }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ optional($inc->reporter)->name }}</td>
                                    <td class="px-6 py-4 font-mono font-bold uppercase text-xs">
                                        @if($inc->isResolved())
                                            <span class="text-emerald-600 dark:text-emerald-400">RESOLVED</span>
                                        @else
                                            <span class="text-amber-600 dark:text-amber-400">{{ $inc->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-mono text-[11px] text-gray-500 max-w-xs truncate">
                                        {{ $inc->resolution_notes ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if(! $inc->isResolved())
                                            <form action="{{ route('risk.resolve_incident', $inc->id) }}" method="POST" class="flex gap-2 justify-end">
                                                @csrf
                                                <input type="text" name="resolution_notes" required placeholder="Catatan Resolusi..." class="bg-gray-50 dark:bg-gray-900 border border-gray-300 text-xs px-2 py-1 rounded">
                                                <button type="submit" class="px-3 py-1 bg-emerald-600 text-white rounded font-bold">Respon & Selesai</button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 font-mono text-[10px]">Telah Ditangani</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada laporan insiden terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
