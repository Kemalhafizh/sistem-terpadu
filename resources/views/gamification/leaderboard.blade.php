<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Papan Peringkat Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Filter & Judul Utama -->
            <div class="bg-gradient-to-r from-indigo-900/90 to-slate-900/90 backdrop-blur-md border border-indigo-500/20 text-white rounded-[2rem] p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 hover:shadow-indigo-500/5 transition-all duration-300">
                <div class="space-y-2 flex-grow">
                    <h3 class="text-2xl font-black tracking-tight text-indigo-300">🏆 Liga Keunggulan Sultan Agung</h3>
                    <p class="text-xs text-gray-300 font-medium leading-relaxed max-w-xl">
                        Apresiasi siswa-siswi teraktif yang konsisten mengumpulkan XP melalui keaktifan belajar, kehadiran presensi masuk kelas, setoran hafalan Tahfidz Al-Quran, dan jurnal mutaba'ah ibadah harian.
                    </p>
                </div>
                <!-- Form Filter Kelas -->
                <form method="GET" action="{{ route('gamification.leaderboard') }}" class="w-full md:w-auto flex-shrink-0 flex items-center gap-3">
                    <label for="classroom_id" class="text-xs font-black uppercase tracking-widest text-indigo-200 whitespace-nowrap">Filter Kelas:</label>
                    <select name="classroom_id" id="classroom_id" onchange="this.form.submit()" class="w-full md:w-48 bg-slate-800/80 border border-indigo-500/30 text-white rounded-xl text-xs font-bold py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Kelas</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>
                                {{ $cls->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- Area Podium 3 Besar (🥇🥈🥉) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-end pt-8 max-w-5xl mx-auto">
                
                <!-- Peringkat 2 (Kiri) -->
                @if(isset($leaderboard[1]))
                    @php $p2 = $leaderboard[1]; @endphp
                    <div class="order-2 md:order-1 bg-white/70 backdrop-blur-lg border border-gray-100 rounded-[2rem] p-6 shadow-lg hover:shadow-indigo-500/5 transition-all hover:scale-105 duration-300 flex flex-col items-center text-center relative overflow-hidden h-[300px] justify-end">
                        <div class="absolute -top-4 -left-4 w-20 h-20 bg-slate-100 rounded-full opacity-30"></div>
                        <div class="w-20 h-20 bg-slate-100 rounded-2xl flex items-center justify-center text-3xl font-black text-slate-500 border-4 border-slate-300 shadow-xl relative z-10 mb-4 animate-pulse">
                            🥈
                        </div>
                        <h4 class="text-lg font-black text-gray-900 leading-tight">{{ $p2->name }}</h4>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">{{ $p2->classroom?->name ?? 'Kelas -' }}</p>
                        <div class="mt-4 px-4 py-2 bg-slate-100 text-slate-700 rounded-full text-xs font-black uppercase tracking-wider">
                            Level {{ $p2->level }} • {{ $p2->total_xp }} XP
                        </div>
                        @if($p2->current_streak > 0)
                            <div class="mt-2 text-[10px] font-black text-orange-500 flex items-center gap-1">
                                🔥 Streak {{ $p2->current_streak }} Hari
                            </div>
                        @endif
                    </div>
                @else
                    <div class="order-2 md:order-1 bg-gray-50/50 border border-dashed border-gray-200 rounded-[2rem] p-6 h-[250px] flex items-center justify-center text-center text-xs text-gray-400 italic">
                        Belum terisi
                    </div>
                @endif

                <!-- Peringkat 1 (Tengah - Tertinggi) -->
                @if(isset($leaderboard[0]))
                    @php $p1 = $leaderboard[0]; @endphp
                    <div class="order-1 md:order-2 bg-gradient-to-b from-amber-50/80 to-yellow-50/80 backdrop-blur-lg border-2 border-amber-400 rounded-[2.5rem] p-8 shadow-2xl hover:shadow-amber-500/10 transition-all hover:scale-108 duration-300 flex flex-col items-center text-center relative overflow-hidden h-[360px] justify-end">
                        <div class="absolute top-4 right-4 text-3xl animate-bounce">👑</div>
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-amber-200 rounded-full opacity-20"></div>
                        <div class="w-24 h-24 bg-amber-100 rounded-2xl flex items-center justify-center text-4xl font-black text-amber-600 border-4 border-amber-400 shadow-xl relative z-10 mb-4 animate-bounce">
                            🥇
                        </div>
                        <h4 class="text-xl font-black text-gray-900 leading-tight">{{ $p1->name }}</h4>
                        <p class="text-xs font-bold text-amber-700 uppercase tracking-widest mt-1">{{ $p1->classroom?->name ?? 'Kelas -' }}</p>
                        <div class="mt-4 px-6 py-2.5 bg-amber-500 text-white rounded-full text-sm font-black uppercase tracking-wider shadow-lg shadow-amber-500/25">
                            Level {{ $p1->level }} • {{ $p1->total_xp }} XP
                        </div>
                        @if($p1->current_streak > 0)
                            <div class="mt-2 text-xs font-black text-orange-600 flex items-center gap-1">
                                🔥 Streak {{ $p1->current_streak }} Hari
                            </div>
                        @endif
                    </div>
                @else
                    <div class="order-1 md:order-2 bg-gray-50/50 border border-dashed border-gray-200 rounded-[2.5rem] p-6 h-[300px] flex items-center justify-center text-center text-xs text-gray-400 italic">
                        Belum terisi
                    </div>
                @endif

                <!-- Peringkat 3 (Kanan) -->
                @if(isset($leaderboard[2]))
                    @php $p3 = $leaderboard[2]; @endphp
                    <div class="order-3 bg-white/70 backdrop-blur-lg border border-gray-100 rounded-[2rem] p-6 shadow-lg hover:shadow-indigo-500/5 transition-all hover:scale-105 duration-300 flex flex-col items-center text-center relative overflow-hidden h-[270px] justify-end">
                        <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-orange-100 rounded-full opacity-30"></div>
                        <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center text-2xl font-black text-orange-600 border-4 border-orange-300 shadow-xl relative z-10 mb-4">
                            🥉
                        </div>
                        <h4 class="text-base font-black text-gray-900 leading-tight">{{ $p3->name }}</h4>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">{{ $p3->classroom?->name ?? 'Kelas -' }}</p>
                        <div class="mt-4 px-4 py-2 bg-orange-50 text-orange-800 rounded-full text-xs font-black uppercase tracking-wider">
                            Level {{ $p3->level }} • {{ $p3->total_xp }} XP
                        </div>
                        @if($p3->current_streak > 0)
                            <div class="mt-2 text-[10px] font-black text-orange-500 flex items-center gap-1">
                                🔥 Streak {{ $p3->current_streak }} Hari
                            </div>
                        @endif
                    </div>
                @else
                    <div class="order-3 bg-gray-50/50 border border-dashed border-gray-200 rounded-[2rem] p-6 h-[220px] flex items-center justify-center text-center text-xs text-gray-400 italic">
                        Belum terisi
                    </div>
                @endif

            </div>

            <!-- Tabel Data Peringkat Selengkapnya (Peringkat 4 Ke Bawah) -->
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100">
                <h4 class="text-lg font-black text-gray-900 mb-6 flex items-center">
                    <span class="w-2.5 h-6 bg-indigo-600 rounded-full mr-3"></span>
                    Daftar Klasemen Selengkapnya
                </h4>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="text-left text-xs font-black uppercase tracking-wider text-gray-400">
                                <th class="pb-4">Posisi</th>
                                <th class="pb-4">Nama Siswa</th>
                                <th class="pb-4">Kelas</th>
                                <th class="pb-4">Level</th>
                                <th class="pb-4">Total XP</th>
                                <th class="pb-4">Streak Presensi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm font-bold text-gray-700">
                            @forelse($leaderboard as $index => $student)
                                @if($index < 3) @continue @endif <!-- Lewati podium -->
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="py-4">
                                        <span class="w-7 h-7 bg-gray-100 text-gray-600 rounded-lg flex items-center justify-center text-xs font-black">
                                            #{{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-gray-900">{{ $student->name }}</td>
                                    <td class="py-4 text-slate-500">{{ $student->classroom?->name ?? 'Kelas -' }}</td>
                                    <td class="py-4">
                                        <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded text-xs font-black">
                                            Lvl {{ $student->level }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-indigo-600 font-extrabold">{{ $student->total_xp }} XP</td>
                                    <td class="py-4">
                                        @if($student->current_streak > 0)
                                            <span class="inline-flex items-center text-orange-600 text-xs font-bold gap-1 bg-orange-50 px-2.5 py-1 rounded-full">
                                                🔥 {{ $student->current_streak }} Hari
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 font-medium">0 Hari</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-gray-400 italic">
                                        Belum ada data peringkat untuk filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
