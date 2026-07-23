<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('points.index') }}" class="p-2 bg-gray-100 rounded-xl text-gray-500 hover:bg-gray-200 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
                Riwayat Poin: {{ $student?->user?->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8">
            
            <!-- Student Header Profile -->
            <div class="bg-gradient-to-br from-primary-800 to-primary-600 rounded-[2.5rem] p-8 shadow-xl text-white mb-8 flex flex-wrap items-center justify-between gap-6">
                <div class="flex items-center">
                    <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-[1.5rem] flex items-center justify-center font-black text-3xl mr-6 border border-white/30">
                        {{ substr($student?->user?->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-3xl font-black tracking-tight mb-1">{{ $student?->user?->name }}</h3>
                        <p class="text-primary-100 font-bold uppercase tracking-widest text-xs opacity-90">
                            Kelas {{ $student?->classroom?->name ?? 'N/A' }} • NIS: {{ $student->nis }} • {{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="bg-white/10 backdrop-blur-xl rounded-[2rem] px-8 py-5 border border-white/20 text-center min-w-[150px]">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-80 mb-1">Total Poin</p>
                        <p class="text-4xl font-black {{ $student->total_points > 50 ? 'text-red-300' : 'text-white' }} tracking-tighter">{{ $student->total_points }}</p>
                    </div>
                    <button onclick="openPointModal()" class="px-8 py-6 bg-white text-primary-700 rounded-[2rem] font-black text-sm shadow-2xl shadow-primary-900/40 hover:scale-105 transition-all">
                        + INPUT POIN
                    </button>
                </div>
            </div>

            <!-- Timeline of Logs -->
            <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-gray-100">
                <h4 class="text-xl font-black text-gray-800 mb-10 flex items-center">
                    <span class="w-2 h-8 bg-primary-600 rounded-full mr-4"></span>
                    Log Kedisiplinan & Prestasi
                </h4>

                <div class="relative">
                    <!-- Line -->
                    <div class="absolute left-6 top-0 bottom-0 w-px bg-gray-100"></div>

                    <div class="space-y-12">
                        @forelse($logs ?? [] as $log)
                            <div class="relative flex items-start group">
                                <!-- Dot -->
                                <div class="absolute left-6 -translate-x-1/2 w-3 h-3 rounded-full ring-4 {{ $log->points > 0 ? 'bg-red-500 ring-red-50' : 'bg-emerald-500 ring-emerald-50' }} z-10"></div>
                                
                                <div class="ml-16 flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-4 mb-2">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm font-black {{ $log->points > 0 ? 'text-red-600' : 'text-emerald-600' }} uppercase tracking-widest">
                                                {{ $log?->category?->name ?? ($log->points > 0 ? 'PELANGGARAN' : ($log->points < 0 ? 'PRESTASI' : 'PENYESUAIAN')) }}
                                            </span>
                                            <span class="text-sm font-bold {{ $log->points > 0 ? 'text-red-500 bg-red-50' : 'text-emerald-600 bg-emerald-50' }} px-2 py-0.5 rounded-md">
                                                {{ $log->points > 0 ? '+' : '' }}{{ $log->points }} Poin
                                            </span>
                                        </div>
                                        <span class="text-xs font-bold text-gray-400">{{ $log->created_at?->format('d M Y • H:i') }}</span>
                                    </div>
                                    <div class="bg-gray-50/50 rounded-2xl p-5 border border-gray-100">
                                        <p class="text-gray-700 font-medium leading-relaxed">{{ $log->description }}</p>
                                        <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                                            <div class="flex items-center text-xs text-gray-400 font-bold uppercase tracking-wider">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                Dicatat oleh: <span class="text-gray-600 ml-1">{{ $log?->creator?->name }}</span>
                                            </div>
                                            
                                            @if(auth()->user()->isAdmin() || auth()->id() == $log->created_by)
                                                <form action="{{ route('points.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat poin ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-20 text-center text-gray-400 italic">
                                <p>Belum ada riwayat poin untuk siswa ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-12">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Point Input Modal -->
    <div id="point-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] p-10 max-w-lg w-full shadow-2xl relative overflow-hidden">
            <h3 class="text-2xl font-black text-gray-800 mb-8">Input Poin Kedisiplinan</h3>

            <form action="{{ route('points.store') }}" method="POST">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Kategori (Standard)</label>
                        <select name="point_category_id" id="modal-category-select" onchange="applyCategory(this)" class="w-full rounded-2xl border-gray-100 bg-gray-50 focus:ring-primary-500 text-sm font-bold p-4">
                            <option value="">-- Input Manual --</option>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}" data-points="{{ $cat->default_points }}" data-type="{{ $cat->type }}" data-name="{{ $cat->name }}">
                                    [{{ $cat->type == 'penalty' ? '+' : '' }}{{ $cat->default_points }}] {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="manual-input-section" class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="type" id="type-penalty" value="penalty" class="hidden peer" checked onchange="updatePointMode('penalty')">
                                <div class="p-4 rounded-2xl border-2 border-gray-100 peer-checked:border-red-500 peer-checked:bg-red-50 text-center transition-all">
                                    <span class="block text-xs font-black uppercase text-gray-400 peer-checked:text-red-700">Pelanggaran</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="type" id="type-reward" value="reward" class="hidden peer" onchange="updatePointMode('reward')">
                                <div class="p-4 rounded-2xl border-2 border-gray-100 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 text-center transition-all">
                                    <span class="block text-xs font-black uppercase text-gray-400 peer-checked:text-emerald-700">Prestasi</span>
                                </div>
                            </label>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Jumlah Poin</label>
                            <div class="relative">
                                <input type="number" name="points" id="modal-points-input" class="w-full px-5 py-4 rounded-2xl bg-gray-50 border-gray-100 text-xl font-black focus:ring-primary-500" placeholder="0" required>
                                <span id="point-prefix" class="absolute right-5 top-4 font-black text-xl text-red-500">+</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Alasan / Keterangan</label>
                        <textarea name="description" id="modal-description" rows="3" class="w-full rounded-2xl border-gray-100 bg-gray-50 focus:ring-primary-500 text-sm font-medium p-4" required></textarea>
                    </div>
                </div>

                <div class="flex space-x-3 mt-10">
                    <button type="button" onclick="closePointModal()" class="flex-1 py-4 bg-gray-100 text-gray-500 font-bold rounded-2xl text-sm hover:bg-gray-200">BATAL</button>
                    <button type="submit" class="flex-1 py-4 bg-primary-600 text-white font-black rounded-2xl text-sm shadow-xl shadow-primary-900/20 hover:scale-[1.02] transition-all">SIMPAN</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPointModal() {
            document.getElementById('point-modal').classList.remove('hidden');
        }

        function closePointModal() {
            document.getElementById('point-modal').classList.add('hidden');
        }

        function applyCategory(select) {
            const option = select.options[select.selectedIndex];
            if (!option.value) return;

            const points = option.getAttribute('data-points');
            const type = option.getAttribute('data-type');
            const name = option.getAttribute('data-name');

            document.getElementById('modal-points-input').value = Math.abs(points);
            document.getElementById('modal-description').value = name;
            
            if (type === 'penalty') {
                document.getElementById('type-penalty').checked = true;
                updatePointMode('penalty');
            } else {
                document.getElementById('type-reward').checked = true;
                updatePointMode('reward');
            }
        }

        function updatePointMode(mode) {
            const prefix = document.getElementById('point-prefix');
            if (mode === 'penalty') {
                prefix.innerText = '+';
                prefix.classList.remove('text-emerald-500');
                prefix.classList.add('text-red-500');
            } else {
                prefix.innerText = '-';
                prefix.classList.remove('text-red-500');
                prefix.classList.add('text-emerald-500');
            }
        }

        document.querySelector('#point-modal form').onsubmit = function() {
            const mode = document.querySelector('input[name="type"]:checked').value;
            const input = document.getElementById('modal-points-input');
            if (mode === 'reward' && input.value > 0) {
                input.value = -Math.abs(input.value);
            } else if(mode === 'penalty' && input.value < 0) {
                 input.value = Math.abs(input.value);
            }
        };
    </script>
</x-app-layout>
