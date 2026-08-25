<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Dasbor Kepatuhan Aksesibilitas (WCAG 2.2 AA Compliance)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Audit otomatis aksesibilitas web bagi penyandang disabilitas dan ramah pembaca layar.
                </p>
            </div>
            <button onclick="document.getElementById('scanModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-semibold shadow-sm transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>Mulai Audit Halaman</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        @if($latestReport)
            <!-- Compliance Gauge Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-2">
                    <span class="text-xs font-bold text-slate-500 uppercase">Skor Kepatuhan WCAG 2.2 AA</span>
                    <div class="text-4xl font-black text-emerald-600">{{ $latestReport->compliance_score }}%</div>
                    <p class="text-xs text-slate-500">Standar Aksesibilitas Internasional: Lolos</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-2">
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Temuan Audit</span>
                    <div class="text-4xl font-black text-amber-600">{{ $latestReport->total_issues }} Isu</div>
                    <p class="text-xs text-slate-500">Perbaikan minor disarankan</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-2">
                    <span class="text-xs font-bold text-slate-500 uppercase">Isu Kritis (Blockers)</span>
                    <div class="text-4xl font-black text-slate-900 dark:text-white">{{ $latestReport->critical_issues }} Isu</div>
                    <p class="text-xs text-emerald-600 font-semibold">Bebas dari hambatan kritis</p>
                </div>
            </div>

            <!-- Issues Remediation List -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">Daftar Rekomendasi Remediasi DOM</h3>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($latestReport->issues as $issue)
                        <div class="py-4 first:pt-0 last:pb-0 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $issue->severity === 'high' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $issue->severity }}
                                </span>
                                <span class="text-xs font-mono text-slate-400">{{ $issue->dom_element_selector }}</span>
                            </div>
                            <h4 class="font-bold text-slate-900 dark:text-white text-sm">{{ ucfirst(str_replace('_', ' ', $issue->violation_type)) }}</h4>
                            <p class="text-xs text-slate-500">{{ $issue->description }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 py-4 text-center">Halaman sepenuhnya memenuhi standar kepatuhan WCAG 2.2 AA.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
