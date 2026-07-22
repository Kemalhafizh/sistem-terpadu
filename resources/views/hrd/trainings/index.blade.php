<x-app-layout>
    <x-slot name="title">Riwayat Pelatihan - {{ $employee->user->name }}</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Pelatihan & Sertifikasi Pegawai
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Pegawai: {{ $employee->user->name }} | Jabatan: {{ $employee->position }}
                </p>
            </div>
            <a href="{{ route('hrd.employees.show', $employee->id) }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                 Lihat Profil
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Tambah Pelatihan -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl self-start">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-4"> Catat Pelatihan Baru</h3>
            
            <form action="{{ route('hrd.employees.trainings.store', $employee->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Pelatihan / Sertifikasi</label>
                    <input type="text" name="title" required placeholder="Workshop Implementasi Kurikulum Merdeka..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Jenis Kegiatan</label>
                        <select name="type" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-white focus:outline-none">
                            <option value="workshop">Workshop</option>
                            <option value="seminar">Seminar</option>
                            <option value="sertifikasi">Sertifikasi Profesi</option>
                            <option value="diklat">Diklat</option>
                            <option value="webinar">Webinar</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Penyelenggara</label>
                        <input type="text" name="organizer" required placeholder="YBWSA / Kemendikbud..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-white focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tgl Mulai</label>
                        <input type="date" name="start_date" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-2 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tgl Selesai</label>
                        <input type="date" name="end_date" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-2 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Durasi (JP)</label>
                        <input type="number" name="hours" placeholder="32" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">No. Sertifikat</label>
                        <input type="text" name="certificate_number" placeholder="CERT-2026-X..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kadaluarsa Sertifikat</label>
                        <input type="date" name="certificate_expiry" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-2 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Catatan Ringkas</label>
                    <textarea name="notes" rows="3" placeholder="Deskripsi ringkas materi pelatihan..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-xs text-gray-800 dark:text-white focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                     Tambah Riwayat Pelatihan
                </button>
            </form>
        </div>

        <!-- Daftar Pelatihan Pegawai -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl lg:col-span-2 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm"> Riwayat Diklat & Pelatihan</h3>
                <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 font-extrabold text-[10px] rounded-lg">
                    Total JP: {{ $employee->trainings->sum('hours') }} JP
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-55 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Pelatihan & Penyelenggara</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tanggal & JP</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">No. Sertifikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @forelse($employee->trainings as $tr)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-all">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800 dark:text-white">{{ $tr->title }}</div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Penyelenggara: {{ $tr->organizer }}</p>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 font-extrabold text-[10px] rounded-lg">
                                        {{ strtoupper($tr->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    <p class="font-medium">{{ $tr->start_date->format('d M Y') }}</p>
                                    <p class="text-emerald-600 font-bold mt-0.5">{{ $tr->hours ?? 0 }} JP</p>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 text-right">
                                    <span class="font-mono text-gray-800 dark:text-white font-bold">{{ $tr->certificate_number ?? '-' }}</span>
                                    @if($tr->certificate_expiry)
                                        <p class="text-[9px] text-gray-400 mt-1">Expiry: {{ $tr->certificate_expiry->format('d M Y') }}</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-12 text-gray-400 italic">
                                    Belum ada catatan diklat atau sertifikasi terdokumentasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
