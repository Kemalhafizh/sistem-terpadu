<x-app-layout>
    <x-slot name="title">Kelulusan Massal Siswa</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Kelulusan Massal (Bulk Graduate)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Ubah status seluruh siswa kelas XII secara massal menjadi alumni.
                </p>
            </div>
            <a href="{{ route('alumni.index') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Kembali
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Pemilihan Kelas -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
            <form method="GET" action="{{ route('alumni.bulk-graduate') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih Kelas XII</label>
                    <select name="classroom_id" required class="w-full bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}" {{ $selectedClassroomId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                         Muat Daftar Siswa
                    </button>
                </div>
                <div></div>
            </form>
        </div>

        @if($selectedClassroomId)
            <!-- Form Luluskan Massal -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
                <form action="{{ route('alumni.bulk-graduate.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-50 dark:bg-gray-900 p-4 rounded-2xl border border-gray-200 dark:border-gray-700">
                        <div>
                            <h4 class="font-extrabold text-sm text-gray-800 dark:text-white"> Konfigurasi Kelulusan</h4>
                            <p class="text-xs text-gray-400 mt-0.5">Tentukan tahun kelulusan untuk siswa yang dipilih di bawah ini.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-gray-500">Tahun Lulus:</label>
                            <input type="number" name="graduation_year" value="{{ date('Y') }}" required min="2000" max="{{ date('Y') + 1 }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:outline-none w-24">
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700/50">
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider w-12">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Lengkap</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">NIS / NISN</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status Sekarang</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                                @forelse($students as $s)
                                    <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-700/5 transition-all">
                                        <td class="px-6 py-4">
                                            <input type="checkbox" name="student_ids[]" value="{{ $s->id }}" class="student-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        </td>
                                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-white">
                                            {{ $s->name }}
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-500">
                                            {{ $s->nis }} / {{ $s->nisn }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">AKTIF</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-8 text-gray-400 italic">
                                            Tidak ada siswa aktif di kelas ini yang dapat diluluskan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($students->isNotEmpty())
                        <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                            <a href="{{ route('alumni.bulk-graduate') }}" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-6 text-xs font-bold transition-all">
                                Batal
                            </a>
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin meluluskan siswa terpilih? Aksi ini akan mengubah status mereka menjadi ALUMNI.')" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2 px-6 text-xs font-bold transition-all shadow-md">
                                 Luluskan Siswa Terpilih
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        @endif
    </div>

    <script>
        function toggleSelectAll(master) {
            const checkboxes = document.getElementsByClassName('student-checkbox');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = master.checked;
            }
        }
    </script>
</x-app-layout>
