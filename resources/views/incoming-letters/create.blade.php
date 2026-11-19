<x-app-layout>
    <x-slot name="title">Catat Surat Masuk</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    Catat Agenda Surat Masuk Baru
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Nomor agenda surat masuk akan diterbitkan otomatis oleh sistem saat disimpan
                </p>
            </div>
            <a href="{{ route('incoming-letters.index') }}" class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white text-xs font-bold">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6 w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form action="{{ route('incoming-letters.store') }}" method="POST" enctype="multipart/form-data" class="bg-white/80 dark:bg-gray-800/80 rounded-3xl border border-gray-200/60 dark:border-gray-700/50 p-6 sm:p-8 shadow-sm space-y-5">
            @csrf

            @if($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold space-y-1">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Surat Asal *</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number') }}" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" placeholder="Contoh: 421/019/DISDIK/2026">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Kategori / Sifat Surat</label>
                    <input type="text" name="category" value="{{ old('category') }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" placeholder="Dinas / Undangan / Edaran">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pengirim / Instansi Asal *</label>
                <input type="text" name="sender" value="{{ old('sender') }}" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" placeholder="Dinas Pendidikan Provinsi Jawa Tengah">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Perihal Surat *</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" placeholder="Undangan Rapat Koordinasi Kepala SMA">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal Surat *</label>
                    <input type="date" name="letter_date" value="{{ old('letter_date', now()->toDateString()) }}" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal Diterima Sekolah *</label>
                    <input type="date" name="received_date" value="{{ old('received_date', now()->toDateString()) }}" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Ringkasan Isi Surat</label>
                <textarea name="summary" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" placeholder="Poin ringkas surat masuk...">{{ old('summary') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pindaian Surat (PDF/JPG/PNG, maksimal 5 MB)</label>
                <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-600 dark:text-gray-300">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('incoming-letters.index') }}" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-bold">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow">Simpan Agenda Surat</button>
            </div>
        </form>
    </div>
</x-app-layout>
