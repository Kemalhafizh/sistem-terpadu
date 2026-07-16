<x-app-layout>
    <x-slot name="title">Bilik Suara Pemilu OSIS</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    🗳️ Bilik Suara E-Voting OSIS
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Agenda: {{ $election->title }} | Jaga Kerahasiaan Pilihan Anda
                </p>
            </div>
            <a href="{{ route('osis.elections') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅️ Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        @if(session('success'))
            <div class="rounded-2xl p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 flex items-center text-emerald-800 dark:text-emerald-300 animate-fade-in">
                <span class="text-sm font-bold">✅ {{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 flex items-center text-red-800 dark:text-red-300 animate-fade-in">
                <span class="text-sm font-bold">❌ {{ session('error') }}</span>
            </div>
        @endif

        @if($hasVoted)
            <div class="bg-emerald-50/50 dark:bg-emerald-950/20 p-8 rounded-3xl border border-emerald-200/50 dark:border-emerald-900/50 text-center space-y-3">
                <div class="text-4xl">🎉</div>
                <h3 class="font-black text-gray-850 dark:text-white text-lg">Terima Kasih, Suara Anda Telah Tercatat!</h3>
                <p class="text-xs text-gray-500">Anda telah berpartisipasi dalam e-voting pemilihan Ketua OSIS periode ini. Partisipasi Anda sangat berharga bagi demokrasi sekolah.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($election->candidates as $cand)
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden flex flex-col justify-between">
                        <div>
                            @if($cand->photo_path)
                                <img src="{{ asset('storage/' . $cand->photo_path) }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gray-100 dark:bg-gray-900/50 flex items-center justify-center text-5xl font-black text-gray-400">
                                    👤
                                </div>
                            @endif

                            <div class="p-6 space-y-4">
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 bg-indigo-600 text-white font-black text-xs rounded-xl">
                                        No Urut: {{ $cand->candidate_number }}
                                    </span>
                                    <h3 class="font-extrabold text-gray-800 dark:text-white text-base">{{ $cand->student->name }}</h3>
                                </div>

                                <div class="space-y-2 text-xs">
                                    <div>
                                        <span class="block text-[8px] text-gray-400 font-bold uppercase">Visi</span>
                                        <p class="text-gray-600 dark:text-gray-300 italic mt-0.5">{{ $cand->vision ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="block text-[8px] text-gray-400 font-bold uppercase">Misi</span>
                                        <p class="text-gray-600 dark:text-gray-300 mt-0.5">{{ $cand->mission ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-t border-gray-100 dark:border-gray-700/50">
                            <form action="{{ route('osis.elections.vote', $election->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="candidate_id" value="{{ $cand->id }}">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                                    🗳️ Coblos Pilihan Ini
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic text-center py-12 col-span-2">Belum ada pasangan calon ketua OSIS terdaftar.</p>
                @endforelse
            </div>
        @endif
    </div>
</x-app-layout>
