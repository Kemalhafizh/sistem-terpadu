<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Riwayat & Persetujuan Mutasi Siswa') }}
            </h2>
            @canVisit('mutations.create')
                <a href="{{ route('mutations.create') }}" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-bold text-xs shadow-sm hover:bg-primary-700 transition-colors">
                    + Catat Permohonan Mutasi
                </a>
            @endcanVisit
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40 text-xs font-semibold text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-4">Siswa &amp; Rombel</th>
                            <th class="px-6 py-4">Tipe &amp; Tanggal</th>
                            <th class="px-6 py-4">Alasan &amp; Sekolah Terkait</th>
                            <th class="px-6 py-4">Status &amp; Tagihan</th>
                            <th class="px-6 py-4 text-right">Berkas &amp; Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($mutations as $mut)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-gray-100">{{ $mut->student_display_name }}</div>
                                    <div class="text-xs text-gray-500">
                                        NIS: {{ $mut->student?->nis ?? ($mut->candidate_data['nis'] ?? '-') }}
                                        @if($mut->classroom)
                                            · Rombel: {{ $mut->classroom->name }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($mut->type === 'in')
                                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold">Mutasi Masuk</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-full text-xs font-semibold">Mutasi Keluar</span>
                                    @endif
                                    <div class="text-xs text-gray-500 mt-1">{{ $mut->mutation_date?->format('Y-m-d') ?? $mut->mutation_date }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800 dark:text-gray-200">{{ $mut->reason }}</div>
                                    <div class="text-xs text-gray-500">{{ $mut->target_school ?: '-' }}</div>
                                    @if($mut->letter_number)
                                        <div class="text-xs text-gray-500">No. Surat: {{ $mut->letter_number }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 space-y-1">
                                    <div>
                                        @if($mut->status === 'approved')
                                            <span class="px-2.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold">Disetujui</span>
                                        @elseif($mut->status === 'rejected')
                                            <span class="px-2.5 py-0.5 rounded bg-red-50 text-red-700 text-xs font-semibold">Ditolak</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded bg-amber-50 text-amber-700 text-xs font-semibold">Menunggu Kepsek</span>
                                        @endif
                                    </div>
                                    @if($mut->type === 'out' && $mut->billing_review_status)
                                        <div class="text-xs text-gray-500">
                                            Tagihan:
                                            @if($mut->billing_review_status === 'needs_settlement')
                                                <span class="text-red-600 font-semibold">
                                                    Perlu Penyelesaian (Rp {{ number_format((float) ($mut->billing_review_summary['total_outstanding_amount'] ?? 0), 0, ',', '.') }})
                                                </span>
                                            @else
                                                <span class="text-emerald-600 font-semibold">Bersih / Selesai</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-y-2">
                                    @if($mut->document_path)
                                        <div>
                                            <a href="{{ route('mutations.document', $mut) }}" target="_blank" class="text-primary-600 hover:underline font-semibold text-xs">
                                                Lihat Berkas
                                            </a>
                                        </div>
                                    @endif

                                    @if($mut->status === 'pending')
                                        @canVisit('mutations.approve')
                                            <form action="{{ route('mutations.approve', $mut) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700">
                                                    Setujui
                                                </button>
                                            </form>
                                        @endcanVisit
                                    @endif

                                    @if($mut->type === 'out' && $mut->billing_review_status === 'needs_settlement')
                                        @canVisit('mutations.review-billings')
                                            <form action="{{ route('mutations.review-billings', $mut) }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="cancel_future_unpaid" value="1">
                                                <button type="submit" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-200">
                                                    Tinjau &amp; Batalkan Tagihan Mendatang
                                                </button>
                                            </form>
                                        @endcanVisit
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">Belum ada riwayat mutasi siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $mutations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
