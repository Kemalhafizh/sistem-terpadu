<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight tracking-tight">
            {{ __('Kuesioner Tracer Study Alumni') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto">
        <!-- Welcome Card -->
        <div class="bg-gradient-to-r from-primary-900 to-indigo-900 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-xl mb-8">
            <div class="relative z-10">
                <h3 class="text-2xl font-black mb-2">Halo, {{ $alumni->student->user->name }}!</h3>
                <p class="text-primary-100 text-sm max-w-2xl leading-relaxed">
                    Terima kasih telah berkontribusi untuk kemajuan almamater tercinta. Tracer study ini bertujuan untuk melacak serapan lulusan, keselarasan kurikulum, serta mendapatkan masukan berharga dari Anda demi perbaikan Sistem Terpadu {{ school_short_name() }}.
                </p>
                <div class="mt-4 inline-flex items-center px-3 py-1 bg-white/10 backdrop-blur-md rounded-xl text-xs font-bold text-primary-200">
                    Graduation Year: {{ $alumni->graduation_year }}
                </div>
            </div>
            <div class="absolute -right-10 -bottom-10 opacity-10">
                <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center space-x-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-700 p-8"
             x-data="{ 
                 activity: '{{ old('current_activity', $existingResponse?->alumni?->current_activity ?? $alumni->current_activity ?? 'other') }}',
                 relevance: {{ old('relevance_score', $existingResponse?->relevance_score ?? 5) }},
                 satisfaction: {{ old('satisfaction_score', $existingResponse?->satisfaction_score ?? 5) }}
             }">
            
            <form action="{{ route('alumni.tracer-study.submit') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Aktivitas Saat Ini -->
                <div class="space-y-4">
                    <label class="block text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                        Aktivitas Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        @foreach([
                            'work' => ['Bekerja', 'briefcase'],
                            'study' => ['Kuliah', 'academic-cap'],
                            'entrepreneur' => ['Wirausaha', 'light-bulb'],
                            'unemployed' => ['Mencari Kerja', 'search'],
                            'other' => ['Lainnya', 'dots-horizontal']
                        ] as $key => $val)
                            <label class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all text-center group"
                                   :class="activity === '{{ $key }}' ? 'border-primary-600 bg-primary-50/50 dark:bg-primary-950/20 text-primary-600' : 'border-gray-100 dark:border-gray-700 hover:border-gray-200 dark:hover:border-gray-600 bg-gray-50/30 dark:bg-gray-800/30'">
                                <input type="radio" name="current_activity" value="{{ $key }}" class="sr-only" x-model="activity">
                                
                                @if($val[1] === 'briefcase')
                                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                @elseif($val[1] === 'academic-cap')
                                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                @elseif($val[1] === 'light-bulb')
                                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                @elseif($val[1] === 'search')
                                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                @else
                                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                                @endif

                                <span class="text-xs font-bold leading-tight">{{ $val[0] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('current_activity')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Conditional Fields: WORK / ENTREPRENEUR -->
                <div x-show="activity === 'work' || activity === 'entrepreneur'" 
                     x-transition:enter="transition ease-out duration-300 transform opacity-0 -translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-6 bg-gray-50/50 dark:bg-gray-900/30 p-6 rounded-3xl border border-gray-100 dark:border-gray-700">
                    
                    <h4 class="text-sm font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest mb-4">Informasi Pekerjaan / Wirausaha</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Perusahaan / Instansi -->
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider">Nama Perusahaan / Bisnis <span class="text-red-500">*</span></label>
                            <input type="text" name="institution_name" value="{{ old('institution_name', $existingResponse?->alumni?->institution_name) }}"
                                   class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                   placeholder="Contoh: PT Telkom Indonesia / Coffee Shop Jaya">
                            @error('institution_name')
                                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jabatan / Posisi -->
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider">Jabatan / Posisi Pekerjaan <span class="text-red-500">*</span></label>
                            <input type="text" name="job_title" value="{{ old('job_title', $existingResponse?->job_title) }}"
                                   class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                   placeholder="Contoh: Junior Developer / Owner">
                            @error('job_title')
                                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Rentang Gaji -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider">Rentang Pendapatan Bulanan <span class="text-red-500">*</span></label>
                            <select name="salary_range" class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                <option value="">Pilih Rentang Gaji</option>
                                <option value="< 3M" {{ old('salary_range', $existingResponse?->salary_range) === '< 3M' ? 'selected' : '' }}>< Rp 3.000.000,-</option>
                                <option value="3M - 5M" {{ old('salary_range', $existingResponse?->salary_range) === '3M - 5M' ? 'selected' : '' }}>Rp 3.000.000 - Rp 5.000.000</option>
                                <option value="5M - 10M" {{ old('salary_range', $existingResponse?->salary_range) === '5M - 10M' ? 'selected' : '' }}>Rp 5.000.000 - Rp 10.000.000</option>
                                <option value="> 10M" {{ old('salary_range', $existingResponse?->salary_range) === '> 10M' ? 'selected' : '' }}>> Rp 10.000.000,-</option>
                            </select>
                            @error('salary_range')
                                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Conditional Fields: STUDY -->
                <div x-show="activity === 'study'" 
                     x-transition:enter="transition ease-out duration-300 transform opacity-0 -translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-6 bg-gray-50/50 dark:bg-gray-900/30 p-6 rounded-3xl border border-gray-100 dark:border-gray-700">
                    
                    <h4 class="text-sm font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest mb-4">Informasi Pendidikan Tinggi</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Perguruan Tinggi -->
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider">Nama Perguruan Tinggi / Universitas <span class="text-red-500">*</span></label>
                            <input type="text" name="institution_name" value="{{ old('institution_name', $existingResponse?->alumni?->institution_name) }}"
                                   class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                   placeholder="Contoh: Universitas Indonesia / ITB">
                            @error('institution_name')
                                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jurusan / Program Studi -->
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider">Program Studi / Jurusan <span class="text-red-500">*</span></label>
                            <input type="text" name="study_major" value="{{ old('study_major', $existingResponse?->study_major) }}"
                                   class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                   placeholder="Contoh: Teknik Informatika / Sastra Arab">
                            @error('study_major')
                                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Evaluasi & Masukan -->
                <div class="space-y-6 border-t border-gray-100 dark:border-gray-700 pt-8">
                    <h3 class="text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">Evaluasi & Kepuasan Layanan</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Keselarasan Kurikulum -->
                        <div class="space-y-3 bg-gray-50/50 dark:bg-gray-900/30 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 flex flex-col justify-center">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Relevansi Kurikulum Sekolah</label>
                            <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                                Sejauh mana pengetahuan dan keterampilan yang Anda peroleh di sekolah relevan dengan aktivitas Anda saat ini?
                            </p>
                            
                            <!-- Star Rating -->
                            <div class="flex items-center space-x-2">
                                <input type="hidden" name="relevance_score" x-model="relevance">
                                <template x-for="i in 5">
                                    <button type="button" @click="relevance = i" class="focus:outline-none transition-transform active:scale-90">
                                        <svg class="w-8 h-8 transition-colors"
                                             :class="i <= relevance ? 'text-amber-400 fill-amber-400' : 'text-gray-300 dark:text-gray-600'"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.25.591 1.813l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.18 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.104c-.769-.563-.37-1.813.591-1.813h4.907a1 1 0 00.95-.69l1.519-4.674z"/>
                                        </svg>
                                    </button>
                                </template>
                                <span class="text-sm font-black text-gray-500 ml-4" x-text="relevance + ' / 5'"></span>
                            </div>
                        </div>

                        <!-- Skor Kepuasan Sekolah -->
                        <div class="space-y-3 bg-gray-50/50 dark:bg-gray-900/30 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 flex flex-col justify-center">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Skor Kepuasan terhadap Sekolah</label>
                            <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                                Secara keseluruhan, seberapa puas Anda dengan pelayanan akademik, pembinaan akhlak, dan fasilitas sekolah selama menempuh studi?
                            </p>
                            
                            <!-- Star Rating -->
                            <div class="flex items-center space-x-2">
                                <input type="hidden" name="satisfaction_score" x-model="satisfaction">
                                <template x-for="i in 5">
                                    <button type="button" @click="satisfaction = i" class="focus:outline-none transition-transform active:scale-90">
                                        <svg class="w-8 h-8 transition-colors"
                                             :class="i <= satisfaction ? 'text-amber-400 fill-amber-400' : 'text-gray-300 dark:text-gray-600'"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.25.591 1.813l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.18 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.104c-.769-.563-.37-1.813.591-1.813h4.907a1 1 0 00.95-.69l1.519-4.674z"/>
                                        </svg>
                                    </button>
                                </template>
                                <span class="text-sm font-black text-gray-500 ml-4" x-text="satisfaction + ' / 5'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Saran/Masukan -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider">Saran & Umpan Balik untuk Kurikulum/Fasilitas Sekolah</label>
                        <textarea name="suggestions" rows="4"
                                  class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                  placeholder="Berikan masukan konstruktif Anda di sini (misal: penambahan materi teknologi terbaru, bimbingan karir yang lebih intensif, dll)...">{{ old('suggestions', $existingResponse?->suggestions) }}</textarea>
                        @error('suggestions')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="submit" class="px-8 py-4 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl text-sm font-black shadow-lg shadow-primary-900/10 transition-all active:scale-95">
                        SIMPAN KUESIONER TRACER
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
