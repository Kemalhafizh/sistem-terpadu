<x-app-layout>
    <x-slot name="title">Edit Transaksi Kas OSIS</x-slot>

    <div class="max-w-3xl space-y-6">
        <x-page-header title="Edit Transaksi Kas OSIS" :subtitle="'Dicatat '.$transaction->created_at?->format('d M Y H:i').($transaction->recorder ? ' oleh '.$transaction->recorder->name : '')">
            <x-slot name="actions">
                <a href="{{ route('osis.dashboard') }}" class="text-sm font-bold text-indigo-600 hover:underline">&larr; Kembali</a>
            </x-slot>
        </x-page-header>

        <x-form.card>
            <form method="POST" action="{{ route('osis.transactions.update', $transaction->id) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.select name="type" label="Jenis" :options="['income' => 'Pemasukan', 'expense' => 'Pengeluaran']" :value="$transaction->type" required />
                    <x-form.select name="category" label="Kategori" :options="array_combine($c = ['Iuran', 'Sponsor', 'Kegiatan', 'Perlengkapan', 'Lainnya'], $c)" :value="$transaction->category" required />
                    <x-form.input name="amount" label="Nominal (Rp)" type="number" min="1" :value="(int) $transaction->amount" required />
                    <x-form.input name="transaction_date" label="Tanggal" type="date" :value="$transaction->transaction_date?->format('Y-m-d')" required />
                </div>
                <x-form.input name="description" label="Keterangan" :value="$transaction->description" maxlength="255" required />
                <div>
                    <x-form.input name="receipt" label="Ganti bukti transaksi (opsional)" type="file" accept=".jpg,.jpeg,.png,.pdf" hint="JPG, PNG atau PDF, maks. 10 MB." />
                    @if ($transaction->receipt_path)
                        <a href="{{ Storage::url($transaction->receipt_path) }}" target="_blank" class="inline-block mt-2 text-xs font-bold text-indigo-600 hover:underline">Lihat bukti saat ini</a>
                    @endif
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('osis.dashboard') }}" class="py-2.5 px-5 text-sm font-bold text-gray-600 dark:text-gray-300">Batal</a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-5 text-sm font-bold">Simpan perubahan</button>
                </div>
            </form>
        </x-form.card>
    </div>
</x-app-layout>
