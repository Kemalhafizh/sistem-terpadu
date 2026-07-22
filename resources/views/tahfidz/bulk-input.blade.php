<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Input Setoran Massal') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Pencatatan Cepat Setoran Tahfidz Kelas
                </p>
            </div>
            <a href="{{ route('tahfidz.dashboard', ['classroom_id' => $classroom?->id]) }}" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                ← KEMBALI
            </a>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden" x-data="bulkInputManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Classroom Selector Card --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="text-xs font-black uppercase tracking-widest text-gray-400 font-bold">Pilih Kelas:</span>
                    <form action="{{ route('tahfidz.bulk-input') }}" method="GET" class="flex items-center gap-2">
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
                    <button type="button" @click="copyFirstRowToAll" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-amber-500/10 transition-all">
                         SALIN BARIS PERTAMA KE SEMUA BARIS AKTIF
                    </button>
                @endif
            </div>

            @if($students->isNotEmpty())
                <form action="{{ route('tahfidz.store-bulk') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">

                    <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                    <th class="pb-3 text-center w-12">Aktif</th>
                                    <th class="pb-3">Siswa</th>
                                    <th class="pb-3 w-40">Surah</th>
                                    <th class="pb-3 w-20 text-center">Ayat Dari</th>
                                    <th class="pb-3 w-20 text-center">Ayat Sampai</th>
                                    <th class="pb-3 w-24 text-center">Tipe</th>
                                    <th class="pb-3 w-20 text-center">Juz (1-30)</th>
                                    <th class="pb-3 w-20 text-center">Halaman</th>
                                    <th class="pb-3 w-20 text-center">Grade</th>
                                    <th class="pb-3 w-40">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs font-bold text-gray-700">
                                @foreach($students as $idx => $st)
                                    <tr class="align-middle">
                                        {{-- Checkbox Toggle --}}
                                        <td class="py-4 text-center">
                                            <input type="checkbox" name="setoran[{{ $idx }}][active]" value="1" 
                                                   x-model="rows[{{ $idx }}].active"
                                                   class="rounded text-primary-600 focus:ring-primary-500 w-4 h-4 border-gray-200">
                                            <input type="hidden" name="setoran[{{ $idx }}][student_id]" value="{{ $st->id }}">
                                            <input type="hidden" name="setoran[{{ $idx }}][date]" value="{{ date('Y-m-d') }}">
                                        </td>
                                        
                                        {{-- Student Info --}}
                                        <td class="py-4">
                                            <p class="font-black text-gray-800">{{ $st->name }}</p>
                                            <p class="text-[9px] text-gray-400 uppercase">{{ $st->nis }}</p>
                                        </td>

                                        {{-- Surah Select --}}
                                        <td class="py-4">
                                            <select name="setoran[{{ $idx }}][surah_number]" 
                                                    id="surah_{{ $idx }}"
                                                    :disabled="!rows[{{ $idx }}].active"
                                                    class="w-full bg-gray-50 border-none rounded-xl px-3 py-2 text-xs font-bold focus:ring-4 focus:ring-primary-100 disabled:opacity-50">
                                                @foreach($surahs as $num => $name)
                                                    <option value="{{ $num }}" {{ $num === 78 ? 'selected' : '' }}>{{ $num }}. {{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        {{-- Ayat Dari --}}
                                        <td class="py-4 text-center">
                                            <input type="number" name="setoran[{{ $idx }}][from_ayat]" 
                                                   id="from_{{ $idx }}"
                                                   value="1" min="1"
                                                   :disabled="!rows[{{ $idx }}].active"
                                                   class="w-16 bg-gray-50 border-none rounded-xl px-2 py-2 text-center text-xs font-bold focus:ring-4 focus:ring-primary-100 disabled:opacity-50">
                                        </td>

                                        {{-- Ayat Sampai --}}
                                        <td class="py-4 text-center">
                                            <input type="number" name="setoran[{{ $idx }}][to_ayat]" 
                                                   id="to_{{ $idx }}"
                                                   value="5" min="1"
                                                   :disabled="!rows[{{ $idx }}].active"
                                                   class="w-16 bg-gray-50 border-none rounded-xl px-2 py-2 text-center text-xs font-bold focus:ring-4 focus:ring-primary-100 disabled:opacity-50">
                                        </td>

                                        {{-- Type --}}
                                        <td class="py-4 text-center">
                                            <select name="setoran[{{ $idx }}][type]" 
                                                    id="type_{{ $idx }}"
                                                    :disabled="!rows[{{ $idx }}].active"
                                                    class="w-24 bg-gray-50 border-none rounded-xl px-2 py-2 text-xs font-bold focus:ring-4 focus:ring-primary-100 disabled:opacity-50">
                                                <option value="ziyadah">Ziyadah</option>
                                                <option value="murajaah">Murajaah</option>
                                            </select>
                                        </td>

                                        {{-- Juz --}}
                                        <td class="py-4 text-center">
                                            <input type="number" name="setoran[{{ $idx }}][juz]" 
                                                   id="juz_{{ $idx }}"
                                                   value="30" min="1" max="30"
                                                   :disabled="!rows[{{ $idx }}].active"
                                                   class="w-16 bg-gray-50 border-none rounded-xl px-2 py-2 text-center text-xs font-bold focus:ring-4 focus:ring-primary-100 disabled:opacity-50">
                                        </td>

                                        {{-- Pages Count --}}
                                        <td class="py-4 text-center">
                                            <input type="number" name="setoran[{{ $idx }}][pages_count]" 
                                                   id="pages_{{ $idx }}"
                                                   value="1" min="0"
                                                   :disabled="!rows[{{ $idx }}].active"
                                                   class="w-16 bg-gray-50 border-none rounded-xl px-2 py-2 text-center text-xs font-bold focus:ring-4 focus:ring-primary-100 disabled:opacity-50">
                                        </td>

                                        {{-- Grade --}}
                                        <td class="py-4 text-center">
                                            <select name="setoran[{{ $idx }}][grade]" 
                                                    id="grade_{{ $idx }}"
                                                    :disabled="!rows[{{ $idx }}].active"
                                                    class="w-16 bg-gray-50 border-none rounded-xl px-2 py-2 text-center text-xs font-bold focus:ring-4 focus:ring-primary-100 disabled:opacity-50">
                                                <option value="A">A</option>
                                                <option value="B">B</option>
                                                <option value="C">C</option>
                                                <option value="D">D</option>
                                            </select>
                                        </td>

                                        {{-- Notes --}}
                                        <td class="py-4">
                                            <input type="text" name="setoran[{{ $idx }}][notes]" 
                                                   id="notes_{{ $idx }}"
                                                   placeholder="Catatan..."
                                                   :disabled="!rows[{{ $idx }}].active"
                                                   class="w-full bg-gray-50 border-none rounded-xl px-3 py-2 text-xs font-bold focus:ring-4 focus:ring-primary-100 disabled:opacity-50">
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
                            Simpan Setoran Massal
                        </button>
                    </div>
                </form>
            @else
                <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-gray-100">
                    <span class="text-4xl"></span>
                    <h3 class="font-black text-lg text-gray-800 mt-4 uppercase">Tidak Ada Siswa</h3>
                    <p class="text-xs text-gray-400 mt-2">Pilih kelas lain atau tambahkan siswa ke kelas ini terlebih dahulu.</p>
                </div>
            @endif

        </div>
    </div>

    <script>
        function bulkInputManager() {
            return {
                rows: @json(collect($students)->map(function ($st, $idx) {
                    return [
                        'index' => $idx,
                        'active' => $idx === 0 // default only first active to guide the user
                    ];
                })->all()),
                
                copyFirstRowToAll() {
                    // Get first row values
                    const firstSurah = document.getElementById('surah_0').value;
                    const firstFrom = document.getElementById('from_0').value;
                    const firstTo = document.getElementById('to_0').value;
                    const firstType = document.getElementById('type_0').value;
                    const firstJuz = document.getElementById('juz_0').value;
                    const firstPages = document.getElementById('pages_0').value;
                    const firstGrade = document.getElementById('grade_0').value;
                    const firstNotes = document.getElementById('notes_0').value;

                    // Copy to all rows that are currently checked as ACTIVE
                    this.rows.forEach(row => {
                        if (row.index > 0 && row.active) {
                            document.getElementById(`surah_${row.index}`).value = firstSurah;
                            document.getElementById(`from_${row.index}`).value = firstFrom;
                            document.getElementById(`to_${row.index}`).value = firstTo;
                            document.getElementById(`type_${row.index}`).value = firstType;
                            document.getElementById(`juz_${row.index}`).value = firstJuz;
                            document.getElementById(`pages_${row.index}`).value = firstPages;
                            document.getElementById(`grade_${row.index}`).value = firstGrade;
                            document.getElementById(`notes_${row.index}`).value = firstNotes;
                        }
                    });
                }
            };
        }
    </script>
</x-app-layout>
