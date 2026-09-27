<x-app-layout>
  <x-slot name="header">
    <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
      {{ __('dashboard.dashboard_title') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="w-full px-4 sm:px-6 lg:px-8 space-y-10">
      
      <!-- Banner Section -->
      @include('dashboard.partials.welcome-banner')

      {{-- Smart Alerts Floating Dashboard Widget (Available to Admin/Staff roles) --}}
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
          <div class="bg-gradient-to-r from-indigo-950/90 to-slate-900/90 backdrop-blur-md border border-indigo-500/20 text-white rounded-3xl p-6 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 hover:shadow-indigo-500/5 transition-all duration-300">
            <div class="flex items-center space-x-4">
              <div class="w-14 h-14 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-2xl relative shadow-lg shadow-indigo-500/10 shrink-0">
                
                @if($dashCriticalCount > 0)
                  <span class="absolute -top-1 -right-1 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                  </span>
                @endif
              </div>
              <div class="space-y-1">
                <h4 class="text-lg font-black tracking-tight text-indigo-300">{{ __('dashboard.smart_alerts_detected') }}</h4>
                <p class="text-xs text-gray-300 font-medium leading-relaxed max-w-xl">
                  {!! __('dashboard.smart_alerts_desc', ['count' => '<strong class="text-white">'.$dashActiveCount.'</strong>', 'critical' => '<strong class="text-white">'.$dashCriticalCount.'</strong>']) !!}
                </p>
              </div>
            </div>
            <a href="{{ route('smart-alerts.index') }}" 
              class="w-full md:w-auto px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 active:scale-95 shadow-lg shadow-indigo-600/25 flex items-center justify-center space-x-2 shrink-0">
              <span>{{ __('dashboard.open_predictive_dashboard') }}</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
          </div>
        @endif
      @endif

      <!-- Papan Pengumuman (Mading Digital) -->
      @if(isset($announcements) && $announcements->count() > 0)
        <div class="space-y-4">
          @foreach($announcements ?? [] as $announcement)
            <div class="rounded-3xl p-6 shadow-md flex flex-col md:flex-row gap-6 relative overflow-hidden transition-all duration-300 hover:shadow-lg border {{ $announcement->is_pinned ? 'bg-amber-50/20 dark:bg-amber-950/10 border-amber-300/40' : ($announcement->priority == 'urgent' ? 'bg-red-50 dark:bg-red-950/20 border-red-200 dark:border-red-900/30' : 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700') }}">
              @if($announcement->is_pinned)
                <div class="absolute top-0 right-0 rtl:left-0 rtl:right-auto px-3 py-1 bg-amber-500 text-white text-[10px] font-black uppercase tracking-widest rounded-bl-lg rtl:rounded-br-lg rtl:rounded-bl-none flex items-center z-10">
                   {{ __('dashboard.pinned_fallback') }}
                </div>
              @elseif($announcement->priority == 'urgent')
                <div class="absolute top-0 right-0 rtl:left-0 rtl:right-auto px-3 py-1 bg-red-500 text-white text-[10px] font-black uppercase tracking-widest rounded-bl-lg rtl:rounded-br-lg rtl:rounded-bl-none flex items-center animate-pulse z-10">
                  <svg class="w-3 h-3 mr-1 rtl:ml-1 rtl:mr-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                  {{ __('dashboard.urgent') }}
                </div>
              @endif
              
              @if($announcement->image_path)
                <div class="shrink-0">
                  <img src="{{ Storage::url($announcement->image_path) }}" alt="Poster" class="w-full md:w-48 h-32 object-cover rounded-2xl border {{ $announcement->priority == 'urgent' ? 'border-red-200 dark:border-red-950' : 'border-gray-200 dark:border-gray-700' }}">
                </div>
              @endif
              
              <div class="grow flex flex-col justify-between">
                <div>
                  <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ 
                      $announcement->category === 'akademik' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : (
                      $announcement->category === 'keuangan' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : (
                      $announcement->category === 'kegiatan' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300' : (
                      $announcement->category === 'keislaman' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')))
                    }}">
                       {{ __('dashboard.category_' . ($announcement->category ?? 'umum')) }}
                    </span>
                    @if($announcement->require_acknowledgement)
                      @if($announcement->isAcknowledgedBy(auth()->id()))
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 flex items-center gap-1">
                           {{ __('dashboard.acknowledged_fallback') }}
                        </span>
                      @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 animate-pulse flex items-center gap-1">
                           {{ __('dashboard.needs_acknowledgement') }}
                        </span>
                      @endif
                    @endif
                  </div>
                  <h4 class="text-lg font-black {{ $announcement->priority == 'urgent' ? 'text-red-800 dark:text-red-300' : 'text-gray-900 dark:text-white' }} mb-2">
                    {{ $announcement->title }}
                  </h4>
                  <p class="text-sm {{ $announcement->priority == 'urgent' ? 'text-red-700/95 dark:text-red-400' : 'text-gray-600 dark:text-gray-300' }} leading-relaxed line-clamp-2">
                    {{ Str::limit($announcement->content, 180) }}
                  </p>
                </div>
                
                <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-gray-100/50 dark:border-gray-700/50">
                  <div class="flex items-center text-xs font-bold text-gray-400 dark:text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    {{ __('dashboard.from') }}: {{ $announcement?->creator?->name }}
                    <span class="mx-2">•</span>
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ $announcement->created_at?->diffForHumans() }}
                  </div>
                  <div class="flex items-center gap-2">
                    @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah', 'waka_humas']) || $announcement->created_by === auth()->id())
                      <a href="{{ route('announcements.receipts', $announcement) }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                         {{ __('dashboard.read_receipts') ?? 'Penerima' }}
                      </a>
                    @endif
                    <a href="{{ route('announcements.show', $announcement) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-primary-600/10">
                      {{ __('dashboard.view_details') ?? 'Lihat Selengkapnya' }} →
                    </a>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif

      <!-- Customizer & Layout Management (Powered by Alpine.js for ALL roles) -->
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
          this.layout = {{ json_encode(auth()->user()->getDefaultDashboardLayout()) }};
          this.saveLayout();
        }
      }" class="relative">

        <!-- Header Customizer Panel -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
          <h3 class="text-xl font-extrabold text-gray-800 dark:text-white flex items-center">
            <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center mr-3">
              
            </span>
            {{ __('dashboard.school_overview_dashboard') }}
          </h3>
          <button @click="showDrawer = true" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
            <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
            {{ __('dashboard.customize_dashboard') }}
          </button>
        </div>

        <!-- Main Layout Container (Order managed dynamically) -->
        <div class="flex flex-col gap-10">

          <!-- ========================================== -->
          <!-- ADMIN & KEPALA SEKOLAH WIDGETS       -->
          <!-- ========================================== -->

          @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('kepala_sekolah') || auth()->user()->hasRole('waka_kurikulum') || auth()->user()->hasRole('waka_kesiswaan'))

            <!-- Widget: General Stats -->
            <div x-show="isWidgetVisible('general_stats')" :style="'order: ' + getWidgetOrder('general_stats')" class="w-full transition-all duration-300" style="display: none;">
              @include('dashboard.partials.stats-admin')
            </div>

            <!-- Widget: Quick Access Menu -->
            <div x-show="isWidgetVisible('quick_access')" :style="'order: ' + getWidgetOrder('quick_access')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-slate-900/90 dark:bg-slate-950/80 backdrop-blur-md border border-slate-800 rounded-4xl p-8 text-white relative overflow-hidden shadow-xl">
                <div class="relative z-10">
                  <h4 class="text-lg font-black mb-4 flex items-center text-amber-400">
                     {{ __('dashboard.quick_access') }}
                  </h4>
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('mutabaah.index') }}" class="bg-slate-800/80 hover:bg-slate-700/80 p-4 rounded-2xl transition-all border border-slate-700 hover:border-emerald-500">
                      <p class="text-sm font-bold">{{ __('dashboard.islamic_tahfidz_module') }}</p>
                    </a>
                    <a href="{{ route('points.index') }}" class="bg-slate-800/80 hover:bg-slate-700/80 p-4 rounded-2xl transition-all border border-slate-700 hover:border-red-500">
                      <p class="text-sm font-bold">{{ __('dashboard.bk_discipline_module') }}</p>
                    </a>
                    <a href="{{ route('finance.billings.index') }}" class="bg-slate-800/80 hover:bg-slate-700/80 p-4 rounded-2xl transition-all border border-slate-700 hover:border-blue-500">
                      <p class="text-sm font-bold">{{ __('dashboard.finance_spp_module') }}</p>
                    </a>
                    <a href="{{ route('users.index') }}" class="bg-slate-800/80 hover:bg-slate-700/80 p-4 rounded-2xl transition-all border border-slate-700 hover:border-purple-500">
                      <p class="text-sm font-bold">{{ __('dashboard.user_teacher_management') }}</p>
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Widget: Attendance Today (Gauge) -->
            <div x-show="isWidgetVisible('attendance_today')" :style="'order: ' + getWidgetOrder('attendance_today')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center space-x-4">
                  <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl {{ $todayAttendanceRate >= 85 ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600' : 'bg-red-50 dark:bg-red-950/30 text-red-600' }}">
                    
                  </div>
                  <div>
                    <h4 class="text-lg font-black text-gray-900 dark:text-white">{{ __('dashboard.widget_attendance_today') }}</h4>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-0.5">{{ school_short_name() }} • {{ $todayAttendanceDateFormatted }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-6">
                  <div class="text-right">
                    <span class="text-3xl font-black {{ $todayAttendanceRate >= 85 ? 'text-emerald-500' : 'text-red-500' }}">{{ $todayAttendanceRate }}%</span>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-wider">Kehadiran Siswa</p>
                  </div>
                  <div class="w-24 bg-gray-100 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                    <div class="h-full rounded-full {{ $todayAttendanceRate >= 85 ? 'bg-emerald-500' : 'bg-red-500' }}" style="width: {{ $todayAttendanceRate }}%"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Widget: Status Keuangan Bulan Ini -->
            <div x-show="isWidgetVisible('financial_status_month')" :style="'order: ' + getWidgetOrder('financial_status_month')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-1 flex flex-col justify-center">
                  <h4 class="text-xs text-gray-400 dark:text-gray-500 font-black uppercase tracking-widest">{{ __('dashboard.widget_financial_status_month') }}</h4>
                  <p class="text-xl font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ $currentMonthName }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100/50 dark:border-gray-700/50">
                  <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase">Total Tagihan</span>
                  <p class="text-lg font-black text-gray-900 dark:text-white mt-1">Rp {{ number_format($totalBillings, 0, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100/50 dark:border-gray-700/50">
                  <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase">Sudah Dibayar</span>
                  <p class="text-lg font-black text-emerald-600 mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100/50 dark:border-gray-700/50 flex items-center justify-between">
                  <div>
                    <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase">Collection Rate</span>
                    <p class="text-lg font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ $collectionRate }}%</p>
                  </div>
                  <div class="w-12 h-12 rounded-full border-4 border-indigo-100 dark:border-indigo-950 flex items-center justify-center text-xs font-black text-indigo-600 dark:text-indigo-400">
                    {{ round($collectionRate) }}%
                  </div>
                </div>
              </div>
            </div>

            <!-- Widget: Jurnal Mengajar Hari Ini -->
            <div x-show="isWidgetVisible('teaching_journal_today')" :style="'order: ' + getWidgetOrder('teaching_journal_today')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <div class="flex justify-between items-center mb-6">
                  <div>
                    <h4 class="text-lg font-black text-gray-900 dark:text-white">{{ __('dashboard.widget_teaching_journal_today') }}</h4>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-0.5">Tanggal: {{ $journalDateFormatted }}</p>
                  </div>
                  <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-black">
                    {{ $journalFilledCount }} / {{ $totalTeachers }} Guru Isi
                  </span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 h-3 rounded-full overflow-hidden mb-6">
                  <div class="bg-indigo-600 h-full rounded-full" style="width: {{ $journalFilledRate }}%"></div>
                </div>
                @if(count($unfilledTeachers) > 0)
                  <div>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-wider mb-3">Guru Belum Mengisi Jurnal:</p>
                    <div class="flex flex-wrap gap-2">
                      @foreach($unfilledTeachers as $ut)
                        <span class="px-3 py-1.5 bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 rounded-xl text-xs font-bold border border-red-100/50 dark:border-red-900/30">
                           {{ $ut['name'] }}
                        </span>
                      @endforeach
                    </div>
                  </div>
                @else
                  <p class="text-xs text-emerald-600 font-bold"> Luar biasa! Semua guru sudah mengisi jurnal mengajar hari ini.</p>
                @endif
              </div>
            </div>

            <!-- Widget: Rata-rata Nilai Semester (ApexCharts) -->
            <div x-show="isWidgetVisible('average_grades_semester')" :style="'order: ' + getWidgetOrder('average_grades_semester')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <div class="flex justify-between items-center mb-6">
                  <div>
                    <h4 class="text-lg font-black text-gray-900 dark:text-white">{{ __('dashboard.widget_average_grades_semester') }}</h4>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-0.5">Rata-rata Penilaian per Mapel & Angkatan</p>
                  </div>
                  <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/30 text-purple-600 flex items-center justify-center text-lg">
                    
                  </div>
                </div>
                <div id="averageGradesChart" class="w-full h-80" data-grades="{{ json_encode($averageGrades) }}"></div>
              </div>
            </div>

            <!-- Widget: Smart Alerts Terbaru -->
            <div x-show="isWidgetVisible('smart_alerts_latest')" :style="'order: ' + getWidgetOrder('smart_alerts_latest')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center">
                  <span class="w-2.5 h-6 bg-rose-500 rounded-full mr-3"></span>
                  {{ __('dashboard.widget_smart_alerts_latest') }}
                </h4>
                <div class="space-y-4">
                  @forelse($latestSmartAlerts as $sa)
                    <div class="p-4 bg-rose-50/50 dark:bg-rose-950/10 border border-rose-100 dark:border-rose-900/30 rounded-2xl flex items-center justify-between">
                      <div>
                        <p class="text-sm font-bold text-rose-900 dark:text-rose-300">{{ $sa->title }}</p>
                        <p class="text-xs text-rose-700 dark:text-rose-400 mt-1">{{ $sa->description }}</p>
                      </div>
                      <span class="px-2.5 py-1 bg-rose-200 dark:bg-rose-950 text-rose-800 dark:text-rose-300 text-[10px] font-black uppercase tracking-wider rounded-lg">
                        {{ $sa->severity }}
                      </span>
                    </div>
                  @empty
                    <p class="text-sm text-gray-400 dark:text-gray-500 italic py-6 text-center">Tidak ada alert aktif saat ini.</p>
                  @endforelse
                </div>
              </div>
            </div>

            <!-- Widget: Approval Pending -->
            <div x-show="isWidgetVisible('approval_pending')" :style="'order: ' + getWidgetOrder('approval_pending')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <div class="w-12 h-12 bg-amber-50 dark:bg-amber-950/30 text-amber-600 rounded-2xl flex items-center justify-center text-xl">
                    
                  </div>
                  <div>
                    <h4 class="text-base font-black text-gray-900 dark:text-white">{{ __('dashboard.widget_approval_pending') }}</h4>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-0.5">Membutuhkan Tinjauan & Approval Peran Anda</p>
                  </div>
                </div>
                <div class="flex items-center space-x-4">
                  <span class="w-10 h-10 rounded-full {{ $pendingApprovalsCount > 0 ? 'bg-amber-500 text-white animate-pulse' : 'bg-gray-100 dark:bg-gray-700 text-gray-500' }} flex items-center justify-center font-black text-sm">
                    {{ $pendingApprovalsCount }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Widget: Top 5 Siswa XP -->
            <div x-show="isWidgetVisible('top_xp_students')" :style="'order: ' + getWidgetOrder('top_xp_students')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center">
                  <span class="w-2.5 h-6 bg-amber-500 rounded-full mr-3"></span>
                   {{ __('dashboard.widget_top_xp_students') }}
                </h4>
                <div class="space-y-4">
                  @foreach($topXpStudents as $idx => $ts)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50">
                      <div class="flex items-center space-x-3">
                        <span class="w-7 h-7 flex items-center justify-center rounded-full text-xs font-black bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900">
                          #{{ $idx + 1 }}
                        </span>
                        <div>
                          <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $ts->user->name ?? 'Siswa' }}</p>
                          <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase">{{ $ts->classroom->name ?? 'Kelas' }}</p>
                        </div>
                      </div>
                      <span class="text-xs font-black text-amber-500">{{ $ts->total_xp }} XP</span>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>

            <!-- Widget: Rekap Mutaba'ah Minggu Ini -->
            <div x-show="isWidgetVisible('weekly_mutabaah_recap')" :style="'order: ' + getWidgetOrder('weekly_mutabaah_recap')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center space-x-4">
                  <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 rounded-2xl flex items-center justify-center text-xl">
                    
                  </div>
                  <div>
                    <h4 class="text-base font-black text-gray-900 dark:text-white">{{ __('dashboard.widget_weekly_mutabaah_recap') }}</h4>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-0.5">Rata-rata Amalan Ibadah Mingguan Siswa</p>
                  </div>
                </div>
                <div class="flex items-center space-x-8">
                  <div class="text-center">
                    <span class="text-2xl font-black text-emerald-600">{{ $mutabaahAverageScore }}%</span>
                    <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider">Skor Amalan</p>
                  </div>
                  <div class="text-center">
                    <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $mutabaahGradeAPercent }}%</span>
                    <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider">Grade A</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Widget: Tren Keuangan & Aktivitas (Existing) -->
            <div x-show="isWidgetVisible('financial_trend') || isWidgetVisible('live_feed')"
               :style="'order: ' + Math.min(getWidgetOrder('financial_trend'), getWidgetOrder('live_feed'))"
               class="grid grid-cols-1 xl:grid-cols-3 gap-8 transition-all duration-300"
               style="display: none;">
              
              <!-- Sub-Widget: Financial Income Chart -->
              <div x-show="isWidgetVisible('financial_trend')"
                 :style="'order: ' + getWidgetOrder('financial_trend')"
                 :class="isWidgetVisible('live_feed') ? 'xl:col-span-2' : 'xl:col-span-3'"
                 class="bg-white dark:bg-gray-800 rounded-5xl p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group transition-all duration-300"
                 style="display: none;">
                <div class="flex justify-between items-center mb-8">
                  <div>
                    <h4 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">{{ __('dashboard.financial_income_trend') }}</h4>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ __('dashboard.last_7_days') }}</p>
                  </div>
                  <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    
                  </div>
                </div>
                
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

                <div x-show="!isLoadingCharts" 
                   id="financialTrendChart" 
                   class="w-full h-80 -ml-4" 
                   style="display: none;"
                   data-has-data="{{ (isset($payment_trend) && $payment_trend->count() > 0) ? 'true' : 'false' }}"
                   data-series="{{ json_encode(isset($payment_trend) ? $payment_trend->pluck('total')->toArray() : []) }}"
                   data-categories="{{ json_encode(isset($payment_trend) ? $payment_trend->pluck('date')->map(function($date) { return \Carbon\Carbon::parse($date)->format('d M'); })->toArray() : []) }}"
                   data-series-name="{{ __('dashboard.financial_income_trend') }} (Rp)"
                   data-no-data-msg="{{ __('dashboard.no_income_data') }}"
                 ></div>
              </div>

              <!-- Sub-Widget: Live Activity Feed -->
              <div x-show="isWidgetVisible('live_feed')"
                 :style="'order: ' + getWidgetOrder('live_feed')"
                 :class="isWidgetVisible('financial_trend') ? 'xl:col-span-1' : 'xl:col-span-3'"
                 class="bg-white dark:bg-gray-800 rounded-5xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col transition-all duration-300"
                 style="display: none;">
                <div class="flex justify-between items-center mb-8">
                  <div>
                    <h4 class="text-lg font-black text-gray-900 dark:text-white">{{ __('dashboard.live_feed') }}</h4>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ __('dashboard.recent_payments') }}</p>
                  </div>
                  <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                    <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                    <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                  </div>
                </div>

                <div x-show="isLoadingCharts" class="space-y-6 flex-1 animate-pulse">
                  <div class="flex items-center space-x-4">
                    <div class="rounded-xl bg-gray-200 dark:bg-gray-700 h-10 w-10"></div>
                    <div class="flex-1 space-y-2 py-1">
                      <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                      <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                    </div>
                  </div>
                </div>

                <div x-show="!isLoadingCharts" class="flex-1 overflow-y-auto pr-2 space-y-6" style="display: none;">
                  @forelse($dashboard_recent_payments ?? [] as $recentPayment)
                    @if(!is_object($recentPayment)) @continue @endif
                    <div class="flex items-start group">
                      <div class="relative">
                        <div class="w-10 h-10 bg-gray-50 dark:bg-gray-750 rounded-xl border border-gray-100 dark:border-gray-700 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold z-10 relative group-hover:scale-110 transition-transform">
                          {{ substr($recentPayment->billing?->student?->user?->name ?? 'A', 0, 1) }}
                        </div>
                        @if(!$loop->last)
                          <div class="absolute top-10 left-1/2 -ml-px w-0.5 h-full bg-gray-100 dark:bg-gray-700"></div>
                        @endif
                      </div>
                      <div class="ml-4 flex-1">
                        <div class="flex justify-between items-start">
                          <p class="text-sm font-bold text-gray-900 dark:text-gray-100 leading-tight">{{ $recentPayment->billing?->student?->user?->name ?? __('navigation.students') }}</p>
                          <span class="text-[10px] font-bold text-gray-400 whitespace-nowrap ml-2">{{ $recentPayment->created_at?->diffForHumans() ?? '-' }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $recentPayment->billing?->category?->name ?? __('dashboard.billing_details') }}</p>
                        <div class="mt-2 inline-flex items-center px-2 py-1 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 rounded text-[10px] font-black uppercase tracking-widest">
                          + Rp {{ number_format($recentPayment->amount_paid ?? 0, 0, ',', '.') }}
                        </div>
                      </div>
                    </div>
                  @empty
                    <div class="flex flex-col items-center justify-center h-full text-center opacity-50 py-10">
                      <p class="text-xs font-bold text-gray-500">{{ __('dashboard.no_transactions') }}</p>
                    </div>
                  @endforelse
                </div>
              </div>
            </div>

            <!-- Widget: SPMB Terpadu (Existing) -->
            @if(isset($spmb_stats))
              <div x-show="isWidgetVisible('spmb_analytics')" :style="'order: ' + getWidgetOrder('spmb_analytics')" class="w-full transition-all duration-300" style="display: none;">
                @include('dashboard.partials.spmb-analytics')
              </div>
            @endif

          @endif


          <!-- ========================================== -->
          <!-- GURU (TEACHER) WIDGETS           -->
          <!-- ========================================== -->

          @if(auth()->user()->hasRole('guru'))

            <!-- Widget: teacher_schedule_today -->
            <div x-show="isWidgetVisible('teacher_schedule_today')" :style="'order: ' + getWidgetOrder('teacher_schedule_today')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center justify-between">
                  <span> {{ __('dashboard.widget_teacher_schedule_today') }} ({{ $todayDayName }})</span>
                  @if($isWeekend)
                    <span class="text-[10px] bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 px-3 py-1 rounded-xl font-bold">Fallback Weekend</span>
                  @endif
                </h4>
                <div class="space-y-4">
                  @forelse($schedules ?? [] as $sch)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50">
                      <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $sch->subject?->name ?? 'Mapel' }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-1">Kelas: {{ $sch->classroom?->name ?? 'N/A' }} • Sesi: {{ $sch->session_start }} - {{ $sch->session_end }}</p>
                      </div>
                      <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 text-xs font-black rounded-lg">
                        Jam ke-{{ $sch->session_start }}
                      </span>
                    </div>
                  @empty
                    <div class="text-center py-6 text-gray-400 dark:text-gray-500 italic text-sm">Tidak ada jadwal mengajar hari ini.</div>
                  @endforelse
                </div>
              </div>
            </div>

            <!-- Widget: teacher_pending_attendance -->
            <div x-show="isWidgetVisible('teacher_pending_attendance')" :style="'order: ' + getWidgetOrder('teacher_pending_attendance')" class="w-full transition-all duration-300" style="display: none;">
              @if(count($pendingAttendanceAlerts) > 0)
                <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/30 text-amber-900 dark:text-amber-300 rounded-3xl p-6 shadow-sm">
                  <h4 class="text-base font-black flex items-center">
                     {{ __('dashboard.widget_teacher_pending_attendance') }}
                  </h4>
                  <div class="mt-4 space-y-2">
                    @foreach($pendingAttendanceAlerts as $paa)
                      <div class="flex items-center justify-between text-sm">
                        <span>Kelas <strong>{{ $paa['classroom_name'] }}</strong> hari ini belum diabsensi.</span>
                        <a href="{{ route('attendances.index', ['classroom_id' => $paa['classroom_id']]) }}" class="text-xs font-black underline uppercase text-indigo-600 dark:text-indigo-400">Isi Absen →</a>
                      </div>
                    @endforeach
                  </div>
                </div>
              @endif
            </div>

            <!-- Widget: teacher_journal_today -->
            <div x-show="isWidgetVisible('teacher_journal_today')" :style="'order: ' + getWidgetOrder('teacher_journal_today')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl {{ $journalTodayFilled ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600' : 'bg-red-50 dark:bg-red-950/30 text-red-600' }}">
                    {{ $journalTodayFilled ? '' : '' }}
                  </div>
                  <div>
                    <h4 class="text-base font-black text-gray-900 dark:text-white">{{ __('dashboard.widget_teacher_journal_today') }}</h4>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-0.5">{{ $journalTodayFilled ? 'Sudah Diisi Hari Ini' : 'Belum Diisi Hari Ini' }}</p>
                  </div>
                </div>
                <a href="{{ route('teaching-journals.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider">
                  {{ $journalTodayFilled ? 'Input Baru' : 'Isi Jurnal' }}
                </a>
              </div>
            </div>

            <!-- Widget: teacher_ungraded_assignments -->
            <div x-show="isWidgetVisible('teacher_ungraded_assignments')" :style="'order: ' + getWidgetOrder('teacher_ungraded_assignments')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/30 text-purple-600 rounded-2xl flex items-center justify-center text-xl">
                    
                  </div>
                  <div>
                    <h4 class="text-base font-black text-gray-900 dark:text-white">{{ __('dashboard.widget_teacher_ungraded_assignments') }}</h4>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-0.5">Pengumpulan tugas e-learning menunggu penilaian</p>
                  </div>
                </div>
                <span class="px-4 py-2 bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 text-sm font-black rounded-xl">
                  {{ $ungradedSubmissionsCount }} Tugas
                </span>
              </div>
            </div>

            <!-- Widget: teacher_tahfidz_progress -->
            <div x-show="isWidgetVisible('teacher_tahfidz_progress')" :style="'order: ' + getWidgetOrder('teacher_tahfidz_progress')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <div class="flex justify-between items-center mb-6">
                  <div>
                    <h4 class="text-lg font-black text-gray-900 dark:text-white">{{ __('dashboard.widget_teacher_tahfidz_progress') }}</h4>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-0.5">Jumlah Setoran Ziyadah/Murajaah 7 Hari Terakhir: <strong class="text-emerald-600">{{ $tahfidzLogsCount }}</strong></p>
                  </div>
                  <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl text-emerald-600 flex items-center justify-center text-lg">
                    
                  </div>
                </div>
                <div id="tahfidzTrendChart" class="w-full h-60" data-trend="{{ json_encode($tahfidzTrend) }}"></div>
              </div>
            </div>

            <!-- Widget: teacher_recent_notifications -->
            <div x-show="isWidgetVisible('teacher_recent_notifications')" :style="'order: ' + getWidgetOrder('teacher_recent_notifications')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center">
                  <span class="w-2.5 h-6 bg-indigo-600 rounded-full mr-3"></span>
                  {{ __('dashboard.widget_teacher_recent_notifications') }}
                </h4>
                <div class="space-y-4">
                  @foreach($recentNotifications as $notif)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50 flex items-center justify-between">
                      <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $notif->title }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $notif->desc }}</p>
                      </div>
                      <span class="text-[10px] text-gray-400 font-bold ml-2">{{ $notif->time }}</span>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>

            <!-- Advisory list if wali kelas (Existing component retained) -->
            @if($isWaliKelas)
              <div class="bg-white dark:bg-gray-800 rounded-4xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                <h4 class="text-lg font-black text-gray-800 dark:text-white mb-4 flex items-center">
                  <span class="w-2.5 h-6 bg-blue-600 rounded-full mr-3"></span>
                  {{ __('dashboard.classroom_advisory') }}
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  @foreach($my_classrooms as $kelas)
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50 flex justify-between items-center">
                      <span class="font-bold text-gray-800 dark:text-gray-200">{{ $kelas->name }}</span>
                      <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">{{ __('dashboard.student_count', ['count' => $kelas->students_count]) }}</span>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif

            <!-- Widget: teacher_todo_widget -->
            <div x-show="isWidgetVisible('teacher_todo_widget')" :style="'order: ' + getWidgetOrder('teacher_todo_widget')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                  <h4 class="text-lg font-black text-gray-800 dark:text-white flex items-center">
                    <span class="w-2.5 h-6 bg-violet-600 rounded-full mr-3"></span>
                     Todo List Saya
                  </h4>
                  <div class="flex items-center gap-2">
                    @if(($todoOverdueCount ?? 0) > 0)
                      <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-lg text-[9px] font-black uppercase tracking-wider animate-pulse">
                         {{ $todoOverdueCount }} Terlambat
                      </span>
                    @endif
                    @if(($todoDueTodayCount ?? 0) > 0)
                      <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-lg text-[9px] font-black uppercase tracking-wider">
                         {{ $todoDueTodayCount }} Hari Ini
                      </span>
                    @endif
                    <a href="{{ route('todos.index') }}" class="px-2.5 py-1 bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400 rounded-lg text-[10px] font-black uppercase tracking-wider hover:bg-violet-100 transition-all">
                      Lihat Semua ({{ $todoPendingCount ?? 0 }})
                    </a>
                  </div>
                </div>

                {{-- Quick Add Form --}}
                <form action="{{ route('todos.store') }}" method="POST" class="mb-5">
                  @csrf
                  <div class="flex gap-2">
                    <input type="text" name="title" required placeholder="Tambah todo baru..." class="flex-1 text-xs font-bold rounded-xl border-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-violet-500">
                    <input type="hidden" name="priority" value="medium">
                    <input type="hidden" name="category" value="mengajar">
                    <button type="submit" class="px-4 py-2 bg-violet-600 text-white rounded-xl text-xs font-black hover:bg-violet-700 transition-all shadow-sm">
                      + Tambah
                    </button>
                  </div>
                </form>

                {{-- Todo Items --}}
                <div class="space-y-2">
                  @forelse(($todoPending ?? collect()) as $todo)
                    @php
                      $prioColors = [
                        'high' => 'border-l-red-400',
                        'medium' => 'border-l-amber-400',
                        'low' => 'border-l-emerald-400',
                      ];
                    @endphp
                    <div class="flex items-center gap-3 border-l-4 {{ $prioColors[$todo->priority] ?? 'border-l-gray-300' }} rounded-xl px-4 py-3 bg-gray-50 dark:bg-gray-900/50 group hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all">
                      <form method="POST" action="{{ route('todos.toggle', $todo) }}" class="flex-shrink-0">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-5 h-5 rounded-md border-2 border-gray-300 dark:border-gray-500 hover:border-violet-400 hover:bg-violet-50 transition-all flex items-center justify-center" title="Tandai selesai">
                        </button>
                      </form>
                      <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">{{ $todo->title }}</p>
                        <div class="flex items-center gap-2 mt-1">
                          <span class="text-[9px] font-black uppercase tracking-wider text-gray-400">{{ $todo->category_icon }} {{ $todo->category_label }}</span>
                          @if($todo->due_date)
                            <span class="text-[9px] font-black uppercase tracking-wider {{ $todo->isOverdue() ? 'text-red-500' : ($todo->isDueToday() ? 'text-amber-500' : 'text-gray-400') }}">
                               {{ $todo->isOverdue() ? 'TERLAMBAT' : ($todo->isDueToday() ? 'HARI INI' : $todo->due_date->translatedFormat('d M')) }}
                            </span>
                          @endif
                        </div>
                      </div>
                      <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase {{ $todo->priority === 'high' ? 'bg-red-100 text-red-600' : ($todo->priority === 'medium' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600') }}">
                        {{ $todo->priority }}
                      </span>
                    </div>
                  @empty
                    <div class="text-center py-6">
                      <p class="text-xs text-gray-400 dark:text-gray-500 font-bold"> Semua tugas selesai! Tidak ada todo pending.</p>
                    </div>
                  @endforelse
                </div>
              </div>
            </div>

          @endif


          <!-- ========================================== -->
          <!-- SISWA (STUDENT) WIDGETS           -->
          <!-- ========================================== -->

          @if(auth()->user()->hasRole('student'))

            <!-- Widget: student_schedule_today -->
            <div x-show="isWidgetVisible('student_schedule_today')" :style="'order: ' + getWidgetOrder('student_schedule_today')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center justify-between">
                  <span> {{ __('dashboard.widget_student_schedule_today') }} ({{ $todayDayName }})</span>
                  @if($isWeekend)
                    <span class="text-[10px] bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 px-3 py-1 rounded-xl font-bold">Fallback Weekend</span>
                  @endif
                </h4>
                <div class="space-y-4">
                  @forelse($studentSchedulesToday ?? [] as $ssch)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50">
                      <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $ssch->subject?->name ?? 'Mapel' }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-1">Guru: {{ $ssch->teacher?->name ?? 'N/A' }} • Sesi: {{ $ssch->session_start }} - {{ $ssch->session_end }}</p>
                      </div>
                      <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 text-xs font-black rounded-lg">
                        Jam ke-{{ $ssch->session_start }}
                      </span>
                    </div>
                  @empty
                    <div class="text-center py-6 text-gray-400 dark:text-gray-500 italic text-sm">Tidak ada pelajaran hari ini.</div>
                  @endforelse
                </div>
              </div>
            </div>

            <!-- Widget: student_pending_assignments -->
            <div x-show="isWidgetVisible('student_pending_assignments')" :style="'order: ' + getWidgetOrder('student_pending_assignments')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center">
                  <span class="w-2.5 h-6 bg-rose-500 rounded-full mr-3"></span>
                  {{ __('dashboard.widget_student_pending_assignments') }}
                </h4>
                <div class="space-y-4">
                  @forelse($unsubmittedAssignments ?? [] as $ua)
                    <div class="p-4 bg-rose-50/50 dark:bg-rose-950/10 border border-rose-100 dark:border-rose-900/30 rounded-2xl flex items-center justify-between">
                      <div>
                        <p class="text-sm font-bold text-rose-950 dark:text-rose-300">{{ $ua->title }}</p>
                        <p class="text-xs text-rose-700 dark:text-rose-400 mt-1">Batas Waktu: {{ $ua->due_at->translatedFormat('d M Y, H:i') }}</p>
                      </div>
                      <a href="{{ route('elearning.assignment.show', $ua->uuid) }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase rounded-xl tracking-wider shrink-0 ml-4">
                        Kirim
                      </a>
                    </div>
                  @empty
                    <p class="text-xs text-gray-400 dark:text-gray-500 italic text-center py-4">Hore! Tidak ada tugas pending yang harus diserahkan.</p>
                  @endforelse
                </div>
              </div>
            </div>

            <!-- Widget: student_gamification -->
            <div x-show="isWidgetVisible('student_gamification')" :style="'order: ' + getWidgetOrder('student_gamification')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-gradient-to-br from-indigo-900 to-indigo-950 text-white rounded-4xl p-6 shadow-lg relative overflow-hidden border border-indigo-800">
                <div class="absolute -right-10 -bottom-10 opacity-10">
                  
                </div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                  <div>
                    <span class="px-3 py-1 bg-indigo-800 text-indigo-300 rounded-xl text-xs font-black uppercase tracking-wider">LEVEL {{ $studentLevel }}</span>
                    <h4 class="text-2xl font-black mt-2 text-white">{{ $student_profile->user->name ?? 'Siswa' }}</h4>
                    <p class="text-xs text-indigo-300 font-bold mt-1">Sistem Gamifikasi Generasi Khaira Ummah</p>
                  </div>
                  <div class="flex items-center space-x-8">
                    <div class="text-center">
                      <span class="text-3xl font-black text-indigo-200">{{ $student_profile->total_xp }}</span>
                      <p class="text-[9px] text-indigo-400 font-bold uppercase tracking-wider">Total XP</p>
                    </div>
                    <div class="text-center">
                      <span class="text-3xl font-black text-amber-400"> {{ $student_profile->current_streak }}</span>
                      <p class="text-[9px] text-indigo-400 font-bold uppercase tracking-wider">Streak Hari</p>
                    </div>
                  </div>
                </div>
                <div class="mt-6">
                  <div class="flex justify-between text-xs text-indigo-300 font-bold mb-2">
                    <span>Progres Level</span>
                    <span>{{ $studentXpProgress }}%</span>
                  </div>
                  <div class="w-full bg-indigo-950 h-3 rounded-full overflow-hidden border border-indigo-800">
                    <div class="bg-gradient-to-r from-indigo-400 to-indigo-300 h-full rounded-full" style="width: {{ $studentXpProgress }}%"></div>
                  </div>
                </div>
                @if($recentBadges->count() > 0)
                  <div class="mt-6 pt-4 border-t border-indigo-800/60">
                    <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider mb-3">Badge Terbaru:</p>
                    <div class="flex space-x-3">
                      @foreach($recentBadges as $badge)
                        <div class="flex items-center space-x-1.5 bg-indigo-950/60 border border-indigo-800 px-3 py-1.5 rounded-xl text-xs font-black text-indigo-200">
                          <span>{{ $badge->icon ?? '' }}</span>
                          <span>{{ $badge->name }}</span>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif
              </div>
            </div>

            <!-- Widget: student_recent_scores -->
            <div x-show="isWidgetVisible('student_recent_scores')" :style="'order: ' + getWidgetOrder('student_recent_scores')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center">
                  <span class="w-2.5 h-6 bg-indigo-500 rounded-full mr-3"></span>
                  {{ __('dashboard.widget_student_recent_scores') }}
                </h4>
                <div class="space-y-4">
                  @forelse($recentScores ?? [] as $rs)
                    <div class="flex items-center justify-between p-3.5 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50">
                      <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $rs->subject?->name ?? 'Mapel' }}</p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase mt-1">{{ $rs->type }} • Predikat: {{ $rs->predikat }}</p>
                      </div>
                      <span class="w-12 h-12 flex items-center justify-center rounded-xl font-black text-base {{ $rs->is_passed ? 'bg-emerald-50 dark:bg-emerald-950 text-emerald-600' : 'bg-red-50 dark:bg-red-950 text-red-600' }}">
                        {{ round($rs->score) }}
                      </span>
                    </div>
                  @empty
                    <p class="text-xs text-gray-400 dark:text-gray-500 italic text-center py-4">Belum ada nilai terbit di semester ini.</p>
                  @endforelse
                </div>
              </div>
            </div>

            <!-- Widget: student_mutabaah_today (AJAX quick toggle toggles mutaba'ah) -->
            <div x-show="isWidgetVisible('student_mutabaah_today')" :style="'order: ' + getWidgetOrder('student_mutabaah_today')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm" x-data="{
                loading: {},
                async toggleAmalan(itemId, currentStatus) {
                  this.loading[itemId] = true;
                  try {
                    const response = await fetch('{{ route('mutabaah.toggle') }}', {
                      method: 'POST',
                      headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      },
                      body: JSON.stringify({
                        student_id: '{{ $student_profile->id ?? 0 }}',
                        item_id: itemId,
                        date: '{{ today()->format('Y-m-d') }}',
                        status: !currentStatus
                      })
                    });
                    const data = await response.json();
                    if (data.success) {
                      window.location.reload();
                    } else {
                      alert('Gagal memperbarui status amalan.');
                    }
                  } catch (e) {
                    alert('Koneksi terputus. Sila coba lagi.');
                  } finally {
                    this.loading[itemId] = false;
                  }
                }
              }">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center justify-between">
                  <span> {{ __('dashboard.widget_student_mutabaah_today') }}</span>
                  <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold">Hari Ini: {{ today()->translatedFormat('d F Y') }}</span>
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  @foreach($mutabaahItems as $item)
                    @php
                      $isCompleted = isset($mutabaahTodayLogs[$item->id]) ? $mutabaahTodayLogs[$item->id]->is_completed : false;
                    @endphp
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50 flex items-center justify-between">
                      <div>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $item->name }}</p>
                        <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold uppercase mt-1">Poin: +{{ $item->points }} XP</p>
                      </div>
                      <button @click="toggleAmalan({{ $item->id }}, {{ $isCompleted ? 'true' : 'false' }})" 
                          :disabled="loading[{{ $item->id }}]"
                          class="px-4 py-2 text-xs font-black rounded-xl uppercase tracking-wider transition-all {{ $isCompleted ? 'bg-emerald-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        <span x-show="loading[{{ $item->id }}]">...</span>
                        <span x-show="!loading[{{ $item->id }}]">{{ $isCompleted ? 'Sudah ' : 'Belum ' }}</span>
                      </button>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>

            <!-- Widget: student_library_loans -->
            <div x-show="isWidgetVisible('student_library_loans')" :style="'order: ' + getWidgetOrder('student_library_loans')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center">
                  <span class="w-2.5 h-6 bg-indigo-500 rounded-full mr-3"></span>
                   {{ __('dashboard.widget_student_library_loans') }}
                </h4>
                <div class="space-y-4">
                  @forelse($borrowedBooks ?? [] as $loan)
                    @php
                      $isOverdue = $loan->due_at->isPast();
                    @endphp
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50 flex items-center justify-between">
                      <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $loan->book?->title ?? 'Judul Buku' }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-bold mt-1">Harus kembali: {{ $loan->due_at->translatedFormat('d M Y') }}</p>
                      </div>
                      @if($isOverdue)
                        <span class="px-2.5 py-1 bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300 text-[10px] font-black uppercase rounded-lg">
                          Terlambat 
                        </span>
                      @else
                        <span class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-[10px] font-black uppercase rounded-lg">
                          Aktif
                        </span>
                      @endif
                    </div>
                  @empty
                    <p class="text-xs text-gray-400 dark:text-gray-500 italic text-center py-4">Tidak ada peminjaman buku perpustakaan aktif.</p>
                  @endforelse
                </div>
              </div>
            </div>

            <!-- Widget: student_recent_forum -->
            <div x-show="isWidgetVisible('student_recent_forum')" :style="'order: ' + getWidgetOrder('student_recent_forum')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center">
                  <span class="w-2.5 h-6 bg-indigo-500 rounded-full mr-3"></span>
                   {{ __('dashboard.widget_student_recent_forum') }}
                </h4>
                <div class="space-y-4">
                  @forelse($studentForumThreads ?? [] as $thread)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50 flex items-center justify-between">
                      <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $thread->title }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Ditulis oleh: {{ $thread->author?->name }} • {{ $thread->created_at->diffForHumans() }}</p>
                      </div>
                      <span class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-black rounded-lg">
                        {{ $thread->reply_count }} Balasan
                      </span>
                    </div>
                  @empty
                    <p class="text-xs text-gray-400 dark:text-gray-500 italic text-center py-4">Belum ada diskusi kelas aktif di forum.</p>
                  @endforelse
                </div>
              </div>
            </div>

          @endif


          <!-- ========================================== -->
          <!-- ORANG TUA (PARENT) WIDGETS         -->
          <!-- ========================================== -->

          @if(auth()->user()->hasRole('parent'))

            <!-- Widget: parent_child_attendance -->
            <div x-show="isWidgetVisible('parent_child_attendance')" :style="'order: ' + getWidgetOrder('parent_child_attendance')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center">
                  <span class="w-2.5 h-6 bg-indigo-500 rounded-full mr-3"></span>
                   {{ __('dashboard.widget_parent_child_attendance') }}
                </h4>
                <div class="space-y-4">
                  @foreach($children as $ch)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50 flex items-center justify-between">
                      <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $ch['profile']->user->name }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Update terakhir: {{ $ch['latestAttendanceDate'] ?? 'Belum ada record' }}</p>
                      </div>
                      <span class="px-4 py-2 text-xs font-black rounded-xl uppercase tracking-wider
                        {{ $ch['childAttendanceToday']?->status === 'H' ? 'bg-emerald-50 dark:bg-emerald-950 text-emerald-600' : 'bg-red-50 dark:bg-red-950 text-red-600' }}">
                        {{ $ch['childAttendanceToday']?->status === 'H' ? 'Hadir ' : ($ch['childAttendanceToday']?->status ?? 'Alpa ') }}
                      </span>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>

            <!-- Widget: parent_child_scores -->
            <div x-show="isWidgetVisible('parent_child_scores')" :style="'order: ' + getWidgetOrder('parent_child_scores')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center">
                  <span class="w-2.5 h-6 bg-indigo-500 rounded-full mr-3"></span>
                  {{ __('dashboard.widget_parent_child_scores') }}
                </h4>
                <div class="space-y-6">
                  @foreach($children as $ch)
                    <div>
                      <p class="text-[10px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-wider mb-3">Nilai untuk: {{ $ch['profile']->user->name }}</p>
                      <div class="space-y-3">
                        @forelse($ch['childRecentScores'] as $rcs)
                          <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50 flex justify-between items-center text-sm">
                            <span>{{ $rcs->subject?->name }} ({{ $rcs->type }})</span>
                            <span class="font-black text-indigo-600 dark:text-indigo-400">{{ round($rcs->score) }}</span>
                          </div>
                        @empty
                          <p class="text-xs text-gray-400 italic">Belum ada nilai terekam.</p>
                        @endforelse
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>

            <!-- Widget: parent_child_mutabaah (Includes Acknowledge action) -->
            <div x-show="isWidgetVisible('parent_child_mutabaah')" :style="'order: ' + getWidgetOrder('parent_child_mutabaah')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center">
                  <span class="w-2.5 h-6 bg-indigo-500 rounded-full mr-3"></span>
                   {{ __('dashboard.widget_parent_child_mutabaah') }}
                </h4>
                <div class="space-y-6">
                  @foreach($children as $ch)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                      <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $ch['profile']->user->name }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Skor Amalan Minggu Ini: <strong class="text-emerald-600">{{ $ch['childMutabaahScore'] }}%</strong> • Grade: <strong>{{ $ch['childMutabaahGrade'] }}</strong></p>
                      </div>
                      @if($ch['latestUnverifiedDate'])
                        <form action="{{ route('mutabaah.verify', $ch['profile']->id) }}" method="POST" class="shrink-0">
                          @csrf
                          <input type="hidden" name="date" value="{{ $ch['latestUnverifiedDate'] }}">
                          <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-emerald-900/15">
                            Acknowledge ({{ Carbon\Carbon::parse($ch['latestUnverifiedDate'])->translatedFormat('d M') }})
                          </button>
                        </form>
                      @else
                        <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 text-xs font-black rounded-lg">
                          Terverifikasi 
                        </span>
                      @endif
                    </div>
                  @endforeach
                </div>
              </div>
            </div>

            <!-- Widget: parent_upcoming_events -->
            <div x-show="isWidgetVisible('parent_upcoming_events')" :style="'order: ' + getWidgetOrder('parent_upcoming_events')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center">
                  <span class="w-2.5 h-6 bg-indigo-500 rounded-full mr-3"></span>
                   {{ __('dashboard.widget_parent_upcoming_events') }}
                </h4>
                <div class="space-y-4">
                  @forelse($upcomingSchoolEvents ?? [] as $event)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50 flex items-center justify-between">
                      <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $event->title }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $event->description }}</p>
                      </div>
                      <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 text-xs font-black rounded-lg">
                        {{ $event->start_date->translatedFormat('d M Y') }}
                      </span>
                    </div>
                  @empty
                    <p class="text-xs text-gray-400 dark:text-gray-500 italic text-center py-4">Belum ada agenda sekolah terdekat.</p>
                  @endforelse
                </div>
              </div>
            </div>

            <!-- Widget: parent_report_progress (Chart) -->
            <div x-show="isWidgetVisible('parent_report_progress')" :style="'order: ' + getWidgetOrder('parent_report_progress')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center">
                  <span class="w-2.5 h-6 bg-indigo-500 rounded-full mr-3"></span>
                   {{ __('dashboard.widget_parent_report_progress') }}
                </h4>
                @foreach($children as $ch)
                  <div class="mb-8 last:mb-0">
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-black uppercase tracking-wider mb-4">Rapor Progress: {{ $ch['profile']->user->name }}</p>
                    <div id="childProgressChart-{{ $ch['profile']->id }}" class="w-full h-60 child-progress-chart" data-grades="{{ json_encode($ch['childGradesBySubject']) }}"></div>
                  </div>
                @endforeach
              </div>
            </div>

            <!-- Widget: parent_homeroom_messages -->
            <div x-show="isWidgetVisible('parent_homeroom_messages')" :style="'order: ' + getWidgetOrder('parent_homeroom_messages')" class="w-full transition-all duration-300" style="display: none;">
              <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-4xl p-6 shadow-sm">
                <div class="flex justify-between items-center mb-6">
                  <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                    <span class="w-2.5 h-6 bg-indigo-500 rounded-full mr-3"></span>
                     {{ __('dashboard.widget_parent_homeroom_messages') }}
                  </h4>
                  @if($unreadHomeroomMessagesCount > 0)
                    <span class="px-2.5 py-1 bg-red-500 text-white rounded-full text-[10px] font-black uppercase tracking-widest animate-pulse">
                      {{ $unreadHomeroomMessagesCount }} Pesan Baru
                    </span>
                  @endif
                </div>
                <div class="space-y-4">
                  @forelse($recentHomeroomMessages ?? [] as $msg)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100/50 dark:border-gray-700/50 flex items-start justify-between">
                      <div>
                        <div class="flex items-center space-x-2">
                          <span class="font-bold text-sm text-gray-800 dark:text-gray-200">{{ $msg->sender?->name }}</span>
                          @if(!$msg->is_read)
                            <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                          @endif
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 leading-relaxed">{{ $msg->message }}</p>
                      </div>
                      <span class="text-[9px] text-gray-400 font-bold ml-4 shrink-0">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                  @empty
                    <p class="text-xs text-gray-400 dark:text-gray-500 italic text-center py-4">Belum ada pesan dari wali kelas.</p>
                  @endforelse
                </div>
              </div>
            </div>

          @endif

        </div>

        <!-- Customizer Sliding Drawer Panel -->
        <div x-show="showDrawer" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
          <div class="absolute inset-0 overflow-hidden">
            <!-- Background overlay -->
            <div x-show="showDrawer" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showDrawer = false"></div>

            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
              <div x-show="showDrawer" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="pointer-events-auto w-screen max-w-md">
                <div class="flex h-full flex-col bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border-l border-gray-200 dark:border-gray-800 shadow-2xl">
                  
                  <!-- Drawer Header -->
                  <div class="px-6 py-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
                    <div>
                      <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">{{ __('dashboard.drawer_title') }}</h3>
                      <p class="text-xs font-semibold text-gray-400 mt-0.5">{{ __('dashboard.drawer_subtitle') }}</p>
                    </div>
                    <button @click="showDrawer = false" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-300 transition-all">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                  </div>

                  <!-- Drawer Body (List of Widgets with Drag & Drop) -->
                  <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                    <div class="space-y-4">
                      <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('dashboard.drag_to_sort') }}</p>
                      
                      <div class="space-y-3">
                        <template x-for="(widget, idx) in layout" :key="widget.id">
                          <div draggable="true"
                             @dragstart="dragStart($event, idx)"
                             @dragover="dragOver($event)"
                             @drop="drop($event, idx)"
                             class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm hover:border-indigo-500/50 dark:hover:border-indigo-500/50 cursor-grab active:cursor-grabbing transition-all group">
                            <div class="flex items-center space-x-3">
                              <!-- Drag Handle -->
                              <div class="text-gray-400 group-hover:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                              </div>
                              <span class="text-sm font-black text-gray-800 dark:text-gray-200" x-text="widget.name"></span>
                            </div>
                            
                            <!-- Visibility Switch Toggle -->
                            <button @click="widget.visible = !widget.visible" :class="widget.visible ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700'" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
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
                      {{ __('dashboard.reset') }}
                    </button>
                    <button @click="saveLayout()" :disabled="isSaving" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-lg shadow-indigo-900/10 flex items-center justify-center space-x-2">
                      <svg x-show="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                      <span x-text="isSaving ? '{{ __('dashboard.saving') }}' : '{{ __('dashboard.save_layout') }}'"></span>
                    </button>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Floating Custom Toast Alert -->
        <div x-show="showToastFlag"
           x-transition:enter="transition ease-out duration-300 transform"
           x-transition:enter-start="translate-y-10 opacity-0 scale-95"
           x-transition:enter-end="translate-y-0 opacity-100 scale-100"
           x-transition:leave="transition ease-in duration-200 transform"
           x-transition:leave-start="translate-y-0 opacity-100 scale-100"
           x-transition:leave-end="translate-y-10 opacity-0 scale-95"
           class="fixed bottom-6 left-6 z-50 rounded-2xl p-4 shadow-2xl flex items-center space-x-3 text-white max-w-sm"
           :class="toastType === 'success' ? 'bg-emerald-950/90 border border-emerald-800' : 'bg-red-950/90 border border-red-800'"
           style="display: none;">
          <div class="shrink-0">
            <svg x-show="toastType === 'success'" class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <svg x-show="toastType !== 'success'" class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <span class="text-xs font-black" x-text="toastMessage"></span>
        </div>

      </div>

    </div>
  </div>

  <!-- ApexCharts rendering code tailored for dynamic widgets -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      
      // 1. Financial Trend Chart (Revenue) - Admin/Superadmin/Kepala Sekolah/Waka
      const financialEl = document.querySelector("#financialTrendChart");
      if (financialEl) {
        const hasData = financialEl.getAttribute('data-has-data') === 'true';
        if (hasData) {
          const seriesData = JSON.parse(financialEl.getAttribute('data-series') || '[]');
          const categoriesData = JSON.parse(financialEl.getAttribute('data-categories') || '[]');
          const seriesName = financialEl.getAttribute('data-series-name') || '';

          const financialOptions = {
            series: [{
              name: seriesName,
              data: seriesData
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
              categories: categoriesData,
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

          const financialChart = new ApexCharts(financialEl, financialOptions);
          financialChart.render();
        } else {
          const noDataMsg = financialEl.getAttribute('data-no-data-msg') || 'Tidak ada data';
          financialEl.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 font-bold text-sm">' + noDataMsg + '</div>';
        }
      }

      // 2. SPMB Trend Chart
      const spmbEl = document.querySelector("#spmbTrendChart");
      if (spmbEl) {
        const hasSpmbData = spmbEl.getAttribute('data-has-data') === 'true';
        if (hasSpmbData) {
          const seriesData = JSON.parse(spmbEl.getAttribute('data-series') || '[]');
          const seriesName = spmbEl.getAttribute('data-series-name') || '';

          const spmbOptions = {
            series: [{
              name: seriesName,
              data: seriesData
            }],
            chart: {
              type: 'area',
              height: '100%',
              parentHeightOffset: 0,
              fontFamily: 'Inter, sans-serif',
              toolbar: { show: false },
              sparkline: { enabled: true }
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
              y: { title: { formatter: function (seriesName) { return 'Pendaftar:'; } } },
              marker: { show: false }
            }
          };

          setTimeout(() => {
            if (spmbEl.tagName.toLowerCase() === 'canvas') {
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
          }, 100);
        }
      }

      // 3. Semester Average Grades Chart (Bar Chart) - Admin/Superadmin/Kepala Sekolah/Waka
      const gradesEl = document.querySelector("#averageGradesChart");
      if (gradesEl) {
        const dataRaw = JSON.parse(gradesEl.getAttribute('data-grades') || '[]');
        if (dataRaw.length > 0) {
          const categories = dataRaw.map(d => d.subject);
          const seriesX = dataRaw.map(d => d.X);
          const seriesXI = dataRaw.map(d => d.XI);
          const seriesXII = dataRaw.map(d => d.XII);

          const gradesOptions = {
            series: [
              { name: 'Kelas X', data: seriesX },
              { name: 'Kelas XI', data: seriesXI },
              { name: 'Kelas XII', data: seriesXII }
            ],
            chart: {
              type: 'bar',
              height: 320,
              fontFamily: 'Inter, sans-serif',
              toolbar: { show: false }
            },
            plotOptions: {
              bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 6
              }
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            colors: ['#4f46e5', '#10b981', '#f59e0b'],
            xaxis: {
              categories: categories,
              labels: { style: { colors: '#9ca3af', fontWeight: 600 } }
            },
            yaxis: {
              max: 100,
              labels: { style: { colors: '#9ca3af', fontWeight: 600 } }
            },
            fill: { opacity: 1 },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            tooltip: {
              y: { formatter: (val) => val + ' / 100' }
            }
          };

          const gradesChart = new ApexCharts(gradesEl, gradesOptions);
          gradesChart.render();
        } else {
          gradesEl.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 font-bold text-sm">Belum ada data nilai akademik.</div>';
        }
      }

      // 4. Tahfidz Trend Chart (Line) - Guru
      const tahfidzEl = document.querySelector("#tahfidzTrendChart");
      if (tahfidzEl) {
        const trendRaw = JSON.parse(tahfidzEl.getAttribute('data-trend') || '[]');
        if (trendRaw.length > 0) {
          const categories = trendRaw.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
          });
          const seriesData = trendRaw.map(d => d.total);

          const tahfidzOptions = {
            series: [{
              name: 'Jumlah Setoran',
              data: seriesData
            }],
            chart: {
              type: 'line',
              height: 240,
              fontFamily: 'Inter, sans-serif',
              toolbar: { show: false }
            },
            colors: ['#10b981'],
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 4 },
            xaxis: {
              categories: categories,
              labels: { style: { colors: '#9ca3af', fontWeight: 600 } }
            },
            yaxis: {
              labels: { style: { colors: '#9ca3af', fontWeight: 600 } }
            },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 }
          };

          const tahfidzChart = new ApexCharts(tahfidzEl, tahfidzOptions);
          tahfidzChart.render();
        } else {
          tahfidzEl.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 font-bold text-sm">Tidak ada aktivitas setoran tahfidz 7 hari terakhir.</div>';
        }
      }

      // 5. Child Report Card Progress Charts - Parent
      const childProgressCharts = document.querySelectorAll(".child-progress-chart");
      childProgressCharts.forEach(el => {
        const gradesRaw = JSON.parse(el.getAttribute('data-grades') || '[]');
        if (gradesRaw.length > 0) {
          const categories = gradesRaw.map(g => g.subject?.name || 'Mapel');
          const seriesData = gradesRaw.map(g => parseFloat(g.avg_score));

          const options = {
            series: [{
              name: 'Nilai Rata-rata',
              data: seriesData
            }],
            chart: {
              type: 'bar',
              height: 240,
              fontFamily: 'Inter, sans-serif',
              toolbar: { show: false }
            },
            plotOptions: {
              bar: {
                horizontal: true,
                barHeight: '50%',
                borderRadius: 4
              }
            },
            colors: ['#4f46e5'],
            dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) },
            xaxis: {
              categories: categories,
              max: 100,
              labels: { style: { colors: '#9ca3af', fontWeight: 600 } }
            },
            yaxis: {
              labels: { style: { colors: '#9ca3af', fontWeight: 600 } }
            },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 }
          };

          const chart = new ApexCharts(el, options);
          chart.render();
        } else {
          el.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 font-bold text-sm">Tidak ada data nilai rapor untuk anak ini.</div>';
        }
      });

    });
  </script>
</x-app-layout>
