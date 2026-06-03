<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-gray-100 leading-tight uppercase tracking-tight flex items-center">
                    <a href="{{ route('import-export.index') }}" class="mr-3 hover:text-blue-500 transition-colors" title="Kembali ke Hub">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    Petakan Kolom Data (Mapping Wizard)
                </h2>
                <p class="text-xs font-semibold text-gray-400 mt-1 uppercase tracking-wider">
                    Selesaikan pencocokan kolom berkas untuk modul <span class="text-blue-500">{{ ucfirst($module) }}</span>
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 rounded-full text-[10px] font-black uppercase tracking-wider">
                    {{ $totalRows }} Baris Terdeteksi
                </span>
                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-gray-400 rounded-full text-[10px] font-black uppercase tracking-wider">
                    {{ count($headers) }} Kolom Berkas
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto" x-data="{ loading: false }">
        <!-- Main Form -->
        <form action="{{ route('import-export.execute') }}" method="POST" @submit="loading = true">
            @csrf
            <input type="hidden" name="module" value="{{ $module }}">
            <input type="hidden" name="file_path" value="{{ $path }}">
            <input type="hidden" name="file_name" value="{{ $fileName }}">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Left: Mappings Card (2 Cols width) -->
                <div class="lg:col-span-2 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl p-6 md:p-8">
                    <h3 class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700 pb-4 mb-6">
                        Pencocokan Kolom Database SISTA
                    </h3>
                    
                    <div class="space-y-6">
                        @foreach($dbColumns as $field => $fieldConfig)
                            @php
                                // Smart Header Auto-Matching
                                $preselectedIndex = '';
                                foreach ($headers as $idx => $header) {
                                    $cleanHeader = strtolower(trim((string)$header));
                                    $cleanField = strtolower((string)$field);
                                    $cleanLabel = strtolower((string)$fieldConfig['label']);
                                    
                                    if ($cleanHeader === $cleanField || 
                                        $cleanHeader === $cleanLabel || 
                                        str_contains($cleanHeader, $cleanField) || 
                                        str_contains($cleanField, $cleanHeader) ||
                                        str_contains($cleanLabel, $cleanHeader) ||
                                        str_contains($cleanHeader, 'nama') && $cleanField === 'name' ||
                                        str_contains($cleanHeader, 'kelas') && $cleanField === 'classroom'
                                    ) {
                                        $preselectedIndex = $idx;
                                        break;
                                    }
                                }
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center border-b border-gray-50 dark:border-gray-700/40 pb-5 last:border-0 last:pb-0">
                                <!-- Field Info -->
                                <div class="md:col-span-1">
                                    <div class="flex items-center space-x-1.5">
                                        <span class="text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-wide">
                                            {{ $fieldConfig['label'] }}
                                        </span>
                                        @if($fieldConfig['required'])
                                            <span class="text-red-500 font-bold" title="Wajib Diisi">*</span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1 leading-relaxed">{{ $fieldConfig['description'] }}</p>
                                </div>

                                <!-- Field Selector -->
                                <div class="md:col-span-2">
                                    <div class="relative">
                                        <select name="mapping[{{ $field }}]" 
                                                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs font-semibold text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer"
                                                {{ $fieldConfig['required'] ? 'required' : '' }}
                                        >
                                            <option value="">-- Lewati Kolom Ini / Skip Column --</option>
                                            @foreach($headers as $idx => $header)
                                                <option value="{{ $idx }}" {{ $preselectedIndex !== '' && $preselectedIndex == $idx ? 'selected' : '' }}>
                                                    Kolom {{ $idx + 1 }}: "{{ $header }}"
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-6">
                        <a href="{{ route('import-export.index') }}" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-6 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-500/20 hover:shadow-blue-500/30 transition-all flex items-center"
                                :disabled="loading"
                        >
                            <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="loading ? 'Memproses Integrasi...' : 'Eksekusi & Impor Sekarang'"></span>
                        </button>
                    </div>
                </div>

                <!-- Right: File Preview (1 Col width) -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- File Info Card -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl p-6">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-4">Informasi Berkas</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between border-b border-gray-50 dark:border-gray-700 pb-2">
                                <span class="text-[11px] font-semibold text-gray-400 uppercase">Nama File:</span>
                                <span class="text-[11px] font-bold text-gray-700 dark:text-gray-200 truncate max-w-[150px]">{{ $fileName }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 dark:border-gray-700 pb-2">
                                <span class="text-[11px] font-semibold text-gray-400 uppercase">Format Berkas:</span>
                                <span class="text-[11px] font-black text-blue-500 uppercase">{{ pathinfo($fileName, PATHINFO_EXTENSION) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[11px] font-semibold text-gray-400 uppercase">Total Data:</span>
                                <span class="text-[11px] font-black text-gray-800 dark:text-gray-100">{{ $totalRows }} Baris</span>
                            </div>
                        </div>
                    </div>

                    <!-- Raw Data Preview Card -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl p-6 overflow-hidden">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-4">Pratinjau Data Asli (File Preview)</h4>
                        
                        <div class="overflow-x-auto max-h-96">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-700">
                                        @foreach(array_slice($headers, 0, 3) as $h)
                                            <th class="pb-2 text-[9px] font-black text-gray-400 uppercase tracking-wider truncate max-w-[80px]">{{ $h }}</th>
                                        @endforeach
                                        @if(count($headers) > 3)
                                            <th class="pb-2 text-[9px] font-black text-gray-400 uppercase tracking-wider text-right">...</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                                    @foreach(array_slice($preview, 0, 5) as $row)
                                        <tr>
                                            @foreach(array_slice($row, 0, 3) as $val)
                                                <td class="py-2 text-[10px] font-medium text-gray-600 dark:text-gray-300 truncate max-w-[80px]" title="{{ $val }}">
                                                    {{ $val }}
                                                </td>
                                            @endforeach
                                            @if(count($row) > 3)
                                                <td class="py-2 text-[10px] font-black text-gray-400 text-right">+{{ count($row) - 3 }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-[9px] font-semibold text-gray-400 uppercase mt-4 text-center">Menampilkan maksimal 5 baris data teratas berkas</p>
                    </div>
                </div>

            </div>
        </form>
    </div>
</x-app-layout>
