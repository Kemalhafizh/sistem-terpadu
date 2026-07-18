<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Detail Laporan Tiket Perbaikan') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">IoT Asset Maintenance Ticket Details</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto space-y-6">
        <!-- Back Link -->
        <div class="mb-2">
            <a href="{{ route('admin.assets.maintenance.index') }}" class="inline-flex items-center text-xs font-bold text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400 transition-all">
                <svg class="w-4 h-4 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Kanban Board
            </a>
        </div>

        <!-- Notification Alerts -->
        @if(session('error'))
            <div class="p-4 bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-100 dark:border-rose-900/50 rounded-2xl flex items-center space-x-3 shadow-md animate-fade-in-up">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="text-sm font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Side: Issue Details (2 columns on large screen) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Asset Profile & Issue Summary Card -->
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-sm border border-gray-150 dark:border-gray-700/50 overflow-hidden">
                    <div class="p-6 md:p-8 space-y-6">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-100 dark:border-gray-700 pb-6">
                            <div>
                                <span class="text-[9px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400 block mb-1">DETAIL TIKET PERBAIKAN</span>
                                <h3 class="text-xl font-black text-gray-950 dark:text-white leading-tight">Tiket #{{ $ticket->id }}</h3>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $ticket->priority_color }}">
                                    {{ $ticket->priority }}
                                </span>
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $ticket->status_color }}">
                                    {{ $ticket->status }}
                                </span>
                            </div>
                        </div>

                        <!-- Asset Details -->
                        <div>
                            <span class="block text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">INFORMASI ASET</span>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-800">
                                <div>
                                    <span class="block text-[9px] font-black text-gray-400 uppercase tracking-wider">Nama Aset</span>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $ticket->asset->name }}</span>
                                </div>
                                <div>
                                    <span class="block text-[9px] font-black text-gray-400 uppercase tracking-wider">Kode Aset</span>
                                    <span class="text-sm font-mono font-bold text-teal-650 dark:text-teal-400">{{ $ticket->asset->asset_code }}</span>
                                </div>
                                <div>
                                    <span class="block text-[9px] font-black text-gray-400 uppercase tracking-wider">Lokasi Ruangan</span>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $ticket->asset->room->name ?? 'N/A' }}</span>
                                    @if($ticket->asset->room && $ticket->asset->room->location)
                                        <span class="block text-[10px] text-gray-400 mt-0.5">{{ $ticket->asset->room->location }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Reported Info -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <span class="block text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">PELAPOR</span>
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 rounded-full bg-teal-50 dark:bg-teal-950/40 border border-teal-150 dark:border-teal-900/50 text-teal-600 dark:text-teal-400 font-black flex items-center justify-center text-xs uppercase">
                                        {{ substr($ticket->reporter->name ?? 'A', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $ticket->reporter->name ?? 'Anonim' }}</div>
                                        <div class="text-[10px] text-gray-400 font-semibold">{{ $ticket->reporter->email ?? '' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">TANGGAL LAPORAN</span>
                                <div class="flex items-center text-sm font-bold text-gray-800 dark:text-gray-200 mt-2">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>{{ $ticket->created_at->format('d M Y H:i') }}</span>
                                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500 ml-1.5">({{ $ticket->created_at->diffForHumans() }})</span>
                                </div>
                            </div>
                        </div>

                        <!-- Description of issue text -->
                        <div class="space-y-2">
                            <span class="block text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">DESKRIPSI KERUSAKAN</span>
                            <div class="bg-slate-50 dark:bg-gray-900/30 p-5 rounded-2xl border border-gray-150 dark:border-gray-800 text-gray-800 dark:text-gray-200 leading-relaxed font-semibold italic text-sm">
                                &ldquo;{{ $ticket->issue_description }}&rdquo;
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photo Evidence Card -->
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-sm border border-gray-150 dark:border-gray-700/50 p-6 md:p-8 space-y-4">
                    <span class="block text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">FOTO BUKTI KERUSAKAN</span>
                    @if($ticket->photo_url)
                        <div class="relative overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-800 group">
                            <img src="{{ $ticket->photo_url }}" alt="Bukti Kerusakan Aset" class="w-full max-h-[450px] object-contain mx-auto bg-gray-50 dark:bg-gray-950 transition-all duration-500 group-hover:scale-[1.02]">
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center p-12 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl text-gray-400 dark:text-gray-650 bg-gray-50 dark:bg-gray-900/20">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm font-semibold">Tidak ada bukti foto yang dilampirkan oleh pelapor.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Side: Resolution Form (1 column) -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-sm border border-gray-150 dark:border-gray-700/50 p-6 md:p-8 space-y-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 left-0 h-2 bg-gradient-to-r from-teal-500 to-indigo-600"></div>
                    
                    <div>
                        <h3 class="text-lg font-black text-gray-950 dark:text-white leading-tight">Resolusi & Status</h3>
                        <p class="text-[10px] font-black text-teal-650 dark:text-teal-400 uppercase tracking-widest mt-1">Form Tindakan Perbaikan</p>
                    </div>

                    <!-- Sync Info Notice Box -->
                    <div class="bg-blue-50 dark:bg-blue-950/30 p-4 rounded-2xl text-[11px] leading-relaxed font-semibold text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50">
                        <h5 class="font-black text-xs uppercase tracking-wider mb-1 flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Sinkronisasi Kondisi Aset
                        </h5>
                        Pembaruan status otomatis memicu sinkronisasi berikut:
                        <ul class="list-disc pl-4 mt-1 space-y-0.5">
                            <li><strong>Selesai (Resolved)</strong>: Kondisi aset diubah menjadi <span class="underline">Baik</span> & Status <span class="underline">Aktif</span>.</li>
                            <li><strong>Afkir (Scrapped)</strong>: Kondisi aset diubah menjadi <span class="underline">Rusak Berat</span> & Status <span class="underline">Nonaktif</span>.</li>
                            <li><strong>Status Lainnya</strong>: Status aset otomatis diubah menjadi <span class="underline">Perbaikan</span>.</li>
                        </ul>
                    </div>

                    <form action="{{ route('admin.assets.maintenance.update-status', $ticket->id) }}" method="POST" class="space-y-5">
                        @csrf
                        
                        <!-- Status Select Dropdown -->
                        <div>
                            <label for="ticket-status" class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">UPDATE STATUS TIKET</label>
                            <select id="ticket-status" name="status" class="w-full rounded-2xl border-gray-250 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm font-bold focus:border-teal-500 focus:ring-teal-500 text-gray-800 dark:text-gray-200">
                                <option value="pending" {{ $ticket->status === 'pending' ? 'selected' : '' }}>Baru (Pending)</option>
                                <option value="investigating" {{ $ticket->status === 'investigating' ? 'selected' : '' }}>Investigasi (Investigating)</option>
                                <option value="repairing" {{ $ticket->status === 'repairing' ? 'selected' : '' }}>Perbaikan (Repairing)</option>
                                <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Selesai (Resolved)</option>
                                <option value="scrapped" {{ $ticket->status === 'scrapped' ? 'selected' : '' }}>Afkir (Scrapped)</option>
                            </select>
                        </div>

                        <!-- Resolution Notes Textarea -->
                        <div>
                            <label for="resolution_notes" class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">
                                CATATAN RESOLUSI <span id="notes-required-indicator" class="text-rose-500 hidden">*</span>
                            </label>
                            <textarea id="resolution_notes" name="resolution_notes" rows="5"
                                class="w-full rounded-2xl border-gray-250 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm focus:border-teal-500 focus:ring-teal-500 text-gray-800 dark:text-gray-200 placeholder-gray-400"
                                placeholder="Jelaskan tindakan perbaikan yang diambil, suku cadang yang diganti, atau rincian aset yang diafkir...">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
                            <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500 mt-1">Wajib diisi jika status diubah menjadi "Selesai" atau "Afkir".</p>
                            @error('resolution_notes')
                                <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit" class="w-full py-4 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-teal-600/15 hover:shadow-teal-600/25 transition-all transform active:scale-[0.98] flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                <span>Simpan Perubahan Status</span>
                            </button>
                        </div>
                    </form>

                    @if($ticket->resolved_by)
                        <!-- Resolver History info -->
                        <div class="border-t border-gray-150 dark:border-gray-750 pt-5 mt-5">
                            <span class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">RIWAYAT RESOLUSI TIKET</span>
                            <div class="grid grid-cols-1 gap-2.5 bg-gray-50 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                                <div>
                                    <span class="block text-[9px] font-black text-gray-400 uppercase tracking-wider">Diselesaikan Oleh</span>
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $ticket->resolver->name ?? 'System' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[9px] font-black text-gray-400 uppercase tracking-wider">Waktu Resolusi</span>
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                        {{ $ticket->resolved_at ? $ticket->resolved_at->format('d M Y H:i') : '-' }}
                                        @if($ticket->resolved_at)
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium font-sans">({{ $ticket->resolved_at->diffForHumans() }})</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Client-Side Form Requirement Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusSelect = document.getElementById('ticket-status');
            const notesTextarea = document.getElementById('resolution_notes');
            const notesRequiredIndicator = document.getElementById('notes-required-indicator');

            function toggleNotesRequired() {
                const val = statusSelect.value;
                if (val === 'resolved' || val === 'scrapped') {
                    notesTextarea.setAttribute('required', 'required');
                    notesRequiredIndicator.classList.remove('hidden');
                } else {
                    notesTextarea.removeAttribute('required');
                    notesRequiredIndicator.classList.add('hidden');
                }
            }

            statusSelect.addEventListener('change', toggleNotesRequired);
            toggleNotesRequired(); // Trigger initially on page load
        });
    </script>
</x-app-layout>
