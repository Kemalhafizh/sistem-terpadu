<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <a href="{{ url()->previous() ?? route('workflows.my-requests') }}" class="text-xs font-black uppercase tracking-wider text-indigo-500 hover:text-indigo-600 transition flex items-center space-x-1">
            <span>&larr; Kembali</span>
          </a>
        </div>
        <h2 class="font-bold text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight mt-1">
          <span>Detail Pelacakan &amp; Audit Trail</span>
        </h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
          UUID: {{ $request->uuid }} • Diajukan oleh {{ $request->requester->name }}
        </p>
      </div>
      
      <div class="flex items-center gap-2">
        @if($request->status === 'approved')
          <span class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
             Disetujui Penuh
          </span>
        @elseif($request->status === 'rejected')
          <span class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
             Ditolak
          </span>
        @elseif($request->status === 'revision')
          <span class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
             Perlu Revisi
          </span>
        @elseif($request->status === 'cancelled')
          <span class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
            Batal
          </span>
        @else
          <span class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
             Sedang Ditinjau
          </span>
        @endif
      </div>
    </div>
  </x-slot>

  <div class="py-8 w-full px-4 sm:px-6 lg:px-8">
    
    {{-- Success & Error Alerts --}}
    @if(session('success'))
      <div class="mb-6 p-4 bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center space-x-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="text-sm font-bold">{{ session('success') }}</span>
      </div>
    @endif

    @if($errors->any())
      <div class="mb-6 p-4 bg-rose-50/80 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 rounded-2xl flex flex-col space-y-1">
        @foreach($errors->all() as $error)
          <div class="flex items-center space-x-2 text-sm font-bold">
            <svg class="w-4 h-4 flex-shrink-0 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span>{{ $error }}</span>
          </div>
        @endforeach
      </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      {{-- Column 1 & 2: Polymorphic Document Details --}}
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl p-6 md:p-8 shadow-xs">
          <div class="border-b border-gray-100 dark:border-slate-800 pb-5 mb-6 flex items-center justify-between">
            <div>
              <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400 block">Dokumen Pengajuan</span>
              <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-1">
                {{ $request->definition->name }}
              </h3>
            </div>
            <span class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 text-xs font-medium text-gray-500 dark:text-gray-400 rounded-lg border border-gray-200 dark:border-slate-700">
              Tipe: {{ class_basename($request->requestable_type) }}
            </span>
          </div>

          {{-- Dinamis Polymorphic Fields View --}}
          @if(class_basename($request->requestable_type) === 'LeaveRequest')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm font-medium text-gray-700 dark:text-gray-300 leading-relaxed">
              <div class="sm:col-span-2 p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-800">
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Pengaju / Guru</span>
                <span class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $request->requester->name }}</span>
              </div>
              <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Tipe / Alasan Cuti</span>
                <span class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $request->requestable?->type_label ?? '–' }}</span>
              </div>
              <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Durasi Tanggal</span>
                <span class="text-base font-semibold text-slate-900 dark:text-slate-100 tabular-nums">
                  {{ $request->requestable->start_date ? \Carbon\Carbon::parse($request->requestable->start_date)->format('d M Y') : '-' }} s/d 
                  {{ $request->requestable->end_date ? \Carbon\Carbon::parse($request->requestable->end_date)->format('d M Y') : '-' }}
                </span>
              </div>
              <div class="sm:col-span-2">
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Alasan &amp; Keperluan Lengkap</span>
                <p class="text-sm text-slate-700 dark:text-slate-300 bg-gray-50 dark:bg-slate-800 p-4 rounded-xl border border-gray-200 dark:border-slate-700 mt-1 leading-relaxed">
                  {{ $request->requestable->reason ?? '-' }}
                </p>
              </div>
              @if($request->requestable?->attachment)
                <div class="sm:col-span-2">
                  <a href="{{ Storage::disk('public')->url($request->requestable->attachment) }}" target="_blank" rel="noopener" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('Lihat lampiran (surat dokter, undangan, dll.)') }}</a>
                </div>
              @endif
            </div>
          @elseif(class_basename($request->requestable_type) === 'Letter')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm font-medium text-gray-700 dark:text-gray-300 leading-relaxed">
              <div class="sm:col-span-2 p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-800">
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Perihal Surat</span>
                <span class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $request->requestable->subject ?? '-' }}</span>
              </div>
              <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Nomor Surat</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 tabular-nums">{{ $request->requestable->letter_number ?? 'Belum Diatur' }}</span>
              </div>
              <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Penerima / Tujuan</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $request->requestable->recipient ?? '-' }}</span>
              </div>
              <div class="sm:col-span-2">
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Penyusun Surat</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $request->requester->name }}</span>
              </div>
            </div>
          @elseif(class_basename($request->requestable_type) === 'GraduationBatch' && $request->requestable)
            @php
              $gBatch = $request->requestable;
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
              <div class="sm:col-span-2 p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                <div>
                  <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Batch Kelulusan Kelas XII</span>
                  <span class="text-base font-semibold text-slate-900 dark:text-slate-100 tabular-nums">
                    Angkatan {{ $gBatch->graduation_year }}
                    @if($gBatch->relationLoaded('schoolYear') && $gBatch->schoolYear)
                      · TA {{ $gBatch->schoolYear->name }}
                    @endif
                  </span>
                </div>
                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">
                  Status: {{ ucfirst((string) $gBatch->status) }}
                </span>
              </div>
              <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Nomor SK Kelulusan</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 tabular-nums">
                  {{ $gBatch->sk_number ?: 'Belum ditetapkan' }}
                </span>
              </div>
              <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal Pengumuman Kelulusan</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 tabular-nums">
                  {{ $gBatch->graduation_date ? $gBatch->graduation_date->format('d M Y') : 'Belum dijadwalkan' }}
                </span>
              </div>
              @if($gBatch->relationLoaded('meetingNote') && $gBatch->meetingNote)
                <div class="sm:col-span-2">
                  <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Notulen Rapat Pleno Dewan Guru</span>
                  <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $gBatch->meetingNote->title }}</span>
                </div>
              @endif
              @if($gBatch->relationLoaded('decisions'))
                <div class="sm:col-span-2 flex flex-wrap gap-2 text-xs tabular-nums">
                  <span class="px-3 py-1 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-medium">
                    {{ $gBatch->decisions->where('decision', 'lulus')->count() }} lulus
                  </span>
                  <span class="px-3 py-1 rounded-md bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 font-medium">
                    {{ $gBatch->decisions->where('decision', 'tidak_lulus')->count() }} belum lulus
                  </span>
                  <span class="px-3 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                    {{ $gBatch->decisions->count() }} total peserta
                  </span>
                </div>
              @endif
              @if($gBatch->notes)
                <div class="sm:col-span-2">
                  <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Catatan Pleno Kelulusan</span>
                  <p class="text-sm text-slate-700 dark:text-slate-300 bg-gray-50 dark:bg-slate-800 p-4 rounded-xl border border-gray-200 dark:border-slate-700 mt-1">
                    {{ $gBatch->notes }}
                  </p>
                </div>
              @endif
              @canVisit('academic.cycle.index')
                <div class="sm:col-span-2">
                  <a href="{{ route('academic.cycle.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ __('Buka rincian keputusan siswa di Siklus Akademik') }} &rarr;
                  </a>
                </div>
              @endcanVisit
            </div>
          @elseif(class_basename($request->requestable_type) === 'StudentMutation' && $request->requestable)
            @php
              $mutation = $request->requestable;
              $mutationStudentName = $mutation->relationLoaded('student')
                  ? $mutation->student_display_name
                  : (is_array($mutation->candidate_data) && filled($mutation->candidate_data['name'] ?? null)
                      ? (string) $mutation->candidate_data['name']
                      : ($mutation->type === 'in' ? 'Calon Siswa Pindahan' : 'Siswa Mutasi Keluar'));
              $billingStatusLabel = match ($mutation->billing_review_status) {
                  'cleared' => 'Bebas tunggakan',
                  'settled' => 'Tagihan telah diselesaikan',
                  'needs_settlement' => 'Perlu penyelesaian tagihan berjalan',
                  default => $mutation->billing_review_status ?: 'Belum ditinjau',
              };
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
              <div class="sm:col-span-2 p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                <div>
                  <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                    {{ $mutation->type === 'in' ? 'Mutasi Masuk (Siswa Pindahan)' : 'Mutasi Keluar Siswa' }}
                  </span>
                  <span class="text-base font-semibold text-slate-900 dark:text-slate-100">
                    {{ $mutationStudentName }}
                    @if($mutation->relationLoaded('classroom') && $mutation->classroom)
                      ({{ $mutation->classroom->name }})
                    @endif
                  </span>
                </div>
                <span class="px-2.5 py-1 rounded-md text-xs font-medium {{ $mutation->type === 'in' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' }}">
                  {{ $mutation->type === 'in' ? 'Mutasi Masuk' : 'Mutasi Keluar' }}
                </span>
              </div>
              <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                  {{ $mutation->type === 'in' ? 'Sekolah Asal' : 'Sekolah Tujuan' }}
                </span>
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                  {{ $mutation->target_school ?: '–' }}
                </span>
              </div>
              <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal Efektif Mutasi</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 tabular-nums">
                  {{ $mutation->mutation_date ? \Carbon\Carbon::parse($mutation->mutation_date)->format('d M Y') : '–' }}
                </span>
              </div>
              <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Nomor Surat Mutasi</span>
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 tabular-nums">
                  {{ $mutation->letter_number ?: 'Belum diatur' }}
                </span>
              </div>
              @if($mutation->type === 'out' || $mutation->billing_review_status)
                <div>
                  <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Status Tinjauan Tagihan</span>
                  <span class="text-sm font-semibold {{ $mutation->billing_review_status === 'needs_settlement' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                    {{ $billingStatusLabel }}
                  </span>
                </div>
              @endif
              <div class="sm:col-span-2">
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Alasan Permohonan Mutasi</span>
                <p class="text-sm text-slate-700 dark:text-slate-300 bg-gray-50 dark:bg-slate-800 p-4 rounded-xl border border-gray-200 dark:border-slate-700 mt-1">
                  {{ $mutation->reason ?: '–' }}
                </p>
              </div>
              <div class="sm:col-span-2 flex flex-wrap items-center gap-4">
                @if($mutation->document_path && \App\Support\Access\RouteAccess::allowsRoute(auth()->user(), 'mutations.document'))
                  <a href="{{ route('mutations.document', $mutation) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ __('Unduh berkas surat mutasi') }}
                  </a>
                @endif
                @canVisit('mutations.index')
                  <a href="{{ route('mutations.index') }}" class="text-xs font-semibold text-slate-600 dark:text-slate-400 hover:underline">
                    {{ __('Buka daftar mutasi siswa') }} &rarr;
                  </a>
                @endcanVisit
              </div>
            </div>
          @else
            {{-- Generic view if no specific class matched --}}
            <div class="p-4 bg-gray-50 dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 text-xs font-medium text-gray-600 dark:text-gray-400 leading-relaxed">
              <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Atribut Model Induk</span>
              <pre class="bg-gray-100 dark:bg-slate-900 p-4 rounded-lg font-mono text-xs overflow-auto max-h-96">
{{ json_encode($request->requestable ? $request->requestable->toArray() : [], JSON_PRETTY_PRINT) }}
              </pre>
            </div>
          @endif

          @if($request->notes)
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-slate-800">
              <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400">Catatan Awal Pengaju:</span>
              <p class="text-xs text-gray-600 dark:text-gray-300 font-medium italic mt-1 leading-relaxed">
                "{{ $request->notes }}"
              </p>
            </div>
          @endif
        </div>
      </div>

      {{-- Column 3: Detailed Audit Trail & Action Form --}}
      <div class="space-y-6">
        
        {{-- Audit Trail Timeline --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm">
          <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest block mb-4">Audit Trail Logs</span>
          
          <div class="relative pl-6 border-l-2 border-gray-200 dark:border-slate-800 space-y-6">
            @foreach($request->approvals as $approval)
              @php
                $isCompleted = $approval->decision === 'approved';
                $isRejected = $approval->decision === 'rejected';
                $isRevision = $approval->decision === 'revision';
                $isActive = ($request->status !== 'approved' && $request->status !== 'rejected' && $request->status !== 'cancelled' && $request->status !== 'revision' && $request->current_step === $approval->step_order);
                $isLocked = (!$isCompleted && !$isActive && !$isRejected && !$isRevision);
              @endphp

              <div class="relative">
                {{-- Timeline Icon node --}}
                <div class="absolute -left-[35px] w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black shadow transition-all duration-300
                   {{ $isCompleted ? 'bg-emerald-500 text-white ring-4 ring-emerald-50 dark:ring-emerald-950/20' : '' }}
                   {{ $isRejected ? 'bg-rose-500 text-white ring-4 ring-rose-50 dark:ring-rose-950/20' : '' }}
                   {{ $isRevision ? 'bg-amber-500 text-white ring-4 ring-amber-50 dark:ring-amber-950/20 animate-pulse' : '' }}
                   {{ $isActive ? 'bg-blue-500 text-white ring-4 ring-blue-50 dark:ring-blue-950/20 animate-pulse' : '' }}
                   {{ $isLocked ? 'bg-gray-100 text-gray-400 dark:bg-slate-800 dark:text-slate-600' : '' }}">
                  @if($isCompleted)
                    
                  @elseif($isRejected)
                    
                  @elseif($isRevision)
                    
                  @else
                    {{ $approval->step_order }}
                  @endif
                </div>

                {{-- Details --}}
                <div class="space-y-1">
                  <h4 class="text-xs font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                    Step {{ $approval->step_order }}: {{ str_replace('_', ' ', $approval->role) }}
                  </h4>
                  
                  @if($isCompleted)
                    <div class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex flex-col">
                      <span> Disetujui</span>
                      <span class="text-gray-500 dark:text-gray-400 mt-0.5">Penilai: {{ $approval->approver->name ?? 'Sistem' }}</span>
                      <span class="text-[9px] text-gray-400 dark:text-gray-500">{{ $approval->decided_at ? $approval->decided_at->format('d-m-Y H:i') : '' }}</span>
                    </div>
                    @if($approval->remarks)
                      <p class="text-[11px] italic font-medium text-gray-600 dark:text-gray-400 mt-1 bg-gray-50 dark:bg-slate-800 p-2 rounded-xl border border-gray-100 dark:border-slate-800 leading-relaxed">
                        "{{ $approval->remarks }}"
                      </p>
                    @endif
                  @elseif($isRejected)
                    <div class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 flex flex-col">
                      <span> Ditolak & Dihentikan</span>
                      <span class="text-gray-500 dark:text-gray-400 mt-0.5">Penilai: {{ $approval->approver->name ?? 'Sistem' }}</span>
                      <span class="text-[9px] text-gray-400 dark:text-gray-500">{{ $approval->decided_at ? $approval->decided_at->format('d-m-Y H:i') : '' }}</span>
                    </div>
                    @if($approval->remarks)
                      <p class="text-[11px] italic font-medium text-rose-800 dark:text-rose-300 mt-1 bg-rose-50/50 dark:bg-rose-950/20 p-2 rounded-xl border border-rose-100 dark:border-rose-900/30 leading-relaxed">
                        "{{ $approval->remarks }}"
                      </p>
                    @endif
                  @elseif($isRevision)
                    <div class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 flex flex-col">
                      <span> Perlu Revisi</span>
                      <span class="text-gray-500 dark:text-gray-400 mt-0.5">Penilai: {{ $approval->approver->name ?? 'Sistem' }}</span>
                      <span class="text-[9px] text-gray-400 dark:text-gray-500">{{ $approval->decided_at ? $approval->decided_at->format('d-m-Y H:i') : '' }}</span>
                    </div>
                    @if($approval->remarks)
                      <p class="text-[11px] italic font-medium text-amber-800 dark:text-amber-300 mt-1 bg-amber-50/50 dark:bg-amber-950/20 p-2 rounded-xl border border-amber-100 dark:border-amber-900/30 leading-relaxed">
                        "{{ $approval->remarks }}"
                      </p>
                    @endif
                  @elseif($isActive)
                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 block animate-pulse">
                       Menunggu Review Saat Ini
                    </span>
                    <span class="text-[9px] text-gray-400 dark:text-gray-500">
                      Menunggu tindakan dari role: {{ str_replace('_', ' ', $approval->role) }}
                    </span>
                  @else
                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-600 block">
                       Dikunci
                    </span>
                  @endif

                  {{-- Info Delegasi --}}
                  @if($approval->delegated_to_id)
                    <div class="mt-1 text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/30 px-2 py-0.5 rounded-lg border border-blue-200 dark:border-blue-800/30 inline-block">
                       Didelegasikan ke: {{ $approval->delegatedTo->name }}
                    </div>
                  @endif

                  {{-- SLA Limit --}}
                  @if($approval->due_at)
                    <div class="text-[9px] text-gray-400 dark:text-gray-500 flex items-center mt-1">
                      <span> SLA: {{ $approval->due_at->format('d-m-Y H:i') }}</span>
                      @if($approval->decision === 'pending' && $approval->due_at->isPast())
                        <span class="ml-2 px-1 py-0.2 bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 text-[8px] font-bold uppercase rounded border border-rose-200 dark:border-rose-900/30 animate-pulse">SLA Breached</span>
                      @endif
                    </div>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Action Panel Form (If pending and authorized for the current user) --}}
        @php
          $currentApproval = $request->currentApproval;
          $isAuthorizedApprover = false;
          if ($request->status !== 'approved' && $request->status !== 'rejected' && $request->status !== 'cancelled' && $request->status !== 'revision' && $currentApproval) {
            $isAuthorizedApprover = ($currentApproval->role === auth()->user()->role && $currentApproval->delegated_to_id === null)
              || $currentApproval->delegated_to_id === auth()->id();
          }
        @endphp

        @if($isAuthorizedApprover)
          <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-indigo-100 dark:border-indigo-950/50 rounded-3xl p-6 shadow-xl relative overflow-hidden"
             x-data="{ activeAction: 'approve', remarks: '', delegatee_id: '' }">
            
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl pointer-events-none"></div>

            <span class="text-[9px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest block mb-1">Tinjauan Anda Diperlukan</span>
            <h4 class="text-sm font-black text-gray-900 dark:text-gray-100 mb-4">Ulas & Berikan Keputusan</h4>

            <div class="grid grid-cols-2 gap-2 bg-gray-50 dark:bg-slate-800 p-1.5 rounded-2xl mb-4 border border-gray-100 dark:border-slate-800">
              <button type="button" @click="activeAction = 'approve'"
                  :class="activeAction === 'approve' ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-500/10' : 'text-gray-600 dark:text-gray-400 font-semibold'"
                  class="py-1.5 text-xs rounded-xl transition-all">
                 Setujui
              </button>
              <button type="button" @click="activeAction = 'reject'"
                  :class="activeAction === 'reject' ? 'bg-rose-600 text-white font-bold shadow-md shadow-rose-500/10' : 'text-gray-600 dark:text-gray-400 font-semibold'"
                  class="py-1.5 text-xs rounded-xl transition-all">
                 Tolak (Final)
              </button>
              <button type="button" @click="activeAction = 'revise'"
                  :class="activeAction === 'revise' ? 'bg-amber-600 text-white font-bold shadow-md shadow-amber-500/10' : 'text-gray-600 dark:text-gray-400 font-semibold'"
                  class="py-1.5 text-xs rounded-xl transition-all col-span-2">
                 Kembalikan untuk Revisi
              </button>
              @if($currentApproval->role === auth()->user()->role)
                <button type="button" @click="activeAction = 'delegate'"
                    :class="activeAction === 'delegate' ? 'bg-blue-600 text-white font-bold shadow-md shadow-blue-500/10' : 'text-gray-600 dark:text-gray-400 font-semibold'"
                    class="py-1.5 text-xs rounded-xl transition-all col-span-2">
                   Delegasikan Tugas
                </button>
              @endif
            </div>

            <form :action="'/workflows/{{ $request->getRouteKey() }}/' + activeAction" method="POST" class="space-y-4">
              @csrf
              
              {{-- Dropdown Pilihan Delegasi --}}
              <div class="space-y-1.5" x-show="activeAction === 'delegate'">
                <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
                  Pilih User Penerima Delegasi <span class="text-rose-500">*</span>
                </label>
                <select name="delegatee_id" x-model="delegatee_id" :required="activeAction === 'delegate'"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs focus:outline-none focus:ring-1 focus:border-blue-500 focus:ring-blue-500 transition-all text-gray-800 dark:text-white">
                  <option value="">-- Pilih Staf --</option>
                  @foreach($staffUsers as $staff)
                    <option value="{{ $staff->id }}">{{ $staff->name }} ({{ str_replace('_', ' ', $staff->role) }})</option>
                  @endforeach
                </select>
              </div>

              <div class="space-y-1.5">
                <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
                  Catatan / Alasan Tindakan <span x-show="activeAction === 'reject' || activeAction === 'revise'" class="text-rose-500">*</span>
                </label>
                <textarea name="remarks" :required="activeAction === 'reject' || activeAction === 'revise'" rows="3" x-model="remarks"
                     :placeholder="activeAction === 'approve' ? 'Tambahkan catatan opsional...' : (activeAction === 'delegate' ? 'Tulis instruksi delegasi (opsional)...' : 'Tuliskan catatan perbaikan/alasan keputusan (wajib, minimal 5 karakter)...')"
                     class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs focus:outline-none focus:ring-1 transition-all"
                     :class="activeAction === 'approve' ? 'focus:border-emerald-500 focus:ring-emerald-500' : (activeAction === 'delegate' ? 'focus:border-blue-500 focus:ring-blue-500' : 'focus:border-rose-500 focus:ring-rose-500')"></textarea>
              </div>

              <button type="submit"
                  :disabled="(activeAction === 'reject' || activeAction === 'revise') && remarks.trim().length < 5 || (activeAction === 'delegate' && !delegatee_id)"
                  :class="activeAction === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700' : (activeAction === 'delegate' ? 'bg-blue-600 hover:bg-blue-700' : (activeAction === 'revise' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-rose-600 hover:bg-rose-700'))"
                  class="w-full py-2.5 disabled:opacity-50 text-white rounded-xl text-xs font-black uppercase tracking-widest transition shadow-lg active:scale-95">
                <span x-text="activeAction === 'approve' ? 'Kirim Persetujuan' : (activeAction === 'delegate' ? 'Kirim Delegasi' : (activeAction === 'revise' ? 'Minta Revisi' : 'Kirim Penolakan'))"></span>
              </button>
            </form>
          </div>
        @endif

        {{-- Resubmit Form (If revision and current user is requester) --}}
        @if($request->status === 'revision' && $request->requester_id === auth()->id())
          <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-amber-200 dark:border-amber-950/50 rounded-3xl p-6 shadow-xl relative overflow-hidden">
            <span class="text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest block mb-1">Pengajuan Perlu Diperbaiki</span>
            <h4 class="text-sm font-black text-gray-900 dark:text-gray-100 mb-4">Kirim Ulang Setelah Revisi</h4>

            <form action="{{ route('workflows.resubmit', $request) }}" method="POST" class="space-y-4">
              @csrf
              <div class="space-y-1.5">
                <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
                  Catatan Tambahan untuk Revisi (Opsional)
                </label>
                <textarea name="notes" rows="3" placeholder="Tuliskan catatan perbaikan yang telah dilakukan..."
                     class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs focus:outline-none focus:ring-1 focus:border-amber-500 focus:ring-amber-500 transition-all"></textarea>
              </div>

              <button type="submit" class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition shadow-lg active:scale-95">
                Kirim Ulang Pengajuan
              </button>
            </form>
          </div>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>
