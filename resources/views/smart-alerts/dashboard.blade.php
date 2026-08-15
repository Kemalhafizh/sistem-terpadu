@php
  $typeLabels = [
    'at_risk_student' => 'Siswa Berisiko Tinggi',
    'attendance_decline' => 'Penurunan Kehadiran',
    'payment_default' => 'Prediksi Gagal Bayar',
    'journal_gap' => 'Guru: Kesenjangan Jurnal',
    'critical_medicine' => 'Kesehatan: Stok Kritis UKS',
    'overdue_asset' => 'Sarpras: Keterlambatan Aset',
    'manual' => 'Input Manual',
  ];
  $typeColors = [
    'at_risk_student' => 'from-rose-500 to-pink-500 text-rose-500 bg-rose-500/10',
    'attendance_decline' => 'from-amber-500 to-orange-500 text-amber-500 bg-amber-500/10',
    'payment_default' => 'from-yellow-500 to-amber-600 text-yellow-500 bg-yellow-500/10',
    'journal_gap' => 'from-indigo-500 to-blue-500 text-indigo-500 bg-indigo-500/10',
    'critical_medicine' => 'from-red-500 to-rose-600 text-red-500 bg-red-500/10',
    'overdue_asset' => 'from-violet-500 to-purple-600 text-violet-500 bg-violet-500/10',
    'manual' => 'from-teal-500 to-emerald-500 text-teal-500 bg-teal-500/10',
  ];
@endphp

