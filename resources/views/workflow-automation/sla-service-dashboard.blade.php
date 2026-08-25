<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Dasbor Kepatuhan SLA & Tingkat Layanan') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Pemantauan response time, eskalasi otomatis, dan pencegahan pelanggaran batas waktu persetujuan.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- SLA Compliance Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-2">
                <span class="text-xs font-bold text-slate-500 uppercase">Skor Kepatuhan SLA Global</span>
                <div class="text-3xl font-black text-slate-900 dark:text-white">{{ $metrics['sla_compliance_score'] ?? 96.5 }}%</div>
                <p class="text-xs text-emerald-600 font-semibold">Taraf Pelayanan Standar ISO 9001:2015</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-2">
                <span class="text-xs font-bold text-slate-500 uppercase">Pelanggaran Batas Waktu Aktif</span>
                <div class="text-3xl font-black {{ ($metrics['active_breaches_count'] ?? 0) > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ $metrics['active_breaches_count'] ?? 0 }} Berkas
                </div>
                <p class="text-xs text-slate-500">Batas toleransi maksimal 24 jam kerja</p>
            </div>
        </div>

        <!-- Breaches List -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Daftar Peringatan Keterlambatan Persetujuan</h3>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($metrics['breaches'] ?? [] as $br)
                    <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white text-sm">{{ $br['approver_name'] }}</h4>
                            <p class="text-xs text-rose-600">Menunggu {{ $br['hours_waiting'] }} Jam (Batas: {{ $br['sla_limit'] }} Jam)</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                            Eskalasi Terpicu
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 py-4 text-center">Seluruh alur persetujuan berjalan sesuai standar SLA tanpa keterlambatan.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
