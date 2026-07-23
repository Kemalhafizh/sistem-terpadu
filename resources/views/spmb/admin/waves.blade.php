<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Gelombang SPMB') }}
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
                
                <!-- Form Tambah Gelombang -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit">
                    <h3 class="font-black text-slate-800 text-sm mb-4">Tambah Gelombang Baru</h3>
                    
                    <form action="{{ route('spmb.admin.waves.store') }}" method="POST" class="space-y-4">
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
                            <label for="name" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Gelombang <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" required placeholder="Misal: Gelombang 1" class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="registration_start" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Mulai Daftar <span class="text-red-500">*</span></label>
                                <input type="date" name="registration_start" id="registration_start" required class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                @error('registration_start') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="registration_end" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tutup Daftar <span class="text-red-500">*</span></label>
                                <input type="date" name="registration_end" id="registration_end" required class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                @error('registration_end') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="announcement_date" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tanggal Pengumuman Kelulusan</label>
                            <input type="date" name="announcement_date" id="announcement_date" class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            @error('announcement_date') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="quota" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Kuota Gelombang <span class="text-red-500">*</span></label>
                                <input type="number" name="quota" id="quota" required min="1" placeholder="50" class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                @error('quota') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="registration_fee" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Biaya Daftar (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" name="registration_fee" id="registration_fee" required min="0" placeholder="150000" class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                @error('registration_fee') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="status" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status <span class="text-red-500">*</span></label>
                            <select name="status" id="status" required class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="upcoming">Upcoming (Belum Mulai)</option>
                                <option value="open">Open (Buka Pendaftaran)</option>
                                <option value="closed">Closed (Ditutup)</option>
                                <option value="announced">Announced (Hasil Seleksi Rilis)</option>
                            </select>
                            @error('status') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md transition-all">Simpan Gelombang</button>
                    </form>
                </div>

                <!-- Daftar Gelombang -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 bg-slate-50">
                        <h3 class="font-black text-slate-800 text-sm">Daftar Gelombang Pendaftaran</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-150 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                    <th class="p-4">Nama Gelombang</th>
                                    <th class="p-4">Periode</th>
                                    <th class="p-4">Tanggal Pendaftaran</th>
                                    <th class="p-4">Kuota / Biaya</th>
                                    <th class="p-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs divide-y divide-slate-100">
                                @forelse($waves as $wave)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="p-4 font-black text-slate-900">{{ $wave->name }}</td>
                                        <td class="p-4 font-bold text-slate-500">{{ $wave->period->name ?? '-' }}</td>
                                        <td class="p-4 font-inter text-slate-600">
                                            {{ $wave->registration_start->format('d/m/Y') }} - {{ $wave->registration_end->format('d/m/Y') }}
                                        </td>
                                        <td class="p-4">
                                            <div class="font-semibold text-slate-700">{{ $wave->quota }} Kursi</div>
                                            <div class="text-[10px] text-slate-400 font-bold">Rp {{ number_format($wave->registration_fee, 0, ',', '.') }}</div>
                                        </td>
                                        <td class="p-4">
                                            @if($wave->status === 'open')
                                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-lg border border-emerald-150">OPEN</span>
                                            @elseif($wave->status === 'closed')
                                                <span class="px-2.5 py-1 bg-red-50 text-red-700 text-[10px] font-black rounded-lg border border-red-150">CLOSED</span>
                                            @else
                                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-[10px] font-black rounded-lg border border-slate-200">{{ strtoupper($wave->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-slate-400">Belum ada gelombang pendaftaran.</td>
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
