<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                    <span class="w-8 h-8 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                    </span>
                    {{ __('Cockpit Pembina Ekstrakurikuler') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">SMA ISLAM SULTAN AGUNG 1 • PANEL MANAJEMEN GURU PEMBINA</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" x-data="{ selectedEkskulId: localStorage.getItem('last_managed_ekskul') || '' }">
            
            <!-- Alert Session Messages -->
            @if(session('success'))
                <div class="rounded-2xl p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 flex items-center text-emerald-800 dark:text-emerald-300 animate-fade-in">
                    <svg class="w-5 h-5 mr-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 flex items-center text-red-800 dark:text-red-300 animate-fade-in">
                    <svg class="w-5 h-5 mr-3 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Main Control Selection -->
            <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="space-y-1 w-full sm:w-auto">
                    <h4 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Pilih Ekstrakurikuler yang Dikelola</h4>
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Silakan pilih ekskul untuk membuka panel presensi & dokumentasi</p>
                </div>
                
                <div class="w-full sm:w-80">
                    <select x-model="selectedEkskulId" @change="localStorage.setItem('last_managed_ekskul', selectedEkskulId)" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 dark:focus:border-emerald-600 focus:ring-emerald-500 dark:focus:ring-emerald-600 rounded-xl shadow-sm font-semibold text-sm">
                        <option value="">-- Pilih Ekstrakurikuler --</option>
                        @foreach($extracurriculars as $ekskul)
                            <option value="{{ $ekskul->id }}">{{ $ekskul->name }} (Pembina: {{ $ekskul->advisor->name }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Empty State if none selected -->
            <div x-show="selectedEkskulId === ''" class="bg-white dark:bg-gray-800 rounded-[2rem] p-12 text-center border border-gray-100 dark:border-gray-700/50 shadow-sm flex flex-col items-center justify-center">
                <svg class="w-16 h-16 text-slate-300 dark:text-gray-600 mb-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                </svg>
                <h4 class="text-lg font-black text-gray-900 dark:text-white">Ekstrakurikuler Belum Dipilih</h4>
                <p class="text-sm font-semibold text-gray-400 dark:text-gray-500 mt-1">Silakan pilih salah satu ekstrakurikuler di atas untuk memuat panel manajemen presensi dan pelaporan latihan.</p>
            </div>

            @foreach($extracurriculars as $ekskul)
                @php
                    $members = $ekskul->activeMembers()->with('student.user', 'student.classroom')->get();
                @endphp
                <div x-show="selectedEkskulId === '{{ $ekskul->id }}'" class="grid grid-cols-1 lg:grid-cols-3 gap-8" style="display: none;">
                    
                    <!-- Left: Post activity reports -->
                    <div class="space-y-8">
                        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-6">
                            <div>
                                <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                                    <span class="w-2 h-6 bg-emerald-600 rounded-full mr-3"></span>
                                    Catat Dokumentasi Latihan
                                </h4>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Buat jurnal laporan dan unggah foto latihan terbaru</p>
                            </div>

                            <form method="POST" action="{{ route('ekskul.activities.store') }}" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <input type="hidden" name="extracurricular_id" value="{{ $ekskul->id }}">

                                <div>
                                    <x-input-label for="title-{{ $ekskul->id }}" value="Judul Kegiatan" />
                                    <x-text-input id="title-{{ $ekskul->id }}" name="title" type="text" class="mt-1 block w-full" placeholder="Latihan Rutin Pramuka, LKBB, dsb." required />
                                </div>

                                <div>
                                    <x-input-label for="activity_date-{{ $ekskul->id }}" value="Tanggal Latihan" />
                                    <x-text-input id="activity_date-{{ $ekskul->id }}" name="activity_date" type="date" class="mt-1 block w-full" value="{{ now()->toDateString() }}" required />
                                </div>

                                <div>
                                    <x-input-label for="description-{{ $ekskul->id }}" value="Deskripsi & Ringkasan Kegiatan" />
                                    <textarea id="description-{{ $ekskul->id }}" name="description" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 dark:focus:border-emerald-600 focus:ring-emerald-500 dark:focus:ring-emerald-600 rounded-xl shadow-sm text-sm" placeholder="Materi yang diajarkan, poin evaluasi latihan, dsb..."></textarea>
                                </div>

                                <div>
                                    <x-input-label for="images-{{ $ekskul->id }}" value="Unggah Foto Dokumentasi (Multiselect)" />
                                    <input id="images-{{ $ekskul->id }}" name="images[]" type="file" multiple accept="image/png, image/jpeg, image/jpg" class="mt-2 block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:bg-emerald-50 dark:file:bg-emerald-950/30 file:text-emerald-700 dark:file:text-emerald-400 hover:file:bg-emerald-100 transition-all" />
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-widest font-black">Maksimal 5MB per berkas foto. Jenis berkas: PNG, JPG, JPEG.</p>
                                </div>

                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-600/10 hover:shadow-emerald-600/20 active:scale-95 border border-emerald-500/20">
                                    Simpan Dokumentasi Jurnal
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Batch attendance sheet -->
                    <div class="lg:col-span-2 space-y-8">
                        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-6 flex flex-col">
                            <div>
                                <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                                    <span class="w-2 h-6 bg-emerald-600 rounded-full mr-3"></span>
                                    Presensi Latihan Mingguan
                                </h4>
                                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Lakukan input kehadiran siswa secara kolektif per tanggal</p>
                            </div>

                            <form method="POST" action="{{ route('ekskul.attendance.store') }}" class="space-y-6">
                                @csrf
                                <input type="hidden" name="extracurricular_id" value="{{ $ekskul->id }}">

                                <div class="flex items-center gap-4 max-w-xs">
                                    <div class="w-full">
                                        <x-input-label for="attendance_date-{{ $ekskul->id }}" value="Tanggal Presensi" />
                                        <x-text-input id="attendance_date-{{ $ekskul->id }}" name="date" type="date" class="mt-1 block w-full" value="{{ now()->toDateString() }}" required />
                                    </div>
                                </div>

                                <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-700/50 pt-4">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="border-b border-gray-100 dark:border-gray-700 text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                                <th class="py-3 px-4">Nama Siswa</th>
                                                <th class="py-3 px-4">Kelas</th>
                                                <th class="py-3 px-4 text-center">Status Kehadiran</th>
                                                <th class="py-3 px-4">Catatan Khusus</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            @forelse($members as $index => $member)
                                                <tr class="hover:bg-slate-50 dark:hover:bg-gray-900/50 transition-colors">
                                                    <td class="py-4 px-4 flex items-center gap-3">
                                                        <input type="hidden" name="attendance[{{ $index }}][student_id]" value="{{ $member->student_id }}">
                                                        <div class="w-8 h-8 bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-300 rounded-lg flex items-center justify-center font-extrabold text-sm">
                                                            {{ substr($member->student->user->name, 0, 1) }}
                                                        </div>
                                                        <span class="font-extrabold text-gray-900 dark:text-white">{{ $member->student->user->name }}</span>
                                                    </td>
                                                    <td class="py-4 px-4 text-gray-500 dark:text-gray-400">
                                                        {{ $member->student->classroom->name ?? '-' }}
                                                    </td>
                                                    <td class="py-4 px-4">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <!-- H (Hadir) -->
                                                            <label class="relative flex items-center justify-center cursor-pointer group">
                                                                <input type="radio" name="attendance[{{ $index }}][status]" value="H" checked class="sr-only peer">
                                                                <span class="w-8 h-8 rounded-full border border-gray-200 dark:border-gray-700 text-slate-500 dark:text-slate-400 flex items-center justify-center font-black transition-all peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-transparent hover:bg-emerald-50 dark:hover:bg-emerald-950/20">H</span>
                                                            </label>

                                                            <!-- S (Sakit) -->
                                                            <label class="relative flex items-center justify-center cursor-pointer group">
                                                                <input type="radio" name="attendance[{{ $index }}][status]" value="S" class="sr-only peer">
                                                                <span class="w-8 h-8 rounded-full border border-gray-200 dark:border-gray-700 text-slate-500 dark:text-slate-400 flex items-center justify-center font-black transition-all peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-transparent hover:bg-blue-50 dark:hover:bg-blue-950/20">S</span>
                                                            </label>

                                                            <!-- I (Izin) -->
                                                            <label class="relative flex items-center justify-center cursor-pointer group">
                                                                <input type="radio" name="attendance[{{ $index }}][status]" value="I" class="sr-only peer">
                                                                <span class="w-8 h-8 rounded-full border border-gray-200 dark:border-gray-700 text-slate-500 dark:text-slate-400 flex items-center justify-center font-black transition-all peer-checked:bg-amber-600 peer-checked:text-white peer-checked:border-transparent hover:bg-amber-50 dark:hover:bg-amber-950/20">I</span>
                                                            </label>

                                                            <!-- A (Alpa) -->
                                                            <label class="relative flex items-center justify-center cursor-pointer group">
                                                                <input type="radio" name="attendance[{{ $index }}][status]" value="A" class="sr-only peer">
                                                                <span class="w-8 h-8 rounded-full border border-gray-200 dark:border-gray-700 text-slate-500 dark:text-slate-400 flex items-center justify-center font-black transition-all peer-checked:bg-red-600 peer-checked:text-white peer-checked:border-transparent hover:bg-red-50 dark:hover:bg-red-950/20">A</span>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-4">
                                                        <input type="text" name="attendance[{{ $index }}][notes]" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 dark:focus:border-emerald-600 focus:ring-emerald-500 dark:focus:ring-emerald-600 rounded-lg shadow-sm text-xs py-1.5" placeholder="Catatan keaktifan/keterangan...">
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-8 text-center text-gray-400 dark:text-gray-500">
                                                        Belum ada anggota terdaftar aktif di ekstrakurikuler ini.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if($members->count() > 0)
                                    <div class="flex justify-end pt-2">
                                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-600/10 hover:shadow-emerald-600/20 active:scale-95 border border-emerald-500/20">
                                            Simpan Rekap Presensi Siswa
                                        </button>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>

                </div>
            @endforeach

        </div>
    </div>
</x-app-layout>
