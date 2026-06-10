<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
            {{ __('Edit Pengumuman') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden">
                <form action="{{ route('announcements.update', $announcement) }}" method="POST" enctype="multipart/form-data" x-data="{ targetRole: '{{ old('target_role', $announcement->target_role) }}' }" class="p-10 space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Judul -->
                    <div>
                        <label for="title" class="block text-sm font-black text-gray-700 dark:text-gray-250 uppercase tracking-widest mb-3">Judul Pengumuman</label>
                        <input id="title" class="w-full rounded-2xl border-gray-250 dark:border-gray-700 dark:bg-gray-750 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4" type="text" name="title" value="{{ old('title', $announcement->title) }}" required autofocus />
                    </div>

                    <!-- Isi Konten -->
                    <div>
                        <label for="content" class="block text-sm font-black text-gray-700 dark:text-gray-250 uppercase tracking-widest mb-3">Isi Pesan</label>
                        <textarea id="content" name="content" rows="6" class="w-full rounded-2xl border-gray-250 dark:border-gray-700 dark:bg-gray-750 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4" required>{{ old('content', $announcement->content) }}</textarea>
                    </div>

                    <!-- Uploads Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Poster Image -->
                        <div>
                            <label for="image" class="block text-sm font-black text-gray-700 dark:text-gray-250 uppercase tracking-widest mb-3">Poster / Foto (Opsional)</label>
                            @if($announcement->image_path)
                                <div class="mb-3 flex items-center space-x-3 bg-gray-50 dark:bg-gray-900 p-2.5 rounded-xl border border-gray-200/50 dark:border-gray-700">
                                    <img src="{{ Storage::url($announcement->image_path) }}" alt="" class="w-20 h-14 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    <span class="text-xs text-gray-400 dark:text-gray-550 font-bold">Gambar aktif saat ini</span>
                                </div>
                            @endif
                            <input id="image" class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 dark:bg-gray-750 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-3 text-sm" type="file" name="image" accept="image/*" />
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500 font-bold">Format: JPG, PNG, GIF. Maksimal ukuran: 2MB.</p>
                        </div>

                        <!-- Attachment -->
                        <div>
                            <label for="attachment" class="block text-sm font-black text-gray-700 dark:text-gray-250 uppercase tracking-widest mb-3">Dokumen Lampiran (Opsional)</label>
                            @if($announcement->attachment_path)
                                <div class="mb-3 flex items-center space-x-3 bg-gray-50 dark:bg-gray-900 p-2.5 rounded-xl border border-gray-200/50 dark:border-gray-700">
                                    <div class="w-10 h-10 rounded bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-650 dark:text-indigo-400 text-lg">
                                        📎
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-gray-400 dark:text-gray-550 font-bold truncate">Ada lampiran aktif</p>
                                        <a href="{{ Storage::url($announcement->attachment_path) }}" target="_blank" class="text-xs text-indigo-600 hover:underline font-bold">Download Lampiran Aktif</a>
                                    </div>
                                </div>
                            @endif
                            <input id="attachment" class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 dark:bg-gray-750 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-3 text-sm" type="file" name="attachment" />
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500 font-bold">Format: PDF, DOC, XLS, ZIP, dll. Maksimal ukuran: 5MB.</p>
                        </div>
                    </div>

                    <!-- Meta Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Kategori -->
                        <div>
                            <label for="category" class="block text-sm font-black text-gray-700 dark:text-gray-250 uppercase tracking-widest mb-3">Kategori</label>
                            <select id="category" name="category" required class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-750 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="umum" {{ old('category', $announcement->category) === 'umum' ? 'selected' : '' }}>Umum</option>
                                <option value="akademik" {{ old('category', $announcement->category) === 'akademik' ? 'selected' : '' }}>Akademik</option>
                                <option value="keuangan" {{ old('category', $announcement->category) === 'keuangan' ? 'selected' : '' }}>Keuangan</option>
                                <option value="kegiatan" {{ old('category', $announcement->category) === 'kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                                <option value="keislaman" {{ old('category', $announcement->category) === 'keislaman' ? 'selected' : '' }}>Keislaman</option>
                            </select>
                        </div>

                        <!-- Prioritas -->
                        <div>
                            <label for="priority" class="block text-sm font-black text-gray-700 dark:text-gray-250 uppercase tracking-widest mb-3">Prioritas</label>
                            <select id="priority" name="priority" required class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-750 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="low" {{ old('priority', $announcement->priority) === 'low' ? 'selected' : '' }}>Rendah (Low)</option>
                                <option value="normal" {{ old('priority', $announcement->priority) === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="urgent" {{ old('priority', $announcement->priority) === 'urgent' ? 'selected' : '' }}>Mendesak (Urgent)</option>
                            </select>
                        </div>

                        <!-- Kedaluwarsa -->
                        <div>
                            <label for="expires_at" class="block text-sm font-black text-gray-700 dark:text-gray-250 uppercase tracking-widest mb-3">Kedaluwarsa (Opsional)</label>
                            <input id="expires_at" class="w-full rounded-2xl border-gray-150 dark:border-gray-700 dark:bg-gray-750 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4" type="datetime-local" name="expires_at" value="{{ old('expires_at', $announcement->expires_at ? \Carbon\Carbon::parse($announcement->expires_at)->format('Y-m-d\TH:i') : '') }}" />
                        </div>
                    </div>

                    <!-- Target Penerima Section -->
                    <div class="pt-8 border-t border-gray-100 dark:border-gray-700 space-y-6">
                        <h3 class="text-base font-black text-gray-900 dark:text-white uppercase tracking-widest">Penargetan & Distribusi</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Target Role -->
                            <div>
                                <label for="target_role" class="block text-sm font-black text-gray-700 dark:text-gray-250 uppercase tracking-widest mb-3">Target Peran (Role)</label>
                                <select id="target_role" name="target_role" x-model="targetRole" class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-750 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                    <option value="">Semua Orang (Publik Sekolah)</option>
                                    <option value="student">Hanya Siswa</option>
                                    <option value="parent">Hanya Wali Murid</option>
                                    <option value="guru">Hanya Guru & Staf</option>
                                </select>
                            </div>

                            <!-- Target Classroom -->
                            <div x-show="['student', 'parent'].includes(targetRole)" x-transition class="transition-all">
                                <label for="target_classroom_id" class="block text-sm font-black text-gray-700 dark:text-gray-250 uppercase tracking-widest mb-3">Spesifik Kelas (Opsional)</label>
                                <select id="target_classroom_id" name="target_classroom_id" class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-750 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                    <option value="">Semua Kelas</option>
                                    @foreach($classrooms ?? [] as $classroom)
                                        <option value="{{ $classroom->id }}" {{ old('target_classroom_id', $announcement->target_classroom_id) == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500 font-bold">Jika dikosongkan, berarti ditujukan ke semua kelas.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan Tambahan Section -->
                    <div class="pt-8 border-t border-gray-100 dark:border-gray-700 space-y-6">
                        <h3 class="text-base font-black text-gray-900 dark:text-white uppercase tracking-widest">Fitur & Pengaturan Lanjutan</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Pin Toggle -->
                            <div class="flex items-start space-x-3 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-750">
                                <input id="is_pinned" type="checkbox" name="is_pinned" value="1" class="h-5 w-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 mt-0.5" {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }}>
                                <div class="ml-2">
                                    <label for="is_pinned" class="text-sm font-bold text-gray-900 dark:text-white">Sematkan Pengumuman (Pin to Dashboard)</label>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 leading-relaxed">Pengumuman ini akan diposisikan paling atas di halaman dasbor pengguna target.</p>
                                </div>
                            </div>

                            <!-- Require Acknowledgement -->
                            <div class="flex items-start space-x-3 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-750">
                                <input id="require_acknowledgement" type="checkbox" name="require_acknowledgement" value="1" class="h-5 w-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 mt-0.5" {{ old('require_acknowledgement', $announcement->require_acknowledgement) ? 'checked' : '' }}>
                                <div class="ml-2">
                                    <label for="require_acknowledgement" class="text-sm font-bold text-gray-900 dark:text-white">Wajib Konfirmasi Pembacaan</label>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 leading-relaxed">Penerima wajib mengonfirmasi secara resmi (klik tombol) bahwa mereka telah membaca dan memahami isi pesan ini.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-8 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end space-x-4">
                        <a href="{{ route('announcements.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="px-10 py-4 bg-primary-600 text-white rounded-[1.5rem] font-black text-sm shadow-xl shadow-primary-600/10 hover:bg-primary-700 hover:-translate-y-0.5 transition-all">
                            SIMPAN PERUBAHAN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
