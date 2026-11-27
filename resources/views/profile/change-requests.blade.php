<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-900 dark:text-white leading-tight">
                {{ __('Perubahan Kontak & Identitas Diri') }}
            </h2>
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-medium bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span>{{ __('Kembali ke Profil') }}</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
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

        @php
            $initialTargetKey = ($targets[0]['target_type'] ?? 'user').':'.($targets[0]['target_id'] ?? $user->id);
            $initialFieldKey = array_key_first($targets[0]['fields'] ?? ['name' => []]) ?? 'name';
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Kontak Langsung --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                    {{ __('Pembaruan Kontak Langsung') }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">
                    {{ __('Nomor HP/WhatsApp dan Email langsung tersimpan tanpa verifikasi Tata Usaha karena tidak mengubah identitas resmi Dapodik.') }}
                </p>

                <form method="POST" action="{{ route('profile.contact.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Nomor HP / WhatsApp') }}</label>
                        <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->getAttributes()['phone_number'] ?? '') }}" placeholder="0812xxxxxxxx" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm tabular-nums">
                    </div>

                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Email') }}</label>
                        <input type="email" id="contact_email" name="email" value="{{ old('email', $user->getAttributes()['email'] ?? '') }}" placeholder="nama@email.com" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                    </div>

                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors">
                        {{ __('Simpan Kontak Sekarang') }}
                    </button>
                </form>
            </div>

            {{-- Pengajuan Perubahan Identitas Resmi --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6"
                 x-data="{
                     targets: @js($targets),
                     selectedKey: @js($initialTargetKey),
                     selectedField: @js(old('field_name', $initialFieldKey)),
                     emptyLabel: @js(__('Belum diisi')),
                     get currentTarget() {
                         return this.targets.find(t => (t.target_type + ':' + t.target_id) === this.selectedKey) || this.targets[0] || { target_type: 'user', target_id: '', fields: {}, current_values: {} };
                     },
                     syncField() {
                         const keys = Object.keys(this.currentTarget.fields || {});
                         if (keys.length > 0 && !keys.includes(this.selectedField)) {
                             this.selectedField = keys[0];
                         }
                     },
                     get recordedValue() {
                         const vals = this.currentTarget.current_values || {};
                         const v = vals[this.selectedField];
                         return (v !== null && v !== undefined && String(v).trim() !== '') ? v : this.emptyLabel;
                     },
                     get dapodikCol() {
                         return (this.currentTarget.fields || {})[this.selectedField]?.dapodik_column || null;
                     }
                 }">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                    {{ __('Ajukan Perubahan Data Identitas') }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">
                    {{ __('Untuk koreksi Nama, NISN, NIK, Tempat/Tanggal Lahir, Jenis Kelamin, Nama Ibu Kandung, atau Alamat KK. Wajib melampirkan foto KK, Akta, atau Ijazah.') }}
                </p>

                <form method="POST" action="{{ route('profile.change-requests.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="target_type" :value="currentTarget.target_type">
                    <input type="hidden" name="target_id" :value="currentTarget.target_id">

                    <div>
                        <label for="target_selector" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Data yang Diubah') }}</label>
                        <select id="target_selector" x-model="selectedKey" @change="syncField()" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                            <template x-for="target in targets" :key="target.target_type + ':' + target.target_id">
                                <option :value="target.target_type + ':' + target.target_id" x-text="target.label"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="field_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Kolom Identitas') }}</label>
                        <select id="field_name" name="field_name" x-model="selectedField" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                            <template x-for="(meta, key) in currentTarget.fields" :key="key">
                                <option :value="key" x-text="meta.label"></option>
                            </template>
                        </select>
                        <div class="mt-2 px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300 flex flex-wrap items-center justify-between gap-2">
                            <span>{{ __('Tercatat saat ini') }}: <strong class="text-slate-900 dark:text-white" x-text="recordedValue"></strong></span>
                            <template x-if="dapodikCol">
                                <span class="text-slate-500 dark:text-slate-400">{{ __('Kolom Dapodik') }}: <span x-text="dapodikCol"></span></span>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label for="new_value" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Nilai Baru (Sesuai Dokumen Resmi)') }}</label>
                        <input type="text" id="new_value" name="new_value" value="{{ old('new_value') }}" required class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Alasan / Keterangan') }}</label>
                        <textarea id="reason" name="reason" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">{{ old('reason') }}</textarea>
                    </div>

                    <div>
                        <label for="evidence" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Bukti Pendukung (KK / Akta / Ijazah, JPG/PNG/PDF maks. 5 MB)') }}</label>
                        <input type="file" id="evidence" name="evidence" accept=".jpg,.jpeg,.png,.pdf" required class="mt-1 block w-full text-sm text-slate-600 dark:text-slate-300 file:me-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-200">
                    </div>

                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium transition-colors">
                        {{ __('Kirim ke Tata Usaha') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Riwayat Pengajuan --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 overflow-x-auto">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-4">{{ __('Riwayat Pengajuan Perubahan Identitas') }}</h3>

            <table class="w-full text-start text-sm border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <th class="py-2.5 pe-3 text-start">{{ __('Tanggal') }}</th>
                        <th class="py-2.5 pe-3 text-start">{{ __('Subjek') }}</th>
                        <th class="py-2.5 pe-3 text-start">{{ __('Kolom') }}</th>
                        <th class="py-2.5 pe-3 text-start">{{ __('Perubahan Nilai') }}</th>
                        <th class="py-2.5 pe-3 text-start">{{ __('Bukti') }}</th>
                        <th class="py-2.5 pe-3 text-start">{{ __('Status') }}</th>
                        <th class="py-2.5 text-end">{{ __('Aksi') }}</th>
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
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3 pe-3 text-xs text-slate-500 dark:text-slate-400 tabular-nums">{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="py-3 pe-3 font-medium text-slate-900 dark:text-white">{{ $item->targetUser?->name ?? '-' }}</td>
                            <td class="py-3 pe-3 text-slate-700 dark:text-slate-200">{{ $item->field_label }}</td>
                            <td class="py-3 pe-3">
                                <span class="text-slate-500 dark:text-slate-400">{{ $item->old_value ?: '-' }}</span>
                                <span class="mx-1 text-slate-400" aria-hidden="true">/</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $item->new_value }}</span>
                            </td>
                            <td class="py-3 pe-3">
                                @if($item->evidence_path)
                                    <a href="{{ route('profile.change-requests.evidence', $item->uuid) }}" target="_blank" rel="noopener" class="text-primary-600 dark:text-primary-400 hover:underline text-xs font-medium">
                                        {{ __('Lihat Bukti') }}
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 pe-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium {{ $statusBadge[1] }}">
                                    {{ $statusBadge[0] }}
                                </span>
                                @if($item->review_notes)
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $item->review_notes }}</div>
                                @endif
                            </td>
                            <td class="py-3 text-end">
                                @if($item->status === \App\Models\ProfileChangeRequest::STATUS_PENDING && (int) $item->requester_user_id === (int) $user->id)
                                    <form method="POST" action="{{ route('profile.change-requests.cancel', $item->uuid) }}" onsubmit="return confirm(@js(__('Batalkan pengajuan perubahan identitas ini?')))">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 text-xs font-medium transition-colors">
                                            {{ __('Batalkan') }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                {{ __('Belum ada pengajuan perubahan data identitas.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
