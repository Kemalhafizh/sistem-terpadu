<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Jalur Seleksi SPMB') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6 flex gap-2">
                <a href="{{ route('spmb.admin.dashboard') }}" class="px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-all">&larr; Kembali ke Dashboard</a>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="trackForm()">
                
                <!-- Form Tambah Jalur -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit">
                    <h3 class="font-black text-slate-800 text-sm mb-4">Tambah Jalur Baru</h3>
                    
                    <form action="{{ route('spmb.admin.tracks.store') }}" method="POST" class="space-y-4" @submit="handleSubmit($event)">
                        @csrf
                        <div>
                            <label for="spmb_period_id" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Periode SPMB <span class="text-red-500">*</span></label>
                            <select name="spmb_period_id" id="spmb_period_id" required class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="">-- Pilih Periode --</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->id }}">{{ $period->name }}</option>
                                @endforeach
                            </select>
                            @error('spmb_period_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Jalur <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" required placeholder="Misal: Reguler / Prestasi / Tahfidz" class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="quota" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Kuota Jalur <span class="text-red-500">*</span></label>
                            <input type="number" name="quota" id="quota" required min="1" placeholder="30" class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            @error('quota') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Scoring Weights inputs -->
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Bobot Penilaian (%)</h4>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-400 mb-1">Bobot Rapor</label>
                                    <input type="number" name="scoring_weights[rapor]" x-model.number="weights.rapor" required min="0" max="100" class="block w-full bg-white border border-slate-250 rounded-lg px-3 py-1.5 text-center font-bold">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-400 mb-1">Bobot Ujian/Tes</label>
                                    <input type="number" name="scoring_weights[tes]" x-model.number="weights.tes" required min="0" max="100" class="block w-full bg-white border border-slate-250 rounded-lg px-3 py-1.5 text-center font-bold">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-400 mb-1">Bobot Prestasi</label>
                                    <input type="number" name="scoring_weights[prestasi]" x-model.number="weights.prestasi" required min="0" max="100" class="block w-full bg-white border border-slate-250 rounded-lg px-3 py-1.5 text-center font-bold">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-400 mb-1">Bobot Tahfidz</label>
                                    <input type="number" name="scoring_weights[tahfidz]" x-model.number="weights.tahfidz" required min="0" max="100" class="block w-full bg-white border border-slate-250 rounded-lg px-3 py-1.5 text-center font-bold">
                                </div>
                            </div>
                            <div class="mt-3 text-[10px] font-black text-center" :class="totalWeight() === 100 ? 'text-emerald-600' : 'text-red-500'">
                                Total Bobot: <span x-text="totalWeight()"></span>% (Harus 100%)
                            </div>
                            <span class="text-xs text-red-500 font-medium block mt-1 text-center animate-pulse" x-show="errorMessage" x-text="errorMessage"></span>
                        </div>

                        <div>
                            <label for="requirements" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Persyaratan Khusus</label>
                            <textarea name="requirements" id="requirements" rows="3" placeholder="Persyaratan khusus jalur..." class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                        </div>

                        <button type="submit" :disabled="totalWeight() !== 100" class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md transition-all">Simpan Jalur</button>
                    </form>
                </div>

                <!-- Daftar Jalur -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 bg-slate-50">
                        <h3 class="font-black text-slate-800 text-sm">Daftar Jalur Seleksi</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-150 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                    <th class="p-4">Nama Jalur</th>
                                    <th class="p-4">Periode</th>
                                    <th class="p-4">Kuota</th>
                                    <th class="p-4">Bobot Penilaian</th>
                                    <th class="p-4">Persyaratan</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs divide-y divide-slate-100">
                                @forelse($tracks as $track)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="p-4 font-black text-slate-900">{{ $track->name }}</td>
                                        <td class="p-4 font-bold text-slate-500">{{ $track->period->name ?? '-' }}</td>
                                        <td class="p-4 font-inter font-semibold text-slate-700">{{ $track->quota }} Kursi</td>
                                        <td class="p-4 font-inter text-slate-650">
                                            @if($track->scoring_weights)
                                                Rapor: {{ $track->scoring_weights['rapor'] ?? 0 }}%, 
                                                Tes: {{ $track->scoring_weights['tes'] ?? 0 }}%, 
                                                Prestasi: {{ $track->scoring_weights['prestasi'] ?? 0 }}%, 
                                                Tahfidz: {{ $track->scoring_weights['tahfidz'] ?? 0 }}%
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="p-4 text-slate-400 truncate max-w-[150px]">{{ $track->requirements ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-slate-400">Belum ada jalur seleksi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script>
        function trackForm() {
            return {
                weights: {
                    rapor: 40,
                    tes: 30,
                    prestasi: 15,
                    tahfidz: 15
                },
                errorMessage: '',
                totalWeight() {
                    return (this.weights.rapor || 0) + 
                           (this.weights.tes || 0) + 
                           (this.weights.prestasi || 0) + 
                           (this.weights.tahfidz || 0);
                },
                handleSubmit(e) {
                    if (this.totalWeight() !== 100) {
                        e.preventDefault();
                        this.errorMessage = 'Total bobot persentase harus tepat bernilai 100%.';
                    } else {
                        this.errorMessage = '';
                    }
                }
            };
        }
    </script>
</x-app-layout>
