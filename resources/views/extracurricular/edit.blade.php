<x-app-layout>
    <x-slot name="title">Edit Ekstrakurikuler - {{ $extracurricular->name }}</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Edit Ekstrakurikuler: {{ $extracurricular->name }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Sesuaikan detail agenda latihan, kuota slot, pembina, atau profil ekskul.
                </p>
            </div>
            <a href="{{ route('ekskul.manage') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
            <form action="{{ route('ekskul.update', $extracurricular->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Ekstrakurikuler</label>
                        <input type="text" name="name" value="{{ old('name', $extracurricular->name) }}" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Guru Pembina (Advisor)</label>
                        <select name="advisor_id" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                            @foreach($advisors as $adv)
                                <option value="{{ $adv->id }}" {{ $extracurricular->advisor_id == $adv->id ? 'selected' : '' }}>{{ $adv->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Hari Latihan</label>
                        <select name="schedule_day" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                                <option value="{{ $day }}" {{ $extracurricular->schedule_day == $day ? 'selected' : '' }}>{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Jam Latihan</label>
                        <input type="text" name="schedule_time" value="{{ old('schedule_time', $extracurricular->schedule_time) }}" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Lokasi Latihan</label>
                        <input type="text" name="location" value="{{ old('location', $extracurricular->location) }}" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kuota Maksimal Anggota</label>
                        <input type="number" name="max_members" value="{{ old('max_members', $extracurricular->max_members) }}" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Foto / Cover Header</label>
                        <input type="file" name="cover_image" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Deskripsi Kegiatan</label>
                    <textarea name="description" rows="4" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 text-xs text-gray-800 dark:text-white focus:outline-none">{{ old('description', $extracurricular->description) }}</textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-150 dark:border-gray-700">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-8 text-xs font-bold transition-all shadow-md">
                         Perbarui Ekskul
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
