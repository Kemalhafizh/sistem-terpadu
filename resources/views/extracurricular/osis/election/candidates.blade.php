<x-app-layout>
    <x-slot name="title">Daftar Calon Ketua OSIS</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Kandidat Calon Ketua OSIS
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Agenda: {{ $election->title }}
                </p>
            </div>
            <a href="{{ route('osis.elections') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Kembali
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Daftarkan Paslon Kandidat -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl self-start">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-4"> Daftarkan Paslon Kandidat</h3>
            
            <form action="{{ route('osis.elections.candidates.store', $election->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih Siswa Calon</label>
                    <select name="student_id" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                        @foreach($students as $st)
                            <option value="{{ $st->id }}">{{ $st->name }} (Class: {{ $st->classroom->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nomor Urut</label>
                        <input type="number" name="candidate_number" min="1" required placeholder="e.g. 1" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Foto Paslon</label>
                        <input type="file" name="photo" class="w-full text-[10px] text-gray-450 file:mr-2 file:py-1 file:px-3 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-750">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Visi Pasangan Calon</label>
                    <textarea name="vision" rows="2" placeholder="Visi paslon..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl p-3 text-xs text-gray-800 dark:text-white focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Misi Pasangan Calon</label>
                    <textarea name="mission" rows="3" placeholder="Misi paslon..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl p-3 text-xs text-gray-800 dark:text-white focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                     Simpan Kandidat
                </button>
            </form>
        </div>

        <!-- Daftar Kandidat -->
        <div class="space-y-6 lg:col-span-2">
            @forelse($election->candidates as $cand)
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex gap-6 flex-wrap md:flex-nowrap">
                    @if($cand->photo_path)
                        <img src="{{ asset('storage/' . $cand->photo_path) }}" class="w-24 h-24 rounded-2xl object-cover border border-gray-200 dark:border-gray-700 shadow-md">
                    @else
                        <div class="w-24 h-24 bg-gray-100 dark:bg-gray-900/50 rounded-2xl flex items-center justify-center text-3xl font-black text-gray-400 border border-gray-200 dark:border-gray-700">
                            
                        </div>
                    @endif

                    <div class="space-y-3 flex-1 text-xs">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400 font-extrabold text-[9px] rounded-lg">
                                    No Urut: {{ $cand->candidate_number }}
                                </span>
                                <h3 class="font-extrabold text-gray-850 dark:text-white text-base mt-1.5">{{ $cand->student->name }}</h3>
                            </div>
                        </div>

                        <div>
                            <span class="block text-[9px] text-gray-400 font-bold uppercase">Visi</span>
                            <p class="text-gray-600 dark:text-gray-300 italic mt-0.5">{{ $cand->vision ?? '-' }}</p>
                        </div>

                        <div>
                            <span class="block text-[9px] text-gray-400 font-bold uppercase">Misi</span>
                            <p class="text-gray-600 dark:text-gray-300 mt-0.5">{{ $cand->mission ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400 italic text-center py-12">Belum ada pasangan calon ketua OSIS terdaftar.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
