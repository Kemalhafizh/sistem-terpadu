<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Nilas Tes Seleksi - ' . $wave->name) }}
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

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-black text-slate-800 text-sm">Lembar Input Nilai Seleksi</h3>
                </div>

                <form action="{{ route('spmb.admin.scoring.store', $wave->id) }}" method="POST">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                    <th class="p-4">No. Pendaftaran</th>
                                    <th class="p-4">Nama Lengkap</th>
                                    <th class="p-4">Sekolah Asal</th>
                                    <th class="p-4">Jalur Seleksi</th>
                                    <th class="p-4 w-40">Nilai Ujian Masuk (0-100) <span class="text-red-500">*</span></th>
                                </tr>
                            </thead>
                            <tbody class="text-xs divide-y divide-slate-100">
                                @forelse($applicants as $app)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="p-4 font-bold text-slate-500">{{ $app->registration_number }}</td>
                                        <td class="p-4 font-black text-slate-900">{{ $app->full_name }}</td>
                                        <td class="p-4 text-slate-500 font-inter">{{ $app->previous_school }}</td>
                                        <td class="p-4 font-semibold text-slate-700">{{ $app->spmbTrack->name ?? 'Reguler' }}</td>
                                        <td class="p-4">
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                name="scores[{{ $app->id }}]" 
                                                value="{{ $app->entrance_test_score }}"
                                                min="0" 
                                                max="100" 
                                                required
                                                class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-center font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                            >
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-slate-400">Tidak ada pendaftar di gelombang ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($applicants->count() > 0)
                        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                            <button type="submit" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md transition-all">Simpan Nilai Seleksi</button>
                        </div>
                    @endif
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
