<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Edit Materi: ') }} {{ $libraryBook->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <form action="{{ route('library.update', $libraryBook) }}" method="POST" enctype="multipart/form-data" class="p-10 space-y-8">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Judul</label>
                            <input type="text" name="title" value="{{ old('title', $libraryBook->title) }}" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Penulis</label>
                            <input type="text" name="author" value="{{ old('author', $libraryBook->author) }}" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Kategori</label>
                            <select name="category_id" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->id }}" {{ $libraryBook->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Mata Pelajaran Terkait</label>
                            <select name="subject_id" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="">Umum / Tidak Terkait</option>
                                @foreach($subjects ?? [] as $subj)
                                    <option value="{{ $subj->id }}" {{ $libraryBook->subject_id == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Tipe</label>
                            <select name="type" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="digital" {{ $libraryBook->type == 'digital' ? 'selected' : '' }}>Digital (e-Book / PDF)</option>
                                <option value="physical" {{ $libraryBook->type == 'physical' ? 'selected' : '' }}>Fisik (Buku Cetak)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Cover Baru (Opsional)</label>
                            @if($libraryBook->cover_path)
                                <img src="{{ Storage::url($libraryBook->cover_path) }}" alt="" class="w-20 h-28 object-cover rounded-xl mb-2 border">
                            @endif
                            <input type="file" name="cover" accept="image/*" class="w-full rounded-2xl border-gray-100 p-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">File Baru (Opsional)</label>
                            @if($libraryBook->file_path)
                                <p class="text-xs text-gray-400 mb-2">File saat ini: {{ basename($libraryBook->file_path) }}</p>
                            @endif
                            <input type="file" name="file" accept=".pdf,.epub" class="w-full rounded-2xl border-gray-100 p-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">URL Video</label>
                            <input type="url" name="video_url" value="{{ old('video_url', $libraryBook->video_url) }}" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4" placeholder="https://youtube.com/...">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Deskripsi</label>
                            <textarea name="description" rows="4" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">{{ old('description', $libraryBook->description) }}</textarea>
                        </div>
                    </div>

                    <div class="pt-10 flex items-center justify-end space-x-4 border-t border-gray-50">
                        <a href="{{ route('library.manage') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-all">Batal</a>
                        <button type="submit" class="px-12 py-4 bg-primary-600 text-white rounded-[1.5rem] font-black text-sm shadow-2xl shadow-primary-200 hover:bg-primary-700 hover:-translate-y-1 transition-all">
                            SIMPAN PERUBAHAN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
