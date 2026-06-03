<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Buku Saku Poin Siswa') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-primary-600 text-white p-4 rounded-2xl shadow-lg shadow-primary-900/20 flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Search & Filter -->
            <div class="bg-white p-6 rounded-[1.5rem] shadow-sm border border-gray-100 flex flex-wrap items-center justify-between gap-4">
                <form action="{{ route('points.index') }}" method="GET" class="flex-1 min-w-[300px]">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa atau NIS..." class="w-full pl-12 pr-4 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:ring-primary-500 focus:bg-white transition-all text-sm font-medium">
                        <div class="absolute left-4 top-3.5 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                </form>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                    Menampilkan {{ $students->total() }} Siswa
                </div>
            </div>

            <!-- Student Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($students ?? [] as $student)
                    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 hover:shadow-xl transition-all group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-primary-100 text-primary-700 rounded-2xl flex items-center justify-center font-black text-lg mr-4">
                                    {{ substr($student?->user?->name, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 group-hover:text-primary-600 transition-colors">{{ $student?->user?->name }}</h4>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter">{{ $student?->classroom?->name ?? 'Tanpa Kelas' }} • NIS: {{ $student->nis }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-black {{ $student->total_points > 50 ? 'text-red-500' : 'text-primary-600' }}">
                                    {{ $student->total_points }}
                                </div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Poin</span>
                            </div>
                        </div>

                        <div class="flex space-x-2 mt-6">
                            <a href="{{ route('points.show', $student->id) }}" class="flex-1 py-2.5 bg-gray-50 text-gray-700 rounded-xl text-center text-xs font-bold border border-gray-100 hover:bg-gray-100 transition-colors">Lihat Riwayat</a>
                            <button onclick="openPointModal({{ $student->id }}, '{{ $student?->user?->name }}')" class="flex-1 py-2.5 bg-primary-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-primary-900/10 hover:bg-primary-700 transition-all">Input Poin</button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center bg-gray-50 rounded-[2.5rem] border-2 border-dashed border-gray-200 text-gray-400">
                        <p class="font-bold italic">Siswa tidak ditemukan.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $students->links() }}
            </div>
        </div>
    </div>

    <!-- Point Input Modal -->
    <div id="point-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] p-10 max-w-lg w-full shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-3 bg-primary-600"></div>
            
            <h3 class="text-2xl font-black text-gray-800 mb-2">Input Poin Kedisiplinan</h3>
            <p class="text-gray-400 text-sm font-medium mb-8" id="modal-student-name">Siswa: -</p>

            <form action="{{ route('points.store') }}" method="POST">
                @csrf
                <input type="hidden" name="student_id" id="modal-student-id">
                
                <div class="space-y-6">
                    <!-- Category Selection -->
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
                        <!-- Type Toggle -->
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="type" id="type-penalty" value="penalty" class="hidden peer" checked onchange="updatePointMode('penalty')">
                                <div class="p-4 rounded-2xl border-2 border-gray-100 peer-checked:border-red-500 peer-checked:bg-red-50 text-center transition-all">
                                    <span class="block text-xs font-black uppercase text-gray-400 peer-checked:text-red-700">Pelanggaran</span>
                                    <span class="block text-[10px] text-gray-400 leading-tight mt-1">(Tambah Poin)</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="type" id="type-reward" value="reward" class="hidden peer" onchange="updatePointMode('reward')">
                                <div class="p-4 rounded-2xl border-2 border-gray-100 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 text-center transition-all">
                                    <span class="block text-xs font-black uppercase text-gray-400 peer-checked:text-emerald-700">Prestasi/Reward</span>
                                    <span class="block text-[10px] text-gray-400 leading-tight mt-1">(Kurangi Poin)</span>
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
                        <textarea name="description" id="modal-description" rows="3" class="w-full rounded-2xl border-gray-100 bg-gray-50 focus:ring-primary-500 text-sm font-medium p-4" placeholder="Contoh: Terlambat masuk sekolah lebih dari 15 menit" required></textarea>
                    </div>
                </div>

                <div class="flex space-x-3 mt-10">
                    <button type="button" onclick="closePointModal()" class="flex-1 py-4 bg-gray-100 text-gray-500 font-bold rounded-2xl text-sm transition-all hover:bg-gray-200">BATAL</button>
                    <button type="submit" class="flex-1 py-4 bg-primary-600 text-white font-black rounded-2xl text-sm shadow-xl shadow-primary-900/20 hover:scale-[1.02] transition-all">SIMPAN CATATAN</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPointModal(id, name) {
            document.getElementById('point-modal').classList.remove('hidden');
            document.getElementById('modal-student-id').value = id;
            document.getElementById('modal-student-name').innerText = "Siswa: " + name;
        }

        function closePointModal() {
            document.getElementById('point-modal').classList.add('hidden');
            document.getElementById('modal-category-select').value = '';
            document.getElementById('modal-points-input').value = '';
            document.getElementById('modal-description').value = '';
        }

        function applyCategory(select) {
            const option = select.options[select.selectedIndex];
            if (!option.value) {
                document.getElementById('manual-input-section').classList.remove('hidden');
                return;
            }

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

        // Logic check: reward points should be negative in database
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
