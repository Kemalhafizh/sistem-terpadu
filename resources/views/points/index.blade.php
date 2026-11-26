<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="font-semibold text-xl text-slate-900 dark:text-slate-100 leading-tight">
                    {{ __('Buku Saku Poin & Kedisiplinan Siswa') }}
                </h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Pemantauan saldo poin pelanggaran, penghargaan prestasi, dan ambang tahapan Surat Peringatan sekolah') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/70 text-emerald-800 dark:text-emerald-200 text-sm font-medium flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/70 text-red-800 dark:text-red-200 text-sm">
                    <ul class="list-disc ps-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Pencarian & Ringkasan Ambang Aktif --}}
            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4">
                <form action="{{ route('points.index') }}" method="GET" class="flex-1 min-w-[260px]">
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="{{ __('Cari nama siswa atau NIS') }}"
                            class="w-full ps-10 pe-4 py-2.5 rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500 focus:bg-white dark:focus:bg-slate-900 transition-colors text-sm"
                        >
                        <div class="absolute start-3.5 top-3 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </form>

                <div class="text-xs font-medium text-slate-600 dark:text-slate-300 tabular-nums">
                    Ambang SP1: {{ $thresholds['SP1'] ?? 50 }} · SP2: {{ $thresholds['SP2'] ?? 75 }} · SP3: {{ $thresholds['SP3'] ?? 100 }} · Konseling Wajib: {{ $mandatoryCounselingThreshold ?? 50 }}
                </div>

                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 tabular-nums">
                    {{ __('Menampilkan :count siswa', ['count' => $students->total()]) }}
                </div>
            </div>

            {{-- Konfigurasi Ambang Dinamis Sekolah --}}
            @if(!empty($canManageThresholds))
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">
                        {{ __('Pengaturan Ambang Poin & Tahapan Surat Peringatan') }}
                    </h2>
                    <form action="{{ route('points.thresholds.update') }}" method="POST" class="grid grid-cols-2 md:grid-cols-6 gap-3 items-end">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="att-threshold" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Perlu perhatian') }}</label>
                            <input id="att-threshold" type="number" name="attention_threshold" value="{{ $attentionThreshold ?? 30 }}" min="1" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 text-sm px-3 py-2 tabular-nums">
                        </div>
                        <div>
                            <label for="sp1-threshold" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Ambang SP1') }}</label>
                            <input id="sp1-threshold" type="number" name="sp1_threshold" value="{{ $thresholds['SP1'] ?? 50 }}" min="2" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 text-sm px-3 py-2 tabular-nums">
                        </div>
                        <div>
                            <label for="sp2-threshold" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Ambang SP2') }}</label>
                            <input id="sp2-threshold" type="number" name="sp2_threshold" value="{{ $thresholds['SP2'] ?? 75 }}" min="3" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 text-sm px-3 py-2 tabular-nums">
                        </div>
                        <div>
                            <label for="sp3-threshold" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Ambang SP3') }}</label>
                            <input id="sp3-threshold" type="number" name="sp3_threshold" value="{{ $thresholds['SP3'] ?? 100 }}" min="4" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 text-sm px-3 py-2 tabular-nums">
                        </div>
                        <div>
                            <label for="counseling-threshold" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('Konseling BK wajib') }}</label>
                            <input id="counseling-threshold" type="number" name="mandatory_counseling_threshold" value="{{ $mandatoryCounselingThreshold ?? ($thresholds['SP1'] ?? 50) }}" min="1" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 text-sm px-3 py-2 tabular-nums">
                        </div>
                        <div>
                            <button type="submit" class="w-full py-2 px-4 bg-primary-600 text-white rounded-lg text-xs font-semibold hover:bg-primary-700 transition-colors">
                                {{ __('Simpan ambang') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Daftar Siswa --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @php
                    $sp1Threshold = $thresholds['SP1'] ?? 50;
                    $attThreshold = $attentionThreshold ?? 30;
                @endphp
                @forelse($students ?? [] as $student)
                    @php
                        $netPoints = (int) $student->total_points;
                        $pointColor = $netPoints >= $sp1Threshold
                            ? 'text-red-600 dark:text-red-400'
                            : ($netPoints >= $attThreshold ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-slate-100');
                    @endphp
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors flex flex-col justify-between">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl flex items-center justify-center font-semibold text-base shrink-0">
                                    {{ mb_substr($student?->user?->name ?? 'S', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-slate-900 dark:text-slate-100 truncate">
                                        {{ $student?->user?->name }}
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 tabular-nums truncate">
                                        {{ $student?->classroom?->name ?? __('Tanpa kelas') }} · NIS {{ $student->nis }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-end shrink-0">
                                <div class="text-2xl font-semibold tabular-nums {{ $pointColor }}">
                                    {{ $netPoints }}
                                </div>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('poin') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5 mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('points.show', $student) }}" class="flex-1 py-2 px-3 bg-slate-50 dark:bg-slate-800/70 text-slate-700 dark:text-slate-200 rounded-lg text-center text-xs font-medium border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                {{ __('Lihat riwayat') }}
                            </a>
                            <button
                                type="button"
                                onclick='openPointModal({{ (int) $student->id }}, @json($student?->user?->name ?? "Siswa"))'
                                class="flex-1 py-2 px-3 bg-primary-600 text-white rounded-lg text-xs font-medium hover:bg-primary-700 transition-colors"
                            >
                                {{ __('Catat poin') }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center bg-white dark:bg-slate-900 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-sm">
                        {{ __('Siswa tidak ditemukan.') }}
                    </div>
                @endforelse
            </div>

            <div>
                {{ $students->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Catat Poin --}}
    <div id="point-modal" class="hidden fixed inset-0 bg-slate-950/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-7 max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-xl">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Catat poin kedisiplinan') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5" id="modal-student-name">Siswa: -</p>
                </div>
                <button type="button" onclick="closePointModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition-colors" aria-label="{{ __('Tutup') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('points.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="student_id" id="modal-student-id">

                <div>
                    <label for="modal-category-select" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('Kategori standar') }}
                    </label>
                    <select name="point_category_id" id="modal-category-select" onchange="applyCategory(this)" class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500 text-sm px-3 py-2">
                        <option value="">{{ __('Input manual') }}</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" data-points="{{ $cat->default_points }}" data-type="{{ $cat->type }}" data-name="{{ $cat->name }}">
                                [{{ $cat->type == 'penalty' ? '+' : '-' }}{{ abs((int) $cat->default_points) }}] {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="manual-input-section" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" id="type-penalty" value="penalty" class="hidden peer" checked onchange="updatePointMode('penalty')">
                            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-950/30 text-center transition-colors">
                                <span class="block text-xs font-semibold text-slate-700 dark:text-slate-300 peer-checked:text-red-700 dark:peer-checked:text-red-300">{{ __('Pelanggaran') }}</span>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Menambah poin pelanggaran') }}</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" id="type-reward" value="reward" class="hidden peer" onchange="updatePointMode('reward')">
                            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-950/30 text-center transition-colors">
                                <span class="block text-xs font-semibold text-slate-700 dark:text-slate-300 peer-checked:text-emerald-700 dark:peer-checked:text-emerald-300">{{ __('Prestasi / penghargaan') }}</span>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Mengurangi saldo pelanggaran') }}</span>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label for="modal-points-input" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Jumlah poin') }}</label>
                        <div class="relative">
                            <input type="number" name="points" id="modal-points-input" class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-950 border-slate-300 dark:border-slate-700 dark:text-slate-100 text-base font-semibold tabular-nums focus:border-primary-500 focus:ring-primary-500" placeholder="0" required>
                            <span id="point-prefix" class="absolute end-3.5 top-2.5 font-semibold text-base text-red-500">+</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="modal-description" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Keterangan kejadian') }}</label>
                    <textarea name="description" id="modal-description" rows="3" class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500 text-sm p-3" placeholder="{{ __('Contoh: Terlambat masuk jam pertama lebih dari 15 menit') }}" required></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" onclick="closePointModal()" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium rounded-lg text-sm transition-colors hover:bg-slate-200 dark:hover:bg-slate-700">
                        {{ __('Batal') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white font-semibold rounded-lg text-sm hover:bg-primary-700 transition-colors">
                        {{ __('Simpan catatan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPointModal(id, name) {
            document.getElementById('point-modal').classList.remove('hidden');
            document.getElementById('modal-student-id').value = id;
            document.getElementById('modal-student-name').innerText = "Siswa: " + name;
        }

        function closePointModal() {
            document.getElementById('point-modal').classList.add('hidden');
            document.getElementById('modal-category-select').value = '';
            document.getElementById('modal-points-input').value = '';
            document.getElementById('modal-description').value = '';
        }

        function applyCategory(select) {
            const option = select.options[select.selectedIndex];
            if (!option.value) {
                document.getElementById('manual-input-section').classList.remove('hidden');
                return;
            }

            const points = option.getAttribute('data-points');
            const type = option.getAttribute('data-type');
            const name = option.getAttribute('data-name');

            document.getElementById('modal-points-input').value = Math.abs(points);
            document.getElementById('modal-description').value = name;

            if (type === 'penalty') {
                document.getElementById('type-penalty').checked = true;
                updatePointMode('penalty');
            } else {
                document.getElementById('type-reward').checked = true;
                updatePointMode('reward');
            }
        }

        function updatePointMode(mode) {
            const prefix = document.getElementById('point-prefix');
            if (mode === 'penalty') {
                prefix.innerText = '+';
                prefix.classList.remove('text-emerald-500');
                prefix.classList.add('text-red-500');
            } else {
                prefix.innerText = '-';
                prefix.classList.remove('text-red-500');
                prefix.classList.add('text-emerald-500');
            }
        }

        document.querySelector('#point-modal form').onsubmit = function() {
            const mode = document.querySelector('input[name="type"]:checked').value;
            const input = document.getElementById('modal-points-input');
            if (mode === 'reward' && input.value > 0) {
                input.value = -Math.abs(input.value);
            } else if (mode === 'penalty' && input.value < 0) {
                input.value = Math.abs(input.value);
            }
        };
    </script>
</x-app-layout>
