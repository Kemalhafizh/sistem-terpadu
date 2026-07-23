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
                    Real-Time Event Streaming Bus & Webhook Hub
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Infrastruktur integrasi event-driven outbound webhook terenkripsi HMAC-SHA256.</p>
            </div>
            <form action="{{ route('admin.webhooks.test-dispatch') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Pancarkan Event Uji Coba
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Summary Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Langganan Webhook</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-gray-900 dark:text-white">{{ count($subscriptions) }}</span>
                        <span class="text-xs text-gray-500">Endpoints</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Tingkat Keberhasilan Pengiriman</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $successRate }}%</span>
                        <span class="text-xs text-gray-500">Success Rate</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Pengiriman (Deliveries)</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $totalDeliveries }}</span>
                        <span class="text-xs text-gray-500">Payloads Sent</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Keamanan Tanda Tangan</span>
                    <div class="mt-2">
                        <span class="px-3 py-1 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 rounded-xl font-mono text-xs font-bold">
                            X-Sista-Signature HMAC-SHA256
                        </span>
                    </div>
                </div>
            </div>

            <!-- Registration Form Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Registrasi Langganan Webhook Baru</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Daftarkan URL endpoint pihak ketiga untuk menerima notifikasi real-time event.</p>
                </div>

                <form action="{{ route('admin.webhooks.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Sistem / Yayasan</label>
                        <input type="text" name="name" required placeholder="Portal Yayasan YBWSA Pusat" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Target Endpoint URL</label>
                        <input type="url" name="target_url" required placeholder="https://api.ybwsa.or.id/webhooks/sista" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white font-mono rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Event (Pilih Minimal 1)</label>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <label class="inline-flex items-center gap-1.5 bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-700">
                                <input type="checkbox" name="events[]" value="student.registered" checked class="rounded border-gray-300 text-indigo-600"> student.registered
                            </label>
                            <label class="inline-flex items-center gap-1.5 bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-700">
                                <input type="checkbox" name="events[]" value="billing.paid" checked class="rounded border-gray-300 text-indigo-600"> billing.paid
                            </label>
                            <label class="inline-flex items-center gap-1.5 bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-700">
                                <input type="checkbox" name="events[]" value="grade.published" class="rounded border-gray-300 text-indigo-600"> grade.published
                            </label>
                        </div>
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all">
                            Simpan Langganan Webhook
                        </button>
                    </div>
                </form>
            </div>

            <!-- Active Webhook Subscriptions Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Daftar Langganan Webhook Aktif</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Endpoint terdaftar yang menerima pancaran event payload terenkripsi.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Nama Sistem</th>
                                <th class="px-6 py-4">Target URL Endpoint</th>
                                <th class="px-6 py-4">Filtered Events</th>
                                <th class="px-6 py-4">Secret Key</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($subscriptions as $sub)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $sub->name }}</td>
                                    <td class="px-6 py-4 font-mono text-indigo-600 dark:text-indigo-400">{{ $sub->target_url }}</td>
                                    <td class="px-6 py-4 font-mono">
                                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-bold">
                                            {{ implode(', ', $sub->events ?? []) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-[11px] text-gray-400">
                                        {{ Str::limit($sub->secret_key, 16) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($sub->is_active)
                                            <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 rounded font-mono text-[10px] font-bold">AKTIF</span>
                                        @else
                                            <span class="px-2.5 py-0.5 bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded font-mono text-[10px] font-bold">NON-AKTIF</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <form action="{{ route('admin.webhooks.toggle', $sub->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 rounded-lg text-[11px] font-bold transition-all">
                                                {{ $sub->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.webhooks.delete', $sub->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus langganan webhook ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 rounded-lg text-[11px] font-bold transition-all">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">Belum ada langganan webhook terdaftar. Gunakan formulir di atas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Webhook Deliveries Log Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden space-y-4">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Log Pengiriman Webhook (Deliveries)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Riwayat pengiriman payload event, respon HTTP, dan fitur Replay Failed Deliveries.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Event Type</th>
                                <th class="px-6 py-4">Target Webhook</th>
                                <th class="px-6 py-4">HMAC Signature</th>
                                <th class="px-6 py-4">Status Pengiriman</th>
                                <th class="px-6 py-4">HTTP Code</th>
                                <th class="px-6 py-4">Waktu</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($recentDeliveries as $del)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $del->event_type }}</td>
                                    <td class="px-6 py-4 font-bold">{{ optional($del->subscription)->name ?? 'Deleted Webhook' }}</td>
                                    <td class="px-6 py-4 font-mono text-[10px] text-gray-400">
                                        <span class="bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded truncate inline-block max-w-[140px]" title="{{ $del->signature }}">
                                            {{ Str::limit($del->signature, 16) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-0.5 rounded font-mono text-[10px] font-bold uppercase {{ $del->status === 'SENT' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400' }}">
                                            {{ $del->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono">{{ $del->response_code }}</td>
                                    <td class="px-6 py-4 font-mono text-gray-500">{{ $del->created_at->format('d M Y H:i:s') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('admin.webhooks.replay', $del->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[11px] font-bold transition-all shadow-sm">
                                                Replay Delivery
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">Belum ada riwayat pengiriman payload webhook.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
