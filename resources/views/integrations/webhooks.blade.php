<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-2xl text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-indigo-300 tracking-tight leading-tight">
                 Outgoing Webhooks & Sinkronisasi Yayasan
            </h2>
            <a href="{{ route('admin.integrations.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-800 rounded-xl text-sm font-semibold text-gray-300 bg-gray-900 hover:bg-gray-800 hover:text-white transition-all duration-200">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-950 text-gray-100 min-h-screen" x-data="{ 
        showCreateModal: false, 
        showEditModal: false,
        showTestResultModal: false,
        editWebhook: { id: '', name: '', url: '', secret: '', events: [], is_active: true },
        testResult: { status: 200, latency: 0, body: '', successful: true, loading: false }
    }">
        <div class="w-full sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="p-4 mb-4 text-sm text-emerald-400 bg-emerald-950/40 border border-emerald-800/50 rounded-xl backdrop-blur-md shadow-lg shadow-emerald-950/20 flex items-center space-x-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Top Actions -->
            <div class="flex justify-between items-center">
                <p class="text-sm text-gray-400">Gunakan Webhook Outgoing untuk mengirim payload real-time ke sistem Yayasan Badan Wakaf saat terjadi aksi tertentu.</p>
                <button @click="showCreateModal = true" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-cyan-500 to-indigo-500 hover:from-cyan-600 hover:to-indigo-600 font-bold text-sm text-gray-950 rounded-2xl shadow-lg transition-all duration-200">
                     Daftarkan Webhook Baru
                </button>
            </div>

            <!-- Webhooks Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($webhooks as $wh)
                    <div class="backdrop-blur-xl bg-gray-900/40 border border-gray-800/80 rounded-3xl p-6 shadow-xl space-y-4 hover:border-cyan-500/20 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-bold text-gray-100">{{ $wh->name }}</h4>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $wh->is_active ? 'bg-cyan-950 text-cyan-400 border border-cyan-800/30' : 'bg-gray-800 text-gray-400 border border-gray-700' }}">
                                {{ $wh->is_active ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </div>

                        <div class="space-y-2 text-xs">
                            <p class="flex flex-col space-y-1">
                                <span class="font-bold text-gray-400">URL Endpoint:</span>
                                <span class="font-mono text-cyan-300 break-all select-all">{{ $wh->url }}</span>
                            </p>
                            <p class="flex items-center space-x-2">
                                <span class="font-bold text-gray-400">Events Terdaftar:</span>
                                <span class="text-gray-300 font-semibold">{{ implode(', ', $wh->events) }}</span>
                            </p>
                            @if($wh->last_triggered_at)
                                <p class="flex items-center space-x-2 text-gray-500">
                                    <span>Picu Terakhir:</span>
                                    <span>{{ $wh->last_triggered_at->diffForHumans() }}</span>
                                    @if($wh->failure_count > 0)
                                        <span class="text-rose-400 font-bold">({{ $wh->failure_count }} Gagal berturut-turut)</span>
                                    @endif
                                </p>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between border-t border-gray-800/50 pt-4 mt-2">
                            <div class="flex space-x-2">
                                <button @click="
                                    editWebhook = {
                                        id: '{{ $wh->id }}',
                                        name: '{{ $wh->name }}',
                                        url: '{{ $wh->url }}',
                                        secret: '{{ $wh->secret }}',
                                        events: {{ json_encode($wh->events) }},
                                        is_active: {{ $wh->is_active ? 'true' : 'false' }}
                                    };
                                    showEditModal = true;
                                " class="px-3.5 py-2 border border-gray-900 hover:border-gray-800 hover:bg-gray-800 text-xs font-semibold rounded-xl text-gray-300 transition-colors">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.integrations.webhooks.destroy', $wh->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus webhook ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3.5 py-2 border border-rose-950 hover:bg-rose-950/40 text-xs font-semibold rounded-xl text-rose-400 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </div>

                            <button @click="
                                testResult.loading = true;
                                showTestResultModal = true;
                                fetch('{{ route('admin.integrations.webhooks.test', $wh->id) }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                            " class="px-4 py-2 bg-cyan-950 hover:bg-cyan-900 border border-cyan-800/30 text-xs font-bold text-cyan-400 rounded-xl transition-colors">
                                 Test Ping
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-2 backdrop-blur-xl bg-gray-900/20 border border-gray-900 rounded-3xl p-12 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        <p class="font-bold text-gray-400">Belum Ada Webhook Terdaftar</p>
                        <p class="text-xs text-gray-500 mt-1">Daftarkan webhook baru untuk mulai menyimulasikan push-data keluar.</p>
                    </div>
                @endforelse
            </div>

            <!-- Outgoing Event Logs Timeline -->
            <div class="backdrop-blur-xl bg-gray-900/40 border border-gray-800/80 rounded-3xl p-6 shadow-xl">
                <h4 class="text-md font-bold text-gray-200 mb-4">Event Pemicu Yang Tersedia</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-400 leading-relaxed">
                    <div class="p-3.5 bg-gray-950 border border-gray-900 rounded-2xl">
                        <p class="font-bold text-emerald-400 mb-1">student.created</p>
                        <p>Dipicu ketika data siswa baru berhasil didaftarkan di platform SISTA.</p>
                    </div>
                    <div class="p-3.5 bg-gray-950 border border-gray-900 rounded-2xl">
                        <p class="font-bold text-cyan-400 mb-1">billing.paid</p>
                        <p>Dipicu ketika iuran SPP siswa lunas terbayar secara real-time via payment gateway.</p>
                    </div>
                </div>
            </div>

            <!-- Create Webhook Modal -->
            <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md" style="display: none;" x-transition>
                <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 max-w-lg w-full space-y-6">
                    <h3 class="text-xl font-bold text-gray-100">Daftarkan Webhook Baru</h3>
                    
                    <form method="POST" action="{{ route('admin.integrations.webhooks.store') }}" class="space-y-4 text-sm text-gray-300">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Webhook</label>
                            <input type="text" name="name" required placeholder="Sync Yayasan Baru" class="w-full bg-gray-950 border border-gray-900 rounded-2xl py-3 px-4 text-gray-200 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Endpoint URL</label>
                            <input type="url" name="url" required placeholder="https://yayasan.smaisa1.sch.id/api/sync" class="w-full bg-gray-950 border border-gray-900 rounded-2xl py-3 px-4 text-gray-200 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Secret Signature (Opsional)</label>
                            <input type="text" name="secret" placeholder="signature_secret_key" class="w-full bg-gray-950 border border-gray-900 rounded-2xl py-3 px-4 text-gray-200 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih Event Pemicu</label>
                            <div class="space-y-2 mt-2">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="events[]" value="student.created" class="rounded bg-gray-950 border-gray-800 text-cyan-500 focus:ring-cyan-500">
                                    <span>student.created</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="events[]" value="billing.paid" class="rounded bg-gray-950 border-gray-800 text-cyan-500 focus:ring-cyan-500">
                                    <span>billing.paid</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-3 pt-4">
                            <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 bg-gray-800 hover:bg-gray-700 font-semibold rounded-2xl text-gray-200 transition-colors">Batal</button>
                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-indigo-500 hover:from-cyan-600 hover:to-indigo-600 font-bold text-gray-950 rounded-2xl shadow-lg transition-colors">Daftarkan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Webhook Modal -->
            <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md" style="display: none;" x-transition>
                <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 max-w-lg w-full space-y-6">
                    <h3 class="text-xl font-bold text-gray-100">Edit Webhook</h3>
                    
                    <form method="POST" :action="'{{ url('admin/integrations/webhooks') }}/' + editWebhook.id" class="space-y-4 text-sm text-gray-300">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Webhook</label>
                            <input type="text" name="name" x-model="editWebhook.name" required class="w-full bg-gray-950 border border-gray-900 rounded-2xl py-3 px-4 text-gray-200 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Endpoint URL</label>
                            <input type="url" name="url" x-model="editWebhook.url" required class="w-full bg-gray-950 border border-gray-900 rounded-2xl py-3 px-4 text-gray-200 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Secret Signature (Opsional)</label>
                            <input type="text" name="secret" x-model="editWebhook.secret" class="w-full bg-gray-950 border border-gray-900 rounded-2xl py-3 px-4 text-gray-200 focus:outline-none focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih Event Pemicu</label>
                            <div class="space-y-2 mt-2">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="events[]" value="student.created" :checked="editWebhook.events.includes('student.created')" class="rounded bg-gray-950 border-gray-800 text-cyan-500 focus:ring-cyan-500">
                                    <span>student.created</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="events[]" value="billing.paid" :checked="editWebhook.events.includes('billing.paid')" class="rounded bg-gray-950 border-gray-800 text-cyan-500 focus:ring-cyan-500">
                                    <span>billing.paid</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 pt-2">
                            <input type="checkbox" name="is_active" value="1" :checked="editWebhook.is_active" id="edit_is_active" class="rounded bg-gray-950 border-gray-800 text-cyan-500 focus:ring-cyan-500">
                            <label for="edit_is_active" class="cursor-pointer font-semibold text-gray-300">Tandai Webhook Aktif</label>
                        </div>

                        <div class="flex items-center justify-end space-x-3 pt-4">
                            <button type="button" @click="showEditModal = false" class="px-5 py-2.5 bg-gray-800 hover:bg-gray-700 font-semibold rounded-2xl text-gray-200 transition-colors">Batal</button>
                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-indigo-500 hover:from-cyan-600 hover:to-indigo-600 font-bold text-gray-950 rounded-2xl shadow-lg transition-colors">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Test Result Modal -->
            <div x-show="showTestResultModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md" style="display: none;" x-transition>
                <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 max-w-lg w-full space-y-6">
                    <h3 class="text-xl font-bold text-gray-100">Simulasi Webhook Outgoing (Mock Test)</h3>
                    
                    <div class="space-y-4">
                        <div x-show="testResult.loading" class="flex flex-col items-center justify-center py-12 space-y-3">
                            <div class="w-8 h-8 rounded-full border-2 border-cyan-500 border-t-transparent animate-spin"></div>
                            <p class="text-xs text-gray-500">Menghubungi endpoint tujuan, silakan tunggu...</p>
                        </div>

                        <div x-show="!testResult.loading" class="space-y-4 text-sm">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-3 bg-gray-950 border border-gray-900 rounded-2xl">
                                    <p class="text-xs font-bold text-gray-400">HTTP Status Code</p>
                                    <p class="text-lg font-black mt-1" :class="testResult.successful ? 'text-emerald-400' : 'text-rose-400'" x-text="testResult.status"></p>
                                </div>
                                <div class="p-3 bg-gray-950 border border-gray-900 rounded-2xl">
                                    <p class="text-xs font-bold text-gray-400">Latensi Respon</p>
                                    <p class="text-lg font-black mt-1 text-cyan-400" x-text="testResult.latency + ' ms'"></p>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400">Body Payload Respon Server Tujuan</p>
                                <pre class="bg-gray-950 border border-gray-900 rounded-2xl p-4 text-xs font-mono max-h-48 overflow-y-auto break-words text-gray-300 leading-relaxed select-all" x-text="testResult.body"></pre>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" @click="showTestResultModal = false" class="px-6 py-2.5 bg-gray-800 hover:bg-gray-700 font-semibold rounded-2xl text-gray-200 transition-colors">Tutup</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
