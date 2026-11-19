<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Persetujuan Izin &amp; Sakit Siswa"
            subtitle="Tinjau pengajuan izin dari wali murid untuk rombel perwalian atau kesiswaan."
        />
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-wrap gap-2">
                @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan', 'all' => 'Semua'] as $key => $label)
                    <a href="{{ route('teacher.leave-requests.index', ['status' => $key]) }}"
                       class="px-4 py-2 rounded-lg text-xs font-bold uppercase {{ $statusFilter === $key ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-300 border border-gray-200 dark:border-slate-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-slate-900 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Siswa &amp; Rombel</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Jenis &amp; Alasan</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                            @forelse ($leaveRequests as $leave)
                                <tr>
                                    <td class="px-4 py-3 font-medium">
                                        {{ $leave->student?->user?->name ?? '-' }}
                                        <div class="text-xs text-gray-400">
                                            {{ $leave->classroom?->name ?? '-' }} &bull; Pengaju: {{ $leave->submitter?->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        {{ $leave->start_date?->format('d/m/Y') }} - {{ $leave->end_date?->format('d/m/Y') }}
                                        <span class="text-gray-400">({{ $leave->days_count }} hr)</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold uppercase text-xs">{{ $leave->type }}</span>
                                        <div class="text-xs text-gray-600 dark:text-slate-300">{{ $leave->reason }}</div>
                                        @if ($leave->attachment_path)
                                            <a href="{{ route('student-leave-requests.attachment', $leave->uuid) }}" class="text-xs text-emerald-600 underline">Unduh Surat Dokter</a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <span class="px-2 py-1 rounded bg-gray-100 dark:bg-slate-700 font-semibold">{{ $leave->status }}</span>
                                        @if ($leave->decision_notes)
                                            <div class="text-gray-500 mt-1">{{ $leave->decision_notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right space-y-2">
                                        @if ($leave->status === \App\Models\StudentLeaveRequest::STATUS_PENDING)
                                            <div class="flex justify-end gap-2">
                                                <form method="POST" action="{{ route('teacher.leave-requests.approve', $leave->uuid) }}">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg">Setujui</button>
                                                </form>
                                                <form method="POST" action="{{ route('teacher.leave-requests.reject', $leave->uuid) }}" class="flex gap-1">
                                                    @csrf
                                                    <input type="text" name="decision_notes" required placeholder="Alasan tolak" class="rounded-lg border-gray-300 text-xs px-2 py-1" />
                                                    <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg">Tolak</button>
                                                </form>
                                            </div>
                                        @elseif ($leave->isApproved())
                                            <form method="POST" action="{{ route('teacher.leave-requests.revoke', $leave->uuid) }}" class="flex justify-end gap-1">
                                                @csrf
                                                <input type="text" name="decision_notes" required placeholder="Alasan cabut izin" class="rounded-lg border-gray-300 text-xs px-2 py-1" />
                                                <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg">Cabut</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Tidak ada pengajuan izin pada filter ini.</td>
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
