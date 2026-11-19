<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight flex items-center space-x-2">
          <span>{{ __('Menunggu Persetujuan Saya') }}</span>
        </h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
          Kotak masuk terpadu untuk seluruh persetujuan, disposisi, izin siswa, akademik, sarpras, dan keuangan
        </p>
      </div>
      <div class="flex flex-wrap gap-2">
        <a href="{{ route('workflows.my-requests') }}" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 text-xs font-bold text-gray-700 dark:text-gray-200">
          Pengajuan Saya
        </a>
        <a href="{{ route('workflows.history') }}" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 text-xs font-bold text-gray-700 dark:text-gray-200">
          Riwayat Persetujuan
        </a>
      </div>
    </div>
  </x-slot>

  <div class="py-8 w-full px-4 sm:px-6 lg:px-8 space-y-6"
     x-data="{ 
      showActionModal: false, 
      modalType: 'approve',
      activeRequestId: null, 
      activeRequestTitle: '',
      activeRequester: '',
      remarks: ''
     }">

    @if(session('success'))
      <div class="p-4 bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center space-x-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="text-sm font-bold">{{ session('success') }}</span>
      </div>
    @endif

    @if($errors->any())
      <div class="p-4 bg-rose-50/80 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 rounded-2xl space-y-1">
        @foreach($errors->all() as $error)
          <div class="text-sm font-bold">{{ $error }}</div>
        @endforeach
      </div>
    @endif

    @php
      $unifiedItems = $unifiedItems ?? collect();
      $moduleSummary = $moduleSummary ?? ['all' => $requests->count()];
      $activeModule = $activeModule ?? 'all';
    @endphp

    {{-- Overview Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <div class="bg-white/90 dark:bg-slate-900/70 border border-gray-100 dark:border-slate-800 rounded-2xl p-5 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest block">Total Tugas Aktif</span>
          <span class="text-3xl font-black text-gray-900 dark:text-gray-100 mt-1 block">{{ $moduleSummary['all'] ?? $unifiedItems->count() }}</span>
        </div>
        <span class="px-3 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/70 dark:border-amber-800/60 text-xs font-bold">Lintas Modul</span>
      </div>

      <div class="bg-white/90 dark:bg-slate-900/70 border border-gray-100 dark:border-slate-800 rounded-2xl p-5 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest block">Peran &amp; Jabatan Aktif</span>
          <span class="text-xs font-black text-gray-800 dark:text-gray-200 mt-1.5 block uppercase tracking-wider">
            {{ collect(auth()->user()->effectiveRoles())->map(fn ($r) => str_replace('_', ' ', $r))->implode(', ') }}
          </span>
        </div>
        <span class="px-3 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-800/60 text-xs font-bold">Berwenang</span>
      </div>

      <div class="bg-white/90 dark:bg-slate-900/70 border border-gray-100 dark:border-slate-800 rounded-2xl p-5 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest block">Menunggu di Alur Kerja</span>
          <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $requests->count() }}</span>
        </div>
        <span class="px-3 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-800/60 text-xs font-bold">Inline Action</span>
      </div>
    </div>

    {{-- Category Filter Tabs --}}
    <div class="flex flex-wrap gap-2">
      @foreach(\App\Services\ApprovalInboxService::MODULE_GROUPS as $groupKey => $groupLabel)
        @php
          $count = $moduleSummary[$groupKey] ?? 0;
          $isSelected = ($activeModule ?: 'all') === $groupKey;
        @endphp
        @if($groupKey === 'all' || $count > 0 || $isSelected)
          <a href="{{ route('workflows.pending', ['module' => $groupKey]) }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border transition {{ $isSelected ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white dark:bg-slate-900 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-800' }}">
            <span>{{ $groupLabel }}</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $isSelected ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-300' }}">{{ $count }}</span>
          </a>
        @endif
      @endforeach
    </div>

    {{-- Unified Cross-Module Cards --}}
    <div class="space-y-4">
      @forelse($unifiedItems as $item)
        @php
          $isWorkflow = ($item['model_type'] ?? '') === 'WorkflowRequest';
          $wfReq = $isWorkflow ? $requests->firstWhere('id', $item['model_id']) : null;
        @endphp
        <div class="bg-white/90 dark:bg-slate-900/70 border border-gray-100 dark:border-slate-800 rounded-2xl p-6 transition-all flex flex-col md:flex-row justify-between gap-6 shadow-sm border-s-4 {{ ($item['urgency'] ?? 'normal') === 'high' ? 'border-s-rose-500' : 'border-s-amber-500' }}">
          <div class="space-y-2.5 flex-grow max-w-4xl">
            <div class="flex flex-wrap items-center gap-2">
              <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900/50">
                {{ $item['module_label'] }}
              </span>
              @if(($item['is_overdue'] ?? false))
                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60">
                  Melewati Tenggat
                </span>
              @elseif(($item['urgency'] ?? 'normal') === 'high')
                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60">
                  Prioritas Tinggi
                </span>
              @endif
              @if($item['due_at'])
                <span class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                  Tenggat: {{ $item['due_at']->translatedFormat('d M Y') }}
                </span>
              @endif
            </div>

            <h3 class="text-base font-black text-gray-900 dark:text-gray-100">
              {{ $item['title'] }}
            </h3>
            <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">
              Diajukan / dikirim oleh <span class="font-bold text-gray-800 dark:text-white">{{ $item['requester_name'] }}</span>
              @if($item['submitted_at'])
                &middot; {{ $item['submitted_at']->translatedFormat('d M Y H:i') }} WIB
              @endif
            </p>

            @if(!empty($item['subtitle']))
              <div class="p-3 bg-gray-50 dark:bg-slate-800/80 rounded-xl border border-gray-100 dark:border-slate-700 text-xs text-gray-700 dark:text-gray-300">
                {{ $item['subtitle'] }}
              </div>
            @endif

            @if($wfReq)
              <div class="flex items-center space-x-2 pt-1">
                <span class="text-[10px] font-bold text-gray-400">Progress:</span>
                <div class="flex items-center space-x-1.5">
                  @foreach($wfReq->approvals as $approval)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $approval->decision === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-300' }}">
                      {{ $approval->step_order }}. {{ str_replace('_', ' ', $approval->role) }}
                    </span>
                    @if(!$loop->last)
                      <span class="text-gray-300 text-xs">&rarr;</span>
                    @endif
                  @endforeach
                </div>
              </div>
            @endif
          </div>

          <div class="flex flex-row md:flex-col items-center justify-end gap-2 flex-shrink-0 w-full md:w-48 pt-4 md:pt-0 border-t border-gray-100 dark:border-slate-800 md:border-t-0">
            <a href="{{ $item['url'] }}"
              class="flex-1 md:w-full text-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-gray-800 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider transition">
              {{ $item['can_decide_inline'] ? 'Detail & Audit Trail' : 'Tinjau & Putuskan' }}
            </a>

            @if($wfReq && $item['can_decide_inline'])
              <button type="button"
                data-title="{{ $item['title'] }}"
                data-requester="{{ $item['requester_name'] }}"
                @click="showActionModal = true; modalType = 'approve'; activeRequestId = '{{ $wfReq->getRouteKey() }}'; activeRequestTitle = $el.dataset.title; activeRequester = $el.dataset.requester; remarks = ''"
                  class="flex-grow md:w-full px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition">
                Setujui
              </button>

              <button type="button"
                data-title="{{ $item['title'] }}"
                data-requester="{{ $item['requester_name'] }}"
                @click="showActionModal = true; modalType = 'reject'; activeRequestId = '{{ $wfReq->getRouteKey() }}'; activeRequestTitle = $el.dataset.title; activeRequester = $el.dataset.requester; remarks = ''"
                  class="flex-grow md:w-full px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition">
                Tolak
              </button>
            @endif
          </div>
        </div>
      @empty
        <div class="bg-white/80 dark:bg-slate-900/70 border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl p-14 text-center">
          <h3 class="text-lg font-black text-gray-800 dark:text-gray-200 mb-2">Kotak Masuk Persetujuan Kosong</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto leading-relaxed">
            Tidak ada pengajuan persetujuan, disposisi surat masuk, atau tinjauan modul lain yang menanti keputusan Anda saat ini.
          </p>
        </div>
      @endforelse
    </div>

    {{-- Inline Decision Modal (WorkflowRequest) --}}
    <div x-show="showActionModal" x-transition.opacity x-cloak
       class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div @click.away="showActionModal = false"
         class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-100 dark:border-slate-800 max-w-md w-full p-6 space-y-6 shadow-2xl">
        <div class="space-y-1">
          <h4 class="text-lg font-black text-gray-900 dark:text-gray-100">
            <span x-text="modalType === 'approve' ? 'Persetujuan Pengajuan' : 'Penolakan Pengajuan'"></span>
          </h4>
          <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider" x-text="activeRequestTitle + ' oleh ' + activeRequester"></p>
        </div>

        <form :action="'/workflows/' + activeRequestId + '/' + modalType" method="POST" class="space-y-4">
          @csrf
          <div class="space-y-2">
            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
              Catatan Tindak Lanjut <span x-show="modalType === 'reject'" class="text-rose-500">*</span>
            </label>
            <textarea name="remarks" :required="modalType === 'reject'" rows="4" x-model="remarks"
                 :placeholder="modalType === 'approve' ? 'Tambahkan catatan opsional jika diperlukan...' : 'Tuliskan alasan penolakan secara jelas (wajib, minimal 5 karakter)...'"
                 class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none"></textarea>
          </div>

          <div class="flex items-center justify-end space-x-2 pt-2">
            <button type="button" @click="showActionModal = false"
                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-bold transition">
              Batal
            </button>
            <button type="submit"
                :class="modalType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
                class="px-6 py-2.5 text-white rounded-xl text-xs font-black uppercase tracking-wider transition">
              <span x-text="modalType === 'approve' ? 'Konfirmasi Setujui' : 'Konfirmasi Tolak'"></span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</x-app-layout>
