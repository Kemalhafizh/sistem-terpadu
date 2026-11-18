<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Periode SPMB') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8">

            <div class="mb-6 flex gap-2">
                <a href="{{ route('spmb.admin.dashboard') }}" class="px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-all">&larr; Kembali ke Dashboard</a>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Form Tambah Periode -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit">
                    <h3 class="font-black text-slate-800 text-sm mb-4">Tambah Periode Baru</h3>
                    
                    <form action="{{ route('spmb.admin.periods.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="academic_year_id" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tahun Ajaran <span class="text-red-500">*</span></label>
                            <select name="academic_year_id" id="academic_year_id" required class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->year }} ({{ $year->semester }})</option>
                                @endforeach
                            </select>
                            @error('academic_year_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Periode <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" required placeholder="Misal: SPMB 2026/2027" class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="total_quota" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Total Kuota Penerimaan <span class="text-red-500">*</span></label>
                            <input type="number" name="total_quota" id="total_quota" required min="1" placeholder="100" class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            @error('total_quota') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status <span class="text-red-500">*</span></label>
                            <select name="status" id="status" required class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="draft">Draft (Mempersiapkan)</option>
                                <option value="open">Open (Membuka Pendaftaran)</option>
                                <option value="closed">Closed (Ditutup)</option>
                                <option value="announced">Announced (Hasil Diumumkan)</option>
                            </select>
                            @error('status') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md transition-all">Simpan Periode</button>
                    </form>
                </div>

                <!-- Daftar Periode -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 bg-slate-50">
                        <h3 class="font-black text-slate-800 text-sm">Daftar Periode SPMB</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                    <th class="p-4">Nama Periode</th>
                                    <th class="p-4">Tahun Ajaran</th>
                                    <th class="p-4">Kuota</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4">Dibuat Pada</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs divide-y divide-slate-100">
                                @forelse($periods as $period)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="p-4 font-black text-slate-900">{{ $period->name }}</td>
                                        <td class="p-4 font-bold text-slate-500">{{ $period->academicYear->year ?? '-' }}</td>
                                        <td class="p-4 font-inter font-semibold text-slate-700">{{ $period->total_quota }} Siswa</td>
                                        <td class="p-4">
                                            @if($period->status === 'open')
                                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-lg border border-emerald-200">ACTIVE / OPEN</span>
                                            @elseif($period->status === 'closed')
                                                <span class="px-2.5 py-1 bg-red-50 text-red-700 text-[10px] font-black rounded-lg border border-red-200">CLOSED</span>
                                            @else
                                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-[10px] font-black rounded-lg border border-slate-200">{{ strtoupper($period->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-slate-400 font-inter">{{ $period->created_at->format('d-m-Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-slate-400">Belum ada periode pendaftaran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
