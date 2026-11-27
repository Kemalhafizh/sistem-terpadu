<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Pengajuan Izin & Sakit Siswa')"
            :subtitle="__('Ajukan ketidakhadiran anak untuk diteruskan ke wali kelas dan disinkronkan ke rekap presensi')"
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

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6"
                 x-data="{
                     type: @js(old('type', 'sakit')),
                     startDate: @js(old('start_date', now()->toDateString())),
                     endDate: @js(old('end_date', now()->toDateString())),
                     threshold: {{ (int) $medicalLetterAfterDays }},
                     get requiresLetter() {
                         if (this.type !== 'sakit' || !this.startDate) return false;
                         const start = new Date(this.startDate);
                         const end = new Date(this.endDate || this.startDate);
                         const diffDays = Math.floor((end - start) / 86400000) + 1;
                         return diffDays > this.threshold;
                     }
                 }">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">{{ __('Form Pengajuan Baru') }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                    {{ __('Izin sakit hingga :auto hari diproses otomatis. Izin sakit lebih dari :letter hari wajib melampirkan surat keterangan dokter (JPG/PNG/PDF).', ['auto' => $autoApproveSickDays, 'letter' => $medicalLetterAfterDays]) }}
                </p>

                <div x-show="requiresLetter" style="display: none;" class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-200 text-xs font-medium">
                    {{ __('Durasi izin sakit melebihi :days hari: lampirkan surat keterangan dokter sebelum mengirim pengajuan.', ['days' => $medicalLetterAfterDays]) }}
                </div>

                <form method="POST" action="{{ route('parent-portal.leave-requests.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf

                    <div>
                        <label for="student_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Anak') }}</label>
                        <select id="student_id" name="student_id" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                            @foreach ($children as $child)
                                <option value="{{ $child->id }}" @selected(old('student_id') == $child->id)>
                                    {{ $child->user?->name ?? '-' }} ({{ $child->classroom?->name ?? __('Tanpa Rombel') }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('student_id')" class="mt-1" />
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Jenis') }}</label>
                        <select id="type" name="type" x-model="type" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                            <option value="sakit" @selected(old('type') === 'sakit')>{{ __('Sakit') }}</option>
                            <option value="izin" @selected(old('type') === 'izin')>{{ __('Izin Keperluan Keluarga / Lainnya') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-1" />
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Tanggal Mulai') }}</label>
                        <input id="start_date" type="date" name="start_date" x-model="startDate" value="{{ old('start_date', now()->toDateString()) }}" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm tabular-nums" />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Tanggal Selesai') }}</label>
                        <input id="end_date" type="date" name="end_date" x-model="endDate" value="{{ old('end_date', now()->toDateString()) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm tabular-nums" />
                        <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                    </div>

                    <div class="md:col-span-2">
                        <label for="reason" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Alasan / Keterangan') }}</label>
                        <textarea id="reason" name="reason" rows="2" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">{{ old('reason') }}</textarea>
                        <x-input-error :messages="$errors->get('reason')" class="mt-1" />
                    </div>

                    <div class="md:col-span-2">
                        <label for="attachment" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            {{ __('Lampiran Surat Dokter / Bukti') }}
                            <span class="font-normal text-xs text-slate-500 dark:text-slate-400">({{ __('Wajib untuk sakit lebih dari :days hari', ['days' => $medicalLetterAfterDays]) }})</span>
                        </label>
                        <input id="attachment" type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf" :required="requiresLetter" class="w-full text-sm text-slate-600 dark:text-slate-300 file:me-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-200" />
                        <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
                    </div>

                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                            {{ __('Kirim Pengajuan') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Riwayat Pengajuan') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-xs font-medium text-slate-600 dark:text-slate-400">
                            <tr>
                                <th class="px-4 py-3 text-start">{{ __('Siswa') }}</th>
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
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $leave->classroom?->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300 tabular-nums">
                                        {{ $leave->start_date?->format('d/m/Y') }} - {{ $leave->end_date?->format('d/m/Y') }}
                                        <span class="text-slate-500 dark:text-slate-400">({{ $leave->days_count }} {{ __('hari') }})</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-xs text-slate-900 dark:text-white">{{ $typeLabel }}</span>
                                        <div class="text-xs text-slate-600 dark:text-slate-300">{{ $leave->reason }}</div>
                                        @if ($leave->attachment_path)
                                            <a href="{{ route('student-leave-requests.attachment', $leave->uuid) }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">{{ __('Lihat Lampiran') }}</a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-md font-medium {{ $statusBadge[1] }}">{{ $statusBadge[0] }}</span>
                                        @if ($leave->decision_notes)
                                            <div class="text-slate-500 dark:text-slate-400 mt-1">{{ $leave->decision_notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        @if ($leave->status === \App\Models\StudentLeaveRequest::STATUS_PENDING)
                                            <form method="POST" action="{{ route('parent-portal.leave-requests.cancel', $leave->uuid) }}" class="inline" onsubmit="return confirm(@js(__('Batalkan pengajuan izin/sakit ini?')))">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 text-xs font-medium rounded-lg transition-colors">{{ __('Batalkan') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">{{ __('Belum ada pengajuan izin atau sakit.') }}</td>
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
