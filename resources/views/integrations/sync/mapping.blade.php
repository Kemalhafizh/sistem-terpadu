<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-700 dark:text-teal-400">Konfigurasi Sinkronisasi</span>
                <h2 class="font-black text-3xl text-gray-800 dark:text-white leading-tight tracking-tight mt-1">
                    Pemetaan Kolom Ekspor - {{ ucfirst($target) }} ({{ ucfirst($table === 'students' ? 'Siswa' : 'Guru') }})
                </h2>
            </div>
            <a href="{{ route('admin.integration.sync.dashboard') }}" 
               class="px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-150 flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    @php
        $targetMappingAssoc = [];
        foreach ($targetFields as $tKey => $tLabel) {
            $targetMappingAssoc[$tKey] = '';
        }
        foreach (($mapping ?? []) as $localKey => $targetKey) {
            if (isset($targetMappingAssoc[$targetKey])) {
                $targetMappingAssoc[$targetKey] = $localKey;
            }
        }
    @endphp

    <div class="py-12" x-data="{
        targetMapping: {{ json_encode($targetMappingAssoc) }},
        get mappingData() {
            let result = [];
            for (let [targetKey, localKey] of Object.entries(this.targetMapping)) {
                if (localKey) {
                    result.push({ localKey: localKey, targetKey: targetKey });
                }
            }
            return result;
        },
        get duplicates() {
            let counts = {};
            let dups = [];
            for (let localKey of Object.values(this.targetMapping)) {
                if (localKey) {
                    counts[localKey] = (counts[localKey] || 0) + 1;
                    if (counts[localKey] > 1 && !dups.includes(localKey)) {
                        dups.push(localKey);
                    }
                }
            }
            return dups;
        }
    }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <form action="{{ route('admin.integration.sync.mapping.store') }}" method="POST" class="space-y-8">
                @csrf
                <input type="hidden" name="system_target" value="{{ $target }}">
                <input type="hidden" name="local_table" value="{{ $table }}">

                <!-- Dynamically generated mapping inputs -->
                <template x-for="item in mappingData" :key="item.localKey">
                    <input type="hidden" :name="'mapping[' + item.localKey + ']'" :value="item.targetKey">
                </template>

                {{-- Alert for duplicates --}}
                <div x-show="duplicates.length > 0" 
                     class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/30 rounded-3xl p-5 shadow-sm flex items-start gap-4 animate-fade-in-up"
                     style="display: none;">
                    <div class="w-10 h-10 bg-amber-500/20 border border-amber-500/40 rounded-2xl flex items-center justify-center text-amber-600 shrink-0">
                        
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Peringatan Duplikasi Kolom</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                            Kolom database lokal SISTA berikut dipetakan ke lebih dari satu kolom target kementerian:
                            <span class="font-bold underline" x-text="duplicates.join(', ')"></span>.
                            Silakan sesuaikan agar pemetaan kolom bersifat unik.
                        </p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="pb-6 border-b border-gray-100 dark:border-gray-700 mb-8">
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Pemetaan Kolom Visual</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-widest font-black">Cocokkan kolom target kementerian (kiri) dengan kolom lokal SISTA (kanan)</p>
                    </div>

                    <div class="space-y-6">
                        @foreach($targetFields as $tKey => $tLabel)
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center p-4 bg-slate-50 dark:bg-gray-900/30 border border-slate-100 dark:border-gray-800 rounded-3xl transition-all duration-150 hover:border-teal-500/30">
                                
                                <!-- Target Field (Left) -->
                                <div class="md:col-span-5 flex flex-col justify-center">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $tLabel }}</span>
                                        <span x-show="targetMapping['{{ $tKey }}']" class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30 text-[9px] font-black uppercase tracking-wider rounded-md">
                                            Terpetakan
                                        </span>
                                        <span x-show="!targetMapping['{{ $tKey }}']" class="px-2 py-0.5 bg-slate-100 dark:bg-gray-900 text-slate-400 dark:text-gray-600 text-[9px] font-black uppercase tracking-wider rounded-md">
                                            Kosong
                                        </span>
                                    </div>
                                    <code class="text-[10px] text-teal-700 dark:text-teal-400 mt-1.5 font-bold uppercase tracking-wider">Target: {{ $tKey }}</code>
                                </div>

                                <!-- Arrow Indicator (Center) -->
                                <div class="hidden md:flex md:col-span-2 justify-center items-center">
                                    <div class="p-2 bg-white dark:bg-gray-800 rounded-full border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-center">
                                        <svg class="w-4 h-4 text-teal-600 dark:text-teal-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Dropdown local fields select (Right) -->
                                <div class="md:col-span-5">
                                    <div class="relative">
                                        <select x-model="targetMapping['{{ $tKey }}']" 
                                                class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-800 dark:text-gray-200 rounded-2xl py-3 pl-4 pr-10 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all shadow-sm appearance-none">
                                            <option value="">-- Lewati Kolom Ini (Kosong) --</option>
                                            @foreach($localFields as $localKey => $localLabel)
                                                <option value="{{ $localKey }}">{{ $localLabel }} ({{ $localKey }})</option>
                                            @endforeach
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-end gap-4">
                    <a href="{{ route('admin.integration.sync.dashboard') }}" 
                       class="w-full sm:w-auto px-6 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-gray-300 text-gray-700 dark:text-gray-300 rounded-2xl text-xs font-black uppercase tracking-wider text-center transition-all shadow-sm">
                        Batalkan
                    </a>
                    <button type="submit" 
                            class="w-full sm:w-auto px-8 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider text-center transition-all shadow-lg shadow-teal-600/20">
                        Simpan Pemetaan Kolom
                    </button>
                </div>

            </form>
            
        </div>
    </div>
</x-app-layout>
