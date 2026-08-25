<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Pengaturan Aksesibilitas & Desain Universal') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Sesuaikan kontras tinggi, font disleksia, ukuran teks, dan mode tema aplikasi.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-6">
            <!-- Color Mode Selection -->
            <div class="space-y-3">
                <h3 class="font-bold text-base text-slate-800 dark:text-white">Pilihan Mode Tampilan (Color Theme)</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach(['light' => 'Terang (Light)', 'dark' => 'Gelap (Dark)', 'system' => 'Otomatis Sistem', 'high_contrast' => 'Kontras Tinggi'] as $mode => $label)
                        <div class="p-4 rounded-2xl border-2 {{ ($preferences->color_mode ?? 'system') === $mode ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30' : 'border-slate-200 dark:border-slate-700' }} text-center cursor-pointer hover:border-emerald-500 transition-all">
                            <span class="text-xs font-bold text-slate-800 dark:text-white block">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Typography & Font Scaling -->
            <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <h3 class="font-bold text-base text-slate-800 dark:text-white">Penskalaan Ukuran Font</h3>
                <div class="flex items-center space-x-3">
                    @foreach(['90%', '100%', '115%', '130%'] as $scale)
                        <button class="px-4 py-2 rounded-xl text-xs font-bold {{ ($preferences->font_scaling ?? '100%') === $scale ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                            {{ $scale }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Dyslexic & Animation Toggles -->
            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <h3 class="font-bold text-base text-slate-800 dark:text-white">Fitur Aksesibilitas Khusus</h3>
                <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60">
                    <div>
                        <h4 class="font-bold text-xs text-slate-900 dark:text-white">Font Ramah Disleksia (OpenDyslexic)</h4>
                        <p class="text-[10px] text-slate-500">Meningkatkan keterbacaan teks bagi siswa dengan disleksia.</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                        {{ ($preferences->dyslexic_font_enabled ?? false) ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60">
                    <div>
                        <h4 class="font-bold text-xs text-slate-900 dark:text-white">Kurangi Gerakan & Animasi (Reduce Motion)</h4>
                        <p class="text-[10px] text-slate-500">Menonaktifkan transisi visual bagi pengguna sensitif gerak.</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                        {{ ($preferences->reduce_animations ?? false) ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
