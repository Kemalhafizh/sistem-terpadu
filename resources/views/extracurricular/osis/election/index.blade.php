<x-app-layout>
    <x-slot name="title">Pemilihan Ketua OSIS</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    🗳️ Pemilihan Ketua OSIS (E-Voting)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Kelola agenda pemilu ketua OSIS aktif.
                </p>
            </div>
            <a href="{{ route('osis.dashboard') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅️ Kembali
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Buat Agenda Pemilu Baru -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl self-start">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-4">🗳️ Buka Pemilu Baru</h3>
            
            <form action="{{ route('osis.elections.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Agenda Pemilihan</label>
                    <input type="text" name="title" required placeholder="e.g. Pemilihan Ketua OSIS 2026/2027..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Mulai Voting</label>
                        <input type="date" name="voting_start" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tanggal Akhir Voting</label>
                        <input type="date" name="voting_end" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-xs text-gray-800 dark:text-white">
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                    🚀 Publikasikan Pemilu
                </button>
            </form>
        </div>

        <!-- Daftar Pemilu OSIS -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl lg:col-span-2 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm">🗳️ Agenda Pemilu Ketua OSIS</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-55 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Agenda</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Masa Aktif</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @forelse($elections as $el)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-all">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800 dark:text-white">{{ $el->title }}</div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Tahun Ajaran: {{ $el->academicYear->year ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    {{ $el->voting_start->format('d M Y') }} s/d {{ $el->voting_end->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-center text-xs">
                                    <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 font-extrabold text-[9px] rounded-lg">
                                        {{ strtoupper($el->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-1.5 text-xs">
                                    <a href="{{ route('osis.elections.candidates', $el->id) }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-1.5 px-3.5 text-xs font-bold transition-all">
                                        👥 Calon
                                    </a>
                                    <a href="{{ route('osis.elections.voting', $el->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-1.5 px-3.5 text-xs font-bold transition-all shadow-md">
                                        🗳️ Bilik Suara
                                    </a>
                                    <a href="{{ route('osis.elections.results', $el->id) }}" class="bg-teal-600 hover:bg-teal-700 text-white rounded-xl py-1.5 px-3.5 text-xs font-bold transition-all shadow-md">
                                        📈 Hasil
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-12 text-gray-400 italic">
                                    Belum ada agenda pemilihan ketua OSIS dibentuk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
