<x-app-layout>
    <x-slot name="title">Pembuatan Surat Resmi Massal</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Pembuatan Surat Resmi Massal (Bulk)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Buat draf surat resmi sekaligus untuk satu kelas / kelompok siswa terpilih.
                </p>
            </div>
            <a href="{{ route('letters.index') }}" class="inline-flex items-center justify-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-8 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-6">
            <h4 class="text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700/50 pb-2">
                 Parameter Pembuatan Massal
            </h4>

            <form action="{{ route('letters.bulk.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- template_id -->
                <div>
                    <label for="template_id" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Pilih Template Surat</label>
                    <select name="template_id" id="template_id" required class="w-full bg-gray-55 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                        <option value="">-- Pilih Template --</option>
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- classroom_id -->
                <div>
                    <label for="classroom_id" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Pilih Kelas Target</label>
                    <select name="classroom_id" id="classroom_id" required class="w-full bg-gray-55 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Common Field Values -->
                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 space-y-4">
                    <span class="block text-[11px] font-black uppercase text-gray-400 tracking-wider"> Variabel Isian Seragam</span>
                    
                    <div>
                        <label for="purpose" class="block text-[11px] font-bold text-gray-500 mb-1">Keperluan (purpose)</label>
                        <input type="text" name="field_values[purpose]" id="purpose" placeholder="Misal: Mengikuti Kegiatan Kunjungan Industri..." class="w-full bg-white dark:bg-gray-850 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="start_date" class="block text-[11px] font-bold text-gray-500 mb-1">Tanggal Mulai (start_date)</label>
                            <input type="date" name="field_values[start_date]" id="start_date" class="w-full bg-white dark:bg-gray-850 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-[11px] font-bold text-gray-500 mb-1">Tanggal Selesai (end_date)</label>
                            <input type="date" name="field_values[end_date]" id="end_date" class="w-full bg-white dark:bg-gray-850 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50 flex gap-2">
                    <a href="{{ route('letters.index') }}" class="w-1/2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-bold py-3 px-4 rounded-xl text-xs uppercase tracking-wider text-center transition-all">
                        Batal
                    </a>
                    <button type="submit" class="w-1/2 bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded-xl text-xs uppercase tracking-wider text-center transition-all shadow-md shadow-teal-500/20">
                        Generate Bulk
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
