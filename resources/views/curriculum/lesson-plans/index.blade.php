<x-app-layout>
    <x-slot name="title">Rencana Pelaksanaan Pembelajaran (RPP)</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight flex items-center gap-2">
                    <span></span> Rencana Pelaksanaan Pembelajaran
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kelola dokumen RPP, ajukan review, dan pantau status persetujuan kurikulum.</p>
            </div>
            <a href="{{ route('curriculum.lesson-plans.create') }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2.5 px-5 text-sm font-black uppercase tracking-wider shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/35 transition-all duration-300 transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Buat RPP Baru
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 rounded-2xl border border-emerald-200 dark:border-emerald-900/50 text-sm font-semibold flex items-center gap-2" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-400 rounded-2xl border border-red-200 dark:border-red-900/50 text-sm font-semibold flex items-center gap-2" x-data="{ show: true }" x-show="show" x-transition>
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('error') }}
        </div>
        @endif

        {{-- KPI Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-xl text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total RPP</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $lessonPlans->total() }}</h3>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-amber-100 dark:bg-amber-900/50 rounded-xl text-amber-600 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Draft</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $lessonPlans->where('status', 'draft')->count() }}</h3>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-sky-100 dark:bg-sky-900/50 rounded-xl text-sky-600 dark:text-sky-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Diajukan</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $lessonPlans->where('status', 'submitted')->count() }}</h3>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Disetujui</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $lessonPlans->where('status', 'approved')->count() }}</h3>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
            <div class="flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/50 pb-4">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <h4 class="text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Filter & Pencarian</h4>
            </div>

            <form action="{{ route('curriculum.lesson-plans.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Mata Pelajaran</label>
                    <select name="subject_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        <option value="">Semua Mapel</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Status RPP</label>
                    <select name="status" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Diajukan (Submitted)</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                        <option value="revision_needed" {{ request('status') === 'revision_needed' ? 'selected' : '' }}>Perlu Revisi</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="w-full bg-gray-800 dark:bg-gray-200 hover:bg-gray-900 dark:hover:bg-white text-white dark:text-gray-950 font-bold py-2.5 px-4 rounded-xl text-sm transition-all text-center">
                        Cari / Filter
                    </button>
                    <a href="{{ route('curriculum.lesson-plans.index') }}" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 p-2.5 rounded-xl text-sm font-bold text-center hover:bg-gray-300 dark:hover:bg-gray-600 transition-all">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Data Table --}}
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Minggu</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Topik</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Mata Pelajaran</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Kelas</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/40 dark:divide-gray-700/40">
                        @forelse($lessonPlans as $plan)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-colors">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 font-extrabold text-sm border border-emerald-200/50 dark:border-emerald-900/50">
                                    {{ $plan->week_number }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800 dark:text-white max-w-[220px]">{{ Str::limit($plan->topic, 50) }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $plan->user->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $plan->subject->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $plan->classroom->name }}</td>
                            <td class="px-6 py-4">
                                @if($plan->status === 'draft')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50">DRAFT</span>
                                @elseif($plan->status === 'submitted')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-blue-100 text-blue-800 dark:bg-blue-950/30 dark:text-blue-400 border border-blue-200 dark:border-blue-900/50">DIAJUKAN</span>
                                @elseif($plan->status === 'approved')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50">DISETUJUI</span>
                                @elseif($plan->status === 'revision_needed')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-red-100 text-red-800 dark:bg-red-950/30 dark:text-red-400 border border-red-200 dark:border-red-900/50">REVISI</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-sm">
                                <div class="flex items-center justify-center flex-wrap gap-1.5">
                                    {{-- View --}}
                                    <a href="{{ route('curriculum.lesson-plans.show', $plan->uuid) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all" title="Lihat Detail">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Lihat
                                    </a>

                                    {{-- Edit --}}
                                    @if(in_array($plan->status, ['draft', 'revision_needed']) && auth()->id() === $plan->user_id)
                                    <a href="{{ route('curriculum.lesson-plans.edit', $plan->uuid) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold bg-yellow-50 dark:bg-yellow-950/20 text-yellow-700 dark:text-yellow-400 hover:bg-yellow-100 dark:hover:bg-yellow-950/40 transition-all border border-yellow-200/50 dark:border-yellow-900/50" title="Edit RPP">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Edit
                                    </a>
                                    @endif

                                    {{-- Submit for Review --}}
                                    @if(in_array($plan->status, ['draft', 'revision_needed']) && auth()->id() === $plan->user_id)
                                    <form action="{{ route('curriculum.lesson-plans.submit', $plan->uuid) }}" method="POST" class="inline-block" onsubmit="return confirm('Ajukan RPP ini untuk direview?')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-950/40 transition-all border border-blue-200/50 dark:border-blue-900/50" title="Ajukan Review">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            Ajukan
                                        </button>
                                    </form>
                                    @endif

                                    {{-- PDF --}}
                                    <a href="{{ route('curriculum.lesson-plans.pdf', $plan->uuid) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-950/40 transition-all border border-red-200/50 dark:border-red-900/50" title="Unduh PDF" target="_blank">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        PDF
                                    </a>

                                    {{-- Delete --}}
                                    @if($plan->status === 'draft' && auth()->id() === $plan->user_id)
                                    <form action="{{ route('curriculum.lesson-plans.destroy', $plan->uuid) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus RPP draft ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold bg-red-100 dark:bg-red-950/30 text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-950/50 transition-all border border-red-200/50 dark:border-red-900/50" title="Hapus RPP">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="text-gray-400 dark:text-gray-500 font-medium italic">Belum ada data RPP yang tersedia.</p>
                                    <a href="{{ route('curriculum.lesson-plans.create') }}" class="mt-3 text-emerald-600 dark:text-emerald-400 font-bold text-sm hover:underline">+ Buat RPP Pertama</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($lessonPlans->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/50">
                {{ $lessonPlans->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
