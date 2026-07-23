<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">
                     Bank Silabus
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Tahun Ajaran {{ $activeYear->year ?? '-' }} — Kelola dokumen silabus mata pelajaran
                </p>
            </div>
            <a href="{{ route('curriculum.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="syllabiManager()">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-2"
                     class="rounded-xl border border-emerald-200 dark:border-emerald-700/50 bg-emerald-50/80 dark:bg-emerald-900/30 backdrop-blur-sm p-4">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-emerald-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
                        <button @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-600 transition-colors"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-2"
                     class="rounded-xl border border-red-200 dark:border-red-700/50 bg-red-50/80 dark:bg-red-900/30 backdrop-blur-sm p-4">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-red-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
                        <button @click="show = false" class="ml-auto text-red-400 hover:text-red-600 transition-colors"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></button>
                    </div>
                </div>
            @endif

            {{-- Header Actions --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-teal-400 to-emerald-500 text-white shadow-lg shadow-emerald-500/25">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.331 0 4.476.884 6.084 2.34M12 6.042A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18c-2.331 0-4.476.884-6.084 2.34" /></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Daftar Silabus</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $syllabi->count() }} dokumen terdaftar</p>
                    </div>
                </div>
                <button @click="showUploadModal = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/25 hover:shadow-lg hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all duration-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Upload Silabus
                </button>
            </div>

            {{-- Data Table --}}
            <div class="rounded-2xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50/80 dark:bg-gray-900/40 border-b border-gray-200/60 dark:border-gray-700/60">
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Mata Pelajaran</th>
                                <th class="px-6 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kelas</th>
                                <th class="px-6 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Semester</th>
                                <th class="px-6 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pengunggah</th>
                                <th class="px-6 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($syllabi as $i => $syllabus)
                                <tr class="hover:bg-emerald-50/40 dark:hover:bg-emerald-900/10 transition-colors duration-200">
                                    <td class="px-6 py-4 text-gray-400 dark:text-gray-500 font-mono text-xs">{{ $i + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                            </div>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $syllabus->subject->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            {{ $syllabus->grade_level ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ $syllabus->semester ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($syllabus->status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-700/50">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                                Disetujui
                                            </span>
                                        @elseif($syllabus->status === 'draft')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300 ring-1 ring-yellow-200 dark:ring-yellow-700/50">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" /><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25h5.5a.75.75 0 010 1.5h-5.5v11h11v-5.5a.75.75 0 011.5 0v5.5c0 .69-.56 1.25-1.25 1.25h-11c-.69 0-1.25-.56-1.25-1.25v-11z" /></svg>
                                                Draft
                                            </span>
                                        @elseif($syllabus->status === 'revision_needed')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-700/50">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
                                                Perlu Revisi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                                {{ ucfirst($syllabus->status ?? '-') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-[10px] font-bold text-white">
                                                {{ strtoupper(substr($syllabus->uploader->name ?? '', 0, 1)) }}
                                            </div>
                                            <span class="text-gray-700 dark:text-gray-300 text-sm">{{ $syllabus->uploader->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-1.5">
                                            {{-- Download --}}
                                            @if($syllabus->file_path)
                                                <a href="{{ route('curriculum.syllabi.download', $syllabus) }}"
                                                   class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors duration-200" title="Download">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                                </a>
                                            @endif

                                            {{-- Review (waka_kurikulum & admin only) --}}
                                            @if(in_array(auth()->user()->role ?? '', ['waka_kurikulum', 'admin']))
                                                <button @click="openReviewModal({{ $syllabus->id }})"
                                                        class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors duration-200" title="Review">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                </button>
                                            @endif

                                            {{-- Delete (owner only) --}}
                                            @if(auth()->id() === $syllabus->uploaded_by)
                                                <button @click="confirmDelete({{ $syllabus->id }})"
                                                        class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors duration-200" title="Hapus">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
                                                <svg class="h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.331 0 4.476.884 6.084 2.34M12 6.042A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18c-2.331 0-4.476.884-6.084 2.34" /></svg>
                                            </div>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada silabus yang diunggah.</p>
                                            <button @click="showUploadModal = true" class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">Upload sekarang →</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- UPLOAD MODAL                                                  --}}
            {{-- ============================================================ --}}
            <div x-show="showUploadModal" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showUploadModal = false"></div>

                {{-- Modal --}}
                <div class="relative w-full max-w-lg rounded-2xl border border-white/20 dark:border-gray-700/40 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl shadow-2xl"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                     @click.away="showUploadModal = false">
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200/60 dark:border-gray-700/60">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-md">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Upload Silabus Baru</h3>
                        </div>
                        <button @click="showUploadModal = false" class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Form --}}
                    <form action="{{ route('curriculum.syllabi.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                        @csrf

                        {{-- Mata Pelajaran --}}
                        <div>
                            <label for="subject_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Mata Pelajaran</label>
                            <select name="subject_id" id="subject_id" required
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:focus:border-emerald-400 dark:focus:ring-emerald-400 transition-colors">
                                <option value="">— Pilih Mata Pelajaran —</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            {{-- Kelas --}}
                            <div>
                                <label for="grade_level" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Kelas</label>
                                <select name="grade_level" id="grade_level" required
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors">
                                    <option value="">— Pilih —</option>
                                    <option value="X">X</option>
                                    <option value="XI">XI</option>
                                    <option value="XII">XII</option>
                                </select>
                            </div>

                            {{-- Semester --}}
                            <div>
                                <label for="semester" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Semester</label>
                                <select name="semester" id="semester" required
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors">
                                    <option value="">— Pilih —</option>
                                    <option value="ganjil">Ganjil</option>
                                    <option value="genap">Genap</option>
                                </select>
                            </div>
                        </div>

                        {{-- File Upload --}}
                        <div>
                            <label for="file" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Dokumen Silabus</label>
                            <div class="relative">
                                <input type="file" name="file" id="file" required accept=".pdf,.doc,.docx"
                                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-900/40 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/60 transition-colors cursor-pointer">
                            </div>
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">Format: PDF, DOC, DOCX. Maks. 10MB</p>
                        </div>

                        {{-- Submit --}}
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" @click="showUploadModal = false"
                                    class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/25 hover:shadow-lg hover:shadow-emerald-500/40 transition-all duration-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                                Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- REVIEW MODAL                                                  --}}
            {{-- ============================================================ --}}
            @if(in_array(auth()->user()->role ?? '', ['waka_kurikulum', 'admin']))
                <div x-show="showReviewModal" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    {{-- Backdrop --}}
                    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showReviewModal = false"></div>

                    {{-- Modal --}}
                    <div class="relative w-full max-w-lg rounded-2xl border border-white/20 dark:border-gray-700/40 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl shadow-2xl"
                         @click.away="showReviewModal = false">
                        {{-- Header --}}
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200/60 dark:border-gray-700/60">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-400 to-indigo-500 text-white shadow-md">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Review Silabus</h3>
                            </div>
                            <button @click="showReviewModal = false" class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        {{-- Form --}}
                        <form :action="`{{ url('curriculum/syllabi') }}/${reviewSyllabusId}/review`" method="POST" class="p-6 space-y-5">
                            @csrf
                            @method('PATCH')

                            {{-- Status --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Keputusan Review</label>
                                <div class="space-y-3">
                                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-emerald-300 dark:hover:border-emerald-600 cursor-pointer transition-colors">
                                        <input type="radio" name="status" value="approved" x-model="reviewStatus"
                                               class="h-4 w-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
                                        <div>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Setujui</span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Silabus memenuhi standar kurikulum</p>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-red-300 dark:hover:border-red-600 cursor-pointer transition-colors">
                                        <input type="radio" name="status" value="revision_needed" x-model="reviewStatus"
                                               class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500">
                                        <div>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Perlu Revisi</span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Dokumen perlu diperbaiki sebelum disetujui</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label for="review_notes" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Catatan Review</label>
                                <textarea name="notes" id="review_notes" rows="4" x-model="reviewNotes"
                                          placeholder="Tuliskan catatan review atau alasan revisi..."
                                          class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors resize-none"></textarea>
                            </div>

                            {{-- Submit --}}
                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" @click="showReviewModal = false"
                                        class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-md shadow-blue-500/25 hover:shadow-lg hover:shadow-blue-500/40 transition-all duration-300">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Simpan Review
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- DELETE CONFIRMATION MODAL                                     --}}
            {{-- ============================================================ --}}
            <div x-show="showDeleteModal" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>
                <div class="relative w-full max-w-sm rounded-2xl border border-white/20 dark:border-gray-700/40 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl shadow-2xl p-6 text-center"
                     @click.away="showDeleteModal = false">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40 mb-4">
                        <svg class="h-7 w-7 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Hapus Silabus?</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Dokumen yang dihapus tidak dapat dikembalikan. Yakin ingin melanjutkan?</p>
                    <div class="flex items-center justify-center gap-3">
                        <button @click="showDeleteModal = false"
                                class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Batal
                        </button>
                        <form :action="`{{ url('curriculum/syllabi') }}/${deleteSyllabusId}`" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-red-500 to-rose-500 text-white shadow-md shadow-red-500/25 hover:shadow-lg hover:shadow-red-500/40 transition-all duration-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function syllabiManager() {
            return {
                showUploadModal: false,
                showReviewModal: false,
                showDeleteModal: false,
                reviewSyllabusId: null,
                reviewStatus: '',
                reviewNotes: '',
                deleteSyllabusId: null,

                openReviewModal(id) {
                    this.reviewSyllabusId = id;
                    this.reviewStatus = '';
                    this.reviewNotes = '';
                    this.showReviewModal = true;
                },

                confirmDelete(id) {
                    this.deleteSyllabusId = id;
                    this.showDeleteModal = true;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
