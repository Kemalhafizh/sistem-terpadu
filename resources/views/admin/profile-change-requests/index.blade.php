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
        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-5">
            <form method="GET" action="{{ route('admin.profile-change-requests.index') }}" class="flex flex-wrap items-center gap-3">
                <label for="status" class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter Status:</label>
                <select name="status" id="status" class="rounded-lg border-gray-300 text-sm">
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-medium hover:bg-gray-700">
                    Terapkan
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-5 overflow-x-auto">
            <form method="POST" action="{{ route('admin.profile-change-requests.bulk-approve') }}" id="bulk-approve-form">
                @csrf
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100">Daftar Pengajuan Perubahan Data</h3>
                    <div class="flex items-center gap-2">
                        <input type="text" name="review_notes" placeholder="Catatan persetujuan massal" class="rounded-lg border-gray-300 text-sm">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium">
                            Setujui Terpilih
                        </button>
                    </div>
                </div>

                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 text-xs uppercase text-gray-500">
                            <th class="py-3 pr-3">#</th>
                            <th class="py-3 pr-3">Pengaju / Subjek</th>
                            <th class="py-3 pr-3">Kolom</th>
                            <th class="py-3 pr-3">Perubahan</th>
                            <th class="py-3 pr-3">Bukti</th>
                            <th class="py-3 pr-3">Status</th>
                            <th class="py-3 text-right">Keputusan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($requests as $item)
                            <tr>
                                <td class="py-3 pr-3">
                                    @if($item->status === \App\Models\ProfileChangeRequest::STATUS_PENDING)
                                        <input type="checkbox" name="request_ids[]" value="{{ $item->id }}" class="rounded border-gray-300">
                                    @endif
                                </td>
                                <td class="py-3 pr-3">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item->targetUser?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">
                                        Oleh: {{ $item->requester?->name ?? '-' }} ({{ $item->target_type }})
                                    </div>
                                </td>
                                <td class="py-3 pr-3 font-medium">{{ $item->field_label }}</td>
                                <td class="py-3 pr-3">
                                    <div class="text-xs text-gray-500">Lama: {{ $item->old_value ?: '-' }}</div>
                                    <div class="font-semibold text-emerald-700 dark:text-emerald-400">Baru: {{ $item->new_value }}</div>
                                    @if($item->reason)
                                        <div class="text-xs text-gray-500 italic mt-0.5">{{ $item->reason }}</div>
                                    @endif
                                </td>
                                <td class="py-3 pr-3">
                                    @if($item->evidence_path)
                                        <a href="{{ route('profile.change-requests.evidence', $item->uuid) }}" target="_blank" class="text-indigo-600 hover:underline text-xs font-medium">
                                            Lihat Dokumen
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 pr-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $item->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($item->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="py-3 text-right space-y-1">
                                    @if($item->status === \App\Models\ProfileChangeRequest::STATUS_PENDING)
                                        <div class="inline-flex items-center gap-2">
                                            <button type="submit" form="approve-{{ $item->id }}" class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-500">
                                                Setujui
                                            </button>
                                            <button type="submit" form="reject-{{ $item->id }}" class="px-3 py-1 rounded-lg bg-rose-600 text-white text-xs hover:bg-rose-500">
                                                Tolak
                                            </button>
                                        </div>
                                        <div>
                                            <input type="text" form="reject-{{ $item->id }}" name="review_notes" placeholder="Alasan penolakan" class="rounded border-gray-300 text-xs w-44 mt-1">
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500">
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
                                <td colspan="7" class="py-8 text-center text-gray-500">
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
