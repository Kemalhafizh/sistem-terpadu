<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-900 dark:text-slate-100 leading-tight tabular-nums">
                {{ __('Verifikasi Perubahan Identitas') }} ({{ $pendingCount }} {{ __('Menunggu') }})
            </h2>
            <a href="{{ route('admin.integrations.dapodik.sync-queue') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-medium bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/60 text-amber-800 dark:text-amber-200 transition-colors">
                <span>{{ __('Buka Antrean Update Dapodik') }}</span>
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
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
                <ul class="list-disc ps-5 space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
            <form method="GET" action="{{ route('admin.profile-change-requests.index') }}" class="flex flex-wrap items-center gap-3">
                <label for="status" class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Filter Status') }}:</label>
                <select name="status" id="status" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                    <option value="pending" @selected($status === 'pending')>{{ __('Menunggu') }}</option>
                    <option value="approved" @selected($status === 'approved')>{{ __('Disetujui') }}</option>
                    <option value="rejected" @selected($status === 'rejected')>{{ __('Ditolak') }}</option>
                    <option value="cancelled" @selected($status === 'cancelled')>{{ __('Dibatalkan') }}</option>
                    <option value="all" @selected($status === 'all')>{{ __('Semua') }}</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-lg bg-slate-800 dark:bg-slate-700 text-white text-sm font-medium hover:bg-slate-700 dark:hover:bg-slate-600 transition-colors">
                    {{ __('Terapkan') }}
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 overflow-x-auto"
             x-data="{ toggleAll(checked) { document.querySelectorAll('#bulk-approve-form input[name=&quot;request_ids[]&quot;]').forEach(el => { el.checked = checked; }); } }">
            <form method="POST" action="{{ route('admin.profile-change-requests.bulk-approve') }}" id="bulk-approve-form">
                @csrf
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ __('Daftar Pengajuan Perubahan Data') }}</h3>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text" name="review_notes" placeholder="{{ __('Catatan persetujuan massal') }}" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors">
                            {{ __('Setujui Terpilih') }}
                        </button>
                    </div>
                </div>

                <table class="w-full text-start text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-600 dark:text-slate-400">
                            <th class="py-3 pe-3 text-start">
                                <input type="checkbox" aria-label="{{ __('Pilih semua pengajuan') }}" @change="toggleAll($event.target.checked)" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 text-emerald-600">
                            </th>
                            <th class="py-3 pe-3 text-start">{{ __('Pengaju / Subjek') }}</th>
                            <th class="py-3 pe-3 text-start">{{ __('Kolom') }}</th>
                            <th class="py-3 pe-3 text-start">{{ __('Perubahan') }}</th>
                            <th class="py-3 pe-3 text-start">{{ __('Bukti') }}</th>
                            <th class="py-3 pe-3 text-start">{{ __('Status') }}</th>
                            <th class="py-3 text-end">{{ __('Keputusan') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($requests as $item)
                            @php
                                $statusBadge = match ($item->status) {
                                    'approved' => [__('Disetujui'), 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300'],
                                    'rejected' => [__('Ditolak'), 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300'],
                                    'cancelled' => [__('Dibatalkan'), 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'],
                                    default => [__('Menunggu'), 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300'],
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors align-top">
                                <td class="py-3 pe-3">
                                    @if($item->status === \App\Models\ProfileChangeRequest::STATUS_PENDING)
                                        <input type="checkbox" name="request_ids[]" value="{{ $item->id }}" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 text-emerald-600">
                                    @endif
                                </td>
                                <td class="py-3 pe-3">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $item->targetUser?->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ __('Oleh') }}: {{ $item->requester?->name ?? '-' }} ({{ $item->target_type }})
                                    </div>
                                </td>
                                <td class="py-3 pe-3 font-medium text-slate-800 dark:text-slate-200">{{ $item->field_label }}</td>
                                <td class="py-3 pe-3">
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ __('Lama') }}: {{ $item->old_value ?: '-' }}</div>
                                    <div class="font-medium text-emerald-700 dark:text-emerald-400">{{ __('Baru') }}: {{ $item->new_value }}</div>
                                    @if($item->reason)
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $item->reason }}</div>
                                    @endif
                                </td>
                                <td class="py-3 pe-3">
                                    @if($item->evidence_path)
                                        <a href="{{ route('profile.change-requests.evidence', $item->uuid) }}" target="_blank" rel="noopener" class="text-primary-600 dark:text-primary-400 hover:underline text-xs font-medium">
                                            {{ __('Lihat Dokumen') }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 pe-3">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium {{ $statusBadge[1] }}">
                                        {{ $statusBadge[0] }}
                                    </span>
                                </td>
                                <td class="py-3 text-end space-y-1.5">
                                    @if($item->status === \App\Models\ProfileChangeRequest::STATUS_PENDING)
                                        <div class="inline-flex items-center gap-2">
                                            <button type="submit" form="approve-{{ $item->id }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-medium hover:bg-emerald-700 transition-colors">
                                                {{ __('Setujui') }}
                                            </button>
                                            <button type="submit" form="reject-{{ $item->id }}" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-medium hover:bg-rose-700 transition-colors">
                                                {{ __('Tolak') }}
                                            </button>
                                        </div>
                                        <div>
                                            <input type="text" form="reject-{{ $item->id }}" name="review_notes" required placeholder="{{ __('Alasan penolakan (wajib)') }}" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-xs w-48 mt-1 px-2.5 py-1">
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
                                    {{ __('Tidak ada pengajuan perubahan data yang sesuai filter.') }}
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
