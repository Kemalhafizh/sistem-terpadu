@csrf
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nama Bank</label>
    <input type="text" name="bank_name" value="{{ old('bank_name', $account->bank_name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
    <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nomor Rekening</label>
    <input type="text" name="account_number" value="{{ old('account_number', $account->account_number) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
    <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nama Rekening</label>
    <input type="text" name="account_name" value="{{ old('account_name', $account->account_name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
    <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jenis Rekening</label>
    <select name="account_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        @foreach(['giro' => 'Giro', 'tabungan' => 'Tabungan', 'virtual_account' => 'Virtual Account'] as $value => $label)
            <option value="{{ $value }}" @selected(old('account_type', $account->account_type) === $value)>{{ $label }}</option>
        @endforeach
    </select></div>
    <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Saldo Awal (Rp)</label>
    <input type="number" step="0.01" min="0" name="current_balance" value="{{ old('current_balance', $account->exists ? $account->current_balance : null) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
    <p class="text-xs text-gray-500 mt-1">Saldo sebelum mutasi pertama yang akan diimpor; dipakai sebagai saldo awal posisi kas.</p></div>
    <div class="flex flex-col justify-center gap-3">
        <input type="hidden" name="is_active" value="0">
        <label class="inline-flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $account->is_active ?? true)) class="rounded border-gray-300"> Rekening aktif
        </label>
        <input type="hidden" name="is_primary" value="0">
        <label class="inline-flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="is_primary" value="1" @checked(old('is_primary', $account->is_primary)) class="rounded border-gray-300"> Jadikan rekening utama
        </label>
    </div>
</div>
<div class="mt-6"><button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Simpan</button></div>
