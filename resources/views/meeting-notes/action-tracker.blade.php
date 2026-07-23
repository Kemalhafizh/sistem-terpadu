<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-800 leading-tight">
                    {{ __('Action Item Tracker') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-1">Pemantauan Tindak Lanjut Keputusan Rapat</p>
            </div>
            <a href="{{ route('meeting-notes.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold text-xs transition-all flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Daftar Notulensi
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Filter Panel -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                <form action="{{ route('meeting-notes.tracker') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Pencarian Tugas atau PIC</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tugas, PIC, nama rapat..." class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Status Penugasan</label>
                        <select name="status" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Belum Selesai)</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 py-2 bg-gray-950 text-white rounded-xl font-bold text-xs hover:bg-gray-800 transition-all">
                            Filter
                        </button>
                        @if(request()->anyFilled(['search', 'status']))
                            <a href="{{ route('meeting-notes.tracker') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold text-xs transition-all flex items-center justify-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Action Items Table -->
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100" 
                 x-data="{
                     toggleItem(meetingId, itemId, event) {
                         const btn = event.currentTarget;
                         btn.disabled = true;
                         
                         fetch(`/meeting-notes/action-items/${meetingId}/${itemId}/toggle`, {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                             }
                         })
                         .then(res => res.json())
                         .then(data => {
                             btn.disabled = false;
                             if(data.success) {
                                 // Update visually
                                 const statusSpan = document.getElementById(`status-badge-${meetingId}-${itemId}`);
                                 const taskText = document.getElementById(`task-text-${meetingId}-${itemId}`);
                                 
                                 if(data.is_completed) {
                                     btn.innerHTML = `<span class='w-3 h-3 rounded-full bg-emerald-500 inline-block border border-emerald-500'></span>`;
                                     statusSpan.className = 'px-2 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[9px] font-black uppercase tracking-wider';
                                     statusSpan.innerText = 'Selesai';
                                     taskText.className = 'font-bold text-gray-400 line-through';
                                 } else {
                                     btn.innerHTML = `<span class='w-3 h-3 rounded-full bg-white inline-block border border-gray-300'></span>`;
                                     statusSpan.className = 'px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg text-[9px] font-black uppercase tracking-wider';
                                     statusSpan.innerText = 'Pending';
                                     taskText.className = 'font-bold text-gray-800';
                                 }
                             } else {
                                 alert(data.message);
                             }
                         })
                         .catch(err => {
                             btn.disabled = false;
                             alert('Koneksi bermasalah.');
                         });
                     }
                 }">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="pb-4 pl-4 text-center w-12">TOGGLE</th>
                                <th class="pb-4">TUGAS / TINDAK LANJUT</th>
                                <th class="pb-4">PENANGGUNG JAWAB (PIC)</th>
                                <th class="pb-4">DEADLINE</th>
                                <th class="pb-4">RAPAT ASAL</th>
                                <th class="pb-4">STATUS</th>
                                <th class="pb-4 text-right pr-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($paginatedItems as $item)
                                @php
                                    $isOverdue = !$item->is_completed && $item->deadline->isPast();
                                @endphp
                                <tr class="text-xs hover:bg-gray-50/50 transition-all">
                                    <td class="py-5 text-center">
                                        <button @click="toggleItem({{ $item->meeting_id }}, {{ $item->id }}, $event)" type="button" class="focus:outline-none transition-transform hover:scale-110 active:scale-95 inline-flex items-center justify-center">
                                            @if($item->is_completed)
                                                <span class="w-3.5 h-3.5 rounded-md bg-emerald-500 border border-emerald-500 text-white flex items-center justify-center">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                </span>
                                            @else
                                                <span class="w-3.5 h-3.5 rounded-md bg-white border border-gray-300 hover:border-primary-500 transition-colors"></span>
                                            @endif
                                        </button>
                                    </td>
                                    <td class="py-5">
                                        <span id="task-text-{{ $item->meeting_id }}-{{ $item->id }}" class="{{ $item->is_completed ? 'font-bold text-gray-400 line-through' : 'font-bold text-gray-800' }}">
                                            {{ $item->task }}
                                        </span>
                                    </td>
                                    <td class="py-5 font-black text-gray-900 uppercase">
                                        {{ $item->pic }}
                                    </td>
                                    <td class="py-5 font-bold">
                                        <span class="{{ $isOverdue ? 'text-red-600 bg-red-50 px-2 py-0.5 rounded-lg border border-red-100' : 'text-gray-700' }}">
                                            {{ $item->deadline->translatedFormat('d M Y') }}
                                            @if($isOverdue)
                                                <span class="text-[9px] font-black uppercase tracking-wider block mt-0.5 text-red-500">TERLAMBAT</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="py-5 font-bold text-gray-400 max-w-xs">
                                        <a href="{{ route('meeting-notes.show', $item->meeting_id) }}" class="hover:text-primary-600 transition-colors uppercase block truncate">
                                            {{ $item->meeting_title }}
                                        </a>
                                        <span class="text-[9px] block mt-0.5">{{ $item->meeting_date->translatedFormat('d M Y') }}</span>
                                    </td>
                                    <td class="py-5">
                                        @if($item->is_completed)
                                            <span id="status-badge-{{ $item->meeting_id }}-{{ $item->id }}" class="px-2 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                                Selesai
                                            </span>
                                        @else
                                            <span id="status-badge-{{ $item->meeting_id }}-{{ $item->id }}" class="px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-5 text-right pr-4">
                                        <a href="{{ route('meeting-notes.show', $item->meeting_id) }}" class="px-2.5 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-xl font-bold text-[10px] uppercase transition-all border border-gray-200">
                                            Lihat Rapat
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-400 italic font-bold">
                                        Tidak ada action items yang cocok dengan kriteria filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $paginatedItems->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
