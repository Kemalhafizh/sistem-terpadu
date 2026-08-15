<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight flex items-center space-x-2">
          <span class="p-2 bg-teal-500/10 text-teal-500 rounded-xl"></span>
          <span>{{ __('Riwayat Penanganan Smart Alerts') }}</span>
        </h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
          Arsip lengkap seluruh anomali dan peringatan yang telah diselesaikan atau dalam pengawasan (monitoring)
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
            class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 hover:text-gray-900 dark:hover:text-gray-100">
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
          class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 bg-teal-500 text-white shadow-lg shadow-teal-500/20">
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

    {{-- Filter Bar --}}
    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5">
      <form action="{{ route('smart-alerts.history') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
        {{-- Type Filter --}}
        <div class="sm:col-span-3 space-y-1.5">
          <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Kategori Alert</label>
          <select name="type"
              class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
            <option value="">Semua Kategori</option>
            <option value="at_risk_student" {{ request('type') === 'at_risk_student' ? 'selected' : '' }}>Siswa Berisiko Tinggi</option>
            <option value="attendance_decline" {{ request('type') === 'attendance_decline' ? 'selected' : '' }}>Penurunan Kehadiran</option>
            <option value="payment_default" {{ request('type') === 'payment_default' ? 'selected' : '' }}>Prediksi Gagal Bayar</option>
            <option value="journal_gap" {{ request('type') === 'journal_gap' ? 'selected' : '' }}>Guru: Kesenjangan Jurnal</option>
            <option value="critical_medicine" {{ request('type') === 'critical_medicine' ? 'selected' : '' }}>Kesehatan: Stok Kritis UKS</option>
            <option value="overdue_asset" {{ request('type') === 'overdue_asset' ? 'selected' : '' }}>Sarpras: Keterlambatan Aset</option>
            <option value="manual" {{ request('type') === 'manual' ? 'selected' : '' }}>Input Manual</option>
          </select>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center space-x-2">
          <button type="submit"
              class="flex-1 px-5 py-2.5 bg-gray-900 dark:bg-slate-100 text-white dark:text-slate-900 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-800 dark:hover:bg-white transition-all shadow-md active:scale-95">
            Filter
          </button>
          @if(request()->filled('type'))
            <a href="{{ route('smart-alerts.history') }}"
              class="px-4 py-2.5 bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-bold hover:bg-gray-200 dark:hover:bg-slate-700 transition-all">
              Reset
            </a>
          @endif
        </div>
      </form>
    </div>

    {{-- History Cards List --}}
    <div class="space-y-4">
      @forelse($alerts as $alert)
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 transition shadow-sm border-l-4 
           {{ $alert->status === 'resolved' ? 'border-l-emerald-500' : 'border-l-blue-500' }}">
          
          <div class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-4">
              <div class="flex flex-wrap items-center gap-2">
                {{-- Severity Badge --}}
                @if($alert->severity === 'critical')
                  <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50">
                     Kritis
                  </span>
                @elseif($alert->severity === 'warning')
                  <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50">
                     Peringatan
                  </span>
                @else
                  <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/50">
                    ℹ Info
                  </span>
                @endif

                {{-- Category/Type Badge --}}
                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/50">
                  {{ str_replace('_', ' ', $alert->type) }}
                </span>

                {{-- Status Badge --}}
                @if($alert->status === 'resolved')
                  <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                     Terselesaikan
                  </span>
                @else
                  <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                     Dipantau (Monitoring)
                  </span>
                @endif
              </div>
              
              {{-- Date --}}
              <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500">
                Terdeteksi: {{ $alert->created_at->format('d M Y H:i') }}
              </span>
            </div>

            {{-- Title --}}
            <div class="flex items-center justify-between gap-4">
              <h3 class="text-base font-black text-gray-900 dark:text-gray-100">
                {{ $alert->title }}
              </h3>
              
              {{-- Target display --}}
              @if($alert->alertable)
                <span class="text-[11px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest bg-indigo-50 dark:bg-indigo-950/20 px-3 py-1 rounded-xl">
                  Target: {{ $alert->alertable->name }}
                </span>
              @endif
            </div>

            {{-- Description --}}
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 leading-relaxed">
              {{ $alert->description }}
            </p>

            {{-- Escalated info --}}
            @if($alert->escalatedTo)
              <div class="p-3 bg-purple-500/5 border border-purple-200/10 rounded-xl text-xs font-semibold text-purple-700 dark:text-purple-300">
                <span class="font-black text-[9px] uppercase tracking-wider text-purple-500"> Informasi Eskalasi:</span>
                <p class="mt-0.5">Dieskalasikan kepada <strong class="font-black">{{ $alert->escalatedTo->name }}</strong></p>
                @if($alert->escalation_notes)
                  <p class="italic text-[11px] text-gray-500 dark:text-gray-400 mt-1">"{{ $alert->escalation_notes }}"</p>
                @endif
              </div>
            @endif

            {{-- Resolution Notes / Acknowledge Log --}}
            @if($alert->status === 'resolved')
              <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50 rounded-xl text-xs font-semibold text-emerald-800 dark:text-emerald-300 space-y-1.5">
                <div class="flex items-center space-x-1.5 text-[10px] font-black uppercase tracking-wider text-emerald-600">
                  <span> Tindakan Penanganan & Solusi:</span>
                </div>
                <p class="italic">"{{ $alert->resolution_notes }}"</p>
                <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold">
                  Diselesaikan oleh <span class="underline">{{ $alert->resolvedBy->name ?? 'Sistem' }}</span> pada {{ $alert->resolved_at->format('d-m-Y H:i') }}
                </p>
              </div>
            @elseif($alert->status === 'acknowledged')
              <div class="p-4 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/50 rounded-xl text-xs font-semibold text-blue-800 dark:text-blue-300">
                <span class="block text-[10px] font-black uppercase tracking-wider text-blue-600 mb-1"> Status Pengawasan (Monitoring):</span>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Kasus sedang dipantau di bawah pengawasan ketat dan dikoordinasikan dengan dinas / guru terkait.</p>
                <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold mt-2">
                  Ditandai Diketahui oleh <span class="underline">{{ $alert->acknowledgedBy->name ?? 'Sistem' }}</span> pada {{ $alert->acknowledged_at->format('d-m-Y H:i') }}
                </p>
              </div>
            @endif
          </div>
        </div>
      @empty
        {{-- Empty State --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl p-16 text-center shadow-sm">
          <div class="flex flex-col items-center">
            <div class="w-20 h-20 bg-gray-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-5 border border-gray-100">
              <span class="text-3xl"></span>
            </div>
            <h3 class="text-lg font-black text-gray-800 dark:text-gray-200 mb-2">Arsip Riwayat Kosong</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm leading-relaxed">
              Belum ada alert yang diselesaikan atau diarsipkan di dalam sistem.
            </p>
          </div>
        </div>
      @endforelse
    </div>

    {{-- Pagination --}}
    @if($alerts->hasPages())
      <div class="mt-6">
        {{ $alerts->links() }}
      </div>
    @endif

  </div>
</x-app-layout>
