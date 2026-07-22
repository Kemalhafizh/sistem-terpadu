<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Atur Target Hafalan Semester') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    {{ $academicYear ? 'Tahun Ajaran ' . $academicYear->year . ' (' . $academicYear->semester . ')' : '' }}
                </p>
            </div>
            <a href="{{ route('tahfidz.dashboard', ['classroom_id' => $classroom?->id]) }}" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                ← KEMBALI
            </a>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden" x-data="targetSettingManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Classroom Selector Card --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="text-xs font-black uppercase tracking-widest text-gray-400 font-bold">Pilih Kelas:</span>
                    <form action="{{ route('tahfidz.target-setting') }}" method="GET" class="flex items-center gap-2">
                        <select name="classroom_id" onchange="this.form.submit()" class="bg-gray-50 border-none rounded-xl px-4 py-2 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                            @foreach($classrooms as $c)
                                <option value="{{ $c->id }}" {{ $classroom && $classroom->id === $c->id ? 'selected' : '' }}>
                                    Kelas {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                @if($students->isNotEmpty())
                    <button type="button" @click="copyFirstTargetToAll" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-amber-500/10 transition-all">
                         SET TARGET BARIS PERTAMA KE SEMUA SISWA
                    </button>
                @endif
            </div>

            @if($students->isNotEmpty())
                <form action="{{ route('tahfidz.store-targets') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">

                    <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[750px]">
                            <thead>
                                <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                    <th class="pb-3">Siswa</th>
                                    <th class="pb-3 w-40 text-center">Target Juz</th>
                                    <th class="pb-3 w-40 text-center">Target Halaman</th>
                                    <th class="pb-3 text-center">Capaian Ziyadah Saat Ini</th>
                                    <th class="pb-3">Catatan Target / Bimbingan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs font-bold text-gray-700">
                                @foreach($students as $idx => $st)
                                    @php
                                        $tgt = $st->tahfidzTargets->first();
                                    @endphp
                                    <tr class="align-middle">
                                        {{-- Student Info --}}
                                        <td class="py-4">
                                            <p class="font-black text-gray-800">{{ $st->name }}</p>
                                            <p class="text-[9px] text-gray-400 uppercase">{{ $st->nis }}</p>
                                            <input type="hidden" name="targets[{{ $idx }}][student_id]" value="{{ $st->id }}">
                                        </td>

                                        {{-- Target Juz --}}
                                        <td class="py-4 text-center">
                                            <input type="number" name="targets[{{ $idx }}][target_juz]" 
                                                   id="target_juz_{{ $idx }}"
                                                   value="{{ $tgt ? $tgt->target_juz : 2 }}" min="0" max="30"
                                                   class="w-24 bg-gray-50 border-none rounded-xl px-3 py-2 text-center text-xs font-bold focus:ring-4 focus:ring-primary-100">
                                        </td>

                                        {{-- Target Halaman --}}
                                        <td class="py-4 text-center">
                                            <input type="number" name="targets[{{ $idx }}][target_pages]" 
                                                   id="target_pages_{{ $idx }}"
                                                   value="{{ $tgt ? $tgt->target_pages : 40 }}" min="0"
                                                   class="w-24 bg-gray-50 border-none rounded-xl px-3 py-2 text-center text-xs font-bold focus:ring-4 focus:ring-primary-100">
                                        </td>

                                        {{-- Current Achieved --}}
                                        <td class="py-4 text-center text-emerald-600">
                                            @if($tgt)
                                                {{ $tgt->achieved_juz }} Juz ({{ $tgt->achieved_pages }} Hal)
                                            @else
                                                Belum Ada Target
                                            @endif
                                        </td>

                                        {{-- Notes --}}
                                        <td class="py-4">
                                            <textarea name="targets[{{ $idx }}][notes]" 
                                                      id="target_notes_{{ $idx }}"
                                                      rows="1" placeholder="Catatan bimbingan khusus..."
                                                      class="w-full bg-gray-50 border-none rounded-xl px-3 py-2 text-xs font-bold focus:ring-4 focus:ring-primary-100">{{ $tgt ? $tgt->notes : '' }}</textarea>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('tahfidz.dashboard', ['classroom_id' => $classroom->id]) }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl text-xs font-black uppercase tracking-widest transition-all">
                            Batal
                        </a>
                        <button type="submit" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-primary-900/10 transition-all">
                            Simpan Target Siswa
                        </button>
                    </div>
                </form>
            @else
                <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-gray-100">
                    <span class="text-4xl"></span>
                    <h3 class="font-black text-lg text-gray-800 mt-4 uppercase">Tidak Ada Siswa</h3>
                    <p class="text-xs text-gray-400 mt-2">Pilih kelas lain atau hubungkan siswa ke kelas ini terlebih dahulu.</p>
                </div>
            @endif

        </div>
    </div>

    <script>
        function targetSettingManager() {
            return {
                copyFirstTargetToAll() {
                    const firstJuz = document.getElementById('target_juz_0').value;
                    const firstPages = document.getElementById('target_pages_0').value;
                    const firstNotes = document.getElementById('target_notes_0').value;

                    const totalRows = {{ $students->count() }};
                    for (let i = 1; i < totalRows; i++) {
                        const juzInput = document.getElementById(`target_juz_${i}`);
                        const pagesInput = document.getElementById(`target_pages_${i}`);
                        const notesInput = document.getElementById(`target_notes_${i}`);

                        if (juzInput) juzInput.value = firstJuz;
                        if (pagesInput) pagesInput.value = firstPages;
                        if (notesInput) notesInput.value = firstNotes;
                    }
                }
            };
        }
    </script>
</x-app-layout>
