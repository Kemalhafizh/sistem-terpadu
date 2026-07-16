<x-app-layout>
    <x-slot name="title">Penilaian Ekskul - {{ $extracurricular->name }}</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    📝 Penilaian Batch Rapor Anggota
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Ekskul: {{ $extracurricular->name }} | Tahun Ajaran Aktif: {{ $academicYear->year }}
                </p>
            </div>
            <a href="{{ route('ekskul.manage') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅️ Kembali
            </a>
        </div>
    </x-slot>

    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
        <form action="{{ route('ekskul.grading.store', $extracurricular->id) }}" method="POST">
            @csrf

            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm">📝 Input Predikat & Capaian Rapor Siswa</h3>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2 px-6 text-xs font-bold transition-all shadow-md">
                    💾 Simpan Semua Nilai
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-55 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider" style="width: 25%;">Nama Siswa / NISN</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider" style="width: 15%;">Kelas</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider" style="width: 15%;">Nilai Predikat</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider" style="width: 45%;">Deskripsi Capaian Kualitatif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @forelse($extracurricular->activeMembers as $index => $member)
                            @php
                                $existing = $grades->get($member->id);
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-all">
                                <td class="px-6 py-4">
                                    <input type="hidden" name="grades[{{ $index }}][member_id]" value="{{ $member->id }}">
                                    <div class="font-bold text-gray-850 dark:text-white">{{ $member->student->name }}</div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">NISN: {{ $member->student->nisn ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    {{ $member->student->classroom->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <select name="grades[{{ $index }}][grade]" class="bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-805 dark:text-white focus:outline-none w-32">
                                        <option value="">-- Predikat --</option>
                                        <option value="A" {{ old("grades.{$index}.grade", $existing->grade ?? '') === 'A' ? 'selected' : '' }}>A (Sangat Baik)</option>
                                        <option value="B" {{ old("grades.{$index}.grade", $existing->grade ?? '') === 'B' ? 'selected' : '' }}>B (Baik)</option>
                                        <option value="C" {{ old("grades.{$index}.grade", $existing->grade ?? '') === 'C' ? 'selected' : '' }}>C (Cukup)</option>
                                        <option value="D" {{ old("grades.{$index}.grade", $existing->grade ?? '') === 'D' ? 'selected' : '' }}>D (Kurang)</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" name="grades[{{ $index }}][description]" value="{{ old("grades.{$index}.description", $existing->description ?? '') }}" placeholder="Siswa aktif dan menguasai teknik dasar dengan sangat baik..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-805 dark:text-white focus:outline-none">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-12 text-gray-400 italic">
                                    Belum ada anggota aktif terdaftar pada ekstrakurikuler ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</x-app-layout>
