<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </span>
                    Event-Driven Queue & Asynchronous Background Bus Monitor
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pemantauan antrean latar belakang (High, Default, Low) dan manajemen Dead Letter Queue (DLQ).</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Pipeline Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-rose-500 uppercase tracking-wider">Queue: High (OTP & WA)</span>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($highQueueCount) }} Jobs</h4>
                    </div>
                    <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl font-black text-xs">HIGH</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-indigo-500 uppercase tracking-wider">Queue: Default (CBT & Score)</span>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($defaultQueueCount) }} Jobs</h4>
                    </div>
                    <div class="p-3 bg-indigo-500/10 text-indigo-500 rounded-2xl font-black text-xs">DEFAULT</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Queue: Low (PDF & Ledger)</span>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($lowQueueCount) }} Jobs</h4>
                    </div>
                    <div class="p-3 bg-slate-500/10 text-slate-500 rounded-2xl font-black text-xs">LOW</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">Dead Letter Queue (DLQ)</span>
                        <h4 class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ number_format($failedJobsCount) }} Failed</h4>
                    </div>
                    <div class="p-3 bg-amber-500/10 text-amber-500 rounded-2xl font-black text-xs">DLQ</div>
                </div>
            </div>

            <!-- Manual Dispatcher Test Panel -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Uji Coba Dispatcher Pekerjaan Latar Belakang (Testing Engine)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kirimkan pekerjaan simulasi ke antrean worker latar belakang secara langsung.</p>
                </div>
                
                <form action="{{ route('admin.observability.queues.dispatch') }}" method="POST" class="flex items-center gap-3 w-full md:w-auto">
                    @csrf
                    <select name="queue_priority" class="bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white text-xs font-bold rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-500">
                        <option value="high">Priority: HIGH (WhatsApp Job)</option>
                        <option value="default">Priority: DEFAULT (CBT Job)</option>
                        <option value="low">Priority: LOW (PDF Report Job)</option>
                    </select>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-500/20 transition-all whitespace-nowrap">
                        + Dispatch Job
                    </button>
                </form>
            </div>

            <!-- Dead Letter Queue Failed Jobs Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Dead Letter Queue (Daftar Pekerjaan Gagal)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manajemen penanganan kegagalan eksekusi worker dan pengulangan otomatis (Retry / Exponential Backoff).</p>
                    </div>

                    @if($failedJobsCount > 0)
                        <form action="{{ route('admin.observability.queues.flush') }}" method="POST" onsubmit="return confirm('Bersihkan seluruh daftar pekerjaan gagal?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                                Bersihkan DLQ (Flush All)
                            </button>
                        </form>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase text-[9px] font-bold">
                            <tr>
                                <th class="p-3 rounded-l-xl">ID & Connection</th>
                                <th class="p-3">Queue Name</th>
                                <th class="p-3">Pengecualian / Eror</th>
                                <th class="p-3">Waktu Gagal</th>
                                <th class="p-3 text-right rounded-r-xl">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-750 font-medium">
                            @forelse($failedJobs as $job)
                                <tr>
                                    <td class="p-3 font-mono font-bold text-gray-900 dark:text-white">
                                        #{{ $job->id }} <span class="text-gray-400 font-sans">({{ $job->connection }})</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $job->queue === 'high' ? 'bg-rose-500/10 text-rose-500' : ($job->queue === 'low' ? 'bg-slate-500/10 text-slate-500' : 'bg-indigo-500/10 text-indigo-500') }}">
                                            {{ $job->queue }}
                                        </span>
                                    </td>
                                    <td class="p-3 max-w-xs truncate font-mono text-[10px] text-rose-600 dark:text-rose-400" title="{{ $job->exception }}">
                                        {{ Str::limit($job->exception, 90) }}
                                    </td>
                                    <td class="p-3 font-mono text-[10px] text-gray-400">
                                        {{ $job->failed_at }}
                                    </td>
                                    <td class="p-3 text-right space-x-2">
                                        <form action="{{ route('admin.observability.queues.retry', $job->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white rounded-lg text-[9px] font-bold transition-all">
                                                Retry Job
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.observability.queues.delete', $job->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white rounded-lg text-[9px] font-bold transition-all">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-400 italic">
                                        Tidak ada pekerjaan gagal di Dead Letter Queue (DLQ). Seluruh antrean beroperasi sempurna.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $failedJobs->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
