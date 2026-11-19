<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-white leading-tight">
                Antrean Pembaruan Dapodik ({{ $pendingCount }} Menunggu)
            </h2>
            <a href="{{ route('admin.integrations.dapodik') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                &larr; Kembali ke Panel Dapodik
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
            <p class="text-sm text-slate-600 dark:text-slate-300 mb-4">
                Daftar perubahan data identitas (siswa &amp; PTK) yang telah diverifikasi dan disetujui TU, untuk disalin oleh operator ke aplikasi Dapodik.
            </p>

            <form method="GET" action="{{ route('admin.integrations.dapodik.sync-queue') }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Status</label>
                    <select name="status" id="status" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu Disalin</option>
                        <option value="synced" {{ $status === 'synced' ? 'selected' : '' }}>Sudah Disalin</option>
                        <option value="dismissed" {{ $status === 'dismissed' ? 'selected' : '' }}>Dilewati</option>
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
                <div>
                    <label for="entity_type" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Entitas</label>
                    <select name="entity_type" id="entity_type" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                        <option value="">Semua</option>
                        <option value="student" {{ $entityType === 'student' ? 'selected' : '' }}>Siswa</option>
                        <option value="employee" {{ $entityType === 'employee' ? 'selected' : '' }}>PTK / Pegawai</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-slate-800 dark:bg-slate-700 text-white text-sm font-medium hover:bg-slate-700 dark:hover:bg-slate-600 transition-colors">
                    Filter
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 overflow-x-auto"
             x-data="{ toggleAll(checked) { document.querySelectorAll('#bulk-sync-form input[name=&quot;item_ids[]&quot;]').forEach(el => { el.checked = checked; }); } }">
            <form method="POST" action="{{ route('admin.integrations.dapodik.sync-queue.bulk') }}" id="bulk-sync-form">
                @csrf
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="font-semibold text-slate-800 dark:text-white">Daftar Item Sinkronisasi</h3>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text" name="notes" placeholder="Catatan operator (opsional)" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors">
                            Tandai Terpilih Selesai
                        </button>
                    </div>
                </div>

                <table class="w-full text-start text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase text-slate-500 dark:text-slate-400">
                            <th class="py-3 pe-3 text-start">
                                <input type="checkbox" aria-label="Pilih semua item" @change="toggleAll($event.target.checked)" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 text-emerald-600">
                            </th>
                            <th class="py-3 pe-3 text-start">Nama / Identitas</th>
                            <th class="py-3 pe-3 text-start">Kolom Dapodik</th>
                            <th class="py-3 pe-3 text-start">Nilai Lama</th>
                            <th class="py-3 pe-3 text-start">Nilai Baru</th>
                            <th class="py-3 pe-3 text-start">Status</th>
                            <th class="py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($items as $syncItem)
                            @php
                                $subjectName = $syncItem->entity_type === 'student'
                                    ? ($syncItem->student?->user?->name ?? "Siswa #{$syncItem->entity_id}")
                                    : ($syncItem->employee?->user?->name ?? "PTK #{$syncItem->entity_id}");
                                $subjectId = $syncItem->entity_type === 'student'
                                    ? ($syncItem->student?->nisn ?: $syncItem->student?->nis)
                                    : ($syncItem->employee?->nuptk ?: $syncItem->employee?->employee_number);
                                $statusLabel = match ($syncItem->status) {
                                    'synced' => 'Sudah Disalin',
                                    'dismissed' => 'Dilewati',
                                    default => 'Menunggu',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="py-3 pe-3">
                                    @if($syncItem->status === \App\Models\DapodikSyncItem::STATUS_PENDING)
                                        <input type="checkbox" name="item_ids[]" value="{{ $syncItem->id }}" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 text-emerald-600">
                                    @endif
                                </td>
                                <td class="py-3 pe-3">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $subjectName }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ strtoupper($syncItem->entity_type) }} {{ $subjectId ? "({$subjectId})" : '' }}</div>
                                </td>
                                <td class="py-3 pe-3 font-medium text-slate-800 dark:text-slate-200">{{ $syncItem->dapodik_column_label }}</td>
                                <td class="py-3 pe-3 text-slate-500 dark:text-slate-400">{{ $syncItem->old_value ?: '-' }}</td>
                                <td class="py-3 pe-3 font-semibold text-emerald-700 dark:text-emerald-400">{{ $syncItem->new_value }}</td>
                                <td class="py-3 pe-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $syncItem->status === 'synced' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' : ($syncItem->status === 'dismissed' ? 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300') }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="py-3 text-end space-x-2">
                                    @if($syncItem->status === \App\Models\DapodikSyncItem::STATUS_PENDING)
                                        <button type="submit" form="mark-{{ $syncItem->id }}" class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition-colors">
                                            Selesai
                                        </button>
                                        <button type="submit" form="dismiss-{{ $syncItem->id }}" class="px-3 py-1 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                                            Lewati
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ $syncItem->synced_at?->format('d/m/Y H:i') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                    Tidak ada item pada antrean sinkronisasi Dapodik.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>

            @foreach($items as $syncItem)
                @if($syncItem->status === \App\Models\DapodikSyncItem::STATUS_PENDING)
                    <form id="mark-{{ $syncItem->id }}" method="POST" action="{{ route('admin.integrations.dapodik.sync-queue.mark', $syncItem->id) }}" class="hidden">
                        @csrf
                    </form>
                    <form id="dismiss-{{ $syncItem->id }}" method="POST" action="{{ route('admin.integrations.dapodik.sync-queue.dismiss', $syncItem->id) }}" class="hidden">
                        @csrf
                    </form>
                @endif
            @endforeach

            <div class="mt-4">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
