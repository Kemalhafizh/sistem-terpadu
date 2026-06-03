<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-200 leading-tight">
                <i class="fa-solid fa-shield-virus text-emerald-400 mr-2"></i> {{ __('Audit Kepatuhan Keamanan') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.security.dashboard') }}" id="btn-security-dashboard" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-medium rounded-lg transition-all duration-200 shadow-md flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke SIEM
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm flex items-center">
                    <i class="fa-solid fa-circle-check mr-2 text-lg"></i>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Top Cards Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Compliance Score Gauge & Overall Status -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl flex flex-col items-center justify-between">
                    <div class="text-center w-full">
                        <h3 class="text-md font-semibold text-slate-300">Skor Kepatuhan Keamanan</h3>
                        <p class="text-xs text-slate-400 mt-1">Status pengerasan parameter sistem saat ini</p>
                    </div>

                    <!-- Circular Progress Gauge -->
                    <div class="relative flex items-center justify-center my-6" x-data="{ score: {{ $report['compliance_score'] }} }">
                        <svg class="w-36 h-36 transform -rotate-90">
                            <!-- Background Circle -->
                            <circle cx="72" cy="72" r="62" stroke-width="10" stroke="rgba(51, 65, 85, 0.5)" fill="transparent" />
                            <!-- Foreground Circle -->
                            <circle cx="72" cy="72" r="62" stroke-width="10" 
                                    :stroke="score >= 90 ? '#10b981' : (score >= 70 ? '#f59e0b' : '#ef4444')" 
                                    stroke-dasharray="389.5" 
                                    :stroke-dashoffset="389.5 - (389.5 * score) / 100" 
                                    stroke-linecap="round"
                                    fill="transparent" 
                                    class="transition-all duration-1000 ease-out" />
                        </svg>
                        <div class="absolute text-center">
                            <span class="text-4xl font-extrabold text-white" x-text="score + '%'"></span>
                            <span class="block text-[10px] uppercase font-bold tracking-wider mt-0.5" 
                                  :class="score >= 90 ? 'text-emerald-400' : (score >= 70 ? 'text-amber-400' : 'text-rose-400')">
                                <span x-show="score >= 90">Sangat Aman</span>
                                <span x-show="score >= 70 && score < 90">Cukup Aman</span>
                                <span x-show="score < 70">Rentan</span>
                            </span>
                        </div>
                    </div>

                    <div class="text-center w-full bg-slate-900/40 border border-slate-700/30 rounded-xl p-4">
                        <span class="text-xs text-slate-400">Rekomendasi Utama:</span>
                        <p class="text-sm font-medium text-slate-200 mt-1">
                            @if($report['compliance_score'] === 100)
                                <i class="fa-solid fa-circle-check text-emerald-400 mr-1.5"></i> Seluruh parameter keamanan telah diperkeras sesuai standar SMA ISA 1.
                            @elseif(!$report['environment']['app_debug']['passed'])
                                <i class="fa-solid fa-triangle-exclamation text-rose-400 mr-1.5"></i> Matikan <strong>APP_DEBUG</strong> di environment produksi segera.
                            @elseif(!$report['routes']['passed'])
                                <i class="fa-solid fa-shield text-amber-400 mr-1.5"></i> Amankan rute-rute administrasi sensitif yang tidak memiliki middleware autentikasi.
                            @elseif(!$report['rate_limiting']['login_throttled'])
                                <i class="fa-solid fa-lock text-amber-400 mr-1.5"></i> Aktifkan rate limiting pada form login untuk mencegah serangan brute force.
                            @else
                                <i class="fa-solid fa-circle-info text-indigo-400 mr-1.5"></i> Selesaikan sisa pemeriksaan parameter untuk mencapai kepatuhan 100%.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Category Summary Audit List -->
                <div class="lg:col-span-2 bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
                    <div>
                        <h3 class="text-md font-semibold text-slate-200 mb-4 flex items-center">
                            <i class="fa-solid fa-list-check text-indigo-400 mr-2"></i> Ringkasan Kategori Audit
                        </h3>
                        <div class="space-y-4">
                            <!-- Environment Variables Security -->
                            <div class="flex items-center justify-between p-3.5 bg-slate-900/25 border border-slate-700/40 rounded-xl hover:bg-slate-900/40 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 rounded-lg bg-indigo-500/10 border border-indigo-500/25 text-indigo-400">
                                        <i class="fa-solid fa-cube text-sm"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-white">Environment & Konfigurasi Dasar</h4>
                                        <p class="text-xs text-slate-400 mt-0.5">Memvalidasi debug mode, environment environment, dan app key.</p>
                                    </div>
                                </div>
                                @php
                                    $envPassed = collect($report['environment'])->every('passed', true);
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $envPassed ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20' }}">
                                    {{ $envPassed ? 'Lulus' : 'Ada Masalah' }}
                                </span>
                            </div>

                            <!-- Session Config Security -->
                            <div class="flex items-center justify-between p-3.5 bg-slate-900/25 border border-slate-700/40 rounded-xl hover:bg-slate-900/40 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 rounded-lg bg-teal-500/10 border border-teal-500/25 text-teal-400">
                                        <i class="fa-solid fa-cookie text-sm"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-white">Keamanan Session & Cookie</h4>
                                        <p class="text-xs text-slate-400 mt-0.5">Enkripsi sesi, HttpOnly cookie, secure HTTPS, dan parameter SameSite.</p>
                                    </div>
                                </div>
                                @php
                                    $sessionPassed = collect($report['session'])->every('passed', true);
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $sessionPassed ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20' }}">
                                    {{ $sessionPassed ? 'Lulus' : 'Ada Masalah' }}
                                </span>
                            </div>

                            <!-- Password Policy Strength -->
                            <div class="flex items-center justify-between p-3.5 bg-slate-900/25 border border-slate-700/40 rounded-xl hover:bg-slate-900/40 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/25 text-emerald-400">
                                        <i class="fa-solid fa-key text-sm"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-white">Kebijakan Kekuatan Password</h4>
                                        <p class="text-xs text-slate-400 mt-0.5">Aturan minimum panjang, huruf besar, dan angka untuk akun civitas.</p>
                                    </div>
                                </div>
                                @php
                                    $passwordPassed = collect($report['password'])->every('passed', true);
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $passwordPassed ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                                    {{ $passwordPassed ? 'Lulus' : 'Kurang Kuat' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-6 pt-4 border-t border-slate-700/30">
                        <div class="text-center p-3 bg-slate-900/30 rounded-xl border border-slate-700/20">
                            <span class="text-2xl font-bold text-white">{{ $report['routes']['total_routes'] }}</span>
                            <span class="block text-[10px] text-slate-400 uppercase tracking-wider mt-0.5">Total Rute Terdaftar</span>
                        </div>
                        <div class="text-center p-3 bg-slate-900/30 rounded-xl border border-slate-700/20">
                            <span class="text-2xl font-bold {{ $report['routes']['passed'] ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ count($report['routes']['vulnerable']) }}
                            </span>
                            <span class="block text-[10px] text-slate-400 uppercase tracking-wider mt-0.5">Rute Sensitif Terbuka</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Detailed Audits Breakdown Tabs/Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- 1. Environment & Config Audit Card -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-5 border-b border-slate-700/50 bg-slate-900/20 flex items-center justify-between">
                        <h3 class="text-md font-semibold text-white flex items-center">
                            <i class="fa-solid fa-cube text-indigo-400 mr-2"></i> Environment & Konfigurasi
                        </h3>
                        <span class="bg-indigo-500/10 text-indigo-400 text-[10px] px-2.5 py-1 rounded-full font-bold border border-indigo-500/20 uppercase">
                            Config
                        </span>
                    </div>
                    <div class="p-6 divide-y divide-slate-700/40">
                        @foreach($report['environment'] as $key => $check)
                            <div class="py-4 first:pt-0 last:pb-0 flex items-start justify-between space-x-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h4 class="text-sm font-semibold text-slate-200">{{ $check['name'] }}</h4>
                                        <span class="bg-slate-700/40 text-slate-300 font-mono text-[10px] px-2 py-0.5 rounded border border-slate-600/30">
                                            {{ $check['value'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">{{ $check['description'] }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    @if($check['passed'])
                                        <span class="bg-emerald-500/10 text-emerald-400 p-1.5 rounded-lg border border-emerald-500/20 block">
                                            <i class="fa-solid fa-check text-sm w-4 text-center"></i>
                                        </span>
                                    @else
                                        <span class="bg-rose-500/10 text-rose-400 p-1.5 rounded-lg border border-rose-500/20 block">
                                            <i class="fa-solid fa-triangle-exclamation text-sm w-4 text-center"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Session & Cookie Audit Card -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-5 border-b border-slate-700/50 bg-slate-900/20 flex items-center justify-between">
                        <h3 class="text-md font-semibold text-white flex items-center">
                            <i class="fa-solid fa-cookie text-teal-400 mr-2"></i> Keamanan Sesi Cookie
                        </h3>
                        <span class="bg-teal-500/10 text-teal-400 text-[10px] px-2.5 py-1 rounded-full font-bold border border-teal-500/20 uppercase">
                            Session
                        </span>
                    </div>
                    <div class="p-6 divide-y divide-slate-700/40">
                        @foreach($report['session'] as $key => $check)
                            <div class="py-4 first:pt-0 last:pb-0 flex items-start justify-between space-x-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h4 class="text-sm font-semibold text-slate-200">{{ $check['name'] }}</h4>
                                        <span class="bg-slate-700/40 text-slate-300 font-mono text-[10px] px-2 py-0.5 rounded border border-slate-600/30">
                                            {{ $check['value'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">{{ $check['description'] }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    @if($check['passed'])
                                        <span class="bg-emerald-500/10 text-emerald-400 p-1.5 rounded-lg border border-emerald-500/20 block">
                                            <i class="fa-solid fa-check text-sm w-4 text-center"></i>
                                        </span>
                                    @else
                                        <span class="bg-rose-500/10 text-rose-400 p-1.5 rounded-lg border border-rose-500/20 block">
                                            <i class="fa-solid fa-triangle-exclamation text-sm w-4 text-center"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 3. Password Policy Strength Audit Card -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-5 border-b border-slate-700/50 bg-slate-900/20 flex items-center justify-between">
                        <h3 class="text-md font-semibold text-white flex items-center">
                            <i class="fa-solid fa-key text-emerald-400 mr-2"></i> Aturan Sandi (Password Policy)
                        </h3>
                        <span class="bg-emerald-500/10 text-emerald-400 text-[10px] px-2.5 py-1 rounded-full font-bold border border-emerald-500/20 uppercase">
                            Auth
                        </span>
                    </div>
                    <div class="p-6 divide-y divide-slate-700/40">
                        @foreach($report['password'] as $key => $check)
                            <div class="py-4 first:pt-0 last:pb-0 flex items-start justify-between space-x-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h4 class="text-sm font-semibold text-slate-200">{{ $check['name'] }}</h4>
                                        <span class="bg-slate-700/40 text-slate-300 font-mono text-[10px] px-2 py-0.5 rounded border border-slate-600/30">
                                            {{ $check['value'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">{{ $check['description'] }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    @if($check['passed'])
                                        <span class="bg-emerald-500/10 text-emerald-400 p-1.5 rounded-lg border border-emerald-500/20 block">
                                            <i class="fa-solid fa-check text-sm w-4 text-center"></i>
                                        </span>
                                    @else
                                        <span class="bg-rose-500/10 text-rose-400 p-1.5 rounded-lg border border-rose-500/20 block">
                                            <i class="fa-solid fa-triangle-exclamation text-sm w-4 text-center"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 4. Rate Limiting Protection Card -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl overflow-hidden shadow-xl">
                    <div class="px-6 py-5 border-b border-slate-700/50 bg-slate-900/20 flex items-center justify-between">
                        <h3 class="text-md font-semibold text-white flex items-center">
                            <i class="fa-solid fa-lock text-amber-400 mr-2"></i> Limitasi Akses & Brute-Force
                        </h3>
                        <span class="bg-amber-500/10 text-amber-400 text-[10px] px-2.5 py-1 rounded-full font-bold border border-amber-500/20 uppercase">
                            Rate Limit
                        </span>
                    </div>
                    <div class="p-6 divide-y divide-slate-700/40">
                        <div class="py-4 first:pt-0 last:pb-0 flex items-start justify-between space-x-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <h4 class="text-sm font-semibold text-slate-200">Rate Limiter pada Form Login</h4>
                                    <span class="bg-slate-700/40 text-slate-300 font-mono text-[10px] px-2 py-0.5 rounded border border-slate-600/30">
                                        {{ $report['rate_limiting']['value'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Mencegah serangan brute-force credential stuffing dengan membatasi percobaan login yang salah secara berulang per IP address.</p>
                            </div>
                            <div class="flex-shrink-0">
                                @if($report['rate_limiting']['login_throttled'])
                                    <span class="bg-emerald-500/10 text-emerald-400 p-1.5 rounded-lg border border-emerald-500/20 block">
                                        <i class="fa-solid fa-check text-sm w-4 text-center"></i>
                                    </span>
                                @else
                                    <span class="bg-rose-500/10 text-rose-400 p-1.5 rounded-lg border border-rose-500/20 block">
                                        <i class="fa-solid fa-triangle-exclamation text-sm w-4 text-center"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Vulnerable Unprotected Routes Scanning Section -->
            <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl overflow-hidden shadow-xl">
                <div class="px-6 py-5 border-b border-slate-700/50 bg-slate-900/20 flex justify-between items-center">
                    <h3 class="text-md font-semibold text-white flex items-center">
                        <i class="fa-solid fa-route text-rose-400 mr-2"></i> Hasil Pemindaian Rute Sensitif Terbuka
                    </h3>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $report['routes']['passed'] ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                        {{ $report['routes']['passed'] ? 'Bersih & Aman' : count($report['routes']['vulnerable']) . ' Masalah Terdeteksi' }}
                    </span>
                </div>
                
                @if(empty($report['routes']['vulnerable']))
                    <div class="p-8 text-center text-slate-400">
                        <div class="h-12 w-12 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mx-auto mb-3 text-emerald-400">
                            <i class="fa-solid fa-shield-halved text-2xl"></i>
                        </div>
                        <p class="font-medium text-slate-200">Seluruh rute sensitif terproteksi dengan aman</p>
                        <p class="text-xs text-slate-400 mt-1">Sistem tidak menemukan rute admin, finance, developer, compliance, atau hrd yang bebas diakses tanpa middleware autentikasi.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-700/50">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Method</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">URI / Path</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Middleware Terdaftar</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider text-right">Rekomendasi Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50 bg-slate-800/20">
                                @foreach($report['routes']['vulnerable'] as $v)
                                    <tr class="hover:bg-slate-700/20 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2.5 py-0.5 rounded font-mono text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                {{ $v['method'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-slate-200">
                                            {{ $v['uri'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-slate-400">
                                            {{ $v['middlewares'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-right text-rose-400 font-semibold flex items-center justify-end space-x-1">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                            <span>Pasang middleware 'auth'</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
