<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20" aria-hidden="true">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </span>
                    Ekspor Data Sekolah
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Unduh data terbaru langsung dari database dalam format Excel, CSV, PDF, JSON-LD, atau XML.</p>
            </div>
            <a href="{{ route('exports.history') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-bold transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Riwayat Ekspor
            </a>
        </div>
    </x-slot>

    @php
        $inputClass = 'w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500';
    @endphp

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Buat ekspor baru</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Berkas dibuat saat itu juga dari data terkini. Alamat, gaji, dan rekening tidak ikut diekspor.</p>
                </div>

                @if ($entities === [])
                    <p class="text-sm text-gray-500 dark:text-gray-400">Peran Anda belum memiliki akses ekspor data.</p>
                @else
                    <form action="{{ route('exports.generate') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                        @csrf
                        <div>
                            <label for="job_name" class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Nama berkas</label>
                            <input id="job_name" type="text" name="job_name" required maxlength="200"
                                   value="{{ old('job_name', 'Ekspor '.now()->locale('id')->translatedFormat('d F Y')) }}" class="{{ $inputClass }}">
                            <x-input-error :messages="$errors->get('job_name')" class="mt-1" />
                        </div>

                        <div>
                            <label for="entity_type" class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Data</label>
                            <select id="entity_type" name="entity_type" class="{{ $inputClass }}">
                                @foreach ($entities as $key => $label)
                                    <option value="{{ $key }}" @selected(old('entity_type') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('entity_type')" class="mt-1" />
                        </div>

                        <div>
                            <label for="format" class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Format</label>
                            <select id="format" name="format" class="{{ $inputClass }}">
                                @foreach ($formats as $key => $format)
                                    <option value="{{ $key }}" @selected(old('format', 'xlsx') === $key)>{{ $format['label'] }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('format')" class="mt-1" />
                        </div>

                        <div class="md:pt-5">
                            <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-600/30">
                                Buat berkas ekspor
                            </button>
                        </div>
                    </form>
                    <p class="text-xs text-gray-500 dark:text-gray-400">PDF memuat maksimal {{ number_format(\App\Services\Content\ExportEngineService::PDF_MAX_ROWS, 0, ',', '.') }} baris; gunakan Excel atau CSV untuk data yang lebih besar.</p>
                @endif
            </div>

            @include('content.exports._jobs-table', ['jobs' => $exportHistory, 'title' => 'Ekspor terbaru'])
        </div>
    </div>
</x-app-layout>
