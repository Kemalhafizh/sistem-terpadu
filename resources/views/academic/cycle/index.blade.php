<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="font-semibold text-xl text-slate-900 dark:text-slate-100 leading-tight">
                    {{ __('Siklus Tahun Ajaran, Peminatan, Kenaikan Kelas & Kelulusan') }}
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Pengelolaan terpadu kalender akademik, survei mata pelajaran pilihan kelas X, kenaikan tingkat, dan pengesahan SK kelulusan kelas XII') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/70 text-emerald-800 dark:text-emerald-200 text-sm font-medium flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/70 text-red-800 dark:text-red-200 text-sm">
                <ul class="list-disc ps-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Ringkasan A0–A4: Tahun Ajaran, Survei Peminatan X, dan Batch Kenaikan Kelas --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                            {{ __('Tahun Ajaran & Kalender Akademik') }}
                        </h2>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400 tabular-nums">
                            {{ $schoolYears->count() }} {{ __('periode') }}
                        </span>
                    </div>
                    <div class="space-y-2.5">
                        @forelse ($schoolYears as $sy)
                            <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm bg-slate-50/50 dark:bg-slate-950/30">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-medium text-slate-900 dark:text-slate-100 tabular-nums">{{ $sy->name }}</span>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md {{ $sy->status === 'aktif' || $sy->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                        {{ ucfirst((string) $sy->status) }}
                                    </span>
                                </div>
                                <div class="mt-1.5 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <span>
                                        {{ __('Kalender') }}: {{ $sy->calendar?->status ?? __('belum ada') }}
                                        @if ($sy->calendar?->needs_review)
                                            <span class="text-amber-600 dark:text-amber-400 font-medium">({{ __('Belum ditinjau') }})</span>
                                        @endif
                                    </span>
                                    @if ($sy->calendar && $sy->calendar->status !== 'published' && ! $sy->calendar->needs_review)
                                        @canVisit('academic.cycle.calendars.approve')
                                            <form action="{{ route('academic.cycle.calendars.approve', $sy->calendar) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                                    {{ __('Sahkan kalender') }}
                                                </button>
                                            </form>
                                        @endcanVisit
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Belum ada data Tahun Ajaran.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    {{ __('Survei Peminatan Kelas X') }}
                </h2>
                @if ($activeSurvey && $surveyStats)
                    <div class="text-sm space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ $activeSurvey->title }}</p>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ ucfirst((string) $activeSurvey->status) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 tabular-nums">
                            {{ __('Sudah mengisi') }}: <strong class="text-slate-900 dark:text-slate-100">{{ $surveyStats['total_submitted_students'] }}</strong>
                            · {{ __('Belum mengisi') }}: <strong class="text-slate-900 dark:text-slate-100">{{ count($surveyStats['unsubmitted_students']) }}</strong>
                        </p>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-1.5">
                            @foreach ($surveyStats['subject_counts'] as $row)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-700 dark:text-slate-300">{{ $row['name'] }}</span>
                                    <span class="font-medium text-slate-900 dark:text-slate-100 tabular-nums">{{ $row['count'] }} {{ __('siswa') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Belum ada survei peminatan yang dibuat.') }}</p>
                @endif
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    {{ __('Batch Kenaikan Kelas') }}
                </h2>
                <div class="space-y-2.5">
                    @forelse ($promotionBatches as $batch)
                        <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm bg-slate-50/50 dark:bg-slate-950/30">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-medium text-slate-900 dark:text-slate-100 tabular-nums">
                                    {{ $batch->fromSchoolYear?->name }} &rarr; {{ $batch->toSchoolYear?->name }}
                                </span>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ ucfirst((string) $batch->status) }}
                                </span>
                            </div>
                            @if ($batch->hold_reason)
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ __('Ditahan') }}: {{ $batch->hold_reason }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Belum ada batch kenaikan kelas.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- A5: Kelulusan Kelas XII & SK Kepala Sekolah --}}
        <section x-data="{ showCreateBatch: false }" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        {{ __('Kelulusan Kelas XII & SK Kepala Sekolah') }}
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ __('Penyiapan draf pleno kelulusan, keputusan per siswa kelas XII, pengajuan SK ke Kepala Sekolah, hingga pengumuman dan pembentukan data alumni') }}
                    </p>
                </div>

                @canVisit('academic.cycle.graduation-batches.store')
                    <button
                        type="button"
                        @click="showCreateBatch = !showCreateBatch"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium transition-colors shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span x-text="showCreateBatch ? '{{ __('Tutup formulir draf') }}' : '{{ __('Siapkan draf kelulusan') }}'">{{ __('Siapkan draf kelulusan') }}</span>
                    </button>
                @endcanVisit
            </div>

            {{-- Form 1: Buat / Sinkronkan Draf Batch Kelulusan --}}
            @canVisit('academic.cycle.graduation-batches.store')
                <div x-show="showCreateBatch" x-cloak class="p-5 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">
                        {{ __('Siapkan atau sinkronkan draf batch kelulusan kelas XII') }}
                    </h3>
                    <form action="{{ route('academic.cycle.graduation-batches.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @csrf
                        <div>
                            <label for="grad-school-year" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Tahun ajaran') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="grad-school-year" name="school_year_id" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-sm px-3 py-2 focus:border-primary-500 focus:ring-primary-500">
                                @foreach ($schoolYears as $sy)
                                    <option value="{{ $sy->id }}">{{ $sy->name }} ({{ $sy->status }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="grad-year" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Tahun angkatan lulus') }}
                            </label>
                            <input id="grad-year" type="number" name="graduation_year" min="2000" max="2100" value="{{ old('graduation_year', now()->year) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-sm px-3 py-2 tabular-nums focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div>
                            <label for="grad-date" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Tanggal pengumuman kelulusan') }}
                            </label>
                            <input id="grad-date" type="date" name="graduation_date" value="{{ old('graduation_date') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-sm px-3 py-2 focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div>
                            <label for="grad-sk-number" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Nomor draf SK Kepala Sekolah') }}
                            </label>
                            <input id="grad-sk-number" type="text" name="sk_number" value="{{ old('sk_number') }}" placeholder="421.3/SK-LULUS/..." class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-sm px-3 py-2 focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div>
                            <label for="grad-sk-date" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Tanggal SK') }}
                            </label>
                            <input id="grad-sk-date" type="date" name="sk_date" value="{{ old('sk_date') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-sm px-3 py-2 focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div>
                            <label for="grad-meeting-note" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Notulen rapat pleno dewan guru (final)') }}
                            </label>
                            <select id="grad-meeting-note" name="meeting_note_id" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-sm px-3 py-2 focus:border-primary-500 focus:ring-primary-500">
                                <option value="">{{ __('Pilih saat pengajuan ke Kepala Sekolah') }}</option>
                                @foreach ($finalizedMeetingNotes ?? [] as $note)
                                    <option value="{{ $note->id }}">{{ $note->title }} ({{ $note->meeting_date ? \Carbon\Carbon::parse($note->meeting_date)->format('d/m/Y') : '-' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <label for="grad-notes" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                {{ __('Catatan pengantar pleno') }}
                            </label>
                            <textarea id="grad-notes" name="notes" rows="2" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-sm px-3 py-2 focus:border-primary-500 focus:ring-primary-500" placeholder="{{ __('Ringkasan hasil rapat pleno kelulusan...') }}">{{ old('notes') }}</textarea>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3 flex justify-end gap-2">
                            <button type="button" @click="showCreateBatch = false" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                {{ __('Batal') }}
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium transition-colors">
                                {{ __('Simpan & sinkronkan siswa kelas XII') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endcanVisit

            {{-- Daftar Batch Kelulusan --}}
            <div class="space-y-5">
                @forelse ($graduationBatches ?? [] as $gBatch)
                    @php
                        $totalStudents = $gBatch->decisions->count();
                        $graduatedCount = $gBatch->decisions->where('decision', 'lulus')->count();
                        $notGraduatedCount = $gBatch->decisions->where('decision', 'tidak_lulus')->count();
                        $undecidedCount = $totalStudents - $graduatedCount - $notGraduatedCount;
                        $isEditableBatch = in_array($gBatch->status, ['draf', 'revision'], true);
                        $canBulkDecide = \App\Support\Access\RouteAccess::allowsRoute(auth()->user(), 'academic.cycle.graduation-batches.bulk-decisions');
                        $canReviewSingle = \App\Support\Access\RouteAccess::allowsRoute(auth()->user(), 'academic.cycle.graduation-decisions.review');
                        $statusMeta = match ($gBatch->status) {
                            'dieksekusi' => ['label' => __('Telah Dieksekusi'), 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/70'],
                            'approved' => ['label' => __('Disetujui Kepsek'), 'class' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800/70'],
                            'pending' => ['label' => __('Menunggu Pengesahan'), 'class' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800/70'],
                            'revision' => ['label' => __('Perlu Revisi'), 'class' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/50 dark:text-orange-300 dark:border-orange-800/70'],
                            'rejected' => ['label' => __('Ditolak'), 'class' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-300 dark:border-red-800/70'],
                            default => ['label' => __('Draf Pleno'), 'class' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'],
                        };
                    @endphp

                    <div x-data="{ openBatch: {{ $loop->first ? 'true' : 'false' }}, editMode: '{{ $canBulkDecide ? 'bulk' : 'single' }}' }" class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                        {{-- Header Ringkasan Batch --}}
                        <div class="p-4 sm:p-5 bg-slate-50/70 dark:bg-slate-950/40 flex flex-wrap items-start justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 tabular-nums">
                                        {{ __('Angkatan') }} {{ $gBatch->graduation_year }} ({{ __('TA') }} {{ $gBatch->schoolYear?->name ?? '-' }})
                                    </h3>
                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded-md border {{ $statusMeta['class'] }}">
                                        {{ $statusMeta['label'] }} ({{ $gBatch->status }})
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                    SK: <span class="font-medium text-slate-800 dark:text-slate-200">{{ $gBatch->sk_number ?: 'Belum ditetapkan' }}</span>
                                    · Notulen Pleno: <span class="font-medium text-slate-800 dark:text-slate-200">{{ $gBatch->meetingNote?->title ?? 'Belum ditautkan' }}</span>
                                    @if ($gBatch->graduation_date)
                                        · {{ __('Tgl Kelulusan') }}: <span class="tabular-nums">{{ $gBatch->graduation_date->format('d/m/Y') }}</span>
                                    @endif
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-2 text-xs tabular-nums">
                                    <span class="px-2.5 py-1 rounded-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                                        {{ $totalStudents }} {{ __('siswa') }}
                                    </span>
                                    <span class="px-2.5 py-1 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-medium">
                                        {{ $graduatedCount }} {{ __('lulus') }}
                                    </span>
                                    <span class="px-2.5 py-1 rounded-md bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 font-medium">
                                        {{ $notGraduatedCount }} {{ __('belum lulus') }}
                                    </span>
                                    @if ($undecidedCount > 0)
                                        <span class="px-2.5 py-1 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-medium">
                                            {{ $undecidedCount }} {{ __('belum diputuskan') }}
                                        </span>
                                    @endif
                                </div>

                                <button
                                    type="button"
                                    @click="openBatch = !openBatch"
                                    class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                >
                                    <span x-text="openBatch ? '{{ __('Sembunyikan rincian') }}' : '{{ __('Kelola keputusan & SK') }}'">{{ __('Kelola keputusan & SK') }}</span>
                                </button>
                            </div>
                        </div>

                        {{-- Detail Keputusan Siswa & Alur Persetujuan --}}
                        <div x-show="openBatch" class="p-4 sm:p-5 border-t border-slate-200 dark:border-slate-800 space-y-6">
                            @if ($gBatch->notes)
                                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ __('Catatan pleno') }}:</span> {{ $gBatch->notes }}
                                </div>
                            @endif

                            @if ($isEditableBatch && $canBulkDecide && $canReviewSingle && $totalStudents > 0)
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ __('Seluruh siswa kelas XII wajib memiliki keputusan sebelum draf diajukan ke Kepala Sekolah.') }}
                                    </p>
                                    <div class="inline-flex rounded-lg border border-slate-200 dark:border-slate-800 p-0.5 bg-slate-100 dark:bg-slate-950 text-xs">
                                        <button type="button" @click="editMode = 'bulk'" :class="editMode === 'bulk' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs' : 'text-slate-600 dark:text-slate-400'" class="px-3 py-1 rounded-md font-medium transition-colors">
                                            {{ __('Keputusan massal') }}
                                        </button>
                                        <button type="button" @click="editMode = 'single'" :class="editMode === 'single' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs' : 'text-slate-600 dark:text-slate-400'" class="px-3 py-1 rounded-md font-medium transition-colors">
                                            {{ __('Tinjau per siswa') }}
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @if ($totalStudents === 0)
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ __('Belum ada peserta kelas XII terdaftar pada batch tahun ajaran ini.') }}
                                </p>
                            @elseif ($isEditableBatch && $canBulkDecide)
                                {{-- Mode 1: Bulk Update Decisions (Waka Kurikulum / Admin) --}}
                                <form x-show="editMode === 'bulk'" action="{{ route('academic.cycle.graduation-batches.bulk-decisions', $gBatch) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                                            <thead class="bg-slate-50 dark:bg-slate-950/60 text-xs font-medium text-slate-600 dark:text-slate-400">
                                                <tr>
                                                    <th scope="col" class="px-4 py-3 text-start">{{ __('Siswa') }}</th>
                                                    <th scope="col" class="px-4 py-3 text-end">{{ __('Rerata rapor') }}</th>
                                                    <th scope="col" class="px-4 py-3 text-end">{{ __('Semester tuntas') }}</th>
                                                    <th scope="col" class="px-4 py-3 text-end">{{ __('Di bawah KKTP') }}</th>
                                                    <th scope="col" class="px-4 py-3 text-end">{{ __('Hari alpa') }}</th>
                                                    <th scope="col" class="px-4 py-3 text-start">{{ __('Keputusan') }}</th>
                                                    <th scope="col" class="px-4 py-3 text-start">{{ __('Catatan pertimbangan') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                                @foreach ($gBatch->decisions as $decision)
                                                    @php
                                                        $basis = is_array($decision->basis) ? $decision->basis : [];
                                                        $avg = $basis['report_card_average'] ?? null;
                                                        $semesters = $basis['completed_semesters'] ?? 0;
                                                        $belowKktp = $basis['below_kktp_count'] ?? null;
                                                        $alphaDays = $basis['alpha_days'] ?? null;
                                                    @endphp
                                                    <tr>
                                                        <td class="px-4 py-3">
                                                            <input type="hidden" name="decisions[{{ $decision->student_id }}][student_id]" value="{{ $decision->student_id }}">
                                                            <div class="font-medium text-slate-900 dark:text-slate-100">
                                                                {{ $decision->student?->user?->name ?? __('Siswa') }}
                                                            </div>
                                                            <div class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                                                                NIS: {{ $decision->student?->nis ?? '-' }}
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 text-end tabular-nums text-slate-700 dark:text-slate-300">
                                                            {{ $avg !== null ? number_format((float) $avg, 2) : __('Belum tercatat') }}
                                                        </td>
                                                        <td class="px-4 py-3 text-end tabular-nums text-slate-700 dark:text-slate-300">
                                                            {{ $semesters }}
                                                        </td>
                                                        <td class="px-4 py-3 text-end tabular-nums text-slate-700 dark:text-slate-300">
                                                            {{ $belowKktp !== null ? $belowKktp : '–' }}
                                                        </td>
                                                        <td class="px-4 py-3 text-end tabular-nums text-slate-700 dark:text-slate-300">
                                                            {{ $alphaDays !== null ? $alphaDays : '–' }}
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <select name="decisions[{{ $decision->student_id }}][decision]" required class="rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 text-xs px-2.5 py-1.5 focus:border-primary-500 focus:ring-primary-500">
                                                                <option value="lulus" @selected(($decision->decision ?? 'lulus') === 'lulus')>{{ __('Lulus') }}</option>
                                                                <option value="tidak_lulus" @selected($decision->decision === 'tidak_lulus')>{{ __('Belum lulus') }}</option>
                                                            </select>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <input type="text" name="decisions[{{ $decision->student_id }}][note]" value="{{ $decision->note }}" placeholder="{{ __('Catatan pleno / wali kelas') }}" class="w-full min-w-[180px] rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 text-xs px-2.5 py-1.5 focus:border-primary-500 focus:ring-primary-500">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors">
                                            {{ __('Simpan keputusan massal') }}
                                        </button>
                                    </div>
                                </form>
                            @endif

                            @if ($totalStudents > 0 && (! ($isEditableBatch && $canBulkDecide) || $canReviewSingle))
                                {{-- Mode 2: Tabel Tinjauan Per Siswa (Wali Kelas XII) atau Baca-Saja (Saat Dikunci/Disetujui/Dieksekusi) --}}
                                <div x-show="{{ ($isEditableBatch && $canBulkDecide) ? "editMode === 'single'" : 'true' }}" class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                                        <thead class="bg-slate-50 dark:bg-slate-950/60 text-xs font-medium text-slate-600 dark:text-slate-400">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 text-start">{{ __('Siswa') }}</th>
                                                <th scope="col" class="px-4 py-3 text-end">{{ __('Rerata rapor') }}</th>
                                                <th scope="col" class="px-4 py-3 text-end">{{ __('Semester') }}</th>
                                                <th scope="col" class="px-4 py-3 text-end">{{ __('Di bawah KKTP') }}</th>
                                                <th scope="col" class="px-4 py-3 text-end">{{ __('Hari alpa') }}</th>
                                                <th scope="col" class="px-4 py-3 text-start">{{ __('Status & tindakan') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                            @foreach ($gBatch->decisions as $decision)
                                                @php
                                                    $basis = is_array($decision->basis) ? $decision->basis : [];
                                                    $avg = $basis['report_card_average'] ?? null;
                                                    $semesters = $basis['completed_semesters'] ?? 0;
                                                    $belowKktp = $basis['below_kktp_count'] ?? null;
                                                    $alphaDays = $basis['alpha_days'] ?? null;
                                                @endphp
                                                <tr>
                                                    <td class="px-4 py-3">
                                                        <div class="font-medium text-slate-900 dark:text-slate-100">
                                                            {{ $decision->student?->user?->name ?? __('Siswa') }}
                                                        </div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                                                            NIS: {{ $decision->student?->nis ?? '-' }}
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 text-end tabular-nums text-slate-700 dark:text-slate-300">
                                                        {{ $avg !== null ? number_format((float) $avg, 2) : __('Belum tercatat') }}
                                                    </td>
                                                    <td class="px-4 py-3 text-end tabular-nums text-slate-700 dark:text-slate-300">
                                                        {{ $semesters }}
                                                    </td>
                                                    <td class="px-4 py-3 text-end tabular-nums text-slate-700 dark:text-slate-300">
                                                        {{ $belowKktp !== null ? $belowKktp : '–' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-end tabular-nums text-slate-700 dark:text-slate-300">
                                                        {{ $alphaDays !== null ? $alphaDays : '–' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if ($isEditableBatch && $canReviewSingle)
                                                            <form action="{{ route('academic.cycle.graduation-decisions.review', $decision) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                                                @csrf
                                                                <select name="decision" required class="rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 text-xs px-2.5 py-1.5">
                                                                    <option value="lulus" @selected($decision->decision === 'lulus')>{{ __('Lulus') }}</option>
                                                                    <option value="tidak_lulus" @selected($decision->decision === 'tidak_lulus')>{{ __('Belum lulus') }}</option>
                                                                </select>
                                                                <input type="text" name="note" value="{{ $decision->note }}" placeholder="{{ __('Catatan wali kelas') }}" class="rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 text-xs px-2.5 py-1.5 w-40">
                                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium transition-colors">
                                                                    {{ __('Simpan') }}
                                                                </button>
                                                            </form>
                                                        @else
                                                            <div class="flex flex-wrap items-center gap-2">
                                                                @if ($decision->decision === 'lulus')
                                                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-xs font-medium">{{ __('Lulus') }}</span>
                                                                @elseif ($decision->decision === 'tidak_lulus')
                                                                    <span class="px-2 py-0.5 rounded-md bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 text-xs font-medium">{{ __('Belum lulus') }}</span>
                                                                @else
                                                                    <span class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 text-xs font-medium">{{ __('Belum diputuskan') }}</span>
                                                                @endif
                                                                @if ($decision->note)
                                                                    <span class="text-xs text-slate-500 dark:text-slate-400">· {{ $decision->note }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            {{-- Panel Aksi Tahapan Alur A5 (Submit -> Approve -> Execute) --}}
                            @if ($isEditableBatch)
                                @canVisit('academic.cycle.graduation-batches.submit')
                                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 space-y-3">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                                {{ __('Ajukan pengesahan SK kelulusan ke Kepala Sekolah') }}
                                            </h4>
                                            @if ($undecidedCount > 0)
                                                <span class="text-xs font-medium text-amber-600 dark:text-amber-400">
                                                    {{ __('Lengkapi :count keputusan siswa terlebih dahulu sebelum mengajukan.', ['count' => $undecidedCount]) }}
                                                </span>
                                            @endif
                                        </div>
                                        <form action="{{ route('academic.cycle.graduation-batches.submit', $gBatch) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                                            @csrf
                                            <div class="lg:col-span-2">
                                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                                    {{ __('Notulen rapat pleno final (wajib)') }} <span class="text-red-500">*</span>
                                                </label>
                                                <select name="meeting_note_id" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-xs px-3 py-2">
                                                    <option value="">{{ __('Pilih notulen rapat pleno dewan guru...') }}</option>
                                                    @foreach ($finalizedMeetingNotes ?? [] as $note)
                                                        <option value="{{ $note->id }}" @selected($gBatch->meeting_note_id === $note->id)>
                                                            {{ $note->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                                    {{ __('Nomor SK') }}
                                                </label>
                                                <input type="text" name="sk_number" value="{{ $gBatch->sk_number }}" placeholder="421.3/SK-LULUS/..." class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-xs px-3 py-2">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                                    {{ __('Tanggal kelulusan') }}
                                                </label>
                                                <input type="date" name="graduation_date" value="{{ $gBatch->graduation_date?->format('Y-m-d') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-xs px-3 py-2">
                                            </div>
                                            <div class="sm:col-span-2 lg:col-span-3">
                                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                                    {{ __('Catatan pengajuan') }}
                                                </label>
                                                <input type="text" name="notes" value="{{ $gBatch->notes }}" placeholder="{{ __('Catatan untuk Kepala Sekolah...') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-xs px-3 py-2">
                                            </div>
                                            <div>
                                                <button type="submit" @disabled($undecidedCount > 0) class="w-full px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold transition-colors">
                                                    {{ __('Ajukan ke Kepala Sekolah') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endcanVisit
                            @endif

                            @if ($gBatch->status === 'pending' && (int) $gBatch->submitted_by !== (int) auth()->id())
                                @canVisit('academic.cycle.graduation-batches.approve')
                                    <div class="p-4 rounded-xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/60 space-y-3">
                                        <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                            {{ __('Pengesahan SK Kelulusan oleh Kepala Sekolah') }}
                                        </h4>
                                        <form action="{{ route('academic.cycle.graduation-batches.approve', $gBatch) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                                    {{ __('Nomor SK resmi') }} <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" name="sk_number" value="{{ $gBatch->sk_number }}" required placeholder="421.3/SK-LULUS/..." class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-xs px-3 py-2">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                                    {{ __('Tanggal SK') }}
                                                </label>
                                                <input type="date" name="sk_date" value="{{ $gBatch->sk_date?->format('Y-m-d') ?? now()->format('Y-m-d') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-xs px-3 py-2">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                                    {{ __('Catatan pengesahan') }}
                                                </label>
                                                <input type="text" name="remarks" placeholder="{{ __('Disahkan sesuai hasil rapat pleno...') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-xs px-3 py-2">
                                            </div>
                                            <div>
                                                <button type="submit" class="w-full px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition-colors">
                                                    {{ __('Sahkan SK Kelulusan') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endcanVisit
                            @endif

                            @if ($gBatch->status === 'approved')
                                @canVisit('academic.cycle.graduation-batches.execute')
                                    <div class="p-4 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div class="text-xs text-slate-700 dark:text-slate-300">
                                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ __('SK Kelulusan telah disahkan dan siap dieksekusi') }}</p>
                                            <p class="mt-0.5">{{ __('Eksekusi akan menutup rombel aktif kelas XII menjadi lulus, memperbarui status alumni untuk Buku Induk dan Tracer Study, serta mengirim notifikasi pengumuman.') }}</p>
                                        </div>
                                        <form action="{{ route('academic.cycle.graduation-batches.execute', $gBatch) }}" method="POST" class="shrink-0">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition-colors">
                                                {{ __('Eksekusi & umumkan kelulusan') }}
                                            </button>
                                        </form>
                                    </div>
                                @endcanVisit
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Belum ada draf kelulusan kelas XII.') }}</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
