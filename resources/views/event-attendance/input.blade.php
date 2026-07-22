<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Input Presensi Kegiatan') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    {{ $event->name }} • {{ $event->event_date?->format('d M Y') }}
                </p>
            </div>
            <div>
                <a href="{{ route('event-attendance.show', $event->id) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    ⬅ KEMBALI
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden" 
         x-data="{ 
             searchQuery: '',
             allStatus(status) {
                 const radios = document.querySelectorAll(`input[type='radio'][value='${status}']`);
                 radios.forEach(radio => {
                     radio.checked = true;
                     // Dispatch change event to trigger updates
                     radio.dispatchEvent(new Event('change', { bubbles: true }));
                 });
             }
         }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Event Details Glassmorphic Card --}}
            <div class="bg-gradient-to-r from-primary-950 to-primary-900 rounded-[2.5rem] p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute right-0 bottom-0 translate-y-12 translate-x-12 opacity-10 pointer-events-none">
                    <span class="text-[12rem]"></span>
                </div>
                <div class="relative z-10 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-primary-300">Nama Kegiatan</p>
                        <h3 class="font-black text-lg mt-1 tracking-tight">{{ $event->name }}</h3>
                        <p class="text-[10px] opacity-75 mt-1">{{ $event->notes ?? 'Tidak ada catatan tambahan.' }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-primary-300">Tipe Kegiatan</p>
                        <p class="font-black text-sm mt-1 uppercase tracking-wider">
                            {{ str_replace('_', ' ', $event->type) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-primary-300">Target Sasaran</p>
                        <p class="font-black text-sm mt-1">
                            @if($event->target === 'all_students')
                                Semua Murid
                            @elseif($event->target === 'all_teachers')
                                Semua Guru
                            @elseif($event->target === 'specific_class')
                                Kelas Tertentu
                            @else
                                Grup Kustom
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-primary-300">Waktu Acara</p>
                        <p class="font-black text-sm mt-1">
                            {{ $event->event_date?->format('d M Y') }}
                            @if($event->start_time)
                                <span class="block text-[10px] opacity-75 font-normal mt-0.5">
                                     {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }}{{ $event->end_time ? ' - ' . \Carbon\Carbon::parse($event->end_time)->format('H:i') : '' }} WIB
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Bulk Action & Live Filter Bar --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Bulk Select Buttons --}}
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest mr-2">Set Semua Ke:</span>
                    <button type="button" @click="allStatus('hadir')" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                         HADIR
                    </button>
                    <button type="button" @click="allStatus('terlambat')" class="px-3 py-1.5 bg-teal-50 text-teal-600 hover:bg-teal-100 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                         TERLAMBAT
                    </button>
                    <button type="button" @click="allStatus('izin')" class="px-3 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                         IZIN
                    </button>
                    <button type="button" @click="allStatus('sakit')" class="px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                         SAKIT
                    </button>
                    <button type="button" @click="allStatus('alpa')" class="px-3 py-1.5 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                         ALPA
                    </button>
                </div>

                {{-- Live Search Input --}}
                <div class="w-full md:w-72">
                    <input type="text" x-model="searchQuery" placeholder="Cari nama peserta..." class="w-full bg-gray-50 border-none rounded-xl px-4 py-2 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                </div>
            </div>

            {{-- Batch Form --}}
            <form method="POST" action="{{ route('event-attendance.store-attendance', $event->id) }}" class="space-y-6">
                @csrf

                <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[950px]">
                        <thead>
                            <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="pb-3 w-12 text-center">No.</th>
                                <th class="pb-3">Nama Lengkap</th>
                                <th class="pb-3 w-32">Kelas / Peran</th>
                                <th class="pb-3 text-center w-[400px]">Status Presensi</th>
                                <th class="pb-3 w-32">Jam Masuk</th>
                                <th class="pb-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-xs font-bold text-gray-700">
                            @forelse($records as $index => $record)
                                @php
                                    $userTypeLabel = $record->user?->role === 'student' ? ($record->user?->student?->classroom?->name ?? 'Siswa') : 'Guru/Staf';
                                @endphp
                                <tr x-show="searchQuery === '' || '{{ strtolower($record->user?->name) }}'.includes(searchQuery.toLowerCase())"
                                    x-data="{ 
                                        status: '{{ old("records.{$index}.status", $record->status) }}',
                                        checkIn: '{{ old("records.{$index}.check_in_time", $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '') }}'
                                    }">
                                    
                                    {{-- Index Number --}}
                                    <td class="py-4 text-center text-gray-400 font-black">
                                        {{ $loop->iteration }}
                                    </td>

                                    {{-- Name --}}
                                    <td class="py-4">
                                        <div class="flex flex-col">
                                            <span class="font-black text-gray-800">{{ $record->user?->name }}</span>
                                            <span class="text-[9px] text-gray-400 uppercase font-black">ID: #{{ $record->user_id }}</span>
                                        </div>
                                        <input type="hidden" name="records[{{ $index }}][user_id]" value="{{ $record->user_id }}">
                                    </td>

                                    {{-- Class/Role --}}
                                    <td class="py-4 text-gray-500">
                                        {{ $userTypeLabel }}
                                    </td>

                                    {{-- Radio Button Status Selector --}}
                                    <td class="py-4">
                                        <div class="flex items-center justify-center space-x-1 sm:space-x-2">
                                            
                                            {{-- Hadir --}}
                                            <label class="flex-1 flex items-center justify-center py-2 rounded-xl border-2 cursor-pointer transition-all hover:bg-emerald-50/20"
                                                   :class="status === 'hadir' ? 'border-emerald-500 bg-emerald-50/20 text-emerald-600' : 'border-gray-100 text-gray-400'">
                                                <input type="radio" name="records[{{ $index }}][status]" value="hadir" x-model="status" 
                                                       @change="if(!checkIn) { checkIn = '{{ date('H:i') }}'; }" class="sr-only">
                                                <span class="text-[9px] font-black uppercase tracking-wider">Hadir</span>
                                            </label>

                                            {{-- Terlambat --}}
                                            <label class="flex-1 flex items-center justify-center py-2 rounded-xl border-2 cursor-pointer transition-all hover:bg-teal-50/20"
                                                   :class="status === 'terlambat' ? 'border-teal-500 bg-teal-50/20 text-teal-600' : 'border-gray-100 text-gray-400'">
                                                <input type="radio" name="records[{{ $index }}][status]" value="terlambat" x-model="status"
                                                       @change="if(!checkIn) { checkIn = '{{ date('H:i') }}'; }" class="sr-only">
                                                <span class="text-[9px] font-black uppercase tracking-wider">Late</span>
                                            </label>

                                            {{-- Izin --}}
                                            <label class="flex-1 flex items-center justify-center py-2 rounded-xl border-2 cursor-pointer transition-all hover:bg-amber-50/20"
                                                   :class="status === 'izin' ? 'border-amber-500 bg-amber-50/20 text-amber-700' : 'border-gray-100 text-gray-400'">
                                                <input type="radio" name="records[{{ $index }}][status]" value="izin" x-model="status" 
                                                       @change="checkIn = ''" class="sr-only">
                                                <span class="text-[9px] font-black uppercase tracking-wider">Izin</span>
                                            </label>

                                            {{-- Sakit --}}
                                            <label class="flex-1 flex items-center justify-center py-2 rounded-xl border-2 cursor-pointer transition-all hover:bg-rose-50/20"
                                                   :class="status === 'sakit' ? 'border-rose-500 bg-rose-50/20 text-rose-600' : 'border-gray-100 text-gray-400'">
                                                <input type="radio" name="records[{{ $index }}][status]" value="sakit" x-model="status"
                                                       @change="checkIn = ''" class="sr-only">
                                                <span class="text-[9px] font-black uppercase tracking-wider">Sakit</span>
                                            </label>

                                            {{-- Alpa --}}
                                            <label class="flex-1 flex items-center justify-center py-2 rounded-xl border-2 cursor-pointer transition-all hover:bg-gray-50/20"
                                                   :class="status === 'alpa' ? 'border-gray-700 bg-gray-50 text-gray-700 font-bold' : 'border-gray-100 text-gray-400'">
                                                <input type="radio" name="records[{{ $index }}][status]" value="alpa" x-model="status"
                                                       @change="checkIn = ''" class="sr-only">
                                                <span class="text-[9px] font-black uppercase tracking-wider">Alpa</span>
                                            </label>
                                        </div>
                                    </td>

                                    {{-- Time Picker (Only activated when Hadir/Terlambat) --}}
                                    <td class="py-4">
                                        <input type="time" name="records[{{ $index }}][check_in_time]" x-model="checkIn"
                                               :disabled="status !== 'hadir' && status !== 'terlambat'"
                                               class="w-full bg-gray-50 disabled:bg-gray-100 disabled:opacity-50 border-none rounded-xl px-2 py-1.5 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                                    </td>

                                    {{-- Notes --}}
                                    <td class="py-4">
                                        <input type="text" name="records[{{ $index }}][notes]" value="{{ old("records.{$index}.notes", $record->notes) }}"
                                               placeholder="Catatan..." class="w-full bg-gray-50 border-none rounded-xl px-3 py-1.5 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400 font-bold">
                                        Tidak ada data peserta untuk diabsensi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Action Panel --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-gray-400 font-bold">Pastikan semua data kehadiran telah diperiksa dengan benar sebelum menyimpan.</span>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('event-attendance.show', $event->id) }}" class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-xl shadow-primary-900/10">
                             Simpan Presensi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
