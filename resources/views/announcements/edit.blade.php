<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Edit Pengumuman') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <form action="{{ route('announcements.update', $announcement) }}" method="POST" enctype="multipart/form-data" class="p-10 space-y-8">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Judul Pengumuman</label>
                        <input type="text" name="title" value="{{ old('title', $announcement->title) }}" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                    </div>

                    <div>
                        <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Konten</label>
                        <textarea name="content" rows="6" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">{{ old('content', $announcement->content) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Gambar (Opsional)</label>
                            @if($announcement->image_path)
                                <div class="mb-3">
                                    <img src="{{ Storage::url($announcement->image_path) }}" alt="" class="w-32 h-20 object-cover rounded-xl border border-gray-200">
                                    <span class="text-xs text-gray-400">Gambar saat ini</span>
                                </div>
                            @endif
                            <input type="file" name="image" accept="image/*" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Prioritas</label>
                            <select name="priority" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="low" {{ $announcement->priority == 'low' ? 'selected' : '' }}>Rendah</option>
                                <option value="normal" {{ $announcement->priority == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="urgent" {{ $announcement->priority == 'urgent' ? 'selected' : '' }}>Mendesak</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Target Role</label>
                            <select name="target_role" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="">Semua</option>
                                <option value="student" {{ $announcement->target_role == 'student' ? 'selected' : '' }}>Siswa</option>
                                <option value="parent" {{ $announcement->target_role == 'parent' ? 'selected' : '' }}>Wali Murid</option>
                                <option value="guru" {{ $announcement->target_role == 'guru' ? 'selected' : '' }}>Guru</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Target Kelas</label>
                            <select name="target_classroom_id" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="">Semua Kelas</option>
                                @foreach($classrooms ?? [] as $classroom)
                                    <option value="{{ $classroom->id }}" {{ $announcement->target_classroom_id == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Kedaluwarsa</label>
                            <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $announcement->expires_at ? \Carbon\Carbon::parse($announcement->expires_at)->format('Y-m-d\TH:i') : '') }}" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                        </div>
                    </div>

                    <div class="pt-10 flex items-center justify-end space-x-4 border-t border-gray-50">
                        <a href="{{ route('announcements.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-all">Batal</a>
                        <button type="submit" class="px-12 py-4 bg-primary-600 text-white rounded-[1.5rem] font-black text-sm shadow-2xl shadow-primary-200 hover:bg-primary-700 hover:-translate-y-1 transition-all">
                            SIMPAN PERUBAHAN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
