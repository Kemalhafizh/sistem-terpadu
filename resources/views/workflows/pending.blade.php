<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight flex items-center space-x-2">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 rounded-xl"></span>
                    <span>{{ __('Inbox Persetujuan') }}</span>
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
                    Evaluasi, berikan catatan, setujui, atau tolak berbagai pengajuan dokumen dan permohonan sekolah secara dinamis
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8"
         x-data="{ 
            showActionModal: false, 
            modalType: 'approve', {{-- 'approve' or 'reject' --}}
            activeRequestId: null, 
            activeRequestTitle: '',
            activeRequester: '',
            remarks: ''
         }">
         
        {{-- Success & Error Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50/80 dark:bg-emerald-950/30 backdrop-blur-sm border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center space-x-3 animate-fade-in">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50/80 dark:bg-rose-950/30 backdrop-blur-sm border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 rounded-2xl flex flex-col space-y-1 animate-fade-in">
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

        {{-- Inbox Stat Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-sm hover:shadow-md transition duration-300">
                <div>
                    <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest block">Menunggu Tindakan</span>
                    <span class="text-3xl font-black text-gray-900 dark:text-gray-100 mt-1 block">{{ $requests->count() }}</span>
                </div>
                <span class="p-3 bg-amber-500/10 text-amber-500 rounded-2xl text-xl">⏳</span>
            </div>
            
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-sm hover:shadow-md transition duration-300">
                <div>
                    <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest block">Tingkat Penilai Anda</span>
                    <span class="text-sm font-black text-gray-800 dark:text-gray-200 mt-2 block uppercase tracking-wider">
                        {{ str_replace('_', ' ', auth()->user()->role) }}
                    </span>
                </div>
                <span class="p-3 bg-indigo-500/10 text-indigo-500 rounded-2xl text-xl"></span>
            </div>

            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 flex items-center justify-between shadow-sm hover:shadow-md transition duration-300">
                <div>
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest block">Status Integrasi</span>
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-2 block">
                        Engine Aktif (Polimorfik)
                    </span>
                </div>
                <span class="p-3 bg-emerald-500/10 text-emerald-500 rounded-2xl text-xl"></span>
            </div>
        </div>

        {{-- Pending Requests List --}}
        <div class="space-y-4">
            @forelse($requests as $req)
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5 flex flex-col md:flex-row justify-between gap-6 shadow-sm border-l-4 border-l-amber-500">
                    
                    {{-- Request Info --}}
                    <div class="space-y-3 flex-grow max-w-4xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/50">
                                 {{ $req->definition->name }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50">
                                Step {{ $req->current_step }} / {{ $req->approvals->count() }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                ID: #{{ substr($req->uuid, 0, 8) }}
                            </span>
                        </div>

                        <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 flex items-center space-x-2">
                            <span>Pengajuan Cuti / Surat Resmi</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 font-semibold">• oleh {{ $req->requester->name }} ({{ str_replace('_', ' ', $req->requester->role) }})</span>
                        </h3>

                        {{-- Request Notes --}}
                        @if($req->notes)
                            <div class="p-3 bg-gray-50 dark:bg-slate-800 rounded-xl border border-gray-100 dark:border-slate-800 max-w-3xl">
                                <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400">Catatan Pengaju:</span>
                                <p class="text-xs text-gray-600 dark:text-gray-300 font-medium leading-relaxed mt-0.5">
                                    "{{ $req->notes }}"
                                </p>
                            </div>
                        @endif

                        {{-- Polymorphic Model Context Info --}}
                        <div class="p-4 bg-indigo-50/50 dark:bg-indigo-950/10 border border-indigo-100/50 dark:border-indigo-950/30 rounded-2xl max-w-3xl space-y-2">
                            <span class="text-[8px] font-black uppercase tracking-widest text-indigo-500 dark:text-indigo-400 block">Detail Konten (Tipe: {{ class_basename($req->requestable_type) }})</span>
                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @if(class_basename($req->requestable_type) === 'LeaveRequest')
                                    <div>
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400">Jenis Cuti</span>
                                        <span class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $req->requestable->leave_type ?? 'Cuti Umum' }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400">Rentang Waktu</span>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100">
                                            {{ $req->requestable->start_date ? \Carbon\Carbon::parse($req->requestable->start_date)->format('d M Y') : '-' }} - 
                                            {{ $req->requestable->end_date ? \Carbon\Carbon::parse($req->requestable->end_date)->format('d M Y') : '-' }}
                                        </span>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400">Alasan Lengkap</span>
                                        <span class="text-xs text-slate-600 dark:text-slate-400 block mt-0.5">{{ $req->requestable->reason ?? '-' }}</span>
                                    </div>
                                @elseif(class_basename($req->requestable_type) === 'Letter')
                                    <div>
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400">Nomor Surat</span>
                                        <span class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $req->requestable->letter_number ?? 'Belum Diatur' }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400">Perihal</span>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $req->requestable->subject ?? '-' }}</span>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400">Penerima / Tujuan</span>
                                        <span class="text-xs text-slate-600 dark:text-slate-400 block mt-0.5">{{ $req->requestable->recipient ?? '-' }}</span>
                                    </div>
                                @else
                                    <div class="sm:col-span-2">
                                        <span class="block text-[8px] font-black uppercase tracking-widest text-gray-400">Deskripsi</span>
                                        <span class="text-xs text-slate-600 dark:text-slate-400 block mt-0.5">Pengajuan terkait dokumen sekolah. Silakan lihat selengkapnya di detail pengajuan.</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Audit Trail Stepper Summary --}}
                        <div class="flex items-center space-x-2 pt-1">
                            <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500">Progress Tracker:</span>
                            <div class="flex items-center space-x-1.5">
                                @foreach($req->approvals as $approval)
                                    <div class="flex items-center space-x-1">
                                        @if($approval->decision === 'approved')
                                            <span class="w-3.5 h-3.5 bg-emerald-500 text-white rounded-full flex items-center justify-center text-[9px] font-black shadow-sm" title="{{ str_replace('_', ' ', $approval->role) }}: Disetujui"></span>
                                        @elseif($approval->decision === 'rejected')
                                            <span class="w-3.5 h-3.5 bg-rose-500 text-white rounded-full flex items-center justify-center text-[9px] font-black shadow-sm" title="{{ str_replace('_', ' ', $approval->role) }}: Ditolak"></span>
                                        @else
                                            <span class="w-3.5 h-3.5 bg-gray-200 dark:bg-slate-700 text-gray-500 dark:text-gray-400 rounded-full flex items-center justify-center text-[8px] font-black" title="{{ str_replace('_', ' ', $approval->role) }}: Menunggu">
                                                {{ $approval->step_order }}
                                            </span>
                                        @endif
                                        <span class="text-[9px] font-semibold text-gray-500 dark:text-gray-400 hidden sm:inline">{{ str_replace('_', ' ', $approval->role) }}</span>
                                        @if(!$loop->last)
                                            <span class="text-gray-300 dark:text-slate-800 text-[10px]">&rarr;</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Actions Block --}}
                    <div class="flex flex-row md:flex-col items-center justify-end gap-2 flex-shrink-0 self-end md:self-center w-full md:w-auto pt-4 md:pt-0 border-t border-gray-100 dark:border-slate-800 md:border-t-0">
                        <a href="{{ route('workflows.show', $req) }}" 
                           class="flex-1 md:w-full text-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition active:scale-95 flex items-center justify-center space-x-1.5 shadow-sm">
                            <span> Audit Trail & Detail</span>
                        </a>

                        <button @click="showActionModal = true; modalType = 'approve'; activeRequestId = {{ $req->id }}; activeRequestTitle = '{{ addslashes($req->definition->name) }}'; activeRequester = '{{ addslashes($req->requester->name) }}'; remarks = ''" 
                                class="flex-grow md:w-full px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition shadow-md shadow-emerald-500/10 active:scale-95 flex items-center justify-center space-x-1">
                            <span> Setujui</span>
                        </button>

                        <button @click="showActionModal = true; modalType = 'reject'; activeRequestId = {{ $req->id }}; activeRequestTitle = '{{ addslashes($req->definition->name) }}'; activeRequester = '{{ addslashes($req->requester->name) }}'; remarks = ''" 
                                class="flex-grow md:w-full px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition shadow-md shadow-rose-500/10 active:scale-95 flex items-center justify-center space-x-1">
                            <span> Tolak</span>
                        </button>
                    </div>
                </div>
            @empty
                {{-- Empty State --}}
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl p-16 text-center shadow-sm">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 bg-gray-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-5 border border-gray-100">
                            <span class="text-3xl"></span>
                        </div>
                        <h3 class="text-lg font-black text-gray-800 dark:text-gray-200 mb-2">Kotak Masuk Kosong</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm leading-relaxed">
                            Tidak ada pengajuan persetujuan yang sedang menanti verifikasi role Anda saat ini. Semua tugas selesai dikerjakan!
                        </p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Premium Action Modal (Approve / Reject) --}}
        <div x-show="showActionModal" x-transition.opacity x-cloak
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
             
            <div @click.away="showActionModal = false"
                 class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-100 dark:border-slate-800 max-w-md w-full p-6 space-y-6 shadow-2xl relative animate-scale-up">
                 
                {{-- Modal Header --}}
                <div class="space-y-1">
                    <h4 class="text-lg font-black text-gray-900 dark:text-gray-100 flex items-center space-x-2">
                        <span :class="modalType === 'approve' ? 'p-1.5 bg-emerald-500/10 text-emerald-600' : 'p-1.5 bg-rose-500/10 text-rose-600'" class="rounded-lg">
                            <span x-text="modalType === 'approve' ? '' : ''"></span>
                        </span>
                        <span x-text="modalType === 'approve' ? 'Persetujuan Pengajuan' : 'Penolakan Pengajuan'"></span>
                    </h4>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-text="activeRequestTitle + ' oleh ' + activeRequester"></p>
                </div>

                {{-- Modal Form --}}
                <form :action="'/workflows/' + activeRequestId + '/' + modalType" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
                            Catatan Tindak Lanjut <span x-show="modalType === 'reject'" class="text-rose-500">*</span>
                        </label>
                        <textarea name="remarks" :required="modalType === 'reject'" rows="4" x-model="remarks"
                                  :placeholder="modalType === 'approve' ? 'Tambahkan catatan opsional jika diperlukan...' : 'Tuliskan alasan penolakan secara jelas (wajib, minimal 5 karakter)...'"
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:ring-1 transition-all font-medium leading-relaxed"
                                  :class="modalType === 'approve' ? 'focus:border-emerald-500 focus:ring-emerald-500' : 'focus:border-rose-500 focus:ring-rose-500'"></textarea>
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button type="button" @click="showActionModal = false"
                                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-bold transition active:scale-95">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="modalType === 'reject' && remarks.trim().length < 5"
                                :class="modalType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/15' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-500/15'"
                                class="px-5 py-2.5 disabled:opacity-50 text-white rounded-xl text-xs font-black uppercase tracking-widest transition shadow-lg active:scale-95">
                            <span x-text="modalType === 'approve' ? 'Setujui Dokumen' : 'Tolak & Kembalikan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
