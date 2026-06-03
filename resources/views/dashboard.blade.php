<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Dashboard Utama') }}
        </h2>
    </x-slot>

    <!-- ApexCharts now loaded via resources/js/app.js -->

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            
            <!-- Banner Section -->
            @include('dashboard.partials.welcome-banner')

            {{-- Smart Alerts Floating Dashboard Widget --}}
            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('kepala_sekolah') || auth()->user()->hasRole('bk') || auth()->user()->hasRole('waka_kesiswaan') || auth()->user()->hasRole('waka_kurikulum') || auth()->user()->hasRole('guru'))
                @php
                    $dashAlertQuery = \App\Models\SmartAlert::where('status', 'active');
                    if (auth()->user()->role === 'guru') {
                        $classroom = \App\Models\Classroom::where('teacher_id', auth()->id())->first();
                        if ($classroom) {
                            $studentIds = $classroom->students()->pluck('id');
                            $dashAlertQuery->where(function ($q) use ($studentIds) {
                                $q->where('alertable_type', \App\Models\Student::class)
                                   ->whereIn('alertable_id', $studentIds);
                            });
                        } else {
                            $dashAlertQuery->where('id', 0);
                        }
                    }
                    $dashActiveCount = $dashAlertQuery->count();
                    $dashCriticalCount = (clone $dashAlertQuery)->where('severity', 'critical')->count();
                @endphp

                @if($dashActiveCount > 0)
                    <div class="bg-gradient-to-r from-indigo-900/90 to-slate-900/90 backdrop-blur-md border border-indigo-500/20 text-white rounded-3xl p-6 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 hover:shadow-indigo-500/5 transition-all duration-300">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-2xl relative shadow-lg shadow-indigo-500/10 flex-shrink-0">
                                🧠
                                @if($dashCriticalCount > 0)
                                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                                    </span>
                                @endif
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-lg font-black tracking-tight text-indigo-300">🧠 Smart Alerts Engine Terdeteksi</h4>
                                <p class="text-xs text-gray-300 font-medium leading-relaxed max-w-xl">
                                    Sistem kecerdasan analitik mendeteksi <strong class="text-white">{{ $dashActiveCount }} anomali/alert aktif</strong> (termasuk {{ $dashCriticalCount }} tindakan kritis) yang membutuhkan peninjauan dan solusi segera di lapangan.
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('smart-alerts.index') }}" 
                           class="w-full md:w-auto px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 active:scale-95 shadow-lg shadow-indigo-600/25 flex items-center justify-center space-x-2 flex-shrink-0">
                            <span>Buka Dasbor Prediktif</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                @endif
            @endif

            <!-- Papan Pengumuman (Mading Digital) -->
            @if(isset($announcements) && $announcements->count() > 0)
                <div class="space-y-4">
                    @foreach($announcements ?? [] as $announcement)
                        <div class="rounded-2xl p-6 shadow-sm flex flex-col md:flex-row gap-6 relative overflow-hidden {{ $announcement->priority == 'urgent' ? 'bg-red-50 border border-red-200' : 'bg-white border border-gray-100' }}">
                            @if($announcement->priority == 'urgent')
                                <div class="absolute top-0 right-0 px-3 py-1 bg-red-500 text-white text-[10px] font-black uppercase tracking-widest rounded-bl-lg flex items-center animate-pulse">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    Mendesak
                                </div>
                            @endif
                            
                            @if($announcement->image_path)
                                <div class="flex-shrink-0">
                                    <img src="{{ Storage::url($announcement->image_path) }}" alt="Poster" class="w-full md:w-48 h-auto object-cover rounded-xl border {{ $announcement->priority == 'urgent' ? 'border-red-200' : 'border-gray-200' }}">
                                </div>
                            @endif
                            
                            <div class="flex-grow">
                                <h4 class="text-lg font-black {{ $announcement->priority == 'urgent' ? 'text-red-800' : 'text-gray-900' }} mb-2">{{ $announcement->title }}</h4>
                                <p class="text-sm {{ $announcement->priority == 'urgent' ? 'text-red-700' : 'text-gray-600' }} leading-relaxed whitespace-pre-line">{{ $announcement->content }}</p>
                                
                                <div class="mt-4 flex items-center text-xs font-bold text-gray-400">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    Dari: {{ $announcement?->creator?->name }}
                                    <span class="mx-2">•</span>
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{ $announcement->created_at?->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Stats/Main Content Section -->
            <div class="space-y-10">
                @if(auth()->user()->hasRole('kepala_sekolah') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'))
                    <div x-data="{
                        showDrawer: false,
                        layout: {{ json_encode($dashboard_layout) }},
                        isSaving: false,
                        isLoadingCharts: true,
                        draggedIndex: null,
                        toastMessage: '',
                        toastType: '',
                        showToastFlag: false,

                        init() {
                            setTimeout(() => { this.isLoadingCharts = false; }, 600);
                        },
                        getWidgetOrder(id) {
                            return this.layout.findIndex(w => w.id === id);
                        },
                        isWidgetVisible(id) {
                            const widget = this.layout.find(w => w.id === id);
                            return widget ? widget.visible : false;
                        },
                        dragStart(e, index) {
                            this.draggedIndex = index;
                            e.dataTransfer.effectAllowed = 'move';
                        },
                        dragOver(e) {
                            e.preventDefault();
                        },
                        drop(e, toIndex) {
                            if (this.draggedIndex !== null && this.draggedIndex !== toIndex) {
                                const temp = this.layout[this.draggedIndex];
                                this.layout.splice(this.draggedIndex, 1);
                                this.layout.splice(toIndex, 0, temp);
                                this.draggedIndex = null;
                            }
                        },
                        showLocalToast(type, msg) {
                            this.toastType = type;
                            this.toastMessage = msg;
                            this.showToastFlag = true;
                            setTimeout(() => { this.showToastFlag = false; }, 3000);
                        },
                        async saveLayout() {
                            this.isSaving = true;
                            try {
                                const response = await fetch('{{ route('dashboard.save-layout') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({ layout: this.layout })
                                });
                                const data = await response.json();
                                if (data.status === 'success') {
                                    this.showLocalToast('success', data.message);
                                } else {
                                    this.showLocalToast('error', 'Gagal menyimpan tata letak.');
                                }
                            } catch (error) {
                                this.showLocalToast('error', 'Terjadi kesalahan saat menyimpan.');
                            } finally {
                                this.isSaving = false;
                            }
                        },
                        resetLayout() {
                            this.layout = [
                                { id: 'general_stats', name: 'Statistik Utama', visible: true },
                                { id: 'quick_access', name: 'Akses Cepat', visible: true },
                                { id: 'financial_trend', name: 'Tren Keuangan', visible: true },
                                { id: 'live_feed', name: 'Aktivitas Terbaru', visible: true },
                                { id: 'spmb_analytics', name: 'Analisis SPMB', visible: true }
                            ];
                            this.saveLayout();
                        }
                    }" class="relative">

                        <!-- Customizer Button Panel -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                            <h3 class="text-xl font-extrabold text-gray-800 dark:text-white flex items-center">
                                <span class="w-8 h-8 bg-primary-100 dark:bg-primary-950 text-primary-600 dark:text-primary-400 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </span>
                                Ikhtisar Sekolah & Dasbor Cerdas
                            </h3>
                            <button @click="showDrawer = true" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                                <svg class="w-4 h-4 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                🔧 Kustomisasi Dasbor
                            </button>
                        </div>

                        <!-- Main Grid Container Backed by Flex order -->
                        <div class="flex flex-col gap-10">

                            <!-- Widget: General Stats -->
                            <div x-show="isWidgetVisible('general_stats')"
                                 :style="'order: ' + getWidgetOrder('general_stats')"
                                 class="w-full transition-all duration-300"
                                 style="display: none;">
                                @include('dashboard.partials.stats-admin')
                            </div>

                            <!-- Widget: Super Akses Menu (Hanya Admin) -->
                            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'))
                                <div x-show="isWidgetVisible('quick_access')"
                                     :style="'order: ' + getWidgetOrder('quick_access')"
                                     class="bg-gray-900 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-xl w-full transition-all duration-300"
                                     style="display: none;">
                                    <div class="relative z-10">
                                        <h4 class="text-lg font-black mb-4 flex items-center text-amber-400">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                            Akses Cepat Superadmin
                                        </h4>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            <a href="{{ route('mutabaah.index') }}" class="bg-gray-800 hover:bg-gray-700 p-4 rounded-2xl transition-all border border-gray-700 hover:border-emerald-500">
                                                <p class="text-sm font-bold">Modul Tahfidz & Keislaman</p>
                                            </a>
                                            <a href="{{ route('points.index') }}" class="bg-gray-800 hover:bg-gray-700 p-4 rounded-2xl transition-all border border-gray-700 hover:border-red-500">
                                                <p class="text-sm font-bold">Modul BK & Kedisiplinan</p>
                                            </a>
                                            <a href="{{ route('finance.billings.index') }}" class="bg-gray-800 hover:bg-gray-700 p-4 rounded-2xl transition-all border border-gray-700 hover:border-blue-500">
                                                <p class="text-sm font-bold">Modul Keuangan (SPP)</p>
                                            </a>
                                            <a href="{{ route('users.index') }}" class="bg-gray-800 hover:bg-gray-700 p-4 rounded-2xl transition-all border border-gray-700 hover:border-purple-500">
                                                <p class="text-sm font-bold">Manajemen User & Guru</p>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Widget: Analytics Area (Tren Keuangan & Live Feed) -->
                            <div x-show="isWidgetVisible('financial_trend') || isWidgetVisible('live_feed')"
                                 :style="'order: ' + Math.min(getWidgetOrder('financial_trend'), getWidgetOrder('live_feed'))"
                                 class="grid grid-cols-1 xl:grid-cols-3 gap-8 transition-all duration-300"
                                 style="display: none;">
                                
                                <!-- Sub-Widget: Main Financial Chart (2/3) -->
                                <div x-show="isWidgetVisible('financial_trend')"
                                     :style="'order: ' + getWidgetOrder('financial_trend')"
                                     :class="isWidgetVisible('live_feed') ? 'xl:col-span-2' : 'xl:col-span-3'"
                                     class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group transition-all duration-300"
                                     style="display: none;">
                                    <div class="flex justify-between items-center mb-8">
                                        <div>
                                            <h4 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Tren Pemasukan Keuangan</h4>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">7 Hari Terakhir</p>
                                        </div>
                                        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                    </div>
                                    
                                    <!-- Skeleton Loader Component -->
                                    <div x-show="isLoadingCharts" class="w-full h-80 flex flex-col justify-between animate-pulse space-y-4">
                                        <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded-lg w-1/3"></div>
                                        <div class="flex-1 flex items-end space-x-4 pt-4">
                                            <div class="bg-gray-200 dark:bg-gray-700 rounded-t-lg h-2/3 flex-1"></div>
                                            <div class="bg-gray-200 dark:bg-gray-700 rounded-t-lg h-4/5 flex-1"></div>
                                            <div class="bg-gray-200 dark:bg-gray-700 rounded-t-lg h-1/2 flex-1"></div>
                                            <div class="bg-gray-200 dark:bg-gray-700 rounded-t-lg h-3/4 flex-1"></div>
                                            <div class="bg-gray-200 dark:bg-gray-700 rounded-t-lg h-2/5 flex-1"></div>
                                        </div>
                                    </div>

                                    <!-- ApexChart Container -->
                                    <div x-show="!isLoadingCharts" id="financialTrendChart" class="w-full h-80 -ml-4" style="display: none;"></div>
                                </div>

                                <!-- Sub-Widget: Live Activity Feed (1/3) -->
                                <div x-show="isWidgetVisible('live_feed')"
                                     :style="'order: ' + getWidgetOrder('live_feed')"
                                     :class="isWidgetVisible('financial_trend') ? 'xl:col-span-1' : 'xl:col-span-3'"
                                     class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col transition-all duration-300"
                                     style="display: none;">
                                    <div class="flex justify-between items-center mb-8">
                                        <div>
                                            <h4 class="text-lg font-black text-gray-900 dark:text-white">Live Feed</h4>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Pembayaran Terbaru</p>
                                        </div>
                                        <div class="flex space-x-1">
                                            <div class="w-2 h-2 bg-primary-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                            <div class="w-2 h-2 bg-primary-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                            <div class="w-2 h-2 bg-primary-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                                        </div>
                                    </div>

                                    <!-- Skeleton Loader Component -->
                                    <div x-show="isLoadingCharts" class="space-y-6 flex-1 animate-pulse">
                                        <div class="flex items-center space-x-4">
                                            <div class="rounded-xl bg-gray-200 dark:bg-gray-700 h-10 w-10"></div>
                                            <div class="flex-1 space-y-2 py-1">
                                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                                                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <div class="rounded-xl bg-gray-200 dark:bg-gray-700 h-10 w-10"></div>
                                            <div class="flex-1 space-y-2 py-1">
                                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
                                                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-show="!isLoadingCharts" class="flex-1 overflow-y-auto pr-2 space-y-6" style="display: none;">
                                        @forelse($dashboard_recent_payments ?? [] as $recentPayment)
                                            @if(!is_object($recentPayment)) @continue @endif
                                            @php /** @var \App\Models\Payment $recentPayment */ @endphp
                                            <div class="flex items-start group">
                                                <div class="relative">
                                                    <div class="w-10 h-10 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold z-10 relative group-hover:scale-110 transition-transform">
                                                        {{ substr($recentPayment->billing?->student?->user?->name ?? 'A', 0, 1) }}
                                                    </div>
                                                    @if(!$loop->last)
                                                        <div class="absolute top-10 left-1/2 -ml-px w-0.5 h-full bg-gray-100 dark:bg-gray-700"></div>
                                                    @endif
                                                </div>
                                                <div class="ml-4 flex-1">
                                                    <div class="flex justify-between items-start">
                                                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100 leading-tight">{{ $recentPayment->billing?->student?->user?->name ?? 'Siswa' }}</p>
                                                        <span class="text-[10px] font-bold text-gray-400 whitespace-nowrap ml-2">{{ $recentPayment->created_at?->diffForHumans() ?? '-' }}</span>
                                                    </div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $recentPayment->billing?->category?->name ?? 'Iuran' }}</p>
                                                    <div class="mt-2 inline-flex items-center px-2 py-1 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 rounded text-[10px] font-black uppercase tracking-widest">
                                                        + Rp {{ number_format($recentPayment->amount_paid ?? 0, 0, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="flex flex-col items-center justify-center h-full text-center opacity-50 py-10">
                                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                                <p class="text-xs font-bold text-gray-500">Belum ada transaksi</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Widget: SPMB Terpadu -->
                            @if(isset($spmb_stats))
                                <div x-show="isWidgetVisible('spmb_analytics')"
                                     :style="'order: ' + getWidgetOrder('spmb_analytics')"
                                     class="w-full transition-all duration-300"
                                     style="display: none;">
                                    @include('dashboard.partials.spmb-analytics')
                                </div>
                            @endif

                        </div>

                        <!-- Sliding Settings Drawer (Panel Kustomisasi Glassmorphic) -->
                        <div x-show="showDrawer" class="fixed inset-0 z-[400] overflow-hidden" style="display: none;">
                            <div class="absolute inset-0 overflow-hidden">
                                <!-- Background overlay -->
                                <div x-show="showDrawer" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showDrawer = false"></div>

                                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                                    <div x-show="showDrawer" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="pointer-events-auto w-screen max-w-md">
                                        <div class="flex h-full flex-col bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-l border-gray-200 dark:border-gray-800 shadow-2xl">
                                            
                                            <!-- Drawer Header -->
                                            <div class="px-6 py-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
                                                <div>
                                                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Kustomisasi Dasbor</h3>
                                                    <p class="text-xs font-semibold text-gray-400 mt-0.5">Atur tata letak & visibilitas widget</p>
                                                </div>
                                                <button @click="showDrawer = false" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-300 transition-all">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>

                                            <!-- Drawer Body (List of Widgets with Drag & Drop) -->
                                            <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                                                <div class="space-y-4">
                                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Seret untuk Mengurutkan & Toggle Aktif</p>
                                                    
                                                    <div class="space-y-3">
                                                        <template x-for="(widget, idx) in layout" :key="widget.id">
                                                            <div draggable="true"
                                                                 @dragstart="dragStart($event, idx)"
                                                                 @dragover="dragOver($event)"
                                                                 @drop="drop($event, idx)"
                                                                 class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm hover:border-primary-500/50 dark:hover:border-primary-500/50 cursor-grab active:cursor-grabbing transition-all group">
                                                                <div class="flex items-center space-x-3">
                                                                    <!-- Drag Handle -->
                                                                    <div class="text-gray-400 group-hover:text-primary-500 transition-colors">
                                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                                                                    </div>
                                                                    <span class="text-sm font-black text-gray-800 dark:text-gray-200" x-text="widget.name"></span>
                                                                </div>
                                                                
                                                                <!-- Visibility Switch Toggle -->
                                                                <button @click="widget.visible = !widget.visible" :class="widget.visible ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                                                                    <span :class="widget.visible ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Drawer Footer -->
                                            <div class="px-6 py-6 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex space-x-3">
                                                <button @click="resetLayout()" class="flex-1 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                                                    Reset
                                                </button>
                                                <button @click="saveLayout()" :disabled="isSaving" class="flex-1 py-3 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-lg shadow-primary-900/10 flex items-center justify-center space-x-2">
                                                    <svg x-show="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Layout'"></span>
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Micro Floating Toast in Dashboard -->
                        <div x-show="showToastFlag"
                             x-transition:enter="transition ease-out duration-300 transform"
                             x-transition:enter-start="translate-y-10 opacity-0 scale-95"
                             x-transition:enter-end="translate-y-0 opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-200 transform"
                             x-transition:leave-start="translate-y-0 opacity-100 scale-100"
                             x-transition:leave-end="translate-y-10 opacity-0 scale-95"
                             class="fixed bottom-6 left-6 z-[350] rounded-2xl p-4 shadow-2xl flex items-center space-x-3 text-white max-w-sm"
                             :class="toastType === 'success' ? 'bg-emerald-950/90 border border-emerald-800' : 'bg-red-950/90 border border-red-800'"
                             style="display: none;">
                            <div class="flex-shrink-0">
                                <svg x-show="toastType === 'success'" class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <svg x-show="toastType !== 'success'" class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="text-xs font-black" x-text="toastMessage"></span>
                        </div>

                    </div>
                @endif

                @if(auth()->user()->hasRole('waka_kurikulum'))
                    <h3 class="text-xl font-extrabold text-gray-800 flex items-center">
                        <span class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </span>
                        Wadah Akademik & Kurikulum
                    </h3>
                    @include('dashboard.partials.stats-kurikulum')
                @endif

                @if(auth()->user()->hasRole('student'))
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                        <!-- Points Card -->
                        <div class="bg-white rounded-[2rem] p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
                            <p class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4">Poin Kedisiplinan Saya</p>
                            <div class="w-32 h-32 rounded-full border-8 {{ $total_points > 50 ? 'border-red-100' : 'border-primary-100' }} flex items-center justify-center mb-6">
                                <span class="text-5xl font-black {{ $total_points > 50 ? 'text-red-500' : 'text-primary-600' }}">{{ $total_points }}</span>
                            </div>
                            <p class="text-sm font-bold text-gray-600 mb-2">Status: {{ $total_points > 50 ? 'Perlu Pembinaan' : 'Baik' }}</p>
                            <a href="{{ route('points.show', $student_profile->id ?? 0) }}" class="text-xs font-bold text-primary-600 hover:underline">Lihat Detail Riwayat →</a>
                        </div>

                        <!-- Finance Card -->
                        <div class="bg-white rounded-[2rem] p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center relative overflow-hidden">
                            @if($unpaid_billings_count > 0)
                                <div class="absolute top-4 right-4 w-3 h-3 bg-red-500 rounded-full animate-ping"></div>
                            @endif
                            <p class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4">Tunggakan Administrasi</p>
                            <div class="text-3xl font-black {{ $unpaid_billings_count > 0 ? 'text-red-500' : 'text-emerald-500' }} mb-2">
                                Rp {{ number_format($total_unpaid_amount, 0, ',', '.') }}
                            </div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-6">{{ $unpaid_billings_count }} Tagihan Belum Lunas</p>
                            
                            <div class="w-full flex space-x-2">
                                <a href="{{ route('finance.student.index') }}" class="flex-1 py-3 bg-gray-50 text-gray-700 rounded-xl text-xs font-bold border border-gray-100">Detail Iuran</a>
                                <a href="{{ route('finance.student.index') }}" class="flex-1 py-3 bg-primary-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-primary-900/10">Bayar Sekarang</a>
                            </div>
                        </div>

                        <!-- Academic Card (New) -->
                        <div class="bg-primary-900 rounded-[2rem] p-6 sm:p-8 shadow-xl flex flex-col items-center justify-center text-center relative overflow-hidden text-white">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            <p class="text-[11px] font-black uppercase tracking-[0.2em] text-primary-300 mb-4 relative z-10">Akademik & E-Rapor</p>
                            <div class="text-4xl font-black mb-2 relative z-10">
                                {{ $total_points > 0 ? 'Aktif' : 'Ready' }}
                            </div>
                            <p class="text-[10px] font-bold text-primary-400 uppercase tracking-widest mb-6 relative z-10">Pantau Nilai & Download Rapor</p>
                            
                            <a href="{{ route('student.academic') }}" class="w-full py-4 bg-white text-primary-900 rounded-2xl text-sm font-black shadow-lg hover:bg-primary-50 transition-all relative z-10">
                                LIHAT NILAI SAYA
                            </a>
                        </div>

                        <!-- Recent Logs -->
                        <div class="md:col-span-2 xl:col-span-2 bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100">
                            <h4 class="text-lg font-black text-gray-800 mb-6 flex items-center">
                                <span class="w-2 h-6 bg-primary-600 rounded-full mr-3"></span>
                                Catatan Terakhir
                            </h4>
                            <div class="space-y-4">
                                @forelse($recent_logs ?? [] as $log) @php /** @var \App\Models\StudentPointLog $log */ @endphp
                                    <div class="p-4 bg-gray-50 rounded-2xl flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 {{ $log->points > 0 ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600' }} rounded-xl flex items-center justify-center font-bold mr-4">
                                                {{ $log->points > 0 ? '+' : '' }}{{ $log->points }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-800">{{ $log->description }}</p>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ $log->created_at?->diffForHumans() }} • Oleh {{ $log?->creator?->name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center py-10 text-gray-400 italic">Belum ada catatan perilaku.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif

                {{-- View Wali Murid (Parent Portal) --}}
                @if(auth()->user()->hasRole('parent'))
                    <div class="space-y-10">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-black text-gray-800">Monitoring Anak</h3>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ count($children) }} Anak Terdaftar</span>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                            @foreach($children ?? [] as $child)
                                <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 relative overflow-hidden group">
                                    <div class="flex items-start justify-between mb-8">
                                        <div class="flex items-center">
                                            <div class="w-16 h-16 bg-primary-100 text-primary-700 rounded-[1.5rem] flex items-center justify-center font-black text-2xl mr-5">
                                                {{ substr($child['profile']?->user?->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h4 class="text-xl font-black text-gray-800">{{ $child['profile']?->user?->name }}</h4>
                                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">{{ $child['profile']?->classroom?->name ?? 'N/A' }} • NIS: {{ $child['profile']->nis }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Poin Kedisiplinan</p>
                                            <p class="text-2xl font-black {{ $child['total_points'] > 50 ? 'text-red-500' : 'text-primary-600' }}">{{ $child['total_points'] }}</p>
                                        </div>
                                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tunggakan Iuran</p>
                                            <p class="text-2xl font-black {{ $child['total_unpaid_amount'] > 0 ? 'text-red-500' : 'text-emerald-600' }}">Rp {{ number_format($child['total_unpaid_amount'], 0, ',', '.') }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Catatan Terakhir</p>
                                        @forelse($child['recent_logs'] ?? [] as $log)
                                            <div class="flex items-center text-sm">
                                                <div class="w-2 h-2 rounded-full {{ $log->points > 0 ? 'bg-red-500' : 'bg-emerald-500' }} mr-3"></div>
                                                <span class="flex-1 text-gray-600 font-medium truncate">{{ $log->description }}</span>
                                                <span class="text-[10px] font-bold text-gray-400 ml-2">{{ $log->created_at?->diffForHumans() }}</span>
                                            </div>
                                        @empty
                                            <p class="text-xs text-gray-300 italic">Belum ada catatan.</p>
                                        @endforelse
                                    </div>

                                    <div class="mt-8 pt-6 border-t border-gray-50 flex flex-col gap-3">
                                        <a href="{{ route('parent-portal.dashboard', ['child_id' => $child['profile']->id]) }}" class="w-full text-center py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-primary-900/10 transition-all">
                                            🌟 Buka Portal Premium
                                        </a>
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="{{ route('parent-portal.report-card', $child['profile']->id) }}" class="text-center py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-xl text-xs font-bold transition-all border border-gray-100">
                                                📝 Rapor Nilai
                                            </a>
                                            <a href="{{ route('parent-portal.schedule', $child['profile']->id) }}" class="text-center py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-xl text-xs font-bold transition-all border border-gray-100">
                                                📅 Jadwal Kelas
                                            </a>
                                            <a href="{{ route('parent-portal.attendance', $child['profile']->id) }}" class="text-center py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-xl text-xs font-bold transition-all border border-gray-100">
                                                ⏰ Presensi Log
                                            </a>
                                            <a href="{{ route('parent-portal.billings', $child['profile']->id) }}" class="text-center py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-xl text-xs font-bold transition-all border border-gray-100">
                                                💰 Tagihan SPP
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- View Guru (Teacher Portal) --}}
                @if(auth()->user()->hasRole('guru'))
                    <div class="space-y-8">
                        <div class="bg-primary-600 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-lg shadow-primary-900/20">
                            <div class="relative z-10">
                                <h3 class="text-2xl font-black mb-2">Selamat Bertugas, Pahlawan Pendidikan!</h3>
                                <p class="text-primary-100 font-medium">Dashboard ini disesuaikan dengan mata pelajaran dan tugas tambahan Anda.</p>
                            </div>
                            <div class="absolute -right-10 -bottom-10 opacity-20">
                                <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                            </div>
                        </div>

                        <!-- Menu Dinamis Berdasarkan Mapel -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <!-- Menu Guru Agama / Tahfidz -->
                            @if(isset($isTahfidz) && $isTahfidz)
                                <a href="{{ route('mutabaah.index') }}" class="group bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-xl hover:border-emerald-200 transition-all duration-300">
                                    <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    </div>
                                    <h4 class="text-lg font-black text-gray-800 mb-2">Modul Keislaman</h4>
                                    <p class="text-sm text-gray-500">Kelola Tahfidz & Mutaba'ah Yaumiyah siswa.</p>
                                </a>
                            @endif

                            <!-- Menu Guru BK -->
                            @if(isset($isBK) && $isBK)
                                <a href="{{ route('points.index') }}" class="group bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-xl hover:border-red-200 transition-all duration-300">
                                    <div class="w-14 h-14 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    </div>
                                    <h4 class="text-lg font-black text-gray-800 mb-2">Kedisiplinan & BK</h4>
                                    <p class="text-sm text-gray-500">Catat pelanggaran dan riwayat konseling siswa.</p>
                                </a>
                            @endif

                            <!-- Menu Wali Kelas -->
                            @if(isset($isWaliKelas) && $isWaliKelas)
                                <div class="group bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-xl hover:border-blue-200 transition-all duration-300 md:col-span-2 lg:col-span-1">
                                    <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    </div>
                                    <h4 class="text-lg font-black text-gray-800 mb-2">Perwalian Kelas</h4>
                                    <p class="text-sm text-gray-500 mb-4">Anda adalah wali kelas dari:</p>
                                    @if(isset($my_classrooms))
                                        <div class="space-y-2">
                                            @foreach($my_classrooms ?? [] as $kelas)
                                                <div class="px-4 py-2 bg-gray-50 rounded-lg text-sm font-bold text-gray-700 flex justify-between">
                                                    <span>{{ $kelas->name }}</span>
                                                    <span class="text-primary-600">{{ $kelas->students_count }} Siswa</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Menu Default Guru -->
                            <a href="#" class="group bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-xl hover:border-gray-300 transition-all duration-300">
                                <div class="w-14 h-14 bg-gray-100 text-gray-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <h4 class="text-lg font-black text-gray-800 mb-2">Jadwal & Absensi</h4>
                                <p class="text-sm text-gray-500">Lihat jadwal mengajar dan isi presensi harian.</p>
                            </a>

                        </div>
                    </div>
                @endif

                {{-- Feedback for other roles as placeholder --}}
                @if(!auth()->user()->hasRole('kepala_sekolah') && !auth()->user()->hasRole('admin') && !auth()->user()->hasRole('waka_kurikulum') && !auth()->user()->hasRole('student') && !auth()->user()->hasRole('parent') && !auth()->user()->hasRole('guru'))
                    <div class="bg-white rounded-[2rem] p-12 text-center border-2 border-dashed border-gray-100">
                        <div class="w-20 h-20 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Modul Sedang Disiapkan</h3>
                        <p class="text-gray-500 max-w-sm mx-auto">Kami sedang merakit wadah digital khusus untuk peran <strong>{{ auth()->user()->role }}</strong>. Dashboard personal Anda akan segera hadir.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Enterprise ApexCharts Scripts -->
    @if(auth()->user()->hasRole('kepala_sekolah') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // 1. Financial Trend Chart (Revenue)
            @if(isset($payment_trend) && $payment_trend->count() > 0)
            const financialOptions = {
                series: [{
                    name: 'Pemasukan (Rp)',
                    data: {!! json_encode($payment_trend->pluck('total')) !!}
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                colors: ['#10b981'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                xaxis: {
                    categories: {!! json_encode($payment_trend->pluck('date')->map(function($date) { return \Carbon\Carbon::parse($date)->format('d M'); })) !!},
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: '#9ca3af', fontSize: '12px', fontWeight: 600 } }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return "Rp " + (value / 1000000).toFixed(1) + "M";
                        },
                        style: { colors: '#9ca3af', fontSize: '12px', fontWeight: 600 }
                    }
                },
                grid: {
                    borderColor: '#f3f4f6',
                    strokeDashArray: 4,
                    yaxis: { lines: { show: true } }
                },
                tooltip: {
                    y: {
                        formatter: function(value) {
                            return "Rp " + value.toLocaleString('id-ID');
                        }
                    }
                }
            };

            const financialChart = new ApexCharts(document.querySelector("#financialTrendChart"), financialOptions);
            financialChart.render();
            @else
                document.querySelector("#financialTrendChart").innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 font-bold text-sm">Belum ada data pemasukan 7 hari terakhir.</div>';
            @endif

            // 2. SPMB Trend Chart (Replace old Chart.js in spmb-analytics)
            @if(isset($spmb_trend))
            const spmbOptions = {
                series: [{
                    name: 'Pendaftar Baru',
                    data: {!! json_encode($spmb_trend->pluck('total')) !!}
                }],
                chart: {
                    type: 'area',
                    height: '100%',
                    parentHeightOffset: 0,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    sparkline: { enabled: true } // Makes it fit perfectly in small cards
                },
                colors: ['#1e1b4b'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.2,
                        opacityTo: 0,
                        stops: [0, 100]
                    }
                },
                stroke: { curve: 'smooth', width: 2 },
                tooltip: {
                    fixed: { enabled: false },
                    x: { show: false },
                    y: { title: { formatter: function (seriesName) { return 'Pendaftar:' } } },
                    marker: { show: false }
                }
            };
            
            // Wait a bit to ensure the spmb container is loaded if it's an include
            setTimeout(() => {
                const spmbEl = document.querySelector("#spmbTrendChart");
                if(spmbEl) {
                    // Because we replaced Chart.js with ApexCharts, we change canvas to div if it exists
                    if(spmbEl.tagName.toLowerCase() === 'canvas') {
                        const newDiv = document.createElement('div');
                        newDiv.id = 'spmbTrendChart';
                        newDiv.className = 'w-full h-full';
                        spmbEl.parentNode.replaceChild(newDiv, spmbEl);
                        const spmbChart = new ApexCharts(newDiv, spmbOptions);
                        spmbChart.render();
                    } else {
                        const spmbChart = new ApexCharts(spmbEl, spmbOptions);
                        spmbChart.render();
                    }
                }
            }, 100);
            @endif

        });
    </script>
    @endif
</x-app-layout>
