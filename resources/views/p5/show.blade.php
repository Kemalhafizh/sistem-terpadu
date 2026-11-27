<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <a href="{{ route('p5.projects.index') }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">&larr; {{ __('Kembali ke Daftar Projek P5') }}</a>
                <h2 class="font-semibold text-xl text-slate-900 dark:text-slate-100 leading-tight mt-1">
                    {{ $project->title }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ $project->theme_label }} · Kelas {{ $project->grade_level }} (Fase {{ $project->phase }}) · TA {{ $project->schoolYear?->name ?? '-' }}
                </p>
            </div>
            <span class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-semibold uppercase text-slate-700 dark:text-slate-300">
                {{ $project->status }}
            </span>
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

        {{-- Pemetaan Dimensi & Capaian Fase --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6">
            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-3">{{ __('Dimensi & Target Capaian Projek') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($project->dimensions as $dim)
                    <div class="border border-slate-200 dark:border-slate-800 rounded-lg p-4 space-y-1">
                        <div class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                            {{ $dim->characterDimension?->name }}
                        </div>
                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ $dim->element }}{{ $dim->sub_element ? ' — '.$dim->sub_element : '' }}
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400">
                            {{ $dim->target_achievement }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Matriks Penilaian Fasilitator per Rombel --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ __('Penilaian Fasilitator per Rombel') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ __('Skala resmi: MB (Mulai Berkembang), SB (Sedang Berkembang), BSH (Berkembang Sesuai Harapan), SAB (Sangat Berkembang)') }}
                    </p>
                </div>
                <form method="GET" action="{{ route('p5.projects.show', $project) }}" class="flex items-center gap-2">
                    <select name="classroom_id" onchange="this.form.submit()" class="rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-sm">
                        @forelse ($classrooms as $cls)
                            <option value="{{ $cls->id }}" @selected((int) $selectedClassroomId === (int) $cls->id)>{{ $cls->name }}</option>
                        @empty
                            <option value="">{{ __('Belum ada rombel tingkat ini') }}</option>
                        @endforelse
                    </select>
                </form>
            </div>

            @if ($students->isNotEmpty() && $project->dimensions->isNotEmpty())
                <form method="POST" action="{{ route('p5.projects.assessments.update', $project) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    <th class="py-3 pe-4">{{ __('Siswa') }}</th>
                                    @foreach ($project->dimensions as $dim)
                                        <th class="py-3 px-3">{{ $dim->element }}</th>
                                    @endforeach
                                    <th class="py-3 px-3">{{ __('Catatan Proses Projek') }}</th>
                                    <th class="py-3 ps-3 text-end">{{ __('Rapor P5') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @foreach ($students as $student)
                                    @php
                                        $studentDimMap = $existingAssessments->get($student->id);
                                        $studentNote = $existingNotes->get($student->id);
                                    @endphp
                                    <tr>
                                        <td class="py-3 pe-4">
                                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $student->user?->name ?? '-' }}</div>
                                            <div class="text-xs text-slate-500 tabular-nums">NIS {{ $student->nis ?? '-' }}</div>
                                            <input type="hidden" name="assessments[{{ $student->id }}][student_id]" value="{{ $student->id }}">
                                        </td>
                                        @foreach ($project->dimensions as $dim)
                                            @php
                                                $currentPredicate = $studentDimMap?->get($dim->id)?->predicate;
                                            @endphp
                                            <td class="py-3 px-3">
                                                <select name="assessments[{{ $student->id }}][predicates][{{ $dim->id }}]" class="rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-xs">
                                                    <option value="">{{ __('-- Belum dinilai --') }}</option>
                                                    @foreach ($predicates as $code => $label)
                                                        <option value="{{ $code }}" @selected($currentPredicate === $code)>{{ $code }} — {{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        @endforeach
                                        <td class="py-3 px-3">
                                            <input type="text" name="assessments[{{ $student->id }}][process_notes]" value="{{ $studentNote?->process_notes }}" class="w-full min-w-[220px] rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 text-xs" placeholder="Catatan keterlibatan siswa...">
                                        </td>
                                        <td class="py-3 ps-3 text-end whitespace-nowrap">
                                            <a href="{{ route('p5.reports.show', $student) }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                                {{ __('Lihat Rapor') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($project->status !== 'closed')
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold">
                                {{ __('Simpan Penilaian Rombel') }}
                            </button>
                        </div>
                    @endif
                </form>
            @else
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Pilih rombel yang memiliki siswa aktif untuk mulai menginput predikat P5.') }}</p>
            @endif
        </div>
    </div>
</x-app-layout>
