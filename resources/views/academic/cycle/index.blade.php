<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Siklus Tahun Ajaran, Survei Peminatan & Kenaikan Kelas') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">
                    {{ __('Tahun Ajaran & Kalender Akademik') }}
                </h3>
                <div class="space-y-3">
                    @forelse ($schoolYears as $sy)
                        <div class="border dark:border-gray-700 rounded-lg p-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">{{ $sy->name }}</span>
                                <span class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-gray-700">
                                    {{ strtoupper($sy->status) }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Kalender: {{ $sy->calendar?->status ?? 'belum ada' }}
                                @if ($sy->calendar?->needs_review)
                                    <span class="text-amber-600 font-medium">(Belum ditinjau)</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada data Tahun Ajaran.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">
                    {{ __('Survei Peminatan Kelas X') }}
                </h3>
                @if ($activeSurvey && $surveyStats)
                    <div class="text-sm space-y-2">
                        <p class="font-semibold">{{ $activeSurvey->title }} ({{ $activeSurvey->status }})</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            Siswa sudah mengisi: <strong>{{ $surveyStats['total_submitted_students'] }}</strong>
                            · Belum mengisi: <strong>{{ count($surveyStats['unsubmitted_students']) }}</strong>
                        </p>
                        <div class="pt-2 space-y-1">
                            @foreach ($surveyStats['subject_counts'] as $row)
                                <div class="flex justify-between text-xs">
                                    <span>{{ $row['name'] }}</span>
                                    <span class="font-medium">{{ $row['count'] }} siswa</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Belum ada survei peminatan yang dibuat.</p>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">
                    {{ __('Batch Kenaikan Kelas') }}
                </h3>
                <div class="space-y-3">
                    @forelse ($promotionBatches as $batch)
                        <div class="border dark:border-gray-700 rounded-lg p-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">
                                    {{ $batch->fromSchoolYear?->name }} → {{ $batch->toSchoolYear?->name }}
                                </span>
                                <span class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-gray-700">
                                    {{ $batch->status }}
                                </span>
                            </div>
                            @if ($batch->hold_reason)
                                <p class="text-xs text-red-600 mt-1">Ditahan: {{ $batch->hold_reason }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada batch kenaikan kelas.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">
                {{ __('Kelulusan Kelas XII & SK Kepala Sekolah') }}
            </h3>
            <div class="space-y-3">
                @forelse ($graduationBatches ?? [] as $gBatch)
                    <div class="border dark:border-gray-700 rounded-lg p-4 text-sm flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                Angkatan {{ $gBatch->graduation_year }} (TA {{ $gBatch->schoolYear?->name ?? '-' }})
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                SK: {{ $gBatch->sk_number ?: 'Belum ditetapkan' }}
                                · Notulen Pleno: {{ $gBatch->meetingNote?->title ?? 'Belum ditautkan' }}
                                · Peserta: {{ $gBatch->decisions->count() }} siswa
                            </div>
                        </div>
                        <span class="px-2.5 py-1 text-xs rounded bg-gray-100 dark:bg-gray-700 font-medium uppercase">
                            {{ $gBatch->status }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Belum ada draf kelulusan kelas XII.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
