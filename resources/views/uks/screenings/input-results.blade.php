<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('uks.screenings.index') }}" class="hover:text-teal-600 transition-colors">
                <span class="w-8 h-8 bg-teal-100 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </span>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Input Hasil Screening: ' . $screening->title) }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">{{ mb_strtoupper(school_short_name()) }} • BATCH INPUT HASIL PEMERIKSAAN</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            
            <form method="POST" action="{{ route('uks.screenings.results.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="screening_id" value="{{ $screening->id }}">

                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-6">
                    <div class="flex justify-between items-center flex-wrap gap-4">
                        <div>
                            <h4 class="text-base font-black text-gray-900 dark:text-white flex items-center">
                                <span class="w-2 h-5 bg-teal-600 rounded-full mr-3"></span>
                                Daftar Murid Sasaran Pemeriksaan
                            </h4>
                            <p class="text-xs text-gray-450 dark:text-gray-550 uppercase tracking-widest mt-1">Harap isi tinggi, berat, penglihatan, dan dental siswa.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-700/50 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    <th class="pb-3 w-1/5">Siswa</th>
                                    <th class="pb-3 text-center w-[100px]">TB (cm)</th>
                                    <th class="pb-3 text-center w-[100px]">BB (kg)</th>
                                    <th class="pb-3 text-center w-[100px]">Mata Kiri (V.L)</th>
                                    <th class="pb-3 text-center w-[100px]">Mata Kanan (V.R)</th>
                                    <th class="pb-3 text-center w-[120px]">Kondisi Gigi</th>
                                    <th class="pb-3 w-1/4">Temuan & Rekomendasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-55/30 dark:divide-gray-700/30">
                                @forelse($students as $index => $std)
                                    @php
                                        $existing = $existingResults->get($std->id);
                                    @endphp
                                    <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-all">
                                        <td class="py-4">
                                            <input type="hidden" name="results[{{ $index }}][student_id]" value="{{ $std->id }}">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center shrink-0 font-extrabold text-xs mr-3">
                                                    {{ substr($std->user->name ?? '-', 0, 1) }}
                                                </div>
                                                <div>
                                                    <h5 class="text-sm font-extrabold text-gray-900 dark:text-white">
                                                        {{ $std->user->name ?? '-' }}
                                                    </h5>
                                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold mt-0.5">
                                                        Kelas: {{ $std->classroom->name ?? '-' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Height -->
                                        <td class="py-4 text-center">
                                            <input type="number" step="0.1" name="results[{{ $index }}][height_cm]" 
                                                   value="{{ old("results.{$index}.height_cm", $existing->height_cm ?? '') }}" placeholder="TB"
                                                   class="w-[80px] text-center bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-xs focus:ring-teal-500 dark:text-gray-300">
                                        </td>

                                        <!-- Weight -->
                                        <td class="py-4 text-center">
                                            <input type="number" step="0.1" name="results[{{ $index }}][weight_kg]" 
                                                   value="{{ old("results.{$index}.weight_kg", $existing->weight_kg ?? '') }}" placeholder="BB"
                                                   class="w-[80px] text-center bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-xs focus:ring-teal-500 dark:text-gray-300">
                                        </td>

                                        <!-- Vision Left -->
                                        <td class="py-4 text-center">
                                            <input type="text" name="results[{{ $index }}][vision_left]" 
                                                   value="{{ old("results.{$index}.vision_left", $existing->vision_left ?? '') }}" placeholder="6/6"
                                                   class="w-[80px] text-center bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-xs focus:ring-teal-500 dark:text-gray-300">
                                        </td>

                                        <!-- Vision Right -->
                                        <td class="py-4 text-center">
                                            <input type="text" name="results[{{ $index }}][vision_right]" 
                                                   value="{{ old("results.{$index}.vision_right", $existing->vision_right ?? '') }}" placeholder="6/6"
                                                   class="w-[80px] text-center bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-xs focus:ring-teal-500 dark:text-gray-300">
                                        </td>

                                        <!-- Dental Status -->
                                        <td class="py-4 text-center">
                                            <input type="text" name="results[{{ $index }}][dental_status]" 
                                                   value="{{ old("results.{$index}.dental_status", $existing->dental_status ?? '') }}" placeholder="Sehat/Karies"
                                                   class="w-[100px] text-center bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-xs focus:ring-teal-500 dark:text-gray-300">
                                        </td>

                                        <!-- Findings & Recommendations -->
                                        <td class="py-4 space-y-2">
                                            <input type="text" name="results[{{ $index }}][findings]" 
                                                   value="{{ old("results.{$index}.findings", $existing->findings ?? '') }}" placeholder="Temuan (Karies gigi, Miopi, dll)"
                                                   class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2.5 py-1 text-xs focus:ring-teal-500 dark:text-gray-300">
                                            <input type="text" name="results[{{ $index }}][recommendations]" 
                                                   value="{{ old("results.{$index}.recommendations", $existing->recommendations ?? '') }}" placeholder="Rekomendasi (Rujuk dokter gigi, Kacamata, dll)"
                                                   class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2.5 py-1 text-xs focus:ring-teal-500 dark:text-gray-300">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-gray-400 dark:text-gray-600 italic">Tidak ada siswa aktif di kelas target saat ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Form submission bar -->
                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('uks.screenings.index') }}" class="px-6 py-3 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Kembali
                    </a>
                    <button type="submit" class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/15">
                        Simpan Hasil Screening
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
