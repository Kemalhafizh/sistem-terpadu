<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-extrabold text-2xl text-slate-900 dark:text-white tracking-tight leading-tight">
                Outgoing Webhooks &amp; Sinkronisasi Yayasan
            </h2>
            <a href="{{ route('admin.integrations.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        showCreateModal: false,
        showEditModal: false,
        showTestResultModal: false,
        editWebhook: { routeKey: '', name: '', url: '', secret: '', events: [], is_active: true },
        testResult: { status: 200, latency: 0, body: '', successful: true, loading: false }
    }">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="p-4 text-sm text-emerald-800 dark:text-emerald-200 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center gap-2.5">
                    <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Top Actions -->
            <div class="flex flex-wrap justify-between items-center gap-3">
                <p class="text-sm text-slate-600 dark:text-slate-300">Gunakan Webhook Outgoing untuk mengirim payload real-time ke sistem Yayasan Badan Wakaf saat terjadi aksi tertentu.</p>
                <button type="button" @click="showCreateModal = true" class="inline-flex items-center px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 font-bold text-sm text-white rounded-xl shadow-sm transition-colors">
                    Daftarkan Webhook Baru
                </button>
            </div>

            <!-- Webhooks Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($webhooks as $wh)
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ $wh->name }}</h4>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $wh->is_active ? 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800/60' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600' }}">
                                {{ $wh->is_active ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </div>

                        <div class="space-y-2 text-xs">
                            <p class="flex flex-col space-y-1">
                                <span class="font-bold text-slate-500 dark:text-slate-400">URL Endpoint:</span>
                                <span class="font-mono text-cyan-700 dark:text-cyan-300 break-all select-all">{{ $wh->url }}</span>
                            </p>
                            <p class="flex flex-wrap items-center gap-1.5">
                                <span class="font-bold text-slate-500 dark:text-slate-400">Events Terdaftar:</span>
                                <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ implode(', ', $wh->events) }}</span>
                            </p>
                            @if($wh->last_triggered_at)
                                <p class="flex flex-wrap items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                    <span>Picu Terakhir:</span>
                                    <span>{{ $wh->last_triggered_at->diffForHumans() }}</span>
                                    @if($wh->failure_count > 0)
                                        <span class="text-rose-600 dark:text-rose-400 font-bold">({{ $wh->failure_count }} Gagal berturut-turut)</span>
                                    @endif
                                </p>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 dark:border-slate-700 pt-4 mt-2">
                            <div class="flex items-center gap-2">
                                <button type="button" @click="
                                    editWebhook = {
                                        routeKey: @js($wh->getRouteKey()),
                                        name: @js($wh->name),
                                        url: @js($wh->url),
                                        secret: @js((string) $wh->secret),
                                        events: @js($wh->events ?? []),
                                        is_active: {{ $wh->is_active ? 'true' : 'false' }}
                                    };
                                    showEditModal = true;
                                " class="px-3.5 py-2 border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-200 transition-colors">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.integrations.webhooks.destroy', $wh) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus webhook ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3.5 py-2 border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-xs font-semibold rounded-xl text-rose-700 dark:text-rose-300 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </div>

                            <button type="button" data-test-url="{{ route('admin.integrations.webhooks.test', $wh) }}" @click="
                                testResult.loading = true;
                                showTestResultModal = true;
                                fetch($el.dataset.testUrl, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || @js(csrf_token()),
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json'
                                    }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    testResult.status = data.status;
                                    testResult.latency = data.latency_ms;
                                    testResult.body = data.body;
                                    testResult.successful = data.successful;
                                    testResult.loading = false;
                                })
                                .catch(err => {
                                    testResult.status = 500;
                                    testResult.latency = 0;
                                    testResult.body = 'Error: ' + err.message;
                                    testResult.successful = false;
                                    testResult.loading = false;
                                });
                            " class="px-4 py-2 bg-cyan-50 dark:bg-cyan-950/60 hover:bg-cyan-100 dark:hover:bg-cyan-900 border border-cyan-200 dark:border-cyan-800/50 text-xs font-bold text-cyan-700 dark:text-cyan-300 rounded-xl transition-colors">
                                Test Ping
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-12 text-center text-slate-500 dark:text-slate-400">
                        <svg class="w-12 h-12 mx-auto text-slate-400 dark:text-slate-500 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        <p class="font-bold text-slate-700 dark:text-slate-200">Belum Ada Webhook Terdaftar</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Daftarkan webhook baru untuk mulai menyimulasikan push-data keluar.</p>
                    </div>
                @endforelse
            </div>

            <!-- Outgoing Event Logs Timeline -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                <h4 class="text-base font-bold text-slate-900 dark:text-white mb-4">Event Pemicu Yang Tersedia</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                        <p class="font-bold text-emerald-700 dark:text-emerald-400 mb-1">student.created</p>
                        <p>Dipicu ketika data siswa baru berhasil didaftarkan di platform SISTA.</p>
                    </div>
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                        <p class="font-bold text-cyan-700 dark:text-cyan-400 mb-1">billing.paid</p>
                        <p>Dipicu ketika iuran SPP siswa lunas terbayar secara real-time via payment gateway.</p>
                    </div>
                </div>
            </div>

            <!-- Create Webhook Modal -->
            <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm" style="display: none;" x-transition>
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 max-w-lg w-full space-y-5 shadow-xl">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Daftarkan Webhook Baru</h3>

                    <form method="POST" action="{{ route('admin.integrations.webhooks.store') }}" class="space-y-4 text-sm text-slate-700 dark:text-slate-200">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Nama Webhook</label>
                            <input type="text" name="name" required placeholder="Sync Yayasan Baru" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 px-3 text-slate-900 dark:text-slate-100 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Endpoint URL</label>
                            <input type="url" name="url" required placeholder="https://yayasan.smaisa1.sch.id/api/sync" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 px-3 text-slate-900 dark:text-slate-100 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Secret Signature (Opsional)</label>
                            <input type="text" name="secret" placeholder="signature_secret_key" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 px-3 text-slate-900 dark:text-slate-100 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Pilih Event Pemicu</label>
                            <div class="space-y-2 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="events[]" value="student.created" class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-cyan-600 focus:ring-cyan-500">
                                    <span>student.created</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="events[]" value="billing.paid" class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-cyan-600 focus:ring-cyan-500">
                                    <span>billing.paid</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4">
                            <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 font-semibold rounded-xl text-slate-700 dark:text-slate-200 transition-colors">Batal</button>
                            <button type="submit" class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 font-bold text-white rounded-xl shadow-sm transition-colors">Daftarkan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Webhook Modal -->
            <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm" style="display: none;" x-transition>
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 max-w-lg w-full space-y-5 shadow-xl">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Webhook</h3>

                    <form method="POST" :action="'{{ url('admin/integrations/webhooks') }}/' + editWebhook.routeKey" class="space-y-4 text-sm text-slate-700 dark:text-slate-200">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Nama Webhook</label>
                            <input type="text" name="name" x-model="editWebhook.name" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 px-3 text-slate-900 dark:text-slate-100 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Endpoint URL</label>
                            <input type="url" name="url" x-model="editWebhook.url" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 px-3 text-slate-900 dark:text-slate-100 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Secret Signature (Opsional)</label>
                            <input type="text" name="secret" x-model="editWebhook.secret" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 px-3 text-slate-900 dark:text-slate-100 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Pilih Event Pemicu</label>
                            <div class="space-y-2 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="events[]" value="student.created" :checked="editWebhook.events.includes('student.created')" class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-cyan-600 focus:ring-cyan-500">
                                    <span>student.created</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="events[]" value="billing.paid" :checked="editWebhook.events.includes('billing.paid')" class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-cyan-600 focus:ring-cyan-500">
                                    <span>billing.paid</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <input type="checkbox" name="is_active" value="1" :checked="editWebhook.is_active" id="edit_is_active" class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-cyan-600 focus:ring-cyan-500">
                            <label for="edit_is_active" class="cursor-pointer font-semibold text-slate-700 dark:text-slate-200">Tandai Webhook Aktif</label>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4">
                            <button type="button" @click="showEditModal = false" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 font-semibold rounded-xl text-slate-700 dark:text-slate-200 transition-colors">Batal</button>
                            <button type="submit" class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 font-bold text-white rounded-xl shadow-sm transition-colors">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Test Result Modal -->
            <div x-show="showTestResultModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm" style="display: none;" x-transition>
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 max-w-lg w-full space-y-5 shadow-xl">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Simulasi Webhook Outgoing (Mock Test)</h3>

                    <div class="space-y-4">
                        <div x-show="testResult.loading" class="flex flex-col items-center justify-center py-12 space-y-3">
                            <div class="w-8 h-8 rounded-full border-2 border-cyan-600 border-t-transparent animate-spin"></div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Menghubungi endpoint tujuan, silakan tunggu...</p>
                        </div>

                        <div x-show="!testResult.loading" class="space-y-4 text-sm">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">HTTP Status Code</p>
                                    <p class="text-lg font-black mt-1" :class="testResult.successful ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'" x-text="testResult.status"></p>
                                </div>
                                <div class="p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Latensi Respon</p>
                                    <p class="text-lg font-black mt-1 text-cyan-700 dark:text-cyan-400" x-text="testResult.latency + ' ms'"></p>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Body Payload Respon Server Tujuan</p>
                                <pre class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-4 text-xs font-mono max-h-48 overflow-y-auto break-words text-slate-700 dark:text-slate-200 leading-relaxed select-all" x-text="testResult.body"></pre>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" @click="showTestResultModal = false" class="px-5 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 font-semibold text-sm rounded-xl text-slate-800 dark:text-slate-100 transition-colors">Tutup</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
