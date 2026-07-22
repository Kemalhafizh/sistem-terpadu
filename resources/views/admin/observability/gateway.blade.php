<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-2xl border border-rose-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </span>
                    API Gateway, Rate Limiting & Circuit Breakers Monitor
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pemantauan status integrasi pihak ketiga, sakelar proteksi eror (Circuit Breakers), dan kebijakan Rate Limit per peran.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Circuit Breaker Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($circuits as $circuit)
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">{{ $circuit['name'] }}</h3>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $circuit['badge'] }}">
                                {{ $circuit['state'] }}
                            </span>
                        </div>

                        <div class="space-y-1 text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex justify-between">
                                <span>Total Eror Beruntun:</span>
                                <strong class="font-mono text-gray-900 dark:text-white">{{ $circuit['failures'] }} / 5</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>Waktu Circuit Terbuka:</span>
                                <span class="font-mono text-gray-900 dark:text-white">{{ $circuit['opened_at'] ?? 'Tidak Pernah' }}</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
                            <form action="{{ route('admin.observability.gateway.reset') }}" method="POST" class="w-1/2">
                                @csrf
                                <input type="hidden" name="service" value="{{ $circuit['key'] }}">
                                <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-[10px] uppercase transition-all shadow-md">
                                    Reset Normal
                                </button>
                            </form>

                            <form action="{{ route('admin.observability.gateway.simulate-failure') }}" method="POST" class="w-1/2">
                                @csrf
                                <input type="hidden" name="service" value="{{ $circuit['key'] }}">
                                <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-[10px] uppercase transition-all shadow-md">
                                    Simulasi 5x Eror
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Rate Limiting Rules Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                <div>
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Kebijakan Rate Limiting Per Peran (Role-Based Token Bucket)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Mencegah serangan DDoS dan kelebihan beban server dengan membatasi jumlah request HTTP per menit.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase text-[9px] font-bold">
                            <tr>
                                <th class="p-3 rounded-l-xl">Peran Pengguna (Role)</th>
                                <th class="p-3">Batas Maksimal Request</th>
                                <th class="p-3">Window Time</th>
                                <th class="p-3">Respon Header</th>
                                <th class="p-3 text-right rounded-r-xl">Status Kebijakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-750 font-medium">
                            <tr>
                                <td class="p-3 font-bold text-gray-900 dark:text-white">Admin & Superadmin</td>
                                <td class="p-3 font-mono font-bold text-indigo-600">300 Request / Min</td>
                                <td class="p-3">60 Detik</td>
                                <td class="p-3 font-mono text-[10px] text-gray-400">X-RateLimit-Limit: 300</td>
                                <td class="p-3 text-right"><span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-500 rounded-full font-bold text-[9px]">AKTIF</span></td>
                            </tr>
                            <tr>
                                <td class="p-3 font-bold text-gray-900 dark:text-white">Guru & Staf TU</td>
                                <td class="p-3 font-mono font-bold text-indigo-600">120 Request / Min</td>
                                <td class="p-3">60 Detik</td>
                                <td class="p-3 font-mono text-[10px] text-gray-400">X-RateLimit-Limit: 120</td>
                                <td class="p-3 text-right"><span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-500 rounded-full font-bold text-[9px]">AKTIF</span></td>
                            </tr>
                            <tr>
                                <td class="p-3 font-bold text-gray-900 dark:text-white">Siswa & Wali Murid</td>
                                <td class="p-3 font-mono font-bold text-indigo-600">60 Request / Min</td>
                                <td class="p-3">60 Detik</td>
                                <td class="p-3 font-mono text-[10px] text-gray-400">X-RateLimit-Limit: 60</td>
                                <td class="p-3 text-right"><span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-500 rounded-full font-bold text-[9px]">AKTIF</span></td>
                            </tr>
                            <tr>
                                <td class="p-3 font-bold text-gray-900 dark:text-white">Public / Guest / Anonymous</td>
                                <td class="p-3 font-mono font-bold text-rose-600">20 Request / Min</td>
                                <td class="p-3">60 Detik</td>
                                <td class="p-3 font-mono text-[10px] text-gray-400">X-RateLimit-Limit: 20</td>
                                <td class="p-3 text-right"><span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-500 rounded-full font-bold text-[9px]">AKTIF</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
