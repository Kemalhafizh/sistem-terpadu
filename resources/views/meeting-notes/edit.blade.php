<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-black text-2xl text-gray-800 leading-tight">
                    {{ __('Edit Notulensi Rapat') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-1">Ubah Informasi Dokumentasi Rapat</p>
            </div>
            <a href="{{ route('meeting-notes.show', $meetingNote->id) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold text-xs transition-all flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-gray-100">

                <form action="{{ route('meeting-notes.update', $meetingNote->id) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- 1. Informasi Umum Rapat -->
                    <div class="space-y-6">
                        <h4 class="text-sm font-black text-gray-800 uppercase tracking-widest flex items-center border-b border-gray-50 pb-2">
                            <span class="w-1.5 h-4 bg-primary-600 rounded-full mr-2"></span> 1. Informasi Umum
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Judul Rapat <span class="text-red-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title', $meetingNote->title) }}" required placeholder="Contoh: Rapat Koordinasi Kurikulum Semester Genap" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                @error('title') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Jenis Rapat <span class="text-red-500">*</span></label>
                                <select name="type" required class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                    <option value="" disabled>Pilih Jenis Rapat</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type }}" {{ old('type', $meetingNote->type) == $type ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Lokasi Rapat</label>
                                <input type="text" name="location" value="{{ old('location', $meetingNote->location) }}" placeholder="Contoh: Aula Utama / Zoom Meeting" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                @error('location') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Tanggal Rapat <span class="text-red-500">*</span></label>
                                <input type="date" name="meeting_date" value="{{ old('meeting_date', $meetingNote->meeting_date->format('Y-m-d')) }}" required class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                @error('meeting_date') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Jam Mulai <span class="text-red-500">*</span></label>
                                    <input type="time" name="start_time" value="{{ old('start_time', date('H:i', strtotime($meetingNote->start_time))) }}" required class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                    @error('start_time') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Jam Selesai</label>
                                    <input type="time" name="end_time" value="{{ old('end_time', $meetingNote->end_time ? date('H:i', strtotime($meetingNote->end_time)) : '') }}" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                    @error('end_time') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Peserta Rapat -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-black text-gray-800 uppercase tracking-widest flex items-center border-b border-gray-50 pb-2">
                            <span class="w-1.5 h-4 bg-primary-600 rounded-full mr-2"></span> 2. Peserta Hadir
                        </h4>
                        
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Anggota Staff / Guru yang Hadir</label>
                            
                            <!-- Search box for list filtering -->
                            <div x-data="{ 
                                search: '', 
                                items: [
                                    @foreach($users as $user)
                                        { id: '{{ $user->id }}', name: '{{ addslashes($user->name) }}', role: '{{ ucfirst($user->role) }}', isChecked: {{ in_array($user->id, $meetingNote->attendees ?? []) ? 'true' : 'false' }} },
                                    @endforeach
                                ]
                            }" class="space-y-3">
                                <input type="text" x-model="search" placeholder="Cari nama peserta..." class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500 mb-2">
                                
                                <div class="h-44 overflow-y-auto border border-gray-100 rounded-2xl p-4 grid grid-cols-1 md:grid-cols-2 gap-2 bg-gray-50/20">
                                    <template x-for="item in items" :key="item.id">
                                        <label x-show="item.name.toLowerCase().includes(search.toLowerCase())" class="flex items-center space-x-2.5 text-xs font-bold text-gray-700 cursor-pointer hover:bg-white p-2 rounded-xl border border-transparent hover:border-gray-100 transition-all">
                                            <input type="checkbox" name="attendees[]" :value="item.id" :checked="item.isChecked" class="rounded text-primary-600 focus:ring-primary-500">
                                            <div>
                                                <span x-text="item.name"></span>
                                                <span class="text-[9px] text-gray-400 font-bold block" x-text="item.role"></span>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Konten Rapat (Agenda, Pembahasan, Keputusan) -->
                    <div class="space-y-6">
                        <h4 class="text-sm font-black text-gray-800 uppercase tracking-widest flex items-center border-b border-gray-50 pb-2">
                            <span class="w-1.5 h-4 bg-primary-600 rounded-full mr-2"></span> 3. Hasil & Rangkuman Rapat
                        </h4>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Agenda Rapat</label>
                                    <span class="text-[9px] text-gray-400 font-bold italic">Mendukung format Markdown</span>
                                </div>
                                <textarea name="agenda" rows="4" placeholder="Gunakan bullet lists (* poin) untuk agenda..." class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">{{ old('agenda', $meetingNote->agenda) }}</textarea>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Pembahasan & Diskusi</label>
                                    <span class="text-[9px] text-gray-400 font-bold italic">Mendukung format Markdown</span>
                                </div>
                                <textarea name="discussion" rows="6" placeholder="Catat poin-poin diskusi utama di sini..." class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">{{ old('discussion', $meetingNote->discussion) }}</textarea>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Keputusan Rapat</label>
                                    <span class="text-[9px] text-gray-400 font-bold italic">Mendukung format Markdown</span>
                                </div>
                                <textarea name="decisions" rows="4" placeholder="Tulis keputusan penting yang disepakati..." class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">{{ old('decisions', $meetingNote->decisions) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Action Items (Tindak Lanjut Rapat) -->
                    <div class="space-y-4" x-data="{ items: {{ json_encode($meetingNote->action_items ?? []) }} }">
                        <div class="flex justify-between items-center border-b border-gray-50 pb-2">
                            <h4 class="text-sm font-black text-gray-800 uppercase tracking-widest flex items-center">
                                <span class="w-1.5 h-4 bg-primary-600 rounded-full mr-2"></span> 4. Tindak Lanjut (Action Items)
                            </h4>
                            <button type="button" @click="items.push({ task: '', pic: '', deadline: '{{ date('Y-m-d') }}', is_completed: false })" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg font-bold text-[10px] uppercase transition-all flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Tambah Item
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50/50 flex flex-col md:flex-row md:items-center gap-3 relative group">
                                    <input type="hidden" :name="'action_items['+index+'][is_completed]'" x-model="item.is_completed">
                                    <div class="flex-1">
                                        <label class="block text-[8px] font-black text-gray-400 uppercase tracking-wider mb-1">Tugas / Tindak Lanjut</label>
                                        <input type="text" :name="'action_items['+index+'][task]'" x-model="item.task" required placeholder="Contoh: Mengirimkan draf kurikulum" class="w-full text-[11px] font-bold rounded-lg border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                    <div class="w-full md:w-48">
                                        <label class="block text-[8px] font-black text-gray-400 uppercase tracking-wider mb-1">PIC (Penanggung Jawab)</label>
                                        <input type="text" :name="'action_items['+index+'][pic]'" x-model="item.pic" required placeholder="Nama Guru / Staf" class="w-full text-[11px] font-bold rounded-lg border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                    <div class="w-full md:w-36">
                                        <label class="block text-[8px] font-black text-gray-400 uppercase tracking-wider mb-1">Batas Waktu</label>
                                        <input type="date" :name="'action_items['+index+'][deadline]'" x-model="item.deadline" required class="w-full text-[11px] font-bold rounded-lg border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                    <div class="pt-4 md:pt-0">
                                        <button type="button" @click="items.splice(index, 1)" class="p-2 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-lg transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div x-show="items.length === 0" class="py-6 text-center text-gray-400 italic text-[11px] border border-dashed border-gray-200 rounded-2xl bg-gray-50/20">
                                Belum ada action items yang didaftarkan. Klik tombol "Tambah Item" untuk menugaskan PIC.
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="pt-6 border-t border-gray-50 flex justify-end gap-3">
                        <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-bold text-xs hover:bg-primary-700 transition-all">
                            Perbarui Notulensi (Draf)
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
