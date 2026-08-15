<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex items-center space-x-3">
        <a href="{{ route('smart-alerts.index') }}" 
          class="p-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-xl transition active:scale-95"
          title="Kembali ke Dashboard">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
        </a>
        <div>
          <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight flex items-center space-x-2">
            <span>Analisis Komprehensif Siswa</span>
          </h2>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
            Rekam analitik prediktif multi-dimensi untuk {{ $student->name }}
          </p>
        </div>
      </div>
    </div>
  </x-slot>

  <div class="py-8 w-full px-4 sm:px-6 lg:px-8 space-y-8">
    
    {{-- Student Profile & Metrics Card --}}
    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 flex flex-col md:flex-row gap-8 justify-between items-center shadow-sm">
      <div class="flex items-center space-x-4">
        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-black shadow-lg shadow-indigo-500/20">
          {{ substr($student->name, 0, 1) }}
        </div>
        <div class="space-y-1">
          <h3 class="text-xl font-black text-gray-900 dark:text-gray-100">{{ $student->name }}</h3>
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-none">
            Kelas: {{ $student->classroom?->name ?? 'Tanpa Kelas' }} | NISN: {{ $student->nisn ?? '-' }}
          </p>
          <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 font-semibold flex items-center space-x-1">
            <span>Gol. Darah: {{ $student->blood_type ?? '-' }}</span>
            @if($student->allergies)
              <span class="px-2 py-0.5 bg-rose-500/10 text-rose-500 rounded text-[9px] uppercase tracking-wider font-bold">Alergi: {{ $student->allergies }}</span>
            @endif
          </p>
        </div>
      </div>

      {{-- 3 KPI Metrics Panel --}}
      <div class="grid grid-cols-3 gap-6 w-full md:w-auto flex-shrink-0">
        {{-- Metric 1: Rata-Rata Nilai --}}
        @php
          $avgGrade = $student->grades()->avg('score');
        @endphp
        <div class="text-center px-4 py-3 bg-gray-50 dark:bg-slate-800/50 rounded-2xl border border-gray-100 dark:border-slate-800">
          <span class="block text-[8px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Rerata Nilai</span>
          <span class="text-lg font-black {{ $avgGrade < 70 ? 'text-rose-600' : 'text-gray-800 dark:text-gray-200' }}">
            {{ $avgGrade !== null ? number_format($avgGrade, 1) : '-' }}
          </span>
        </div>

        {{-- Metric 2: Kehadiran --}}
        @php
          $attendances = $student->attendances()->get();
          $attendanceRate = null;
          if ($attendances->count() > 0) {
            $present = $attendances->where('status', 'H')->count();
            $attendanceRate = ($present / $attendances->count()) * 100;
          }
        @endphp
        <div class="text-center px-4 py-3 bg-gray-50 dark:bg-slate-800/50 rounded-2xl border border-gray-100 dark:border-slate-800">
          <span class="block text-[8px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Absensi</span>
          <span class="text-lg font-black {{ $attendanceRate < 80 ? 'text-rose-600' : 'text-gray-800 dark:text-gray-200' }}">
            {{ $attendanceRate !== null ? number_format($attendanceRate, 1) . '%' : '-' }}
          </span>
        </div>

        {{-- Metric 3: Poin BK --}}
        <div class="text-center px-4 py-3 bg-gray-50 dark:bg-slate-800/50 rounded-2xl border border-gray-100 dark:border-slate-800">
          <span class="block text-[8px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Poin BK</span>
          <span class="text-lg font-black {{ $student->total_points >= 50 ? 'text-rose-600' : 'text-gray-800 dark:text-gray-200' }}">
            {{ $student->total_points }}
          </span>
        </div>
      </div>
    </div>

    {{-- Timeline Section Header --}}
    <div class="space-y-4">
      <h4 class="text-sm font-black uppercase tracking-widest text-gray-400 flex items-center space-x-1.5">
        <span> Timeline Riwayat Smart Alerts</span>
      </h4>

      {{-- Timeline Container --}}
      <div class="relative border-l border-gray-200 dark:border-slate-800 ml-4 space-y-6">
        @forelse($alerts as $alert)
          <div class="relative pl-6">
            {{-- Icon bullet indicator --}}
            <div class="absolute -left-3 top-1.5 w-6 h-6 rounded-full flex items-center justify-center text-xs 
               {{ $alert->status === 'resolved' ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-300' : ($alert->severity === 'critical' ? 'bg-rose-500/10 text-rose-500 border border-rose-300 animate-pulse' : 'bg-amber-500/10 text-amber-500 border border-amber-300') }}">
              {{ $alert->status === 'resolved' ? '' : '!' }}
            </div>

            {{-- Alert Card --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 space-y-3 hover:shadow-lg transition">
              <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600">
                    {{ str_replace('_', ' ', $alert->type) }}
                  </span>
                  @if($alert->severity === 'critical')
                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-rose-50 text-rose-600 border border-rose-200">Kritis</span>
                  @else
                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-amber-50 text-amber-600 border border-amber-200">Peringatan</span>
                  @endif
                </div>
                <span class="text-[9px] font-bold text-gray-400">{{ $alert->created_at->format('d-m-Y H:i') }}</span>
              </div>

              <h5 class="text-sm font-black text-gray-900 dark:text-gray-100">{{ $alert->title }}</h5>
              <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 leading-relaxed">{{ $alert->description }}</p>

              @if($alert->status === 'resolved')
                <div class="p-3 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                  <span class="block text-[8px] font-black uppercase tracking-widest text-emerald-600 mb-1">Catatan Tindak Lanjut:</span>
                  <p class="italic">"{{ $alert->resolution_notes }}"</p>
                  <p class="text-[9px] text-gray-400 dark:text-gray-500 mt-1">Diselesaikan oleh {{ $alert->resolvedBy->name ?? 'Sistem' }} pada {{ $alert->resolved_at->format('d-m-Y H:i') }}</p>
                </div>
              @elseif($alert->status === 'acknowledged')
                <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold">
                  Dipantau sejak {{ $alert->acknowledged_at->format('d-m-Y H:i') }} oleh {{ $alert->acknowledgedBy->name ?? '-' }}
                </p>
              @else
                <div class="flex items-center space-x-2">
                  @if($alert->status === 'active')
                    <form action="{{ route('smart-alerts.acknowledge', $alert) }}" method="POST">
                      @csrf
                      <button type="submit" class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-[9px] font-black uppercase tracking-wider transition active:scale-95">
                         Pantau
                      </button>
                    </form>
                  @endif
                  <button @click="showResolveModal = true; activeAlertId = {{ $alert->id }}; activeAlertTitle = '{{ addslashes($alert->title) }}'; resolutionNotes = ''" 
                      class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[9px] font-black uppercase tracking-wider transition active:scale-95">
                     Selesaikan
                  </button>
                </div>
              @endif
            </div>
          </div>
        @empty
          <div class="pl-6 text-gray-500 dark:text-gray-400 text-xs font-bold">
            Belum ada riwayat peringatan atau analitik risiko terdeteksi untuk siswa ini.
          </div>
        @endforelse
      </div>
    </div>
  </div>
</x-app-layout>
