<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Persetujuan Izin &amp; Sakit Siswa"
            subtitle="Tinjau pengajuan izin dari wali murid untuk rombel perwalian atau kesiswaan."
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

            <div class="flex flex-wrap gap-2">
                @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan', 'all' => 'Semua'] as $key => $label)
                    <a href="{{ route('teacher.leave-requests.index', ['status' => $key]) }}"
                       class="px-4 py-2 rounded-lg text-xs font-bold uppercase {{ $statusFilter === $key ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-900 text-xs uppercase text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3 text-start">Siswa &amp; Rombel</th>
                                <th class="px-4 py-3 text-start">Tanggal</th>
                                <th class="px-4 py-3 text-start">Jenis &amp; Alasan</th>
                                <th class="px-4 py-3 text-start">Status</th>
                                <th class="px-4 py-3 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($leaveRequests as $leave)
                                @php
                                    $statusBadge = match ($leave->status) {
                                        'approved' => ['Disetujui', 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300'],
                                        'rejected' => ['Ditolak', 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300'],
                                        'cancelled' => ['Dibatalkan', 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'],
                                        'revoked' => ['Dicabut', 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300'],
                                        default => ['Menunggu', 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300'],
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                        {{ $leave->student?->user?->name ?? '-' }}
                                        <div class="text-xs text-slate-400 dark:text-slate-400">
                                            {{ $leave->classroom?->name ?? '-' }} &bull; Pengaju: {{ $leave->submitter?->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300">
                                        {{ $leave->start_date?->format('d/m/Y') }} - {{ $leave->end_date?->format('d/m/Y') }}
                                        <span class="text-slate-400 dark:text-slate-500">({{ $leave->days_count }} hr)</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold uppercase text-xs text-slate-900 dark:text-white">{{ $leave->type }}</span>
                                        <div class="text-xs text-slate-600 dark:text-slate-300">{{ $leave->reason }}</div>
                                        @if ($leave->attachment_path)
                                            <a href="{{ route('student-leave-requests.attachment', $leave->uuid) }}" class="text-xs text-emerald-600 dark:text-emerald-400 underline">Unduh Surat Dokter</a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <span class="px-2.5 py-1 rounded-full font-semibold {{ $statusBadge[1] }}">{{ $statusBadge[0] }}</span>
                                        @if ($leave->decision_notes)
                                            <div class="text-slate-500 dark:text-slate-400 mt-1">{{ $leave->decision_notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-end space-y-2">
                                        @if ($leave->status === \App\Models\StudentLeaveRequest::STATUS_PENDING)
                                            <div class="flex flex-wrap justify-end gap-2">
                                                <form method="POST" action="{{ route('teacher.leave-requests.approve', $leave->uuid) }}">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors">Setujui</button>
                                                </form>
                                                <form method="POST" action="{{ route('teacher.leave-requests.reject', $leave->uuid) }}" class="flex gap-1">
                                                    @csrf
                                                    <input type="text" name="decision_notes" required placeholder="Alasan tolak" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-xs px-2 py-1" />
                                                    <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition-colors">Tolak</button>
                                                </form>
                                            </div>
                                        @elseif ($leave->isApproved())
                                            <form method="POST" action="{{ route('teacher.leave-requests.revoke', $leave->uuid) }}" class="flex justify-end gap-1">
                                                @csrf
                                                <input type="text" name="decision_notes" required placeholder="Alasan cabut izin" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-xs px-2 py-1" />
                                                <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg transition-colors">Cabut</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Tidak ada pengajuan izin pada filter ini.</td>
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
