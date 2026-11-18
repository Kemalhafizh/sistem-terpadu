<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            title="Memo & Notulensi Rapat Digital" 
            subtitle="Catatan notulensi resmi, daftar hadir peserta rapat, dan action item tindak lanjut hasil rapat sekolah."
        >
            <x-slot name="actions">
                <a href="{{ route('meeting-notes.tracker') }}" class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-700 text-indigo-700 hover:text-white border border-indigo-200 dark:border-indigo-900/40 rounded-lg font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                    Action Item Tracker
                </a>
                <a href="{{ route('meeting-notes.create') }}" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Notulensi
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Panel -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-xs border border-gray-200 dark:border-slate-700/80">
                <form action="{{ route('meeting-notes.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 dark:text-slate-400 uppercase tracking-widest mb-1.5">Pencarian</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, lokasi..." class="w-full text-xs font-bold rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-primary-500 focus:ring-primary-500 py-2 px-3">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 dark:text-slate-400 uppercase tracking-widest mb-1.5">Jenis Rapat</label>
                        <select name="type" class="w-full text-xs font-bold rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-primary-500 focus:ring-primary-500 py-2 px-3">
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
                            <label class="block text-[10px] font-black text-gray-400 dark:text-slate-400 uppercase tracking-widest mb-1.5">Bulan</label>
                            <select name="month" class="w-full text-xs font-bold rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-primary-500 focus:ring-primary-500 py-2 px-3">
                                <option value="">Semua</option>
                                @foreach($monthsList as $num => $name)
                                    <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 dark:text-slate-400 uppercase tracking-widest mb-1.5">Tahun</label>
                            <select name="year" class="w-full text-xs font-bold rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-primary-500 focus:ring-primary-500 py-2 px-3">
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
                        <button type="submit" class="flex-1 py-2 bg-gray-800 dark:bg-slate-700 hover:bg-gray-700 text-white rounded-lg font-bold text-xs uppercase tracking-wider transition-all shadow-xs py-2 px-4 border border-transparent">
                            Filter
                        </button>
                        @if(request()->anyFilled(['search', 'type', 'month']))
                            <a href="{{ route('meeting-notes.index') }}" class="py-2 px-3 bg-gray-100 dark:bg-slate-900 hover:bg-gray-200 text-gray-600 dark:text-slate-400 rounded-lg font-bold text-xs transition-all flex items-center justify-center border border-gray-200 dark:border-slate-700">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Notes List -->
            <x-data-table 
                :headers="[
                    ['label' => 'Judul & Agenda'],
                    ['label' => 'Jenis Rapat'],
                    ['label' => 'Tanggal & Waktu'],
                    ['label' => 'Lokasi'],
                    ['label' => 'Status'],
                    ['label' => 'Action Items'],
                    ['label' => 'Aksi', 'align' => 'right']
                ]"
                :items="$meetingNotes"
                emptyIcon="document"
                emptyMessage="Belum ada notulensi"
                emptySubMessage="Tulis notulensi rapat sekolah pertama Anda untuk membagikan keputusan penting."
                emptyActionUrl="{{ route('meeting-notes.create') }}"
                emptyActionText="Tulis Notulensi"
            >
                @foreach ($meetingNotes as $note)
                    @php
                        $typeColors = [
                            'rapat_guru' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-900/30',
                            'rapat_osis' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-900/30',
                            'rapat_wali_murid' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/30',
                            'rapat_yayasan' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900/30',
                            'rapat_panitia' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900/30',
                            'lainnya' => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800/80 dark:text-slate-300 dark:border-slate-700/60',
                        ];
                        $colorClass = $typeColors[$note->type] ?? 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800/80 dark:text-slate-300 dark:border-slate-700/60';
                        
                        // Count action items stats
                        $actionCount = count($note->action_items ?? []);
                        $completedCount = collect($note->action_items ?? [])->where('is_completed', true)->count();
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="py-4 px-6 max-w-xs">
                            <div class="text-sm font-bold text-gray-900 dark:text-white leading-tight">
                                <a href="{{ route('meeting-notes.show', $note->id) }}" class="hover:text-primary-600 transition-colors">
                                    {{ $note->title }}
                                </a>
                            </div>
                            <div class="text-[10px] font-medium text-gray-400 dark:text-slate-500 mt-1">
                                Penulis: {{ $note->creator->name ?? 'Staf Sekolah' }}
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-md text-[9px] font-extrabold border uppercase tracking-wider {{ $colorClass }}">
                                {{ str_replace('_', ' ', $note->type) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-sm font-semibold text-gray-700 dark:text-slate-300">
                            <div>{{ $note->meeting_date->translatedFormat('d M Y') }}</div>
                            <div class="text-[10px] text-gray-500 dark:text-slate-500 mt-0.5">{{ date('H:i', strtotime($note->start_time)) }} WIB</div>
                        </td>
                        <td class="py-4 px-6 text-sm font-medium text-gray-500 dark:text-slate-400">
                            {{ $note->location ?? '-' }}
                        </td>
                        <td class="py-4 px-6">
                            @if($note->status === 'finalized')
                                <x-status-badge status="aktif">Final</x-status-badge>
                            @else
                                <x-status-badge status="pending">Draf</x-status-badge>
                            @endif
                        </td>
                        <td class="py-4 px-6 font-bold text-sm text-gray-800 dark:text-slate-300">
                            @if($actionCount > 0)
                                <div class="flex items-center gap-1.5">
                                    <span>{{ $completedCount }}/{{ $actionCount }}</span>
                                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-md border {{ $completedCount == $actionCount ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900/30' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/30' }}">
                                        {{ $completedCount == $actionCount ? 'Selesai' : 'Pending' }}
                                    </span>
                                </div>
                            @else
                                <span class="text-gray-300 dark:text-slate-600">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap">
                            <div class="inline-flex rounded-lg shadow-xs">
                                <a href="{{ route('meeting-notes.show', $note->id) }}" class="px-3 py-1.5 bg-gray-50 dark:bg-slate-700 hover:bg-gray-100 text-gray-800 dark:text-slate-300 rounded-l-lg font-bold text-[10px] uppercase border border-gray-200 dark:border-slate-600 transition-colors">
                                    Detail
                                </a>
                                @if($note->status === 'draft' && (auth()->id() == $note->created_by || in_array(auth()->user()->role, ['admin', 'superadmin'])))
                                    <a href="{{ route('meeting-notes.edit', $note->id) }}" class="px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-yellow-50 hover:text-yellow-800 dark:hover:bg-slate-700 text-gray-800 dark:text-slate-300 font-bold text-[10px] uppercase border-y border-r border-gray-200 dark:border-slate-600 transition-colors">
                                        Edit
                                    </a>
                                @endif
                                <a href="{{ route('meeting-notes.pdf', $note->id) }}" class="px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-slate-700 text-gray-800 dark:text-slate-300 rounded-r-lg font-bold text-[10px] uppercase border-y border-r border-gray-200 dark:border-slate-600 transition-colors">
                                    PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-app-layout>
