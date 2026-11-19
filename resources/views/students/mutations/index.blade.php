<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Riwayat Mutasi Siswa') }}
            </h2>
            <a href="{{ route('mutations.create') }}" class="px-6 py-2 bg-primary-600 text-white rounded-xl font-black text-xs shadow-lg shadow-primary-200 hover:bg-primary-700 transition-all">+ Catat Mutasi Baru</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Siswa</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tipe Mutasi</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Alasan / Tujuan</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($mutations as $mut)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="font-bold text-gray-900">{{ $mut?->student?->user?->name }}</div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase">NIS: {{ $mut?->student?->nis }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    @if($mut?->type == 'in')
                                        <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-tighter">Mutasi Masuk</span>
                                    @else
                                        <span class="px-3 py-1 bg-red-50 text-red-600 rounded-full text-[10px] font-black uppercase tracking-tighter">Mutasi Keluar</span>
                                    @endif
                                </td>
                                <td class="px-8 py-5">
                                    <span class="text-sm font-medium text-gray-600">{{ $mut?->mutation_date }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-xs font-bold text-gray-700">{{ $mut?->reason }}</div>
                                    <div class="text-[10px] text-gray-400 italic">{{ $mut?->target_school ?? '-' }}</div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    @if($mut?->document_path)
                                        <a href="{{ route('mutations.document', $mut) }}" target="_blank" class="text-primary-600 hover:text-primary-800 font-black text-[10px] uppercase underline tracking-widest">Lihat Berkas</a>
                                    @else
                                        <span class="text-[10px] font-bold text-gray-300 uppercase">Tidak Ada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-12 text-center text-gray-400 italic text-sm">Belum ada riwayat mutasi siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
