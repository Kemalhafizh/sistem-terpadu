<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kelola Galeri Foto Sekolah') }}
            </h2>
            <a href="{{ route('cms.dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-all flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Dashboard CMS</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- List Column (2/3 width) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
                        <div class="p-6 border-b border-gray-100">
                            <h3 class="text-base font-bold text-gray-800">Daftar Foto Galeri</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Foto</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Informasi</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Urutan</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($galleries as $gallery)
                                        <tr class="hover:bg-gray-50 transition-all">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="w-20 h-14 rounded bg-gray-100 overflow-hidden border border-gray-200 shadow-sm">
                                                    <img src="{{ asset('storage/' . $gallery->image_path) }}" class="w-full h-full object-cover" alt="{{ $gallery->title }}">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-bold text-gray-900">{{ $gallery->title }}</div>
                                                <div class="text-xs text-gray-400 mt-0.5 max-w-xs truncate">{{ $gallery->caption ?? 'Tanpa deskripsi' }}</div>
                                                <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-[10px] font-bold bg-primary-50 text-primary-700">
                                                    {{ $gallery->category ?? 'Umum' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-bold text-gray-800">
                                                {{ $gallery->order }}
                                            </td>
                                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                                @if($gallery->is_active)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                                        Aktif
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500">
                                                        Nonaktif
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <button type="button" 
                                                            onclick="openEditModal('{{ $gallery->id }}', '{{ addslashes($gallery->title) }}', '{{ addslashes($gallery->caption) }}', '{{ addslashes($gallery->category) }}', '{{ $gallery->order }}', '{{ $gallery->is_active }}', '{{ asset('storage/' . $gallery->image_path) }}')"
                                                            class="p-1.5 bg-blue-50 text-blue-600 hover:text-blue-900 hover:bg-blue-100 rounded-lg transition-all" title="Edit">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    </button>
                                                    <form action="{{ route('cms.galleries.destroy', $gallery->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto galeri ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1.5 bg-red-50 text-red-600 hover:text-red-900 hover:bg-red-100 rounded-lg transition-all" title="Hapus">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-sm font-medium text-gray-400">
                                                Belum ada foto galeri. Silakan tambahkan melalui form di samping.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($galleries->hasPages())
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                                {{ $galleries->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Add Form Column (1/3 width) -->
                <div>
                    <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-6 space-y-6 h-fit sticky top-6">
                        <h3 class="text-base font-bold text-gray-800 pb-3 border-b border-gray-100">Tambah Foto Galeri</h3>
                        <form action="{{ route('cms.galleries.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf

                            <div>
                                <label for="title" class="block text-sm font-bold text-gray-700">Judul Foto</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-medium"
                                       placeholder="Contoh: Gedung Laboratorium Baru">
                                @error('title')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="category" class="block text-sm font-bold text-gray-700">Kategori</label>
                                <input type="text" name="category" id="category" value="{{ old('category', 'Fasilitas') }}" required
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-medium"
                                       placeholder="Contoh: Fasilitas, Kegiatan, KBM, Prestasi">
                                @error('category')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="caption" class="block text-sm font-bold text-gray-700">Keterangan / Keterangan</label>
                                <textarea name="caption" id="caption" rows="3"
                                          class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-medium"
                                          placeholder="Tulis deskripsi singkat mengenai foto ini...">{{ old('caption') }}</textarea>
                                @error('caption')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">File Foto</label>
                                <label class="w-full flex flex-col items-center justify-center px-4 py-4 bg-white text-gray-400 rounded-xl border-2 border-dashed border-gray-300 hover:border-primary-500 hover:text-primary-500 transition-all cursor-pointer text-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span class="mt-1 text-xs font-semibold" id="file-label">Pilih File Gambar</span>
                                    <span class="text-[9px] text-gray-400 mt-0.5">JPG, PNG, GIF maks 3MB</span>
                                    <input type="file" name="image_path" id="image_path" class="hidden" accept="image/*" required onchange="updateFileLabel(this)">
                                </label>
                                @error('image_path')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="order" class="block text-sm font-bold text-gray-700">Urutan</label>
                                    <input type="number" name="order" id="order" value="{{ old('order', 0) }}" required
                                           class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-bold">
                                </div>
                                <div class="flex items-center pt-5">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" checked>
                                        <span class="ml-2 text-sm font-bold text-gray-700">Aktif</span>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="w-full mt-4 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow transition-all text-center">
                                Simpan Foto
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal Dialog -->
    <div id="editModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEditModal()"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200">
                <div class="p-6 space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h3 class="text-base font-bold text-gray-800" id="modal-title">Edit Foto Galeri</h3>
                        <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="edit_title" class="block text-sm font-bold text-gray-700">Judul Foto</label>
                            <input type="text" name="title" id="edit_title" required
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-medium">
                        </div>

                        <div>
                            <label for="edit_category" class="block text-sm font-bold text-gray-700">Kategori</label>
                            <input type="text" name="category" id="edit_category" required
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-medium">
                        </div>

                        <div>
                            <label for="edit_caption" class="block text-sm font-bold text-gray-700">Keterangan / Caption</label>
                            <textarea name="caption" id="edit_caption" rows="3"
                                      class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-medium"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Ganti Foto (Opsional)</label>
                            <div class="flex items-center space-x-4">
                                <div class="w-20 h-14 rounded overflow-hidden border border-gray-200 flex-shrink-0 bg-gray-50">
                                    <img id="edit_img_preview" src="#" class="w-full h-full object-cover">
                                </div>
                                <label class="flex-1 flex flex-col items-center justify-center px-3 py-3 bg-white text-gray-400 rounded-xl border-2 border-dashed border-gray-200 hover:border-primary-500 hover:text-primary-500 transition-all cursor-pointer text-center">
                                    <span class="text-xs font-semibold" id="edit-file-label">Ganti Foto Baru</span>
                                    <input type="file" name="image_path" id="edit_image_path" class="hidden" accept="image/*" onchange="updateEditFileLabel(this)">
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="edit_order" class="block text-sm font-bold text-gray-700">Urutan</label>
                                <input type="number" name="order" id="edit_order" required
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-bold">
                            </div>
                            <div class="flex items-center pt-5">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm font-bold text-gray-700">Aktif</span>
                                </label>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-2">
                            <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow transition-all">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateFileLabel(input) {
            const label = document.getElementById('file-label');
            if (input.files.length > 0) {
                label.innerText = input.files[0].name;
            } else {
                label.innerText = 'Pilih File Gambar';
            }
        }

        function updateEditFileLabel(input) {
            const label = document.getElementById('edit-file-label');
            const imgPreview = document.getElementById('edit_img_preview');
            if (input.files.length > 0) {
                label.innerText = input.files[0].name;
                const reader = new FileReader();
                reader.onload = function (e) {
                    imgPreview.setAttribute('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                label.innerText = 'Ganti Foto Baru';
            }
        }

        function openEditModal(id, title, caption, category, order, isActive, imageUrl) {
            const form = document.getElementById('editForm');
            form.setAttribute('action', `/cms/galleries/${id}`);

            document.getElementById('edit_title').value = title;
            document.getElementById('edit_caption').value = caption;
            document.getElementById('edit_category').value = category;
            document.getElementById('edit_order').value = order;
            document.getElementById('edit_img_preview').setAttribute('src', imageUrl);

            const activeCheckbox = document.getElementById('edit_is_active');
            if (isActive == '1' || isActive == true || isActive == 'true') {
                activeCheckbox.checked = true;
            } else {
                activeCheckbox.checked = false;
            }

            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('edit_image_path').value = '';
            document.getElementById('edit-file-label').innerText = 'Ganti Foto Baru';
        }
    </script>
</x-app-layout>
