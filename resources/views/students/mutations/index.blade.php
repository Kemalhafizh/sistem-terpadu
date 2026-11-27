<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-slate-900 dark:text-slate-100 leading-tight">
                    {{ __('Riwayat & Persetujuan Mutasi Siswa') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ __('Pencatatan perpindahan keluar/masuk siswa, peninjauan tagihan, dan keputusan kepala sekolah') }}
                </p>
            </div>
            @canVisit('mutations.create')
                <a href="{{ route('mutations.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg font-medium text-sm shadow-sm hover:bg-primary-700 transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>{{ __('Catat Permohonan Mutasi') }}</span>
                </a>
            @endcanVisit
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200/80 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-start text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-600 dark:text-slate-400">
                            <tr>
                                <th class="px-5 py-3.5 text-start">{{ __('Siswa & Rombel') }}</th>
                                <th class="px-5 py-3.5 text-start">{{ __('Tipe & Tanggal') }}</th>
                                <th class="px-5 py-3.5 text-start">{{ __('Alasan & Sekolah Terkait') }}</th>
                                <th class="px-5 py-3.5 text-start">{{ __('Status & Tagihan') }}</th>
                                <th class="px-5 py-3.5 text-end">{{ __('Berkas & Keputusan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70">
                            @forelse($mutations as $mut)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors align-top">
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $mut->student_display_name }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 tabular-nums">
                                            {{ __('NIS') }}: {{ $mut->student?->nis ?? ($mut->candidate_data['nis'] ?? '-') }}
                                            @if($mut->classroom)
                                                &middot; {{ __('Rombel') }}: {{ $mut->classroom->name }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($mut->type === 'in')
                                            <span class="inline-flex items-center px-2.5 py-0.5 bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300 border border-sky-200/70 dark:border-sky-800/60 rounded-md text-xs font-medium">
                                                {{ __('Mutasi Masuk') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200/70 dark:border-rose-800/60 rounded-md text-xs font-medium">
                                                {{ __('Mutasi Keluar') }}
                                            </span>
                                        @endif
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 tabular-nums">
                                            {{ $mut->mutation_date?->format('Y-m-d') ?? $mut->mutation_date }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-slate-800 dark:text-slate-200">{{ $mut->reason }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $mut->target_school ?: '-' }}</div>
                                        @if($mut->letter_number)
                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 tabular-nums">{{ __('No. Surat') }}: {{ $mut->letter_number }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 space-y-1.5">
                                        <div>
                                            @if($mut->status === 'approved')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-800/60 text-xs font-medium">
                                                    {{ __('Disetujui') }}
                                                </span>
                                            @elseif($mut->status === 'rejected')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200/70 dark:border-rose-800/60 text-xs font-medium">
                                                    {{ __('Ditolak') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/70 dark:border-amber-800/60 text-xs font-medium">
                                                    {{ __('Menunggu Kepala Sekolah') }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($mut->type === 'out' && $mut->billing_review_status)
                                            <div class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                                                {{ __('Tagihan') }}:
                                                @if($mut->billing_review_status === 'needs_settlement')
                                                    <span class="text-rose-600 dark:text-rose-400 font-medium">
                                                        {{ __('Perlu Penyelesaian') }} (Rp {{ number_format((float) ($mut->billing_review_summary['total_outstanding_amount'] ?? 0), 0, ',', '.') }})
                                                    </span>
                                                @else
                                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ __('Bersih / Selesai') }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-end space-y-2">
                                        @if($mut->document_path)
                                            <div>
                                                <a href="{{ route('mutations.document', $mut) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-primary-600 dark:text-primary-400 hover:underline font-medium text-xs">
                                                    <span>{{ __('Lihat Berkas') }}</span>
                                                </a>
                                            </div>
                                        @endif

                                        @if($mut->status === 'pending')
                                            <div class="flex flex-col items-end gap-2">
                                                @canVisit('mutations.approve')
                                                    <form action="{{ route('mutations.approve', $mut) }}" method="POST" class="flex flex-wrap justify-end items-center gap-1.5">
                                                        @csrf
                                                        <input type="text" name="remarks" placeholder="{{ __('Catatan persetujuan (opsional)') }}" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-xs px-2.5 py-1.5 w-44">
                                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-medium hover:bg-emerald-700 transition-colors">
                                                            {{ __('Setujui') }}
                                                        </button>
                                                    </form>
                                                @endcanVisit

                                                @canVisit('mutations.reject')
                                                    <form action="{{ route('mutations.reject', $mut) }}" method="POST" class="flex flex-wrap justify-end items-center gap-1.5">
                                                        @csrf
                                                        <input type="text" name="reason" required placeholder="{{ __('Alasan penolakan (wajib)') }}" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-xs px-2.5 py-1.5 w-44">
                                                        <button type="submit" class="px-3 py-1.5 bg-rose-600 text-white rounded-lg text-xs font-medium hover:bg-rose-700 transition-colors">
                                                            {{ __('Tolak') }}
                                                        </button>
                                                    </form>
                                                @endcanVisit
                                            </div>
                                        @endif

                                        @if($mut->type === 'out' && $mut->billing_review_status === 'needs_settlement')
                                            @canVisit('mutations.review-billings')
                                                <form action="{{ route('mutations.review-billings', $mut) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <input type="hidden" name="cancel_future_unpaid" value="1">
                                                    <button type="submit" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                                        {{ __('Tinjau & Batalkan Tagihan Mendatang') }}
                                                    </button>
                                                </form>
                                            @endcanVisit
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400 text-sm">
                                        {{ __('Belum ada riwayat mutasi siswa.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                {{ $mutations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
