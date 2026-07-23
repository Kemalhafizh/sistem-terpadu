<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">Master Preset Waktu</h2>
            <button onclick="document.getElementById('modal-preset').classList.remove('hidden')" class="bg-primary-600 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-lg shadow-primary-900/20">
                + Buat Preset Baru
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-md shadow-sm">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- List Presets -->
                <div class="lg:col-span-1 space-y-4">
                    <h3 class="font-bold text-gray-700 uppercase text-xs tracking-widest px-2">Daftar Preset</h3>
                    @forelse($presets ?? [] as $preset)
                        <div class="bg-white p-5 rounded-2xl border {{ $preset->is_active ? 'border-primary-500 ring-2 ring-primary-50' : 'border-gray-100' }} shadow-sm">
                            <div class="flex justify-between items-start mb-3">
                                <h4 class="font-bold text-gray-800">{{ $preset->name }}</h4>
                                @if($preset->is_active)
                                    <span class="bg-primary-100 text-primary-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase">Aktif</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mb-4">{{ $preset->times_count }} Sesi terdefinisi</p>
                            
                            <div class="flex space-x-2">
                                <a href="?preset_id={{ $preset->id }}" class="flex-1 text-center py-2 bg-gray-50 text-gray-700 rounded-lg text-xs font-bold border border-gray-200">Edit Waktu</a>
                                @if(!$preset->is_active)
                                    <form action="{{ route('schedules.presets.activate', $preset->id) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full py-2 bg-primary-600 text-white rounded-lg text-xs font-bold shadow-sm">Aktifkan</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 italic py-10">Belum ada preset.</p>
                    @endforelse
                </div>

                <!-- Setup Times for Selected Preset -->
                <div class="lg:col-span-2">
                    @php
                        $selectedPreset = request('preset_id') ? $presets->find(request('preset_id')) : $presets->where('is_active', true)->first();
                    @endphp

                    @if($selectedPreset)
                        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                            <h3 class="text-xl font-extrabold text-gray-800 mb-6">Setup Sesi: {{ $selectedPreset->name }}</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest">
                                            <th class="pb-4">Sesi Ke-</th>
                                            <th class="pb-4">Jam Mulai</th>
                                            <th class="pb-4">Jam Selesai</th>
                                            <th class="pb-4 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @for($i = 1; $i <= 11; $i++)
                                            @php $time = $selectedPreset->times->where('session_number', $i)->first(); @endphp
                                            <tr>
                                                <td class="py-4 font-bold text-gray-700">Jam ke-{{ $i }}</td>
                                                <form action="{{ route('schedules.presets.times.store', $selectedPreset->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="session_number" value="{{ $i }}">
                                                    <td class="py-2">
                                                        <input type="time" name="start_time" value="{{ $time ? $time->start_time : '' }}" class="rounded-lg border-gray-200 text-sm focus:ring-primary-500">
                                                    </td>
                                                    <td class="py-2">
                                                        <input type="time" name="end_time" value="{{ $time ? $time->end_time : '' }}" class="rounded-lg border-gray-200 text-sm focus:ring-primary-500">
                                                    </td>
                                                    <td class="py-2 text-right">
                                                        <button type="submit" class="p-2 bg-gray-50 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        </button>
                                                    </td>
                                                </form>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-[2rem] p-12 text-center border-2 border-dashed border-gray-200">
                            <p class="text-gray-500 font-medium">Pilih salah satu preset di sebelah kiri untuk mengatur waktu sesi.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Buat Preset -->
    <div id="modal-preset" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Buat Preset Baru</h3>
            <form action="{{ route('schedules.presets.store') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Preset</label>
                    <input type="text" name="name" class="w-full rounded-xl border-gray-200 focus:ring-primary-500" placeholder="Misal: Jadwal Reguler 2026" required>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="document.getElementById('modal-preset').classList.add('hidden')" class="flex-1 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl text-sm">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-primary-600 text-white font-bold rounded-xl text-sm shadow-lg shadow-primary-900/20">Simpan Preset</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
