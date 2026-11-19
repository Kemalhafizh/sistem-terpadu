<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                Antrean Pembaruan Dapodik ({{ $pendingCount }} Menunggu)
            </h2>
            <a href="{{ route('admin.integrations.dapodik') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 hover:bg-gray-200 text-gray-700">
                &larr; Kembali ke Panel Dapodik
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-5">
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                Daftar perubahan data identitas (siswa &amp; PTK) yang telah diverifikasi dan disetujui TU, untuk disalin oleh operator ke aplikasi Dapodik.
            </p>

            <form method="GET" action="{{ route('admin.integrations.dapodik.sync-queue') }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label for="status" class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</label>
                    <select name="status" id="status" class="rounded-lg border-gray-300 text-sm">
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu Disalin</option>
                        <option value="synced" {{ $status === 'synced' ? 'selected' : '' }}>Sudah Disalin</option>
                        <option value="dismissed" {{ $status === 'dismissed' ? 'selected' : '' }}>Dilewati</option>
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
                <div>
                    <label for="entity_type" class="block text-xs font-semibold text-gray-500 uppercase mb-1">Entitas</label>
                    <select name="entity_type" id="entity_type" class="rounded-lg border-gray-300 text-sm">
                        <option value="">Semua</option>
                        <option value="student" {{ $entityType === 'student' ? 'selected' : '' }}>Siswa</option>
                        <option value="employee" {{ $entityType === 'employee' ? 'selected' : '' }}>PTK / Pegawai</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-medium hover:bg-gray-700">
                    Filter
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-5 overflow-x-auto">
            <form method="POST" action="{{ route('admin.integrations.dapodik.sync-queue.bulk') }}" id="bulk-sync-form">
                @csrf
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100">Daftar Item Sinkronisasi</h3>
                    <div class="flex items-center gap-2">
                        <input type="text" name="notes" placeholder="Catatan operator (opsional)" class="rounded-lg border-gray-300 text-sm">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium">
                            Tandai Terpilih Selesai
                        </button>
                    </div>
                </div>

                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 text-xs uppercase text-gray-500">
                            <th class="py-3 pr-3">#</th>
                            <th class="py-3 pr-3">Nama / Identitas</th>
                            <th class="py-3 pr-3">Kolom Dapodik</th>
                            <th class="py-3 pr-3">Nilai Lama</th>
                            <th class="py-3 pr-3">Nilai Baru</th>
                            <th class="py-3 pr-3">Status</th>
                            <th class="py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($items as $syncItem)
                            @php
                                $subjectName = $syncItem->entity_type === 'student'
                                    ? ($syncItem->student?->user?->name ?? "Siswa #{$syncItem->entity_id}")
                                    : ($syncItem->employee?->user?->name ?? "PTK #{$syncItem->entity_id}");
                                $subjectId = $syncItem->entity_type === 'student'
                                    ? ($syncItem->student?->nisn ?: $syncItem->student?->nis)
                                    : ($syncItem->employee?->nuptk ?: $syncItem->employee?->employee_number);
                            @endphp
                            <tr>
                                <td class="py-3 pr-3">
                                    @if($syncItem->status === \App\Models\DapodikSyncItem::STATUS_PENDING)
                                        <input type="checkbox" name="item_ids[]" value="{{ $syncItem->id }}" class="rounded border-gray-300">
                                    @endif
                                </td>
                                <td class="py-3 pr-3">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $subjectName }}</div>
                                    <div class="text-xs text-gray-500">{{ strtoupper($syncItem->entity_type) }} {{ $subjectId ? "({$subjectId})" : '' }}</div>
                                </td>
                                <td class="py-3 pr-3 font-medium">{{ $syncItem->dapodik_column_label }}</td>
                                <td class="py-3 pr-3 text-gray-500">{{ $syncItem->old_value ?: '-' }}</td>
                                <td class="py-3 pr-3 font-semibold text-emerald-700 dark:text-emerald-400">{{ $syncItem->new_value }}</td>
                                <td class="py-3 pr-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $syncItem->status === 'synced' ? 'bg-emerald-100 text-emerald-800' : ($syncItem->status === 'dismissed' ? 'bg-gray-100 text-gray-600' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $syncItem->status }}
                                    </span>
                                </td>
                                <td class="py-3 text-right space-x-2">
                                    @if($syncItem->status === \App\Models\DapodikSyncItem::STATUS_PENDING)
                                        <button type="submit" form="mark-{{ $syncItem->id }}" class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-500">
                                            Selesai
                                        </button>
                                        <button type="submit" form="dismiss-{{ $syncItem->id }}" class="px-3 py-1 rounded-lg bg-gray-200 text-gray-700 text-xs hover:bg-gray-300">
                                            Lewati
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400">{{ $syncItem->synced_at?->format('d/m/Y H:i') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-500">
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
