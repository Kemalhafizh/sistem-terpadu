<x-app-layout>
    <x-slot name="title">Timeline Persiapan Akreditasi</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight flex items-center gap-2">
                    <span></span> Timeline Persiapan Akreditasi
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Agenda, target waktu, dan pelacakan kelengkapan visitasi tim penilai akreditasi BAN-S/M.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="$dispatch('open-modal', 'add-milestone-modal')" class="inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white rounded-xl py-2.5 px-5 text-sm font-black uppercase tracking-wider shadow-lg shadow-primary-500/20 hover:shadow-primary-500/35 transition-all duration-300 transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Agenda
                </button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Submenu Navigation -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex flex-wrap gap-1">
            <a href="{{ route('compliance.index') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-800 dark:hover:text-white">
                 Dashboard Kepatuhan
            </a>
            <a href="{{ route('compliance.checklist') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-800 dark:hover:text-white">
                 Checklist Instrumen
            </a>
            <a href="{{ route('compliance.documents') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-800 dark:hover:text-white">
                 Repositori Bukti Fisik
            </a>
            <a href="{{ route('compliance.timeline') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all bg-primary-600 text-white shadow-md shadow-primary-500/20">
                 Timeline Visitasi
            </a>
        </div>

        <!-- Progress Box -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
            <div class="flex justify-between items-center text-xs font-bold text-gray-450 dark:text-gray-400">
                <span>Progress Agenda Visitasi</span>
                <span>{{ $milestonesProgress }}% Terpenuhi</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-900 h-3 rounded-full overflow-hidden mt-2.5">
                <div class="h-full bg-gradient-to-r from-primary-500 to-indigo-500 rounded-full transition-all duration-500" :style="'width: ' + {{ $milestonesProgress }} + '%'"></div>
            </div>
        </div>

        <!-- Vertical Timeline visual list -->
        <div class="relative border-l border-gray-200 dark:border-gray-700 ml-4 md:ml-6 space-y-8 py-4">
            @forelse($milestones as $milestone)
            @php
                $isCompleted = $milestone->status === 'completed';
                $circleColor = $isCompleted ? 'bg-emerald-500 text-white ring-emerald-200 dark:ring-emerald-950/50' : 'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-400 ring-gray-100 dark:ring-gray-900';
            @endphp
            <div class="relative pl-8 md:pl-10">
                <!-- Outer Ring Indicator -->
                <span class="absolute -left-[14px] top-1.5 flex h-7 w-7 items-center justify-center rounded-full ring-4 {{ $circleColor }} transition-all duration-300">
                    @if($isCompleted)
                        
                    @else
                        ○
                    @endif
                </span>

                <!-- Box Content -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-md flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition-all duration-300 hover:shadow-lg">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <h4 class="text-sm font-black {{ $isCompleted ? 'line-through text-gray-400 dark:text-gray-500' : 'text-gray-850 dark:text-white' }}">
                                {{ $milestone->title }}
                            </h4>
                            @if($isCompleted)
                                <span class="text-[9px] font-black tracking-widest text-emerald-500 uppercase px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/20 rounded">
                                    SELESAI
                                </span>
                            @else
                                <span class="text-[9px] font-black tracking-widest text-amber-500 uppercase px-1.5 py-0.5 bg-amber-50 dark:bg-amber-950/20 rounded">
                                    PENDING
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-450 dark:text-gray-500 font-semibold leading-relaxed">
                            Target Tanggal: <strong class="text-gray-700 dark:text-gray-300">{{ $milestone->due_date->format('d M Y') }}</strong>
                            @if($milestone->completed_at)
                                <span class="ml-2 font-normal text-emerald-600 dark:text-emerald-450 italic">(Selesai pada: {{ $milestone->completed_at->format('d M Y H:i') }})</span>
                            @endif
                        </p>
                        @if($milestone->description)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 font-normal leading-relaxed">
                            {{ $milestone->description }}
                        </p>
                        @endif
                    </div>

                    <div class="flex space-x-2 w-full sm:w-auto justify-end">
                        <form method="post" action="{{ route('compliance.milestones.toggle', $milestone->id) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-xs font-black uppercase tracking-wider rounded-xl transition-all border {{ $isCompleted ? 'bg-gray-100 hover:bg-gray-200 border-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-650 dark:border-gray-650 dark:text-gray-200' : 'bg-primary-600 hover:bg-primary-700 border-primary-600 text-white shadow-md shadow-primary-500/20' }}">
                                @if($isCompleted)
                                    ↩ Batal Selesai
                                @else
                                     Tandai Selesai
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="pl-8 md:pl-10 text-center py-12 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl max-w-lg mx-auto">
                <span class="text-5xl"></span>
                <h3 class="text-base font-black text-gray-800 dark:text-white mt-4">Belum Ada Agenda Persiapan</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 max-w-xs mx-auto">Mulai susun milestone agenda persiapan visitasi penilai akreditasi dengan menekan tombol Tambah Agenda.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Tambah Agenda / Milestone -->
    <x-modal name="add-milestone-modal" focusable>
        <form method="post" action="{{ route('compliance.milestones.store') }}" class="p-6 bg-white dark:bg-gray-900 rounded-3xl">
            @csrf
            
            <h2 class="text-lg font-black text-gray-950 dark:text-white flex items-center gap-1.5">
                <span></span> Tambah Agenda Persiapan Baru
            </h2>
            
            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-normal">
                Buat target tugas persiapan baru untuk pelacakan visitasi akreditasi (milestone).
            </p>

            <div class="mt-6 space-y-4">
                <div>
                    <label for="title" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Judul Tugas / Agenda</label>
                    <input 
                        id="title" 
                        name="title" 
                        type="text" 
                        required 
                        placeholder="Contoh: Rapat Koordinasi Audit Mutu Internal" 
                        class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label for="due_date" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Target Tanggal Selesai</label>
                    <input 
                        id="due_date" 
                        name="due_date" 
                        type="date" 
                        required 
                        class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-xl px-4 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label for="description" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi / Detail Agenda</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="3"
                        placeholder="Detail tugas, pembagian koordinator standar, atau sasaran audit mutu internal..." 
                        class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    ></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <button type="button" @click="$dispatch('close')" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-880 transition-all">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-extrabold uppercase tracking-wider shadow-lg shadow-primary-500/20 transition-all">
                    Simpan Agenda
                </button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
