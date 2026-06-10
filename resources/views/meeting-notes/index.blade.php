<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-800 leading-tight">
                    {{ __('Memo & Notulensi Rapat Digital') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-1">SMA ISLAM SULTAN AGUNG 1 SEMARANG</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('meeting-notes.tracker') }}" class="px-4 py-2 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-xl font-bold text-xs hover:bg-indigo-600 hover:text-white transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                    Action Item Tracker
                </a>
                <a href="{{ route('meeting-notes.create') }}" class="px-4 py-2 bg-primary-600 text-white rounded-xl font-bold text-xs hover:bg-primary-700 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Notulensi Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Filter Panel -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                <form action="{{ route('meeting-notes.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Pencarian</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, lokasi..." class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Jenis Rapat</label>
                        <select name="type" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Semua Jenis</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Bulan</label>
                            <select name="month" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                <option value="">Semua</option>
                                @foreach($monthsList as $num => $name)
                                    <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Tahun</label>
                            <select name="year" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                <option value="">Semua</option>
                                @foreach($yearsList as $yr)
                                    <option value="{{ $yr }}" {{ request('year', now()->year) == $yr ? 'selected' : '' }}>
                                        {{ $yr }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 py-2 bg-gray-950 text-white rounded-xl font-bold text-xs hover:bg-gray-800 transition-all">
                            Filter Data
                        </button>
                        @if(request()->anyFilled(['search', 'type', 'month']))
                            <a href="{{ route('meeting-notes.index') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold text-xs transition-all flex items-center justify-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Notes List -->
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="pb-4 pl-4">JUDUL & AGENDA</th>
                                <th class="pb-4">JENIS RAPAT</th>
                                <th class="pb-4">TANGGAL & WAKTU</th>
                                <th class="pb-4">LOKASI</th>
                                <th class="pb-4">STATUS</th>
                                <th class="pb-4">ACTION ITEMS</th>
                                <th class="pb-4 text-right pr-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($meetingNotes as $note)
                                @php
                                    $typeColors = [
                                        'rapat_guru' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                        'rapat_osis' => 'bg-purple-50 text-purple-700 border-purple-100',
                                        'rapat_wali_murid' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        'rapat_yayasan' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'rapat_panitia' => 'bg-rose-50 text-rose-700 border-rose-100',
                                        'lainnya' => 'bg-gray-50 text-gray-700 border-gray-100',
                                    ];
                                    $colorClass = $typeColors[$note->type] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                                    
                                    // Count action items stats
                                    $actionCount = count($note->action_items ?? []);
                                    $completedCount = collect($note->action_items ?? [])->where('is_completed', true)->count();
                                @endphp
                                <tr class="text-xs hover:bg-gray-50/50 transition-all">
                                    <td class="py-5 pl-4 max-w-xs">
                                        <div class="font-black text-gray-900 uppercase leading-snug">
                                            <a href="{{ route('meeting-notes.show', $note->id) }}" class="hover:text-primary-600 transition-colors">
                                                {{ $note->title }}
                                            </a>
                                        </div>
                                        <div class="text-[9px] font-bold text-gray-400 mt-1 uppercase tracking-wider">
                                            Dibuat oleh: {{ $note->creator->name ?? 'Staf Sekolah' }}
                                        </div>
                                    </td>
                                    <td class="py-5">
                                        <span class="px-2.5 py-1 rounded-lg text-[9px] font-black border uppercase tracking-wider {{ $colorClass }}">
                                            {{ str_replace('_', ' ', $note->type) }}
                                        </span>
                                    </td>
                                    <td class="py-5 font-bold text-gray-700">
                                        <div>{{ $note->meeting_date->translatedFormat('d M Y') }}</div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">{{ date('H:i', strtotime($note->start_time)) }} WIB</div>
                                    </td>
                                    <td class="py-5 text-gray-500 font-bold">
                                        {{ $note->location ?? '-' }}
                                    </td>
                                    <td class="py-5">
                                        @if($note->status === 'finalized')
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                                Final
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 bg-yellow-50 text-yellow-600 border border-yellow-100 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                                Draf
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-5 font-bold">
                                        @if($actionCount > 0)
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-gray-900">{{ $completedCount }}/{{ $actionCount }}</span>
                                                <span class="text-[9px] font-bold uppercase {{ $completedCount == $actionCount ? 'text-emerald-500' : 'text-amber-500' }}">
                                                    ({{ $completedCount == $actionCount ? 'Selesai' : 'Pending' }})
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-gray-300 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="py-5 text-right pr-4 space-x-1.5">
                                        <div class="inline-flex rounded-xl shadow-xs">
                                            <a href="{{ route('meeting-notes.show', $note->id) }}" class="px-2.5 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-l-xl font-bold text-[10px] uppercase transition-all border border-gray-200">
                                                Detail
                                            </a>
                                            @if($note->status === 'draft' && (auth()->id() == $note->created_by || in_array(auth()->user()->role, ['admin', 'superadmin'])))
                                                <a href="{{ route('meeting-notes.edit', $note->id) }}" class="px-2.5 py-1.5 bg-white hover:bg-yellow-50 hover:text-yellow-600 text-gray-600 font-bold text-[10px] uppercase transition-all border-y border-r border-gray-200">
                                                    Edit
                                                </a>
                                            @endif
                                            <a href="{{ route('meeting-notes.pdf', $note->id) }}" class="px-2.5 py-1.5 bg-white hover:bg-rose-50 hover:text-rose-600 text-gray-600 rounded-r-xl font-bold text-[10px] uppercase transition-all border-y border-r border-gray-200">
                                                PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-400 italic font-bold">
                                        Belum ada notulensi rapat yang tersimpan untuk filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $meetingNotes->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
