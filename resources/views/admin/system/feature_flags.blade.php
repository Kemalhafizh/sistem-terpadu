<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                        </svg>
                    </span>
                    Zero-Downtime Feature Flag Engine
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pengaktifan sakelar fitur instan, peluncuran bertahap (Percentage Rollout), dan penargetan peran pengguna.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($flags as $flag)
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="px-2.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded font-mono text-[10px] font-bold">
                                    {{ $flag->key }}
                                </span>
                                <h3 class="text-base font-black text-gray-900 dark:text-white mt-1">{{ $flag->name }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $flag->description }}</p>
                            </div>

                            <form action="{{ route('admin.system.feature-flags.toggle', $flag) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 {{ $flag->is_active ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'bg-gray-200 dark:bg-gray-700 text-gray-500' }}">
                                    <span class="w-2 h-2 rounded-full {{ $flag->is_active ? 'bg-white animate-pulse' : 'bg-gray-400' }}"></span>
                                    {{ $flag->is_active ? 'ENABLED (ON)' : 'DISABLED (OFF)' }}
                                </button>
                            </form>
                        </div>

                        <form action="{{ route('admin.system.feature-flags.update', $flag) }}" method="POST" class="pt-4 border-t border-gray-100 dark:border-gray-700 space-y-4">
                            @csrf
                            <div>
                                <div class="flex justify-between text-xs font-bold mb-1">
                                    <span class="text-gray-700 dark:text-gray-300">Percentage Rollout:</span>
                                    <span class="font-mono text-indigo-500" x-data="{ pct: {{ $flag->rollout_percentage }} }" x-text="pct + '%'"></span>
                                </div>
                                <input type="range" name="rollout_percentage" min="0" max="100" value="{{ $flag->rollout_percentage }}" class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Penargetan Peran (Target Roles)</label>
                                <div class="flex flex-wrap gap-2 text-xs">
                                    @php
                                        $rolesList = ['admin', 'superadmin', 'guru', 'bendahara', 'staf_tu', 'student', 'wali_murid'];
                                        $currentRoles = $flag->target_roles ?? [];
                                    @endphp
                                    @foreach($rolesList as $role)
                                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer">
                                            <input type="checkbox" name="target_roles[]" value="{{ $role }}" {{ in_array($role, $currentRoles) ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500">
                                            <span class="font-mono text-[10px] uppercase font-bold text-gray-700 dark:text-gray-300">{{ $role }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition-all shadow-md">
                                Simpan Konfigurasi Rollout
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="col-span-2 p-12 bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 text-center text-gray-400 text-xs">
                        Belum ada Feature Flag terdaftar dalam sistem.
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
