<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Pengajuan Izin &amp; Sakit Siswa"
            subtitle="Ajukan ketidakhadiran anak untuk diteruskan ke wali kelas dan disinkronkan ke rekap presensi."
        />
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Form Pengajuan Baru</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-4">
                    Izin sakit hingga {{ $autoApproveSickDays }} hari diproses otomatis. Izin sakit lebih dari {{ $medicalLetterAfterDays }} hari wajib melampirkan surat keterangan dokter (JPG/PNG/PDF).
                </p>

                <form method="POST" action="{{ route('parent-portal.leave-requests.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf

                    <div>
                        <label for="student_id" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">Anak</label>
                        <select id="student_id" name="student_id" required class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 text-sm">
                            @foreach ($children as $child)
                                <option value="{{ $child->id }}" @selected(old('student_id') == $child->id)>
                                    {{ $child->user?->name ?? '-' }} ({{ $child->classroom?->name ?? 'Tanpa Rombel' }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('student_id')" class="mt-1" />
                    </div>

                    <div>
                        <label for="type" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">Jenis</label>
                        <select id="type" name="type" required class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 text-sm">
                            <option value="sakit" @selected(old('type') === 'sakit')>Sakit</option>
                            <option value="izin" @selected(old('type') === 'izin')>Izin Keperluan Keluarga / Lainnya</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-1" />
                    </div>

                    <div>
                        <label for="start_date" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">Tanggal Mulai</label>
                        <input id="start_date" type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" required class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 text-sm" />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                    </div>

                    <div>
                        <label for="end_date" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">Tanggal Selesai</label>
                        <input id="end_date" type="date" name="end_date" value="{{ old('end_date', now()->toDateString()) }}" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 text-sm" />
                        <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                    </div>

                    <div class="md:col-span-2">
                        <label for="reason" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">Alasan / Keterangan</label>
                        <textarea id="reason" name="reason" rows="2" required class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 text-sm">{{ old('reason') }}</textarea>
                        <x-input-error :messages="$errors->get('reason')" class="mt-1" />
                    </div>

                    <div class="md:col-span-2">
                        <label for="attachment" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1">Lampiran Surat Dokter / Bukti (Opsional kecuali sakit &gt; {{ $medicalLetterAfterDays }} hari)</label>
                        <input id="attachment" type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-sm text-gray-600" />
                        <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
                    </div>

                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase rounded-lg">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Riwayat Pengajuan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-slate-900 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Siswa</th>
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
                                        <div class="text-xs text-gray-400">{{ $leave->classroom?->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        {{ $leave->start_date?->format('d/m/Y') }} - {{ $leave->end_date?->format('d/m/Y') }}
                                        <span class="text-gray-400">({{ $leave->days_count }} hr)</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold uppercase text-xs">{{ $leave->type }}</span>
                                        <div class="text-xs text-gray-600 dark:text-slate-300">{{ $leave->reason }}</div>
                                        @if ($leave->attachment_path)
                                            <a href="{{ route('student-leave-requests.attachment', $leave->uuid) }}" class="text-xs text-emerald-600 underline">Lihat Lampiran</a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <span class="px-2 py-1 rounded bg-gray-100 dark:bg-slate-700 font-semibold">{{ $leave->status }}</span>
                                        @if ($leave->decision_notes)
                                            <div class="text-gray-500 mt-1">{{ $leave->decision_notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if ($leave->status === \App\Models\StudentLeaveRequest::STATUS_PENDING)
                                            <form method="POST" action="{{ route('parent-portal.leave-requests.cancel', $leave->uuid) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-bold rounded-lg">Batalkan</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada pengajuan izin atau sakit.</td>
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
