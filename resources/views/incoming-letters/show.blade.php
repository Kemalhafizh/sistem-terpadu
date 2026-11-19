<x-app-layout>
    <x-slot name="title">Detail Surat Masuk {{ $incomingLetter->agenda_number }}</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    Agenda {{ $incomingLetter->agenda_number }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ $incomingLetter->sender }} &mdash; {{ $incomingLetter->subject }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('incoming-letters.index') }}" class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white text-xs font-bold">
                    Kembali
                </a>
                @if($incomingLetter->attachment_path)
                    <a href="{{ route('incoming-letters.attachment', $incomingLetter) }}" target="_blank" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">
                        Lihat Scan Surat
                    </a>
                @endif
                @if($canArchive && $incomingLetter->status !== \App\Models\IncomingLetter::STATUS_COMPLETED)
                    <form action="{{ route('incoming-letters.archive', $incomingLetter) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Tandai surat masuk ini selesai dan arsipkan?')" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                            Selesai &amp; Arsipkan
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-200/60 dark:border-gray-700/50 space-y-4 shadow-sm">
                <h3 class="text-sm font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700 pb-2">
                    Identitas Surat
                </h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-[11px] font-bold uppercase text-gray-400">Status</dt>
                        <dd class="font-bold text-indigo-700 dark:text-indigo-400 mt-0.5">{{ $incomingLetter->statusLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase text-gray-400">Nomor Surat Asal</dt>
                        <dd class="font-semibold text-gray-900 dark:text-white">{{ $incomingLetter->reference_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase text-gray-400">Pengirim</dt>
                        <dd class="font-semibold text-gray-900 dark:text-white">{{ $incomingLetter->sender }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase text-gray-400">Tanggal Surat / Tanggal Diterima</dt>
                        <dd class="text-gray-700 dark:text-gray-300">
                            {{ $incomingLetter->letter_date?->translatedFormat('d M Y') }} / {{ $incomingLetter->received_date?->translatedFormat('d M Y') }}
                        </dd>
                    </div>
                    @if($incomingLetter->category)
                        <div>
                            <dt class="text-[11px] font-bold uppercase text-gray-400">Kategori</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ $incomingLetter->category }}</dd>
                        </div>
                    @endif
                    @if($incomingLetter->summary)
                        <div>
                            <dt class="text-[11px] font-bold uppercase text-gray-400">Ringkasan</dt>
                            <dd class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $incomingLetter->summary }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-[11px] font-bold uppercase text-gray-400">Dicatat Oleh</dt>
                        <dd class="text-gray-700 dark:text-gray-300">{{ $incomingLetter->creator?->name ?? '-' }}</dd>
                    </div>
                    @if($incomingLetter->archiver)
                        <div>
                            <dt class="text-[11px] font-bold uppercase text-gray-400">Diarsipkan Oleh</dt>
                            <dd class="text-emerald-700 dark:text-emerald-400 font-semibold">
                                {{ $incomingLetter->archiver->name }} ({{ $incomingLetter->archived_at?->translatedFormat('d M Y H:i') }} WIB)
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="lg:col-span-2 space-y-6">
                @if($canDispose && $incomingLetter->status !== \App\Models\IncomingLetter::STATUS_COMPLETED)
                    <div class="bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-200/60 dark:border-gray-700/50 shadow-sm space-y-4">
                        <h3 class="text-sm font-black uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            Kirim Disposisi Pimpinan
                        </h3>
                        <form action="{{ route('incoming-letters.dispose', $incomingLetter) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1">Diteruskan Kepada *</label>
                                    <select name="to_user_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                        <option value="">Pilih Pejabat / Staf...</option>
                                        @foreach($recipients as $recipient)
                                            <option value="{{ $recipient->id }}">{{ $recipient->name }} ({{ str_replace('_', ' ', $recipient->role) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1">Prioritas *</label>
                                    <select name="priority" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                        <option value="normal">Normal</option>
                                        <option value="penting">Penting</option>
                                        <option value="segera">Segera</option>
                                        <option value="rendah">Rendah</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1">Tenggat Tindak Lanjut</label>
                                    <input type="date" name="due_at" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1">Instruksi Disposisi *</label>
                                <textarea name="instruction" rows="2" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" placeholder="Mohon ditindaklanjuti dan koordinasikan perwakilan sekolah..."></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">
                                    Kirim Disposisi
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <div class="bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-200/60 dark:border-gray-700/50 shadow-sm space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-wider text-gray-600 dark:text-gray-300">
                        Riwayat Disposisi &amp; Tindak Lanjut
                    </h3>

                    @forelse($incomingLetter->dispositions as $disposition)
                        <div class="p-4 rounded-2xl border border-gray-200/70 dark:border-gray-700 space-y-3 bg-gray-50/60 dark:bg-gray-900/40">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $disposition->toUser?->name ?? '-' }}</span>
                                    <span class="text-xs text-gray-500">dari {{ $disposition->fromUser?->name ?? '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-[11px] font-bold">
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 uppercase">{{ $disposition->priority }}</span>
                                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 uppercase">{{ $disposition->status }}</span>
                                    @if($disposition->due_at)
                                        <span class="text-gray-500">Tenggat: {{ $disposition->due_at->translatedFormat('d M Y') }}</span>
                                    @endif
                                </div>
                            </div>

                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $disposition->instruction }}</p>

                            @if($disposition->follow_up_notes)
                                <div class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-300">
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400 block mb-0.5">Laporan Tindak Lanjut:</span>
                                    {{ $disposition->follow_up_notes }}
                                </div>
                            @endif

                            @if((int) $disposition->to_user_id === (int) auth()->id() && $disposition->status !== \App\Models\LetterDisposition::STATUS_DONE)
                                <form action="{{ route('letter-dispositions.follow-up', $disposition) }}" method="POST" class="space-y-2 pt-2 border-t border-gray-200/60 dark:border-gray-700">
                                    @csrf
                                    <textarea name="follow_up_notes" rows="2" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-xs" placeholder="Tuliskan catatan tindak lanjut...">{{ $disposition->follow_up_notes }}</textarea>
                                    <div class="flex flex-wrap gap-2 justify-end">
                                        <button type="submit" name="status" value="diproses" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold">
                                            Tandai Diproses
                                        </button>
                                        <button type="submit" name="status" value="selesai" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                                            Selesai Ditindaklanjuti
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Belum ada disposisi untuk surat masuk ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
