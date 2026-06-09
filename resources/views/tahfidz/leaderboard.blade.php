<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Peringkat Tahfidz Siswa (Leaderboard)') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Siswa Penghafal Terbaik Semester Ini
                </p>
            </div>
            <a href="{{ route('tahfidz.dashboard') }}" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                ← DASHBOARD
            </a>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Classroom Filter Card --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 font-bold">Filter Berdasarkan Kelas:</h3>
                </div>
                <form action="{{ route('tahfidz.leaderboard') }}" method="GET" class="flex items-center gap-2">
                    <select name="classroom_id" onchange="this.form.submit()" class="bg-gray-50 border-none rounded-xl px-4 py-2 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                        <option value="">Semua Kelas (Seluruh Sekolah)</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}" {{ $classroomId && (int)$classroomId === $c->id ? 'selected' : '' }}>
                                Kelas {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Top 3 Podium Cards --}}
            @if($leaderboard->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end pt-6">
                    {{-- 2nd Place --}}
                    @if($leaderboard->count() >= 2)
                        @php $st2 = $leaderboard->values()[1]; @endphp
                        <div class="bg-white rounded-[2rem] p-6 text-center border border-gray-100 shadow-sm relative overflow-hidden order-2 md:order-1">
                            <div class="absolute top-0 left-0 right-0 h-2 bg-slate-300"></div>
                            <span class="text-3xl block mt-2">🥈</span>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Peringkat 2</span>
                            <h4 class="font-black text-gray-800 text-sm mt-2">{{ $st2->name }}</h4>
                            <p class="text-[10px] font-bold text-gray-400 uppercase mt-0.5">Kelas {{ $st2->classroom?->name }}</p>
                            
                            <div class="mt-4 bg-slate-50 py-3 rounded-2xl">
                                <span class="block text-xl font-black text-slate-700">{{ $st2->total_pages_memorized ?? 0 }}</span>
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Halaman Memorized</span>
                            </div>
                        </div>
                    @endif

                    {{-- 1st Place --}}
                    @if($leaderboard->count() >= 1)
                        @php $st1 = $leaderboard->values()[0]; @endphp
                        <div class="bg-white rounded-[2.5rem] p-8 text-center border-2 border-amber-300 shadow-xl relative overflow-hidden order-1 md:order-2 md:scale-105">
                            <div class="absolute top-0 left-0 right-0 h-3 bg-amber-400"></div>
                            <span class="text-5xl block mt-2">👑</span>
                            <span class="text-[10px] font-black text-amber-500 uppercase tracking-[0.2em]">Peringkat 1</span>
                            <h4 class="font-black text-gray-900 text-lg mt-2">{{ $st1->name }}</h4>
                            <p class="text-[10px] font-bold text-gray-400 uppercase mt-0.5">Kelas {{ $st1->classroom?->name }}</p>
                            
                            <div class="mt-4 bg-amber-50 py-4 rounded-2xl border border-amber-100">
                                <span class="block text-2xl font-black text-amber-800">{{ $st1->total_pages_memorized ?? 0 }}</span>
                                <span class="text-[9px] font-black text-amber-600 uppercase tracking-wider">Halaman Memorized</span>
                            </div>
                        </div>
                    @endif

                    {{-- 3rd Place --}}
                    @if($leaderboard->count() >= 3)
                        @php $st3 = $leaderboard->values()[2]; @endphp
                        <div class="bg-white rounded-[2rem] p-6 text-center border border-gray-100 shadow-sm relative overflow-hidden order-3">
                            <div class="absolute top-0 left-0 right-0 h-2 bg-amber-600"></div>
                            <span class="text-3xl block mt-2">🥉</span>
                            <span class="text-[9px] font-black text-amber-700 uppercase tracking-widest">Peringkat 3</span>
                            <h4 class="font-black text-gray-800 text-sm mt-2">{{ $st3->name }}</h4>
                            <p class="text-[10px] font-bold text-gray-400 uppercase mt-0.5">Kelas {{ $st3->classroom?->name }}</p>
                            
                            <div class="mt-4 bg-amber-50/50 py-3 rounded-2xl">
                                <span class="block text-xl font-black text-amber-900">{{ $st3->total_pages_memorized ?? 0 }}</span>
                                <span class="text-[9px] font-black text-amber-600 uppercase tracking-wider">Halaman Memorized</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Leaderboard Table for rest of ranks --}}
                <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100">
                    <h3 class="text-xs font-black uppercase tracking-[0.3em] text-gray-400 mb-6">Peringkat Lengkap Hafalan</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                    <th class="pb-3 text-center w-16">Peringkat</th>
                                    <th class="pb-3">Siswa</th>
                                    <th class="pb-3">Kelas</th>
                                    <th class="pb-3 text-center">Setoran Semester Ini</th>
                                    <th class="pb-3 text-center">Total Halaman Ziyadah</th>
                                    <th class="pb-3 text-center">Capaian Target</th>
                                    <th class="pb-3 text-center">Tingkat</th>
                                    <th class="pb-3 text-right">XP Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs font-bold text-gray-700">
                                @foreach($leaderboard as $idx => $st)
                                    @php
                                        $rank = $idx + 1;
                                    @endphp
                                    <tr class="{{ $rank <= 3 ? 'bg-primary-50/10' : '' }}">
                                        <td class="py-4 text-center">
                                            @if($rank == 1)
                                                <span class="text-lg">🥇</span>
                                            @elseif($rank == 2)
                                                <span class="text-lg">🥈</span>
                                            @elseif($rank == 3)
                                                <span class="text-lg">🥉</span>
                                            @else
                                                <span class="px-2 py-1 rounded-lg bg-gray-100 text-gray-600 text-[10px] font-black">#{{ $rank }}</span>
                                            @endif
                                        </td>
                                        <td class="py-4">
                                            <p class="font-black text-gray-800">{{ $st->name }}</p>
                                            <p class="text-[9px] text-gray-400 uppercase">{{ $st->nis }}</p>
                                        </td>
                                        <td class="py-4 text-gray-600 font-black">Kelas {{ $st->classroom?->name }}</td>
                                        <td class="py-4 text-center">{{ $st->tahfidz_logs_count }} Kali</td>
                                        <td class="py-4 text-center text-primary-600 font-black text-sm">{{ $st->total_pages_memorized ?? 0 }} Halaman</td>
                                        <td class="py-4 text-center">
                                            @if($st->tahfidz_target)
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-600">
                                                    {{ $st->tahfidz_target->achieved_juz }} / {{ $st->tahfidz_target->target_juz }} Juz
                                                </span>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                        <td class="py-4 text-center">
                                            <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700">Lvl {{ $st->level }}</span>
                                        </td>
                                        <td class="py-4 text-right text-gray-800 font-black">{{ $st->total_xp }} XP</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-gray-100">
                    <span class="text-4xl">🏆</span>
                    <h3 class="font-black text-lg text-gray-800 mt-4 uppercase font-bold">Belum Ada Peringkat</h3>
                    <p class="text-xs text-gray-400 mt-2">Belum ada data setoran ziyadah tercatat di sistem semester ini.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
