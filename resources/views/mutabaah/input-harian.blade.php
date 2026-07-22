<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Input Mutaba\'ah Harian') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Silakan isi amalan Anda untuk tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
                </p>
            </div>
            <a href="{{ route('mutabaah.index', ['date' => $date]) }}" 
               class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold uppercase tracking-wider border border-gray-200 shadow-sm transition-all flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Kalender
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('mutabaah.store-daily-log') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <input type="hidden" name="date" value="{{ $date }}">

                {{-- Shalat Fardhu Wajib --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider flex items-center gap-2">
                            <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg"></span>
                            Shalat 5 Waktu Wajib
                        </h3>
                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full uppercase tracking-wider">Maksimal 75 Poin</span>
                    </div>

                    <div class="space-y-4">
                        @foreach([
                            'subuh' => 'Shalat Subuh',
                            'dzuhur' => 'Shalat Dzuhur',
                            'ashar' => 'Shalat Ashar',
                            'maghrib' => 'Shalat Maghrib',
                            'isya' => 'Shalat Isya'
                        ] as $field => $label)
                            @php
                                $val = old("sholat_{$field}", $log ? $log->{"sholat_{$field}"} : 0);
                            @endphp
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-gray-50/50 rounded-2xl transition-all border border-transparent hover:border-gray-100/60">
                                <span class="text-xs font-black text-gray-700 mb-2 sm:mb-0">{{ $label }}</span>
                                <div class="grid grid-cols-3 gap-2 w-full sm:w-auto">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="sholat_{$field}" value="0" {{ $val === 0 ? 'checked' : '' }} class="sr-only peer">
                                        <div class="px-3 py-2 text-center rounded-xl border border-gray-200 text-[10px] font-black uppercase tracking-wider peer-checked:bg-rose-500 peer-checked:text-white peer-checked:border-rose-500 hover:bg-gray-50 transition-all">
                                            Tidak Shalat
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="sholat_{$field}" value="1" {{ $val === 1 ? 'checked' : '' }} class="sr-only peer">
                                        <div class="px-3 py-2 text-center rounded-xl border border-gray-200 text-[10px] font-black uppercase tracking-wider peer-checked:bg-primary-500 peer-checked:text-white peer-checked:border-primary-500 hover:bg-gray-50 transition-all">
                                            Munfarid
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="sholat_{$field}" value="2" {{ $val === 2 ? 'checked' : '' }} class="sr-only peer">
                                        <div class="px-3 py-2 text-center rounded-xl border border-gray-200 text-[10px] font-black uppercase tracking-wider peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:border-emerald-500 hover:bg-gray-50 transition-all">
                                            Berjamaah
                                        </div>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Shalat Sunnah & Quran --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider flex items-center gap-2">
                            <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg"></span>
                            Sunnah & Tadarus Al-Qur'an
                        </h3>
                        <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full uppercase tracking-wider">Maksimal 20 Poin</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-black text-gray-700 block mb-1">Shalat Sunnah Dhuha</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Melakukan shalat dhuha hari ini? (+5 pts)</span>
                            </div>
                            <div class="flex items-center space-x-3 mt-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="sholat_dhuha" value="0" {{ old('sholat_dhuha', $log ? $log->sholat_dhuha : 0) === 0 ? 'checked' : '' }} class="sr-only peer">
                                    <div class="px-4 py-2 rounded-xl border border-gray-200 text-[10px] font-black uppercase tracking-wider peer-checked:bg-rose-500 peer-checked:text-white peer-checked:border-rose-500 hover:bg-gray-50 transition-all">Tidak</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="sholat_dhuha" value="1" {{ old('sholat_dhuha', $log ? $log->sholat_dhuha : 0) === 1 ? 'checked' : '' }} class="sr-only peer">
                                    <div class="px-4 py-2 rounded-xl border border-gray-200 text-[10px] font-black uppercase tracking-wider peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:border-emerald-500 hover:bg-gray-50 transition-all">Ya</div>
                                </label>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-black text-gray-700 block mb-1">Shalat Qiyamul Lail (Tahajud)</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Melakukan shalat tahajud semalam? (+5 pts)</span>
                            </div>
                            <div class="flex items-center space-x-3 mt-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="sholat_tahajud" value="0" {{ old('sholat_tahajud', $log ? $log->sholat_tahajud : 0) === 0 ? 'checked' : '' }} class="sr-only peer">
                                    <div class="px-4 py-2 rounded-xl border border-gray-200 text-[10px] font-black uppercase tracking-wider peer-checked:bg-rose-500 peer-checked:text-white peer-checked:border-rose-500 hover:bg-gray-50 transition-all">Tidak</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="sholat_tahajud" value="1" {{ old('sholat_tahajud', $log ? $log->sholat_tahajud : 0) === 1 ? 'checked' : '' }} class="sr-only peer">
                                    <div class="px-4 py-2 rounded-xl border border-gray-200 text-[10px] font-black uppercase tracking-wider peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:border-emerald-500 hover:bg-gray-50 transition-all">Ya</div>
                                </label>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100 md:col-span-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <span class="text-xs font-black text-gray-700 block mb-1">Tadarus Al-Qur'an (Halaman)</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Berapa halaman Al-Qur'an yang Anda baca hari ini? (+10 pts jika &ge; 1 halaman)</span>
                            </div>
                            <div class="w-full sm:w-32">
                                <input type="number" name="tadarus_pages" min="0" value="{{ old('tadarus_pages', $log ? $log->tadarus_pages : 0) }}" 
                                       class="w-full border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl text-sm font-black text-gray-800 text-center">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Amalan Harian Lainnya --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider flex items-center gap-2">
                            <span class="p-1.5 bg-rose-50 text-rose-600 rounded-lg"></span>
                            Puasa, Sedekah & Dzikir
                        </h3>
                        <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full uppercase tracking-wider">Maksimal 15 Poin</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Puasa Sunnah --}}
                        <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                            <div>
                                <span class="text-xs font-black text-gray-700 block">Puasa Sunnah</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Senin-Kamis/lainnya (+5 pts)</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="puasa_sunnah" value="1" {{ old('puasa_sunnah', $log ? $log->puasa_sunnah : false) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        {{-- Sedekah --}}
                        <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                            <div>
                                <span class="text-xs font-black text-gray-700 block">Sedekah Harian</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Membantu sesama (+5 pts)</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="sedekah" value="1" {{ old('sedekah', $log ? $log->sedekah : false) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        {{-- Dzikir Pagi --}}
                        <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                            <div>
                                <span class="text-xs font-black text-gray-700 block">Dzikir Pagi</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Membaca dzikir ma'tsurat pagi (+2.5 pts)</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="dzikir_pagi" value="1" {{ old('dzikir_pagi', $log ? $log->dzikir_pagi : false) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        {{-- Dzikir Sore --}}
                        <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                            <div>
                                <span class="text-xs font-black text-gray-700 block">Dzikir Sore</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Membaca dzikir ma'tsurat sore (+2.5 pts)</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="dzikir_sore" value="1" {{ old('dzikir_sore', $log ? $log->dzikir_sore : false) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Catatan Harian / Refleksi --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 space-y-4">
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 pb-3">
                        <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg"></span>
                        Catatan Harian & Refleksi Diri
                    </h3>
                    <div>
                        <label for="catatan_harian" class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Catatan</label>
                        <textarea id="catatan_harian" name="catatan_harian" rows="3" placeholder="Bagikan refleksi Anda hari ini, misalnya: tantangan shalat tepat waktu, resolusi esok hari, atau ucapan syukur..."
                                  class="w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-2xl text-xs font-bold text-gray-700 placeholder-gray-400">{{ old('catatan_harian', $log ? $log->catatan_harian : '') }}</textarea>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('mutabaah.index', ['date' => $date]) }}" 
                       class="px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-black uppercase tracking-widest border border-gray-200 transition-all">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-900/20 hover:scale-[1.02] transition-all">
                        Simpan Amalan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
