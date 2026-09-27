<x-app-layout>
    <x-slot name="title">Radar Kadaluarsa Dokumen & Sertifikat SISTA</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Radar Kadaluarsa Dokumen & Sertifikasi
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Pemantauan masa aktif dokumen Yayasan, SK tugas, dan sertifikat profesional guru {{ school_name() }}.
                </p>
            </div>
            <a href="{{ route('hrd.dashboard') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 1. Sertifikasi / Pelatihan -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm"> Expiry Sertifikat Pendidik & Pelatihan (90 Hari)</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700/50 p-6 space-y-4 max-h-[500px] overflow-y-auto pr-2">
                @forelse($expiringTrainings as $tr)
                    <div class="pt-3 first:pt-0 flex justify-between items-center text-xs">
                        <div>
                            <span class="font-bold text-gray-850 dark:text-white">{{ $tr->employee->user->name }}</span>
                            <span class="text-[10px] text-gray-450 ml-1">({{ $tr->employee->position }})</span>
                            <p class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold mt-1">
                                 {{ $tr->title }} (No: {{ $tr->certificate_number ?? '-' }})
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 font-extrabold text-[10px] rounded-lg">
                                Expiry: {{ $tr->certificate_expiry->format('d M Y') }}
                            </span>
                            <p class="text-[10px] text-gray-400 mt-1">{{ now()->diffInDays($tr->certificate_expiry) }} hari lagi</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic text-center py-12">Tidak ada sertifikat pelatihan guru yang mendekati masa kadaluarsa.</p>
                @endforelse
            </div>
        </div>

        <!-- 2. Dokumen / Kontrak SK -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm"> Expiry Kontrak Kerja & SK Pengangkatan (90 Hari)</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700/50 p-6 space-y-4 max-h-[500px] overflow-y-auto pr-2">
                @forelse($expiringDocuments as $doc)
                    <div class="pt-3 first:pt-0 flex justify-between items-center text-xs">
                        <div>
                            <span class="font-bold text-gray-850 dark:text-white">{{ $doc->employee->user->name }}</span>
                            <span class="text-[10px] text-gray-450 ml-1">({{ $doc->employee->position }})</span>
                            <p class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold mt-1">
                                 {{ $doc->title }} (Tipe: {{ strtoupper($doc->type) }})
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 font-extrabold text-[10px] rounded-lg">
                                Expiry: {{ $doc->expiry_date->format('d M Y') }}
                            </span>
                            <p class="text-[10px] text-gray-400 mt-1">{{ now()->diffInDays($doc->expiry_date) }} hari lagi</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic text-center py-12">Semua kontrak SK yayasan dan dokumen identitas berstatus aman.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
