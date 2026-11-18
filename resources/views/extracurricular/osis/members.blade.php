<x-app-layout>
    <x-slot name="title">Kepengurusan OSIS</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Struktur Kepengurusan OSIS
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Kelola pengurus OSIS aktif Tahun Ajaran: {{ $activeYear->year ?? '-' }}
                </p>
            </div>
            <a href="{{ route('osis.dashboard') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Kembali
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Daftarkan Pengurus -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl self-start">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-4"> Daftarkan Pengurus Baru</h3>
            
            <form action="{{ route('osis.members.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih Siswa</label>
                    <select name="student_id" required class="w-full bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                        @foreach($students as $st)
                            <option value="{{ $st->id }}">{{ $st->name }} (Class: {{ $st->classroom->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Jabatan Kepengurusan</label>
                    <select name="position" required class="w-full bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                        <option value="Ketua Umum">Ketua Umum</option>
                        <option value="Wakil Ketua">Wakil Ketua</option>
                        <option value="Sekretaris I">Sekretaris I</option>
                        <option value="Sekretaris II">Sekretaris II</option>
                        <option value="Bendahara I">Bendahara I</option>
                        <option value="Bendahara II">Bendahara II</option>
                        <option value="Ketua Sekbid 1 (Keimanan)">Ketua Sekbid 1 (Keimanan)</option>
                        <option value="Ketua Sekbid 2 (Budi Pekerti)">Ketua Sekbid 2 (Budi Pekerti)</option>
                        <option value="Ketua Sekbid 3 (Bela Negara)">Ketua Sekbid 3 (Bela Negara)</option>
                        <option value="Ketua Sekbid 4 (Prestasi/Olahraga)">Ketua Sekbid 4 (Prestasi/Olahraga)</option>
                        <option value="Ketua Sekbid 5 (HAM/Politik)">Ketua Sekbid 5 (HAM/Politik)</option>
                        <option value="Ketua Sekbid 6 (Kewirausahaan)">Ketua Sekbid 6 (Kewirausahaan)</option>
                        <option value="Ketua Sekbid 7 (Kesehatan/Gizi)">Ketua Sekbid 7 (Kesehatan/Gizi)</option>
                        <option value="Ketua Sekbid 8 (Seni/Sastra)">Ketua Sekbid 8 (Seni/Sastra)</option>
                        <option value="Anggota Sekbid">Anggota Sekbid</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                     Simpan Pengurus
                </button>
            </form>
        </div>

        <!-- Daftar Pengurus OSIS -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl lg:col-span-2 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm"> Daftar Pengurus Aktif</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Pengurus</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Jabatan</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @forelse($members as $mb)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-all">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800 dark:text-white">{{ $mb->student->name }}</div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">NISN: {{ $mb->student->nisn ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-gray-800 dark:text-gray-300">
                                    {{ $mb->position }}
                                </td>
                                <td class="px-6 py-4 text-center text-xs">
                                    <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 font-extrabold text-[9px] rounded-lg">
                                        {{ strtoupper($mb->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('osis.members.destroy', $mb->id) }}" method="POST" onsubmit="return confirm('Nonaktifkan pengurus ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-700 text-xs font-bold">
                                             Nonaktifkan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-12 text-gray-400 italic">
                                    Belum ada pengurus OSIS aktif terdaftar untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
