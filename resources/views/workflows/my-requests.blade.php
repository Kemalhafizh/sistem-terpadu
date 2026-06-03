<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight flex items-center space-x-2">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 rounded-xl">📤</span>
                    <span>{{ __('Pelacakan Pengajuan Saya') }}</span>
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
                    Pantau alur persetujuan berjenjang dari pengajuan cuti, RPP, surat resmi, atau usulan lainnya yang telah Anda daftarkan
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        {{-- Stat Overview --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex items-center justify-between shadow-sm hover:shadow-md transition">
                <div>
                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Total Pengajuan</span>
                    <span class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1 block">{{ $requests->count() }}</span>
                </div>
                <span class="p-2 bg-indigo-500/10 text-indigo-500 rounded-xl text-lg">📋</span>
            </div>
            
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex items-center justify-between shadow-sm hover:shadow-md transition">
                <div>
                    <span class="text-[9px] font-black text-blue-500 uppercase tracking-widest block">Sedang Ditinjau</span>
                    <span class="text-2xl font-black text-blue-600 mt-1 block">
                        {{ $requests->whereIn('status', ['pending', 'in_review'])->count() }}
                    </span>
                </div>
                <span class="p-2 bg-blue-500/10 text-blue-500 rounded-xl text-lg">⏳</span>
            </div>

            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex items-center justify-between shadow-sm hover:shadow-md transition">
                <div>
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest block">Telah Disetujui</span>
                    <span class="text-2xl font-black text-emerald-600 mt-1 block">
                        {{ $requests->where('status', 'approved')->count() }}
                    </span>
                </div>
                <span class="p-2 bg-emerald-500/10 text-emerald-500 rounded-xl text-lg">✅</span>
            </div>

            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex items-center justify-between shadow-sm hover:shadow-md transition">
                <div>
                    <span class="text-[9px] font-black text-rose-500 uppercase tracking-widest block">Ditolak / Batal</span>
                    <span class="text-2xl font-black text-rose-600 mt-1 block">
                        {{ $requests->whereIn('status', ['rejected', 'cancelled'])->count() }}
                    </span>
                </div>
                <span class="p-2 bg-rose-500/10 text-rose-500 rounded-xl text-lg">❌</span>
            </div>
        </div>

        {{-- Requests List --}}
        <div class="space-y-6">
            @forelse($requests as $req)
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg shadow-sm border-l-4 
                     {{ $req->status === 'approved' ? 'border-l-emerald-500' : ($req->status === 'rejected' ? 'border-l-rose-500' : ($req->status === 'cancelled' ? 'border-l-gray-400' : 'border-l-blue-500')) }}">
                    
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        {{-- Info Block --}}
                        <div class="space-y-3 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/50">
                                    {{ $req->definition->name }}
                                </span>
                                
                                {{-- Status Badges --}}
                                @if($req->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                                        ✓ Disetujui Penuh
                                    </span>
                                @elseif($req->status === 'rejected')
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300">
                                        ✗ Ditolak
                                    </span>
                                @elseif($req->status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        Batal
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 animate-pulse">
                                        ⏳ Ditinjau (Step {{ $req->current_step }}/{{ $req->approvals->count() }})
                                    </span>
                                @endif
                                
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold">
                                    Diajukan pada {{ $req->created_at->format('d-m-Y H:i') }}
                                </span>
                            </div>

                            <h3 class="text-lg font-black text-gray-900 dark:text-gray-100">
                                @if(class_basename($req->requestable_type) === 'LeaveRequest')
                                    Permohonan Cuti: {{ $req->requestable->leave_type ?? 'Cuti Tahunan' }}
                                @elseif(class_basename($req->requestable_type) === 'Letter')
                                    Surat Keluar: {{ $req->requestable->subject ?? 'Dokumen Resmi' }}
                                @else
                                    Pengajuan Dokumen Polimorfik #{{ substr($req->uuid, 0, 8) }}
                                @endif
                            </h3>

                            @if($req->notes)
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold italic">
                                    "{{ $req->notes }}"
                                </p>
                            @endif

                            {{-- High Fidelity Horizontal Stepper Tracker --}}
                            <div class="pt-4 pb-2">
                                <div class="flex items-center w-full max-w-2xl">
                                    @foreach($req->approvals as $index => $approval)
                                        @php
                                            $isCompleted = $approval->decision === 'approved';
                                            $isActive = ($req->status !== 'approved' && $req->status !== 'rejected' && $req->status !== 'cancelled' && $req->current_step === $approval->step_order);
                                            $isRejected = $approval->decision === 'rejected';
                                            $isLocked = (!$isCompleted && !$isActive && !$isRejected);
                                        @endphp
                                        
                                        <div class="flex items-center flex-1 last:flex-initial">
                                            {{-- Stepper Node with custom Tooltip via hover group --}}
                                            <div class="relative group">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-xs transition duration-300 shadow-md cursor-pointer
                                                     {{ $isCompleted ? 'bg-emerald-500 text-white ring-4 ring-emerald-100 dark:ring-emerald-950/30' : '' }}
                                                     {{ $isActive ? 'bg-blue-500 text-white ring-4 ring-blue-100 dark:ring-blue-950/30 animate-pulse' : '' }}
                                                     {{ $isRejected ? 'bg-rose-500 text-white ring-4 ring-rose-100 dark:ring-rose-950/30' : '' }}
                                                     {{ $isLocked ? 'bg-gray-100 text-gray-400 dark:bg-slate-800 dark:text-slate-600' : '' }}">
                                                    
                                                    @if($isCompleted)
                                                        ✓
                                                    @elseif($isRejected)
                                                        ✗
                                                    @else
                                                        {{ $approval->step_order }}
                                                    @endif
                                                </div>

                                                {{-- Dropdown Tooltip --}}
                                                <div class="absolute bottom-12 left-1/2 transform -translate-x-1/2 bg-slate-900/95 dark:bg-slate-950/95 border border-slate-700/50 backdrop-blur-md rounded-xl p-3 text-white text-xs shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 transition-all duration-300 w-52 z-30 leading-normal">
                                                    <span class="block font-black text-[9px] uppercase tracking-widest text-indigo-400 mb-1">
                                                        Step {{ $approval->step_order }}: {{ str_replace('_', ' ', $approval->role) }}
                                                    </span>
                                                    @if($approval->decision === 'approved')
                                                        <span class="text-emerald-400 font-bold">✓ Disetujui</span>
                                                        <span class="block text-[10px] text-gray-300 mt-1">Oleh: {{ $approval->approver->name ?? 'Sistem' }}</span>
                                                        @if($approval->remarks)
                                                            <span class="block text-[10px] italic text-gray-300 mt-1 bg-slate-800 p-1.5 rounded-lg">"{{ $approval->remarks }}"</span>
                                                        @endif
                                                    @elseif($approval->decision === 'rejected')
                                                        <span class="text-rose-400 font-bold">✗ Ditolak</span>
                                                        <span class="block text-[10px] text-gray-300 mt-1">Oleh: {{ $approval->approver->name ?? 'Sistem' }}</span>
                                                        @if($approval->remarks)
                                                            <span class="block text-[10px] italic text-rose-300 mt-1 bg-rose-950/40 p-1.5 rounded-lg border border-rose-800/30">"{{ $approval->remarks }}"</span>
                                                        @endif
                                                    @else
                                                        <span class="text-amber-400 font-bold">⏳ Menunggu Tinjauan</span>
                                                        <span class="block text-[10px] text-gray-400 mt-0.5">Role berwenang: {{ str_replace('_', ' ', $approval->role) }}</span>
                                                    @endif
                                                </div>

                                                {{-- Tiny sub-label below step --}}
                                                <span class="absolute top-11 left-1/2 transform -translate-x-1/2 text-[8px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                                    {{ str_replace('_', ' ', $approval->role) }}
                                                </span>
                                            </div>

                                            {{-- Connecting line --}}
                                            @if(!$loop->last)
                                                <div class="flex-1 h-1 mx-2 rounded transition duration-300
                                                     {{ $isCompleted ? 'bg-emerald-500 shadow-sm shadow-emerald-500/20' : 'bg-gray-200 dark:bg-slate-800' }}">
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Action Block --}}
                        <div class="flex flex-row lg:flex-col gap-2 flex-shrink-0 self-end lg:self-center w-full lg:w-auto pt-4 lg:pt-0 border-t border-gray-100 dark:border-slate-800 lg:border-t-0">
                            <a href="{{ route('workflows.show', $req) }}" 
                               class="flex-1 lg:w-full text-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition active:scale-95 shadow-lg shadow-indigo-500/10 flex items-center justify-center space-x-1.5">
                                <span>🔍 Audit Lengkap</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Empty State --}}
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl p-16 text-center shadow-sm">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 bg-gray-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-5 border border-gray-100">
                            <span class="text-3xl">📤</span>
                        </div>
                        <h3 class="text-lg font-black text-gray-800 dark:text-gray-200 mb-2">Belum Ada Pengajuan</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm leading-relaxed">
                            Anda belum pernah mengajukan permohonan cuti, surat keluar, atau RPP berjenjang lewat workflow engine.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