<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight flex items-center space-x-2">
          <span class="p-2 bg-teal-500/10 text-teal-500 rounded-xl"></span>
          <span>{{ __('Smart Alerts Dashboard') }}</span>
        </h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
          Analisis prediktif, tingkat keparahan insiden, dan tren pencegahan risiko sekolah
        </p>
      </div>
    </div>
  </x-slot>

  <div class="py-8 w-full px-4 sm:px-6 lg:px-8 space-y-8">
    
    {{-- Navigation/Tabs Bar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white/50 dark:bg-slate-900/50 backdrop-blur-md p-2 rounded-2xl border border-gray-100 dark:border-slate-800/80 shadow-sm">
      <div class="flex flex-wrap items-center gap-1">
        @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah']))
          <a href="{{ route('smart-alerts.dashboard') }}" 
            class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-teal-500 text-white shadow-lg shadow-teal-500/20">
            Dashboard
          </a>
        @endif
        <a href="{{ route('smart-alerts.index') }}" 
          class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 hover:text-gray-900 dark:hover:text-gray-100">
          Alert Aktif
        </a>
        @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah']))
          <a href="{{ route('smart-alerts.rules') }}" 
            class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 hover:text-gray-900 dark:hover:text-gray-100">
            Aturan Deteksi
          </a>
        @endif
        <a href="{{ route('smart-alerts.history') }}" 
          class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 hover:text-gray-900 dark:hover:text-gray-100">
          Riwayat
        </a>
      </div>
      
      <div class="flex items-center gap-2">
        @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah']))
          <a href="{{ route('smart-alerts.export') }}" 
            class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 active:scale-95 flex items-center space-x-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>Unduh PDF</span>
          </a>
        @endif
        @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah', 'bk']))
          <a href="{{ route('smart-alerts.create') }}" 
            class="px-4 py-2 bg-gradient-to-r from-teal-500 to-indigo-600 hover:from-teal-600 hover:to-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200 active:scale-95 shadow-md shadow-teal-500/10 flex items-center space-x-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Alert Baru</span>
          </a>
        @endif
      </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
      {{-- KPI 1: Total --}}
      <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
        <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Total Alert Terdeteksi</span>
        <div class="flex items-end justify-between mt-2">
          <span class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ $kpis['total'] }}</span>
          <span class="p-2 bg-gray-500/10 text-gray-500 rounded-xl text-lg"></span>
        </div>
      </div>

      {{-- KPI 2: Active --}}
      <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
        <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest">Alert Aktif</span>
        <div class="flex items-end justify-between mt-2">
          <span class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $kpis['active'] }}</span>
          <span class="p-2 bg-indigo-500/10 text-indigo-500 rounded-xl text-lg"></span>
        </div>
      </div>

      {{-- KPI 3: Critical --}}
      <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
        <span class="text-[9px] font-black text-rose-500 uppercase tracking-widest">Kritis (Critical)</span>
        <div class="flex items-end justify-between mt-2">
          <span class="text-3xl font-black text-rose-600 dark:text-rose-400">{{ $kpis['critical'] }}</span>
          <span class="p-2 bg-rose-500/10 text-rose-500 rounded-xl text-lg"></span>
        </div>
      </div>

      {{-- KPI 4: Warning --}}
      <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
        <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest">Peringatan (Warning)</span>
        <div class="flex items-end justify-between mt-2">
          <span class="text-3xl font-black text-amber-600 dark:text-amber-400">{{ $kpis['warning'] }}</span>
          <span class="p-2 bg-amber-500/10 text-amber-500 rounded-xl text-lg"></span>
        </div>
      </div>

      {{-- KPI 5: Resolved --}}
      <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
        <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Terselesaikan</span>
        <div class="flex items-end justify-between mt-2">
          <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $kpis['resolved'] }}</span>
          <span class="p-2 bg-emerald-500/10 text-emerald-500 rounded-xl text-lg"></span>
        </div>
      </div>

      {{-- KPI 6: Escalated --}}
      <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between hover:shadow-lg transition-all duration-300 col-span-2 lg:col-span-1">
        <span class="text-[9px] font-black text-purple-500 uppercase tracking-widest">Dieskalasikan</span>
        <div class="flex items-end justify-between mt-2">
          <span class="text-3xl font-black text-purple-600 dark:text-purple-400">{{ $kpis['escalated'] }}</span>
          <span class="p-2 bg-purple-500/10 text-purple-500 rounded-xl text-lg"></span>
        </div>
      </div>
    </div>

    {{-- Row 2: Severity Distribution & Type Breakdown --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      
      {{-- Left Card: Severity Distribution --}}
      <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 flex flex-col justify-between shadow-sm">
        <div>
          <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-1">Distribusi Tingkat Bahaya Alert Aktif</h3>
          <p class="text-xs text-gray-400 font-semibold mb-6">Visualisasi proporsi urgensi penanganan masalah</p>
        </div>
        
        @php
          $criticalCount = $severityStats['critical'] ?? 0;
          $warningCount = $severityStats['warning'] ?? 0;
          $totalActive = $criticalCount + $warningCount;
          $critPct = $totalActive > 0 ? round(($criticalCount / $totalActive) * 100) : 0;
          $warnPct = $totalActive > 0 ? round(($warningCount / $totalActive) * 100) : 0;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center py-4">
          {{-- Circular Ring Charts --}}
          <div class="flex items-center justify-center space-x-8">
            {{-- Critical Circle --}}
            <div class="flex flex-col items-center space-y-2">
              <div class="relative w-28 h-28 flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                  <path class="text-gray-100 dark:text-slate-800" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                  <path class="text-rose-500 transition-all duration-1000" stroke-width="3" stroke-dasharray="{{ $critPct }}, 100" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <div class="absolute flex flex-col items-center justify-center">
                  <span class="text-xl font-black text-rose-600 dark:text-rose-400">{{ $critPct }}%</span>
                  <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Kritis</span>
                </div>
              </div>
            </div>

            {{-- Warning Circle --}}
            <div class="flex flex-col items-center space-y-2">
              <div class="relative w-28 h-28 flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                  <path class="text-gray-100 dark:text-slate-800" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                  <path class="text-amber-500 transition-all duration-1000" stroke-width="3" stroke-dasharray="{{ $warnPct }}, 100" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <div class="absolute flex flex-col items-center justify-center">
                  <span class="text-xl font-black text-amber-600 dark:text-amber-400">{{ $warnPct }}%</span>
                  <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Warning</span>
                </div>
              </div>
            </div>
          </div>

          {{-- Legend & Detailed Info --}}
          <div class="space-y-4">
            <div class="p-4 bg-rose-500/5 border border-rose-200/20 rounded-2xl flex items-center justify-between">
              <div class="flex items-center space-x-2.5">
                <span class="w-3 h-3 rounded-full bg-rose-500 shadow-sm"></span>
                <span class="text-xs font-black text-gray-700 dark:text-gray-300"> Kritis (Critical)</span>
              </div>
              <span class="text-sm font-black text-rose-600 dark:text-rose-400">{{ $criticalCount }} Alert</span>
            </div>
            <div class="p-4 bg-amber-500/5 border border-amber-200/20 rounded-2xl flex items-center justify-between">
              <div class="flex items-center space-x-2.5">
                <span class="w-3 h-3 rounded-full bg-amber-500 shadow-sm"></span>
                <span class="text-xs font-black text-gray-700 dark:text-gray-300"> Peringatan (Warning)</span>
              </div>
              <span class="text-sm font-black text-amber-600 dark:text-amber-400">{{ $warningCount }} Alert</span>
            </div>
          </div>
        </div>

        {{-- Summary footer --}}
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-slate-800 text-[11px] font-semibold text-gray-500 dark:text-gray-400">
          Proporsi kasus kritis membutuhkan intervensi kepemimpinan / BK segera dalam 24 jam.
        </div>
      </div>

      {{-- Right Card: Type Breakdown --}}
      <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 flex flex-col justify-between shadow-sm">
        <div>
          <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-1">Rincian Kategori Isu Alert</h3>
          <p class="text-xs text-gray-400 font-semibold mb-6">Distribusi kasus berdasarkan pemicu deteksi dini</p>
        </div>

        @php
          $maxTypeCount = count($typeStats) > 0 ? max($typeStats->toArray()) : 1;
        @endphp

        <div class="space-y-4">
          @forelse($typeStats as $key => $count)
            @php
              $label = $typeLabels[$key] ?? ucwords(str_replace('_', ' ', $key));
              $colorClasses = $typeColors[$key] ?? 'from-teal-500 to-indigo-500 text-teal-500 bg-teal-500/10';
              $colorGradient = explode(' ', $colorClasses)[0] . ' ' . explode(' ', $colorClasses)[1];
              $widthPct = ($count / $maxTypeCount) * 100;
            @endphp
            <div class="space-y-1">
              <div class="flex justify-between items-center text-xs">
                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $label }}</span>
                <span class="font-black text-gray-900 dark:text-gray-100">{{ $count }} Kasus</span>
              </div>
              <div class="w-full bg-gray-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                <div class="bg-gradient-to-r {{ $colorGradient }} h-full rounded-full transition-all duration-1000" style="width: {{ $widthPct }}%"></div>
              </div>
            </div>
          @empty
            <div class="py-12 text-center text-xs font-semibold text-gray-400 dark:text-gray-500">
              Tidak ada kategori alert terdeteksi saat ini.
            </div>
          @endforelse
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-slate-800 text-[11px] font-semibold text-gray-500 dark:text-gray-400 flex items-center justify-between">
          <span>Target mitigasi prioritas:</span>
          <span class="font-black text-teal-600 dark:text-teal-400 uppercase tracking-widest text-[9px]">Sistem Terotomasi</span>
        </div>
      </div>
    </div>

    {{-- Row 3: 7-Day Trend Chart & Shortcuts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      {{-- Left Area: 7-Day Trend Chart (span 2) --}}
      @php
        $createdData = $trendData['created'] ?? [0,0,0,0,0,0,0];
        $resolvedData = $trendData['resolved'] ?? [0,0,0,0,0,0,0];
        $maxVal = max(array_merge($createdData, $resolvedData, [5]));
        
        // SVG dimensions
        $width = 600;
        $height = 220;
        $paddingTop = 20;
        $paddingBottom = 40;
        $paddingLeft = 40;
        $paddingRight = 20;
        
        $chartWidth = $width - $paddingLeft - $paddingRight;
        $chartHeight = $height - $paddingTop - $paddingBottom;
        
        $createdPoints = [];
        $resolvedPoints = [];
        
        for ($i = 0; $i < 7; $i++) {
          $x = $paddingLeft + ($i * ($chartWidth / 6));
          
          $cVal = $createdData[$i] ?? 0;
          $cy = $height - $paddingBottom - (($cVal / $maxVal) * $chartHeight);
          $createdPoints[] = "$x,$cy";
          
          $rVal = $resolvedData[$i] ?? 0;
          $ry = $height - $paddingBottom - (($rVal / $maxVal) * $chartHeight);
          $resolvedPoints[] = "$x,$ry";
        }
        
        $createdPath = "M " . implode(" L ", $createdPoints);
        $resolvedPath = "M " . implode(" L ", $resolvedPoints);
        
        // Paths for gradient fills
        $createdFillPath = $createdPath . " L " . ($paddingLeft + $chartWidth) . "," . ($height - $paddingBottom) . " L " . $paddingLeft . "," . ($height - $paddingBottom) . " Z";
        $resolvedFillPath = $resolvedPath . " L " . ($paddingLeft + $chartWidth) . "," . ($height - $paddingBottom) . " L " . $paddingLeft . "," . ($height - $paddingBottom) . " Z";
      @endphp

      <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm lg:col-span-2 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between flex-wrap gap-4 mb-4">
            <div>
              <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-1">Tren Kasus & Penyelesaian (7 Hari Terakhir)</h3>
              <p class="text-xs text-gray-400 font-semibold">Membandingkan frekuensi deteksi kasus baru vs tingkat penyelesaian</p>
            </div>
            <div class="flex items-center space-x-4 text-[10px] font-black uppercase tracking-wider">
              <span class="flex items-center space-x-1.5 text-indigo-500">
                <span class="w-3 h-1 bg-indigo-500 rounded"></span>
                <span>Isu Baru</span>
              </span>
              <span class="flex items-center space-x-1.5 text-teal-500">
                <span class="w-3 h-1 bg-teal-500 rounded"></span>
                <span>Terselesaikan</span>
              </span>
            </div>
          </div>

          {{-- Responsive SVG Container --}}
          <div class="relative w-full overflow-hidden py-2">
            <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-auto text-gray-200 dark:text-slate-800">
              <defs>
                <linearGradient id="createdGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="rgba(99, 102, 241, 0.2)"/>
                  <stop offset="100%" stop-color="rgba(99, 102, 241, 0)"/>
                </linearGradient>
                <linearGradient id="resolvedGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="rgba(20, 184, 166, 0.2)"/>
                  <stop offset="100%" stop-color="rgba(20, 184, 166, 0)"/>
                </linearGradient>
              </defs>

              {{-- Grid lines --}}
              @for($j = 0; $j <= 4; $j++)
                @php
                  $ratio = $j / 4;
                  $gridY = $height - $paddingBottom - ($ratio * $chartHeight);
                  $val = round($ratio * $maxVal, 0);
                @endphp
                <line x1="{{ $paddingLeft }}" y1="{{ $gridY }}" x2="{{ $width - $paddingRight }}" y2="{{ $gridY }}" stroke="currentColor" class="text-gray-100 dark:text-slate-800" stroke-dasharray="4" />
                <text x="{{ $paddingLeft - 10 }}" y="{{ $gridY + 4 }}" text-anchor="end" class="text-[9px] fill-gray-400 dark:fill-gray-500 font-bold">{{ $val }}</text>
              @endfor

              {{-- Fills under line --}}
              <path d="{{ $createdFillPath }}" fill="url(#createdGrad)"></path>
              <path d="{{ $resolvedFillPath }}" fill="url(#resolvedGrad)"></path>

              {{-- Trend lines --}}
              <path d="{{ $createdPath }}" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
              <path d="{{ $resolvedPath }}" fill="none" stroke="#14b8a6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>

              {{-- Data Dots --}}
              @for($i = 0; $i < 7; $i++)
                @php
                  $cPos = explode(',', $createdPoints[$i]);
                  $rPos = explode(',', $resolvedPoints[$i]);
                @endphp
                <circle cx="{{ $cPos[0] }}" cy="{{ $cPos[1] }}" r="4" fill="#6366f1" stroke="#ffffff" stroke-width="1.5" class="shadow-sm"></circle>
                <circle cx="{{ $rPos[0] }}" cy="{{ $rPos[1] }}" r="4" fill="#14b8a6" stroke="#ffffff" stroke-width="1.5" class="shadow-sm"></circle>
              @endfor

              {{-- X axis labels --}}
              @foreach($trendLabels as $idx => $label)
                @php
                  $x = $paddingLeft + ($idx * ($chartWidth / 6));
                @endphp
                <text x="{{ $x }}" y="{{ $height - 12 }}" text-anchor="middle" class="text-[9px] fill-gray-400 dark:fill-gray-500 font-black uppercase tracking-wider">{{ $label }}</text>
              @endforeach
            </svg>
          </div>
        </div>

        <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 italic">
          * Grafik di atas memetakan rasio tindakan pencegahan dibanding total kasus baru yang masuk sistem secara real-time.
        </div>
      </div>

      {{-- Right Area: Shortcuts --}}
      <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
        <div>
          <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-1">Navigasi Pintar & Pintasan</h3>
          <p class="text-xs text-gray-400 font-semibold mb-6">Akses cepat ke fungsionalitas manajemen modul</p>
          
          <div class="space-y-4">
            @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah', 'bk']))
              <a href="{{ route('smart-alerts.create') }}" 
                class="flex items-center justify-between p-4 bg-gradient-to-r from-teal-500/5 to-teal-500/10 hover:from-teal-500/10 hover:to-teal-500/20 border border-teal-500/20 rounded-2xl transition duration-200 group active:scale-[0.98]">
                <div class="flex items-center space-x-3">
                  <span class="text-lg"></span>
                  <div class="text-left">
                    <span class="block text-xs font-black text-teal-800 dark:text-teal-300">Buat Alert Manual</span>
                    <span class="block text-[9px] text-gray-500 dark:text-gray-400 font-medium">Laporkan anomali di luar sistem</span>
                  </div>
                </div>
                <span class="text-teal-600 font-black transform group-hover:translate-x-1 transition-all">→</span>
              </a>
            @endif

            @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah']))
              <a href="{{ route('smart-alerts.rules') }}" 
                class="flex items-center justify-between p-4 bg-gradient-to-r from-indigo-500/5 to-indigo-500/10 hover:from-indigo-500/10 hover:to-indigo-500/20 border border-indigo-500/20 rounded-2xl transition duration-200 group active:scale-[0.98]">
                <div class="flex items-center space-x-3">
                  <span class="text-lg"></span>
                  <div class="text-left">
                    <span class="block text-xs font-black text-indigo-800 dark:text-indigo-300">Edit Aturan Deteksi</span>
                    <span class="block text-[9px] text-gray-500 dark:text-gray-400 font-medium">Ubah batas ambang kriteria pemicu</span>
                  </div>
                </div>
                <span class="text-indigo-600 font-black transform group-hover:translate-x-1 transition-all">→</span>
              </a>
            @endif

            <a href="{{ route('smart-alerts.history') }}" 
              class="flex items-center justify-between p-4 bg-gradient-to-r from-slate-500/5 to-slate-500/10 hover:from-slate-500/10 hover:to-slate-500/20 border border-slate-500/20 rounded-2xl transition duration-200 group active:scale-[0.98]">
              <div class="flex items-center space-x-3">
                <span class="text-lg"></span>
                <div class="text-left">
                  <span class="block text-xs font-black text-slate-800 dark:text-slate-300 font-bold">Lihat Riwayat Penanganan</span>
                  <span class="block text-[9px] text-gray-500 dark:text-gray-400 font-medium">Arsip alert yang telah diselesaikan</span>
                </div>
              </div>
              <span class="text-slate-600 font-black transform group-hover:translate-x-1 transition-all">→</span>
            </a>
          </div>
        </div>

        <div class="mt-6 text-[10px] text-gray-400 font-bold uppercase tracking-widest text-center">
          Sistem Terpadu SISTA © 2026
        </div>
      </div>

    </div>

  </div>
</x-app-layout>
