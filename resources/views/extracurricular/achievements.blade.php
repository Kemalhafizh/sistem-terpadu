<x-app-layout>
    <x-slot name="title">Prestasi Lomba - {{ $extracurricular->name }}</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    🏆 Prestasi & Penghargaan Lomba
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Ekskul: {{ $extracurricular->name }} | Pembina: {{ $extracurricular->advisor->name ?? '-' }}
                </p>
            </div>
            <a href="{{ route('ekskul.manage') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅️ Kembali
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Tambah Prestasi -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl self-start">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-4">🏆 Catat Prestasi Baru</h3>
            
            <form action="{{ route('ekskul.achievements.store', $extracurricular->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Penghargaan / Lomba</label>
                    <input type="text" name="title" required placeholder="Juara 1 Turnamen Futsal Walikota..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tingkat Lomba</label>
                        <select name="level" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                            <option value="sekolah">Sekolah</option>
                            <option value="kecamatan">Kecamatan</option>
                            <option value="kota">Kota / Kabupaten</option>
                            <option value="provinsi">Provinsi</option>
                            <option value="nasional">Nasional</option>
                            <option value="internasional">Internasional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Penyelenggara</label>
                        <input type="text" name="organizer" required placeholder="Dinas Pendidikan Kota..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Lomba</label>
                        <input type="date" name="event_date" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Sertifikat (PDF/JPG)</label>
                        <input type="file" name="certificate" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Unggah Foto Piala / Dokumentasi</label>
                    <input type="file" name="photo" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <!-- Select Siswa Berpartisipasi -->
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih Siswa Berpartisipasi (Tahan Ctrl/Cmd untuk multi-select)</label>
                    <select name="participants[]" required multiple class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl p-3 text-xs text-gray-800 dark:text-white focus:outline-none min-h-[120px]">
                        @foreach($students as $st)
                            <option value="{{ $st->id }}">{{ $st->name }} (Class: {{ $st->classroom->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Catatan Tambahan</label>
                    <textarea name="notes" rows="3" placeholder="Tulis rincian perolehan medali atau keterangan tambahan..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-xs text-gray-800 dark:text-white focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                    🚀 Unggah Prestasi
                </button>
            </form>
        </div>

        <!-- Daftar Prestasi -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl lg:col-span-2 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm">🏆 Daftar Prestasi Juara & Lomba</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-55 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Lomba / Penyelenggara</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tingkat</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Anggota Terlibat</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Lampiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @forelse($extracurricular->achievements as $ach)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-all">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800 dark:text-white">{{ $ach->title }}</div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $ach->organizer }} | {{ $ach->event_date->format('d M Y') }}</p>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 font-extrabold text-[10px] rounded-lg">
                                        {{ strtoupper($ach->level) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 max-w-[150px]">
                                    @php
                                        $pIds = is_string($ach->participants) ? json_decode($ach->participants, true) : $ach->participants;
                                        $pNames = \App\Models\Student::whereIn('id', $pIds ?? [])->pluck('name')->toArray();
                                    @endphp
                                    <p class="truncate">{{ implode(', ', $pNames) }}</p>
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-1.5 text-xs">
                                    @if($ach->certificate_path)
                                        <a href="{{ asset('storage/' . $ach->certificate_path) }}" target="_blank" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-1 px-3.5 text-xs font-bold transition-all">
                                            📜 Sertifikat
                                        </a>
                                    @endif
                                    @if($ach->photo_path)
                                        <a href="{{ asset('storage/' . $ach->photo_path) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-1 px-3.5 text-xs font-bold transition-all shadow-md">
                                            🖼️ Foto
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-12 text-gray-400 italic">
                                    Belum ada prestasi kesiswaan tercatat untuk ekskul ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
