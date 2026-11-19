<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                Perubahan Kontak &amp; Identitas Diri
            </h2>
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 hover:bg-gray-200 text-gray-700">
                &larr; Kembali ke Profil
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Kontak Langsung (Tanpa Persetujuan TU) -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Pembaruan Kontak Langsung
                </h3>
                <p class="text-xs text-gray-500 mt-1 mb-4">
                    Nomor HP/WhatsApp dan Email langsung tersimpan tanpa verifikasi Tata Usaha karena tidak mengubah identitas resmi Dapodik.
                </p>

                <form method="POST" action="{{ route('profile.contact.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor HP / WhatsApp</label>
                        <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" placeholder="0812xxxxxxxx" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                    </div>

                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" id="contact_email" name="email" value="{{ old('email', $user->email) }}" placeholder="nama@email.com" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                    </div>

                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold">
                        Simpan Kontak Sekarang
                    </button>
                </form>
            </div>

            <!-- Pengajuan Perubahan Identitas Resmi -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-6"
                 x-data="{
                     targets: @js($targets),
                     selectedKey: @js(($targets[0]['target_type'] ?? 'user').':'.($targets[0]['target_id'] ?? $user->id)),
                     get currentTarget() {
                         return this.targets.find(t => (t.target_type + ':' + t.target_id) === this.selectedKey) || this.targets[0] || { target_type: 'user', target_id: '', fields: {} };
                     }
                 }">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Ajukan Perubahan Data Identitas
                </h3>
                <p class="text-xs text-gray-500 mt-1 mb-4">
                    Untuk koreksi Nama, NISN, NIK, Tempat/Tanggal Lahir, Jenis Kelamin, Nama Ibu Kandung, atau Alamat KK. Wajib melampirkan foto KK / Akta / Ijazah.
                </p>

                <form method="POST" action="{{ route('profile.change-requests.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="target_type" :value="currentTarget.target_type">
                    <input type="hidden" name="target_id" :value="currentTarget.target_id">

                    <div>
                        <label for="target_selector" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data yang Diubah</label>
                        <select id="target_selector" x-model="selectedKey" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                            <template x-for="target in targets" :key="target.target_type + ':' + target.target_id">
                                <option :value="target.target_type + ':' + target.target_id" x-text="target.label"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="field_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kolom Identitas</label>
                        <select id="field_name" name="field_name" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                            <template x-for="(meta, key) in currentTarget.fields" :key="key">
                                <option :value="key" x-text="meta.label"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="new_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nilai Baru (Sesuai Dokumen Resmi)</label>
                        <input type="text" id="new_value" name="new_value" value="{{ old('new_value') }}" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alasan / Keterangan</label>
                        <textarea id="reason" name="reason" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">{{ old('reason') }}</textarea>
                    </div>

                    <div>
                        <label for="evidence" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bukti Pendukung (KK / Akta / Ijazah — JPG/PNG/PDF, maks. 5 MB)</label>
                        <input type="file" id="evidence" name="evidence" accept=".jpg,.jpeg,.png,.pdf" required class="mt-1 block w-full text-sm text-gray-600">
                    </div>

                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold">
                        Kirim ke Tata Usaha
                    </button>
                </form>
            </div>
        </div>

        <!-- Riwayat Pengajuan -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 overflow-x-auto">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Riwayat Pengajuan Perubahan Identitas</h3>

            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800 text-xs uppercase text-gray-500">
                        <th class="py-2 pr-3">Tanggal</th>
                        <th class="py-2 pr-3">Subjek</th>
                        <th class="py-2 pr-3">Kolom</th>
                        <th class="py-2 pr-3">Lama &rarr; Baru</th>
                        <th class="py-2 pr-3">Bukti</th>
                        <th class="py-2 pr-3">Status</th>
                        <th class="py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($requests as $item)
                        <tr>
                            <td class="py-3 pr-3 text-xs text-gray-500">{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="py-3 pr-3 font-medium">{{ $item->targetUser?->name ?? '-' }}</td>
                            <td class="py-3 pr-3">{{ $item->field_label }}</td>
                            <td class="py-3 pr-3">
                                <span class="text-gray-500">{{ $item->old_value ?: '-' }}</span>
                                &rarr;
                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $item->new_value }}</span>
                            </td>
                            <td class="py-3 pr-3">
                                @if($item->evidence_path)
                                    <a href="{{ route('profile.change-requests.evidence', $item->uuid) }}" target="_blank" class="text-indigo-600 hover:underline text-xs">
                                        Lihat Bukti
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 pr-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $item->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($item->status === 'rejected' ? 'bg-rose-100 text-rose-800' : ($item->status === 'cancelled' ? 'bg-gray-100 text-gray-600' : 'bg-amber-100 text-amber-800')) }}">
                                    {{ $item->status }}
                                </span>
                                @if($item->review_notes)
                                    <div class="text-xs text-gray-500 mt-1">{{ $item->review_notes }}</div>
                                @endif
                            </td>
                            <td class="py-3 text-right">
                                @if($item->status === \App\Models\ProfileChangeRequest::STATUS_PENDING && (int) $item->requester_user_id === (int) $user->id)
                                    <form method="POST" action="{{ route('profile.change-requests.cancel', $item->uuid) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs">
                                            Batalkan
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-gray-500">
                                Belum ada pengajuan perubahan data identitas.
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
