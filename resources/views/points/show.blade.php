<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('points.index') }}" class="p-2 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" aria-label="{{ __('Kembali ke daftar poin') }}">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="font-semibold text-xl text-slate-900 dark:text-slate-100 leading-tight">
                    {{ __('Riwayat Poin') }}: {{ $student?->user?->name }}
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 tabular-nums mt-0.5">
                    {{ __('Kelas') }} {{ $student?->classroom?->name ?? 'N/A' }} · NIS {{ $student->nis }}
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

            {{-- Ringkasan Profil & Saldo Poin Siswa --}}
            @php
                $sp1Threshold = $thresholds['SP1'] ?? 50;
                $netPoints = (int) $student->total_points;
            @endphp
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-xl flex items-center justify-center font-semibold text-xl border border-slate-200 dark:border-slate-700 shrink-0">
                        {{ mb_substr($student?->user?->name ?? 'S', 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $student?->user?->name }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 tabular-nums">
                            {{ __('Kelas') }} {{ $student?->classroom?->name ?? 'N/A' }} · NIS {{ $student->nis }} · {{ $student->gender == 'L' ? __('Laki-laki') : __('Perempuan') }}
                        </p>
                        <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                            @foreach($thresholds ?? [] as $stageKey => $stageValue)
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $stageKey }}: {{ $stageValue }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="bg-slate-50 dark:bg-slate-950/60 rounded-xl px-5 py-3.5 border border-slate-200 dark:border-slate-800 text-center min-w-[130px]">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Total poin bersih') }}</p>
                        <p class="text-3xl font-semibold tabular-nums mt-0.5 {{ $netPoints >= $sp1Threshold ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-slate-100' }}">
                            {{ $netPoints }}
                        </p>
                    </div>
                    <button type="button" onclick="openPointModal()" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium text-sm transition-colors">
                        {{ __('Catat poin') }}
                    </button>
                </div>
            </div>

            {{-- Penerbitan Manual & Riwayat Surat Peringatan (SP) --}}
            <div x-data="{ showIssueSp: false }" class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ __('Riwayat Surat Peringatan (SP)') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ __('Penerbitan SP menjadwalkan konseling wajib BK serta memberi tahu orang tua dan wali kelas secara otomatis') }}
                        </p>
                    </div>

                    @canVisit('points.warning-letters.store')
                        <button
                            type="button"
                            @click="showIssueSp = !showIssueSp"
                            class="px-3.5 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-medium transition-colors"
                        >
                            <span x-text="showIssueSp ? '{{ __('Tutup formulir SP') }}' : '{{ __('Terbitkan SP manual') }}'">{{ __('Terbitkan SP manual') }}</span>
                        </button>
                    @endcanVisit
                </div>

                @canVisit('points.warning-letters.store')
                    <form x-show="showIssueSp" x-cloak action="{{ route('points.warning-letters.store', $student) }}" method="POST" class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                        @csrf
                        <div>
                            <label for="sp-level" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Tahapan SP') }}</label>
                            <select id="sp-level" name="level" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-xs px-3 py-2">
                                @foreach(($thresholds ?? ['SP1' => 50, 'SP2' => 75, 'SP3' => 100]) as $stageLevel => $stageVal)
                                    <option value="{{ $stageLevel }}">{{ $stageLevel }} ({{ __('ambang') }} {{ $stageVal }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="sp-notes" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Catatan pembinaan / dasar penerbitan') }}</label>
                            <input id="sp-notes" type="text" name="notes" placeholder="{{ __('Catatan tindak lanjut untuk orang tua dan guru BK...') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-xs px-3 py-2">
                        </div>
                        <div>
                            <button type="submit" class="w-full px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-semibold transition-colors">
                                {{ __('Terbitkan SP') }}
                            </button>
                        </div>
                    </form>
                @endcanVisit

                @if(($warningLetters ?? collect())->isNotEmpty())
                    <div class="divide-y divide-slate-200 dark:divide-slate-800 border-t border-slate-200 dark:border-slate-800 pt-2">
                        @foreach($warningLetters as $wl)
                            <div class="py-3.5 flex flex-wrap items-center justify-between gap-4 text-sm">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-md bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 font-semibold text-xs">
                                        {{ $wl->level }}
                                    </span>
                                    <span class="text-slate-600 dark:text-slate-300 text-xs tabular-nums">
                                        Ambang {{ $wl->point_threshold }} Poin · Terbit {{ $wl->issued_at?->format('d/m/Y') }}
                                    </span>
                                    @if($wl->signed_at)
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs font-medium">
                                            Ditandatangani Orang Tua
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 text-xs font-medium">
                                            Menunggu Tanda Tangan Orang Tua
                                        </span>
                                    @endif
                                    @if($wl->counselingSession)
                                        <span class="px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 text-xs font-medium">
                                            Konseling Wajib Terjadwal
                                        </span>
                                    @endif
                                </div>
                                <a href="{{ route('points.warning-letters.pdf', $wl) }}" target="_blank" rel="noopener" class="text-primary-600 dark:text-primary-400 hover:underline text-xs font-semibold">
                                    Cetak PDF SP
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-800">
                        {{ __('Belum ada Surat Peringatan yang diterbitkan untuk siswa ini.') }}
                    </p>
                @endif
            </div>

            {{-- Linimasa Log Kedisiplinan & Prestasi --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-6">
                    {{ __('Log Kedisiplinan & Prestasi') }}
                </h3>

                <div class="relative">
                    <div class="absolute start-4 top-0 bottom-0 w-px bg-slate-200 dark:bg-slate-800"></div>

                    <div class="space-y-6">
                        @forelse($logs ?? [] as $log)
                            <div class="relative flex items-start">
                                <div class="absolute start-4 -translate-x-1/2 rtl:translate-x-1/2 mt-1.5 w-2.5 h-2.5 rounded-full ring-4 {{ $log->points > 0 ? 'bg-red-500 ring-red-50 dark:ring-red-950/60' : 'bg-emerald-500 ring-emerald-50 dark:ring-emerald-950/60' }} z-10"></div>

                                <div class="ms-10 flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-xs font-semibold {{ $log->points > 0 ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                                                {{ $log?->category?->name ?? ($log->points > 0 ? __('Pelanggaran') : ($log->points < 0 ? __('Prestasi') : __('Penyesuaian'))) }}
                                            </span>
                                            <span class="text-xs font-semibold tabular-nums {{ $log->points > 0 ? 'text-red-600 bg-red-50 dark:bg-red-950/40 dark:text-red-300' : 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-300' }} px-2 py-0.5 rounded-md">
                                                {{ $log->points > 0 ? '+' : '' }}{{ $log->points }} {{ __('Poin') }}
                                            </span>
                                        </div>
                                        <span class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">{{ $log->created_at?->format('d M Y · H:i') }}</span>
                                    </div>

                                    <div class="bg-slate-50 dark:bg-slate-950/50 rounded-xl p-4 border border-slate-200 dark:border-slate-800">
                                        <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ $log->description }}</p>
                                        <div class="mt-3 pt-3 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between gap-3">
                                            <div class="flex items-center text-xs text-slate-500 dark:text-slate-400">
                                                <span>{{ __('Dicatat oleh') }}: <strong class="font-medium text-slate-700 dark:text-slate-200">{{ $log?->creator?->name ?? '-' }}</strong></span>
                                            </div>

                                            @if(auth()->user()->isAdmin() || auth()->id() == $log->created_by)
                                                <form action="{{ route('points.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat poin ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1" aria-label="{{ __('Hapus log poin') }}">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                <p>{{ __('Belum ada riwayat poin untuk siswa ini.') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Catat Poin --}}
    <div id="point-modal" class="hidden fixed inset-0 bg-slate-950/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-7 max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-xl">
            <div class="flex items-start justify-between gap-4 mb-5">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Catat poin kedisiplinan') }}</h3>
                <button type="button" onclick="closePointModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition-colors" aria-label="{{ __('Tutup') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('points.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">

                <div>
                    <label for="modal-category-select" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Kategori standar') }}</label>
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
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" id="type-reward" value="reward" class="hidden peer" onchange="updatePointMode('reward')">
                            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-950/30 text-center transition-colors">
                                <span class="block text-xs font-semibold text-slate-700 dark:text-slate-300 peer-checked:text-emerald-700 dark:peer-checked:text-emerald-300">{{ __('Prestasi') }}</span>
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
                    <textarea name="description" id="modal-description" rows="3" class="w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 dark:text-slate-100 focus:border-primary-500 focus:ring-primary-500 text-sm p-3" required></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" onclick="closePointModal()" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium rounded-lg text-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        {{ __('Batal') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white font-semibold rounded-lg text-sm hover:bg-primary-700 transition-colors">
                        {{ __('Simpan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPointModal() {
            document.getElementById('point-modal').classList.remove('hidden');
        }

        function closePointModal() {
            document.getElementById('point-modal').classList.add('hidden');
        }

        function applyCategory(select) {
            const option = select.options[select.selectedIndex];
            if (!option.value) return;

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
