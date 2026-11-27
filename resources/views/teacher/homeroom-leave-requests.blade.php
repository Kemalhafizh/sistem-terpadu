<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Persetujuan Izin & Sakit Siswa')"
            :subtitle="__('Tinjau pengajuan izin dari wali murid untuk rombel perwalian atau kesiswaan')"
        />
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @php
                $filterOptions = [
                    'pending' => __('Menunggu'),
                    'approved' => __('Disetujui'),
                    'auto_approved' => __('Disetujui Otomatis'),
                    'rejected' => __('Ditolak'),
                    'cancelled' => __('Dibatalkan'),
                    'all' => __('Semua'),
                ];
            @endphp
            <div class="flex flex-wrap gap-2">
                @foreach ($filterOptions as $key => $label)
                    <a href="{{ route('teacher.leave-requests.index', ['status' => $key]) }}"
                       class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors {{ $statusFilter === $key ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-xs font-medium text-slate-600 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3 text-start">{{ __('Siswa & Rombel') }}</th>
                                <th class="px-4 py-3 text-start">{{ __('Tanggal') }}</th>
                                <th class="px-4 py-3 text-start">{{ __('Jenis & Alasan') }}</th>
                                <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-end">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($leaveRequests as $leave)
                                @php
                                    $statusBadge = match ($leave->status) {
                                        'approved' => [__('Disetujui'), 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300'],
                                        'auto_approved' => [__('Disetujui Otomatis'), 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300'],
                                        'rejected' => [__('Ditolak'), 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300'],
                                        'cancelled' => [__('Dibatalkan'), 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'],
                                        'revoked' => [__('Dicabut'), 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300'],
                                        default => [__('Menunggu'), 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300'],
                                    };
                                    $typeLabel = $leave->type === 'sakit' ? __('Sakit') : __('Izin');
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                        {{ $leave->student?->user?->name ?? '-' }}
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $leave->classroom?->name ?? '-' }} &middot; {{ __('Pengaju') }}: {{ $leave->submitter?->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300 tabular-nums">
                                        {{ $leave->start_date?->format('d/m/Y') }} - {{ $leave->end_date?->format('d/m/Y') }}
                                        <span class="text-slate-500 dark:text-slate-400">({{ $leave->days_count }} {{ __('hari') }})</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-xs text-slate-900 dark:text-white">{{ $typeLabel }}</span>
                                        <div class="text-xs text-slate-600 dark:text-slate-300">{{ $leave->reason }}</div>
                                        @if ($leave->attachment_path)
                                            <a href="{{ route('student-leave-requests.attachment', $leave->uuid) }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">{{ __('Unduh Surat Dokter') }}</a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-md font-medium {{ $statusBadge[1] }}">{{ $statusBadge[0] }}</span>
                                            @if ($leave->escalated_at && $leave->status === \App\Models\StudentLeaveRequest::STATUS_PENDING)
                                                <span class="inline-flex px-2 py-0.5 rounded-md font-medium bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    {{ __('Eskalasi Kesiswaan') }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($leave->decision_notes)
                                            <div class="text-slate-500 dark:text-slate-400 mt-1">{{ $leave->decision_notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-end space-y-2">
                                        @if ($leave->status === \App\Models\StudentLeaveRequest::STATUS_PENDING)
                                            <div class="flex flex-wrap justify-end gap-2">
                                                <form method="POST" action="{{ route('teacher.leave-requests.approve', $leave->uuid) }}">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition-colors">{{ __('Setujui') }}</button>
                                                </form>
                                                <form method="POST" action="{{ route('teacher.leave-requests.reject', $leave->uuid) }}" class="flex gap-1.5">
                                                    @csrf
                                                    <input type="text" name="decision_notes" required placeholder="{{ __('Alasan tolak') }}" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-xs px-2.5 py-1" />
                                                    <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-medium rounded-lg transition-colors">{{ __('Tolak') }}</button>
                                                </form>
                                            </div>
                                        @elseif ($leave->isApproved())
                                            <form method="POST" action="{{ route('teacher.leave-requests.revoke', $leave->uuid) }}" class="flex justify-end gap-1.5">
                                                @csrf
                                                <input type="text" name="decision_notes" required placeholder="{{ __('Alasan cabut izin') }}" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-xs px-2.5 py-1" />
                                                <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded-lg transition-colors">{{ __('Cabut') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">{{ __('Tidak ada pengajuan izin pada filter ini.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $leaveRequests->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
