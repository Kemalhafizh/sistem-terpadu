<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-800 leading-tight">
            {{ __('Upload Materi Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <form action="{{ route('library.store') }}" method="POST" enctype="multipart/form-data" class="p-10 space-y-8">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Judul Buku / Materi</label>
                            <input type="text" name="title" required class="w-full rounded-2xl border-gray-100 p-4 focus:border-primary-500 focus:ring-primary-500" placeholder="Contoh: Modul Fisika Kuantum XI">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Penulis / Sumber</label>
                            <input type="text" name="author" class="w-full rounded-2xl border-gray-100 p-4 focus:border-primary-500 focus:ring-primary-500" placeholder="Nama Guru atau Penerbit">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Tipe Materi</label>
                            <select name="type" required class="w-full rounded-2xl border-gray-100 p-4 focus:border-primary-500 focus:ring-primary-500">
                                <option value="digital">E-Book / PDF (Digital)</option>
                                <option value="physical">Buku Cetak (Fisik)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Kategori</label>
                            <select name="category_id" required class="w-full rounded-2xl border-gray-100 p-4 focus:border-primary-500 focus:ring-primary-500">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat?->id }}">{{ $cat?->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Mata Pelajaran (Opsional)</label>
                            <select name="subject_id" class="w-full rounded-2xl border-gray-100 p-4 focus:border-primary-500 focus:ring-primary-500">
                                <option value="">Umum / Tidak Spesifik</option>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub?->id }}">{{ $sub?->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Sampul Buku (JPG/PNG)</label>
                            <input type="file" name="cover" accept="image/*" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">File Materi (PDF/EPUB)</label>
                            <input type="file" name="file" accept=".pdf,.epub" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Video Pembelajaran (URL YouTube/Vimeo)</label>
                            <input type="url" name="video_url" class="w-full rounded-2xl border-gray-100 p-4 focus:border-primary-500 focus:ring-primary-500" placeholder="https://youtube.com/watch?v=...">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Sinopsis / Deskripsi</label>
                            <textarea name="description" rows="4" class="w-full rounded-2xl border-gray-100 p-4 focus:border-primary-500 focus:ring-primary-500" placeholder="Jelaskan isi singkat dari buku atau materi ini..."></textarea>
                        </div>
                    </div>

                    <div class="pt-10 flex items-center justify-end space-x-4 border-t border-gray-50">
                        <a href="{{ route('library.manage') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600">Batal</a>
                        <button type="submit" class="px-12 py-4 bg-gray-900 text-white rounded-[1.5rem] font-black text-sm shadow-2xl shadow-gray-200 hover:bg-black hover:-translate-y-1 transition-all">UPLOAD SEKARANG</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
