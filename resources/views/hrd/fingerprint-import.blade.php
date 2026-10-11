<x-app-layout>
    <x-slot name="title">{{ __('Impor Presensi Sidik Jari') }}</x-slot>

    <x-slot name="header">
        <div>
            <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">{{ __('Impor Presensi Sidik Jari') }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ __('Unggah log yang diekspor mesin sidik jari (CSV, TXT, DAT, atau Excel). Scan pertama sehari menjadi jam masuk, scan terakhir menjadi jam pulang. Mengimpor file yang sama dua kali tidak menggandakan data.') }}
            </p>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <form method="POST" action="{{ route('hrd.fingerprint-import.store') }}" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-4 md:items-end">
                @csrf
                <div class="flex-1">
                    <label for="file" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ __('File log mesin') }}</label>
                    <input id="file" type="file" name="file" required accept=".csv,.txt,.dat,.xlsx,.xls" class="w-full text-sm">
                    @error('file')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-bold">{{ __('Impor') }}</button>
            </form>
        </div>

        @if($lastImport)
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                <h3 class="font-bold text-gray-800 dark:text-white">{{ __('Hasil impor: :file', ['file' => $lastImport->original_name]) }}</h3>
                <dl class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                    <div><dt class="text-xs text-gray-400">{{ __('Baris dibaca') }}</dt><dd class="font-black">{{ $lastImport->rows_read }}</dd></div>
                    <div><dt class="text-xs text-gray-400">{{ __('Hari presensi baru') }}</dt><dd class="font-black">{{ $lastImport->attendances_created }}</dd></div>
                    <div><dt class="text-xs text-gray-400">{{ __('Hari diperbarui') }}</dt><dd class="font-black">{{ $lastImport->attendances_updated }}</dd></div>
                    <div><dt class="text-xs text-gray-400">{{ __('Sudah ada, tidak berubah') }}</dt><dd class="font-black">{{ $lastImport->attendances_unchanged }}</dd></div>
                    <div><dt class="text-xs text-gray-400">{{ __('Hari cuti/izin dibiarkan') }}</dt><dd class="font-black">{{ $lastImport->leave_days_kept }}</dd></div>
                </dl>

                @if($lastImport->unknown_ids)
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-sm space-y-2">
                        <p class="font-bold">{{ __('ID mesin berikut belum dipetakan ke pegawai mana pun, jadi scan-nya tidak dicatat:') }}</p>
                        <ul class="list-disc pl-5">
                            @foreach($lastImport->unknown_ids as $machineId => $count)
                                <li>{{ __('ID :id (:count scan)', ['id' => $machineId, 'count' => $count]) }}</li>
                            @endforeach
                        </ul>
                        <p class="text-xs">{{ __('Isi "ID di Mesin Sidik Jari" pada data pegawainya, lalu impor ulang file yang sama.') }}</p>
                    </div>
                @endif

                @if($lastImport->invalid_rows)
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-sm space-y-2">
                        <p class="font-bold">{{ __('Baris berikut tidak bisa dibaca dan dilewati:') }}</p>
                        <ul class="list-disc pl-5">
                            @foreach(array_slice($lastImport->invalid_rows, 0, 20) as $invalid)
                                <li>{{ __('Baris :line: :reason', ['line' => $invalid['line'], 'reason' => $invalid['reason']]) }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        @if($unmapped->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <h3 class="font-bold text-gray-800 dark:text-white mb-2">{{ __('Pegawai tanpa ID mesin sidik jari') }}</h3>
                <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                    @foreach($unmapped as $employee)
                        <li>
                            <a href="{{ route('hrd.employees.edit', $employee) }}" class="text-primary-600 hover:underline">{{ $employee->user->name ?? $employee->employee_number }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h3 class="font-bold text-gray-800 dark:text-white mb-3">{{ __('Riwayat impor') }}</h3>
            @forelse($imports as $import)
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ $import->created_at->translatedFormat('d M Y H:i') }} · {{ $import->original_name }} ·
                    {{ $import->first_date?->translatedFormat('d M') ?? '–' }}–{{ $import->last_date?->translatedFormat('d M Y') ?? '–' }} ·
                    {{ __(':created baru, :updated diperbarui', ['created' => $import->attendances_created, 'updated' => $import->attendances_updated]) }}
                    · {{ $import->importer?->name ?? '–' }}
                </p>
            @empty
                <p class="text-sm text-gray-400">{{ __('Belum ada log yang diimpor.') }}</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
