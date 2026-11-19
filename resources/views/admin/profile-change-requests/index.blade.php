<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                Verifikasi Perubahan Identitas ({{ $pendingCount }} Menunggu)
            </h2>
            <a href="{{ route('admin.integrations.dapodik.sync-queue') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-amber-100 hover:bg-amber-200 text-amber-900">
                Buka Antrean Update Dapodik &rarr;
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
        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
            <form method="GET" action="{{ route('admin.profile-change-requests.index') }}" class="flex flex-wrap items-center gap-3">
                <label for="status" class="text-sm font-medium text-slate-700 dark:text-slate-300">Filter Status:</label>
                <select name="status" id="status" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-lg bg-slate-800 dark:bg-slate-700 text-white text-sm font-medium hover:bg-slate-700 dark:hover:bg-slate-600 transition-colors">
                    Terapkan
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 overflow-x-auto"
             x-data="{ toggleAll(checked) { document.querySelectorAll('#bulk-approve-form input[name=&quot;request_ids[]&quot;]').forEach(el => { el.checked = checked; }); } }">
            <form method="POST" action="{{ route('admin.profile-change-requests.bulk-approve') }}" id="bulk-approve-form">
                @csrf
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="font-semibold text-slate-800 dark:text-white">Daftar Pengajuan Perubahan Data</h3>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text" name="review_notes" placeholder="Catatan persetujuan massal" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors">
                            Setujui Terpilih
                        </button>
                    </div>
                </div>

                <table class="w-full text-start text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase text-slate-500 dark:text-slate-400">
                            <th class="py-3 pe-3 text-start">
                                <input type="checkbox" aria-label="Pilih semua pengajuan" @change="toggleAll($event.target.checked)" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 text-emerald-600">
                            </th>
                            <th class="py-3 pe-3 text-start">Pengaju / Subjek</th>
                            <th class="py-3 pe-3 text-start">Kolom</th>
                            <th class="py-3 pe-3 text-start">Perubahan</th>
                            <th class="py-3 pe-3 text-start">Bukti</th>
                            <th class="py-3 pe-3 text-start">Status</th>
                            <th class="py-3 text-end">Keputusan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($requests as $item)
                            @php
                                $statusBadge = match ($item->status) {
                                    'approved' => ['Disetujui', 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300'],
                                    'rejected' => ['Ditolak', 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300'],
                                    'cancelled' => ['Dibatalkan', 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'],
                                    default => ['Menunggu', 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300'],
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="py-3 pe-3">
                                    @if($item->status === \App\Models\ProfileChangeRequest::STATUS_PENDING)
                                        <input type="checkbox" name="request_ids[]" value="{{ $item->id }}" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 text-emerald-600">
                                    @endif
                                </td>
                                <td class="py-3 pe-3">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $item->targetUser?->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        Oleh: {{ $item->requester?->name ?? '-' }} ({{ $item->target_type }})
                                    </div>
                                </td>
                                <td class="py-3 pe-3 font-medium text-slate-800 dark:text-slate-200">{{ $item->field_label }}</td>
                                <td class="py-3 pe-3">
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Lama: {{ $item->old_value ?: '-' }}</div>
                                    <div class="font-semibold text-emerald-700 dark:text-emerald-400">Baru: {{ $item->new_value }}</div>
                                    @if($item->reason)
                                        <div class="text-xs text-slate-500 dark:text-slate-400 italic mt-0.5">{{ $item->reason }}</div>
                                    @endif
                                </td>
                                <td class="py-3 pe-3">
                                    @if($item->evidence_path)
                                        <a href="{{ route('profile.change-requests.evidence', $item->uuid) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs font-medium">
                                            Lihat Dokumen
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 pe-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusBadge[1] }}">
                                        {{ $statusBadge[0] }}
                                    </span>
                                </td>
                                <td class="py-3 text-end space-y-1">
                                    @if($item->status === \App\Models\ProfileChangeRequest::STATUS_PENDING)
                                        <div class="inline-flex items-center gap-2">
                                            <button type="submit" form="approve-{{ $item->id }}" class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition-colors">
                                                Setujui
                                            </button>
                                            <button type="submit" form="reject-{{ $item->id }}" class="px-3 py-1 rounded-lg bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 transition-colors">
                                                Tolak
                                            </button>
                                        </div>
                                        <div>
                                            <input type="text" form="reject-{{ $item->id }}" name="review_notes" placeholder="Alasan penolakan" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-xs w-44 mt-1">
                                        </div>
                                    @else
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $item->reviewer?->name ?? '-' }}
                                            @if($item->review_notes)
                                                <div>{{ $item->review_notes }}</div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                    Tidak ada pengajuan perubahan data yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>

            @foreach($requests as $item)
                @if($item->status === \App\Models\ProfileChangeRequest::STATUS_PENDING)
                    <form id="approve-{{ $item->id }}" method="POST" action="{{ route('admin.profile-change-requests.approve', $item->uuid) }}" class="hidden">
                        @csrf
                    </form>
                    <form id="reject-{{ $item->id }}" method="POST" action="{{ route('admin.profile-change-requests.reject', $item->uuid) }}" class="hidden">
                        @csrf
                    </form>
                @endif
            @endforeach

            <div class="mt-4">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
