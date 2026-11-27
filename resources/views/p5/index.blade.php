<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-slate-900 dark:text-slate-100 leading-tight">
                    {{ __('Projek Penguatan Profil Pelajar Pancasila (P5)') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ __('Pengelolaan tema, dimensi capaian fase, penilaian fasilitator (MB/SB/BSH/SAB), dan Rapor Projek Kurikulum Merdeka') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm">
                <ul class="list-disc ps-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('p5.projects.index') }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex flex-wrap items-end gap-4">
            <div>
                <label for="school_year_id" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Tahun Ajaran') }}</label>
                <select name="school_year_id" id="school_year_id" class="rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm">
                    <option value="">{{ __('Semua Tahun Ajaran') }}</option>
                    @foreach ($schoolYears as $sy)
                        <option value="{{ $sy->id }}" @selected((int) $selectedYearId === (int) $sy->id)>{{ $sy->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="grade_level" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Tingkat') }}</label>
                <select name="grade_level" id="grade_level" class="rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm">
                    <option value="">{{ __('Semua Tingkat') }}</option>
                    @foreach (['X', 'XI', 'XII'] as $lvl)
                        <option value="{{ $lvl }}" @selected($gradeFilter === $lvl)>Kelas {{ $lvl }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-sm font-medium">
                {{ __('Terapkan Filter') }}
            </button>
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Daftar Projek P5 --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4">{{ __('Daftar Projek P5') }}</h3>
                    <div class="space-y-4">
                        @forelse ($projects as $project)
                            <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        <span class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-medium">
                                            Kelas {{ $project->grade_level }} · Fase {{ $project->phase }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                                            {{ $project->theme_label }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-medium uppercase">
                                            {{ $project->status }}
                                        </span>
                                    </div>
                                    <h4 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $project->title }}
                                    </h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        TA {{ $project->schoolYear?->name ?? '-' }}
                                        · Koordinator: {{ $project->coordinator?->name ?? '-' }}
                                        · {{ $project->dimensions->count() }} dimensi
                                        · {{ $project->assessments_count }} penilaian tercatat
                                    </p>
                                </div>
                                <div class="shrink-0">
                                    <a href="{{ route('p5.projects.show', $project) }}" class="inline-flex items-center px-3.5 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                                        {{ __('Kelola & Nilai') }} &rarr;
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Belum ada Projek P5 pada filter yang dipilih.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Form Buat Projek P5 (Koordinator P5 / Waka Kurikulum) --}}
            <div class="space-y-4">
                @if ($canManage)
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4">{{ __('Buat Projek P5 Baru') }}</h3>
                        <form method="POST" action="{{ route('p5.projects.store') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Tahun Ajaran') }}</label>
                                <select name="school_year_id" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm">
                                    @foreach ($schoolYears as $sy)
                                        <option value="{{ $sy->id }}" @selected((int) $selectedYearId === (int) $sy->id)>{{ $sy->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Tingkat') }}</label>
                                    <select name="grade_level" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm">
                                        <option value="X">Kelas X (Fase E)</option>
                                        <option value="XI">Kelas XI (Fase F)</option>
                                        <option value="XII">Kelas XII (Fase F)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Koordinator') }}</label>
                                    <select name="coordinator_id" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm">
                                        @foreach ($teachers as $t)
                                            <option value="{{ $t->id }}" @selected(auth()->id() === $t->id)>{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Tema Resmi Kemendikdasmen') }}</label>
                                <select name="theme" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm">
                                    @foreach ($themes as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Judul Projek') }}</label>
                                <input type="text" name="title" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm" placeholder="Contoh: Jejak Budaya Pesisir Semarang">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Deskripsi Singkat') }}</label>
                                <textarea name="description" rows="2" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm"></textarea>
                            </div>

                            <div class="border-t border-slate-200 dark:border-slate-800 pt-3 space-y-3">
                                <span class="block text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Dimensi Utama & Capaian Fase') }}</span>
                                <div>
                                    <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('Dimensi Profil Pelajar Pancasila') }}</label>
                                    <select name="dimensions[0][character_dimension_id]" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm">
                                        @foreach ($dimensions as $dim)
                                            <option value="{{ $dim->id }}">{{ $dim->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('Elemen') }}</label>
                                    <input type="text" name="dimensions[0][element]" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm" placeholder="Contoh: Kolaborasi">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('Sub-Elemen (Opsional)') }}</label>
                                    <input type="text" name="dimensions[0][sub_element]" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm" placeholder="Contoh: Kerja sama">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('Target Capaian Akhir Fase') }}</label>
                                    <textarea name="dimensions[0][target_achievement]" rows="2" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm" placeholder="Membangun tim dan mengelola kerjasama untuk mencapai tujuan bersama."></textarea>
                                </div>
                            </div>

                            <button type="submit" class="w-full py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold">
                                {{ __('Simpan Projek P5') }}
                            </button>
                        </form>
                    </div>
                @endif

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-2">{{ __('6 Dimensi Resmi Profil Pelajar Pancasila') }}</h3>
                    <ol class="list-decimal ps-4 space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                        @foreach ($dimensions as $dim)
                            <li>{{ $dim->name }}</li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
