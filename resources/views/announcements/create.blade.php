<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Pengumuman Baru') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
            <div class="p-8 bg-white border-b border-gray-200">
                <form method="POST" action="{{ route('announcements.store') }}" enctype="multipart/form-data" x-data="{ targetRole: '' }">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="md:col-span-2">
                            <label for="title" class="block font-medium text-sm text-gray-700 mb-1">Judul Pengumuman</label>
                            <input id="title" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="text" name="title" value="{{ old('title') }}" required autofocus placeholder="Contoh: Rapat Wali Murid Semester Ganjil" />
                        </div>

                        <div class="md:col-span-2">
                            <label for="content" class="block font-medium text-sm text-gray-700 mb-1">Isi Pesan</label>
                            <textarea id="content" name="content" rows="4" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required placeholder="Tuliskan isi pengumuman secara detail...">{{ old('content') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="image" class="block font-medium text-sm text-gray-700 mb-1">Upload Poster / Foto (Opsional)</label>
                            <input id="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 border border-gray-300 rounded-md shadow-sm cursor-pointer" type="file" name="image" accept="image/*" />
                            <p class="mt-1 text-xs text-gray-400">Format: JPG, PNG, GIF. Maksimal ukuran: 2MB.</p>
                        </div>

                        <div>
                            <label for="priority" class="block font-medium text-sm text-gray-700 mb-1">Prioritas</label>
                            <select id="priority" name="priority" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                <option value="low">Rendah (Low)</option>
                                <option value="normal" selected>Normal</option>
                                <option value="urgent">Mendesak (Urgent) - Warna Merah</option>
                            </select>
                        </div>

                        <div>
                            <label for="expires_at" class="block font-medium text-sm text-gray-700 mb-1">Tanggal Kedaluwarsa (Opsional)</label>
                            <input id="expires_at" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" />
                            <p class="mt-1 text-xs text-gray-400">Pengumuman akan otomatis hilang setelah tanggal ini.</p>
                        </div>

                        <div class="md:col-span-2 pt-4 border-t border-gray-100 mt-2">
                            <h3 class="text-sm font-bold text-gray-800 mb-4">Penargetan (Siapa yang akan melihat ini?)</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="target_role" class="block font-medium text-sm text-gray-700 mb-1">Target Peran (Role)</label>
                                    <select id="target_role" name="target_role" x-model="targetRole" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                        <option value="">Semua Orang (Publik Sekolah)</option>
                                        <option value="student">Hanya Siswa</option>
                                        <option value="parent">Hanya Wali Murid</option>
                                        <option value="guru">Hanya Guru & Staf</option>
                                    </select>
                                </div>

                                <div x-show="['student', 'parent'].includes(targetRole)" style="display: none;" x-transition>
                                    <label for="target_classroom_id" class="block font-medium text-sm text-gray-700 mb-1">Spesifik Kelas (Opsional)</label>
                                    <select id="target_classroom_id" name="target_classroom_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                        <option value="">Semua Kelas</option>
                                        @foreach($classrooms ?? [] as $classroom)
                                            <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-gray-400">Jika dikosongkan, berarti dikirim ke semua kelas.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6 pt-4 border-t border-gray-100">
                        <a href="{{ route('announcements.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:ring ring-primary-300 shadow-sm transition-all">
                            Sebarkan Pengumuman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
