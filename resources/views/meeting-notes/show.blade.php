<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-black text-2xl text-gray-800 leading-tight">
                        {{ $meetingNote->title }}
                    </h2>
                    @if($meetingNote->status === 'finalized')
                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[9px] font-black uppercase tracking-wider">
                            Finalized
                        </span>
                    @else
                        <span class="px-2 py-0.5 bg-yellow-50 text-yellow-600 border border-yellow-100 rounded-lg text-[9px] font-black uppercase tracking-wider">
                            Draft
                        </span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-1">Detail Rapat & Hasil Notulensi</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('meeting-notes.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold text-xs transition-all flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali
                </a>
                
                <a href="{{ route('meeting-notes.pdf', $meetingNote->id) }}" class="px-4 py-2 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl font-bold text-xs hover:bg-rose-600 hover:text-white transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Cetak PDF
                </a>

                @if($meetingNote->status === 'draft' && (auth()->id() == $meetingNote->created_by || in_array(auth()->user()->role, ['admin', 'superadmin'])))
                    <a href="{{ route('meeting-notes.edit', $meetingNote->id) }}" class="px-4 py-2 bg-amber-50 text-amber-600 border border-amber-100 rounded-xl font-bold text-xs hover:bg-amber-500 hover:text-white transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Draf
                    </a>

                    <form action="{{ route('meeting-notes.finalize', $meetingNote->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memfinalisasi notulensi ini? Notulensi yang difinalisasi tidak dapat diubah kembali untuk selamanya.')" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold text-xs hover:bg-emerald-700 transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            Finalisasi Rapat
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Meta info cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Jenis Rapat</p>
                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-lg text-[10px] font-black uppercase tracking-wider inline-block">
                        {{ str_replace('_', ' ', $meetingNote->type) }}
                    </span>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Waktu Rapat</p>
                    <h3 class="text-sm font-black text-gray-800 uppercase leading-snug">
                        {{ $meetingNote->meeting_date->translatedFormat('d F Y') }}
                    </h3>
                    <p class="text-[10px] text-gray-400 font-bold mt-1">Pukul {{ date('H:i', strtotime($meetingNote->start_time)) }} WIB - Selesai</p>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Lokasi Tempat</p>
                    <h3 class="text-sm font-black text-gray-800 uppercase leading-snug">
                        {{ $meetingNote->location ?? 'Tidak Ditentukan' }}
                    </h3>
                    <p class="text-[10px] text-gray-400 font-bold mt-1">Fisik / Virtual Classroom</p>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Notulis Pembuat</p>
                    <h3 class="text-sm font-black text-gray-800 uppercase leading-snug">
                        {{ $meetingNote->creator->name ?? 'Staf Sekolah' }}
                    </h3>
                    <p class="text-[10px] text-gray-400 font-bold mt-1">Role: {{ ucfirst($meetingNote->creator->role ?? 'Staf') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Meeting Notes Body -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Agenda -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 space-y-4">
                        <h4 class="text-sm font-black text-gray-800 uppercase tracking-widest flex items-center border-b border-gray-50 pb-3">
                            <span class="w-1.5 h-4 bg-primary-600 rounded-full mr-2"></span> Agenda Rapat
                        </h4>
                        <div class="prose max-w-none text-xs leading-relaxed text-gray-600 font-medium">
                            {!! $agendaHtml ?: '<p class="italic text-gray-400">Tidak ada agenda tertulis.</p>' !!}
                        </div>
                    </div>

                    <!-- Pembahasan -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 space-y-4">
                        <h4 class="text-sm font-black text-gray-800 uppercase tracking-widest flex items-center border-b border-gray-50 pb-3">
                            <span class="w-1.5 h-4 bg-primary-600 rounded-full mr-2"></span> Pembahasan & Diskusi
                        </h4>
                        <div class="prose max-w-none text-xs leading-relaxed text-gray-600 font-medium">
                            {!! $discussionHtml ?: '<p class="italic text-gray-400">Tidak ada rincian pembahasan tertulis.</p>' !!}
                        </div>
                    </div>

                    <!-- Keputusan -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 space-y-4">
                        <h4 class="text-sm font-black text-gray-800 uppercase tracking-widest flex items-center border-b border-gray-50 pb-3">
                            <span class="w-1.5 h-4 bg-emerald-600 rounded-full mr-2"></span> Hasil Keputusan Rapat
                        </h4>
                        <div class="prose max-w-none text-xs leading-relaxed text-gray-600 font-medium p-4 bg-emerald-50/20 border border-emerald-100/50 rounded-2xl">
                            {!! $decisionsHtml ?: '<p class="italic text-gray-400">Belum ada keputusan tertulis yang dirumuskan.</p>' !!}
                        </div>
                    </div>
                </div>

                <!-- Attendees & Action Items Sidebar -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Attendees List -->
                    <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-gray-100 space-y-4">
                        <h4 class="text-sm font-black text-gray-800 uppercase tracking-widest flex items-center border-b border-gray-50 pb-3">
                            <span class="w-1.5 h-4 bg-primary-600 rounded-full mr-2"></span> Daftar Kehadiran ({{ $attendeeUsers->count() }})
                        </h4>
                        
                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            @forelse($attendeeUsers as $user)
                                <div class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-50 bg-gray-50/20">
                                    <div class="w-8 h-8 rounded-full bg-primary-50 text-primary-600 font-black text-xs flex items-center justify-center uppercase">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="text-[11px] font-black text-gray-800 uppercase leading-none">{{ $user->name }}</div>
                                        <div class="text-[9px] text-gray-400 font-bold mt-1 uppercase">{{ $user->role }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-xs text-gray-400 italic font-bold">
                                    Tidak ada daftar peserta terdaftar.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Action Items Tracker Widget -->
                    <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-gray-100 space-y-4" 
                         x-data="{ 
                             actionItems: {{ json_encode($meetingNote->action_items ?? []) }},
                             toggleItem(itemId) {
                                 fetch(`/meeting-notes/action-items/{{ $meetingNote->id }}/${itemId}/toggle`, {
                                     method: 'POST',
                                     headers: {
                                         'Content-Type': 'application/json',
                                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                     }
                                 })
                                 .then(res => res.json())
                                 .then(data => {
                                     if(data.success) {
                                         const item = this.actionItems.find(i => i.id === itemId);
                                         if (item) {
                                             item.is_completed = data.is_completed;
                                         }
                                     } else {
                                         alert(data.message);
                                     }
                                 });
                             }
                         }">
                        <h4 class="text-sm font-black text-gray-800 uppercase tracking-widest flex items-center border-b border-gray-50 pb-3">
                            <span class="w-1.5 h-4 bg-indigo-600 rounded-full mr-2"></span> Tindak Lanjut Rapat
                        </h4>

                        <div class="space-y-3">
                            <template x-for="item in actionItems" :key="item.id">
                                <div class="p-3.5 rounded-2xl border transition-all"
                                     :class="item.is_completed ? 'border-emerald-100 bg-emerald-50/20' : 'border-gray-100 bg-gray-50/30'">
                                    <div class="flex items-start gap-2.5">
                                        <!-- Checkbox button using SVG -->
                                        <button @click="toggleItem(item.id)" type="button" class="mt-0.5 shrink-0 focus:outline-none">
                                            <template x-if="item.is_completed">
                                                <div class="w-4.5 h-4.5 rounded-md bg-emerald-500 text-white flex items-center justify-center border border-emerald-500">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                            </template>
                                            <template x-if="!item.is_completed">
                                                <div class="w-4.5 h-4.5 rounded-md bg-white border border-gray-300 hover:border-indigo-500 transition-colors"></div>
                                            </template>
                                        </button>
                                        
                                        <div class="flex-1">
                                            <div class="text-[11px] font-bold text-gray-800 leading-snug"
                                                 :class="item.is_completed ? 'line-through text-gray-400' : ''"
                                                 x-text="item.task"></div>
                                            <div class="flex flex-wrap justify-between items-center mt-2 text-[9px] font-black uppercase tracking-wider text-gray-400">
                                                <span>PIC: <span class="text-gray-600" x-text="item.pic"></span></span>
                                                <span :class="new Date(item.deadline) < new Date() && !item.is_completed ? 'text-red-500' : ''"
                                                      x-text="'DL: ' + new Date(item.deadline).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'})"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="actionItems.length === 0">
                                <div class="text-center py-4 text-xs text-gray-400 italic font-bold">
                                    Tidak ada action items terdaftar.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
