<x-app-layout>
    <x-slot name="title">Organogram Sekolah - SISTA</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Struktur Organisasi Sekolah (Organogram)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Bagan hierarki komando dan koordinasi kerja KBM {{ school_name() }}.
                </p>
            </div>
            <a href="{{ route('hrd.dashboard') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="bg-white/85 dark:bg-gray-800/85 backdrop-blur-md p-8 rounded-[2rem] border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-x-auto min-h-[600px] flex flex-col items-center">
        <!-- CSS Tree Style -->
        <style>
            .org-tree {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 24px;
                width: 100%;
            }
            .org-row {
                display: flex;
                justify-content: center;
                gap: 20px;
                width: 100%;
                flex-wrap: wrap;
                position: relative;
            }
            .org-card {
                background: rgba(255, 255, 255, 0.9);
                border: 1px solid rgba(229, 231, 235, 0.8);
                border-radius: 20px;
                padding: 16px;
                width: 220px;
                text-align: center;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
                transition: all 0.3s ease;
            }
            .dark .org-card {
                background: rgba(31, 41, 55, 0.9);
                border-color: rgba(75, 85, 99, 0.8);
            }
            .org-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.1);
                border-color: rgba(99, 102, 241, 0.5);
            }
            .connector-v {
                width: 2px;
                height: 24px;
                background-color: #cbd5e1;
            }
            .dark .connector-v {
                background-color: #4b5563;
            }
        </style>

        <div class="org-tree">
            <!-- 1. Kepala Sekolah (Top Level) -->
            <div class="org-row">
                <div class="org-card border-t-4 border-t-indigo-600">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-full flex items-center justify-center font-black mx-auto mb-2 text-sm shadow-sm">
                        
                    </div>
                    <h4 class="font-extrabold text-xs text-gray-800 dark:text-white leading-tight">
                        {{ $kepsek->user->name ?? (school_principal_name() ?: 'Kepala sekolah belum ditetapkan') }}
                    </h4>
                    <span class="block text-[9px] text-gray-400 font-bold uppercase mt-1">Kepala Sekolah</span>
                    <p class="text-[9px] text-indigo-500 font-bold mt-0.5">{{ $kepsek->employee_number ?? 'ADM-2026-0001' }}</p>
                </div>
            </div>

            <div class="connector-v"></div>

            <!-- 2. Jajaran Wakil Kepala Sekolah (Mid Level) -->
            <div class="org-row">
                @forelse($wakas as $wk)
                    <div class="org-card border-t-4 border-t-blue-500">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full flex items-center justify-center font-black mx-auto mb-2 text-xs">
                            
                        </div>
                        <h4 class="font-extrabold text-[11px] text-gray-800 dark:text-white leading-tight">
                            {{ $wk->user->name }}
                        </h4>
                        <span class="block text-[8px] text-gray-400 font-bold uppercase mt-1">{{ $wk->position }}</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic">Belum ada data Wakil Kepala Sekolah terdaftar.</p>
                @endforelse
            </div>

            <div class="connector-v"></div>

            <!-- 3. Staf TU & BK -->
            <div class="org-row">
                @forelse($staffAndBk as $st)
                    <div class="org-card border-t-4 border-t-purple-500">
                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 rounded-full flex items-center justify-center font-black mx-auto mb-2 text-xs">
                            
                        </div>
                        <h4 class="font-extrabold text-[11px] text-gray-800 dark:text-white leading-tight">
                            {{ $st->user->name }}
                        </h4>
                        <span class="block text-[8px] text-gray-400 font-bold uppercase mt-1">{{ $st->position }}</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic">Belum ada Staf TU/BK terdaftar.</p>
                @endforelse
            </div>

            <div class="connector-v"></div>

            <!-- 4. Tenaga Pengajar (Guru) -->
            <div class="org-row">
                @forelse($gurus as $gr)
                    <div class="org-card border-t-4 border-t-emerald-500">
                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full flex items-center justify-center font-black mx-auto mb-2 text-xs">
                            
                        </div>
                        <h4 class="font-extrabold text-[11px] text-gray-800 dark:text-white leading-tight">
                            {{ $gr->user->name }}
                        </h4>
                        <span class="block text-[8px] text-gray-400 font-bold uppercase mt-1">Guru / Tenaga Pengajar</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic">Belum ada data Guru terdaftar.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
