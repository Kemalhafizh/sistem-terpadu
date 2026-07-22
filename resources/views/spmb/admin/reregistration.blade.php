<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Daftar Ulang - ' . $wave->name) }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('spmb.admin.dashboard') }}" class="px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-all">&larr; Kembali ke Dashboard</a>
                
                <form action="{{ route('spmb.admin.bulk-convert', $wave->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center gap-1.5">
                         Konversi Massal ke Akun Siswa Baru
                    </button>
                </form>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="font-black text-slate-800 text-sm">Status Daftar Ulang Calon Siswa Diterima</h3>
                    <span class="text-xs text-slate-400 font-semibold font-inter">Hanya calon siswa dengan status "DITERIMA" yang muncul di sini</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-150 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                <th class="p-4">No. Pendaftaran</th>
                                <th class="p-4">Nama Lengkap</th>
                                <th class="p-4">Jalur Seleksi</th>
                                <th class="p-4 text-center">Status Daftar Ulang</th>
                                <th class="p-4 text-center">Status Akun SISTA</th>
                                <th class="p-4 text-center">Aksi Konfirmasi</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y divide-slate-100">
                            @forelse($applicants as $app)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4 font-bold text-slate-500">{{ $app->registration_number }}</td>
                                    <td class="p-4 font-black text-slate-900">{{ $app->full_name }}</td>
                                    <td class="p-4 font-semibold text-slate-700">{{ $app->spmbTrack->name ?? 'Reguler' }}</td>
                                    <td class="p-4 text-center">
                                        @if($app->has_reregistered)
                                            <span class="px-2.5 py-1 bg-emerald-550 text-emerald-700 bg-emerald-50 border border-emerald-200 text-[10px] font-black rounded-lg">SUDAH BAYAR / DAFTAR ULANG</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-[10px] font-black rounded-lg border border-amber-150">BELUM DAFTAR ULANG</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center font-inter font-semibold">
                                        @if($app->user_id)
                                            <span class="text-emerald-600 font-bold"> AKTIF (User ID: {{ $app->user_id }})</span>
                                        @else
                                            <span class="text-slate-400">Belum Dikoneksi</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center">
                                        @if(!$app->has_reregistered)
                                            <form action="{{ route('spmb.admin.reregistration.confirm', $app->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-lg transition-all">Konfirmasi Bayar</button>
                                            </form>
                                        @else
                                            <span class="text-slate-350 text-[11px] font-bold">Terkonfirmasi</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400">Belum ada pendaftar yang diterima pada gelombang ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
