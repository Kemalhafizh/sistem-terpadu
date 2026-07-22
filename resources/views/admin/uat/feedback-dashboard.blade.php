<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold leading-tight text-gray-800 dark:text-white">
            {{ __('UAT Feedback Dashboard') }}
        </h2>
    </x-slot>

    <x-slot name="breadcrumbs">
        <nav class="flex text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Dashboard</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 md:ml-2 text-gray-400 dark:text-gray-500">UAT</span>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 md:ml-2 text-gray-700 dark:text-gray-300">Feedback List</span>
                    </div>
                </li>
            </ol>
        </nav>
    </x-slot>

    <div x-data="{ 
        activeFeedback: null, 
        lightboxImage: null,
        openDetail(feedback) {
            this.activeFeedback = feedback;
        },
        closeDetail() {
            this.activeFeedback = null;
        }
    }" class="space-y-8">
        
        <!-- Stats Row -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total Stats -->
            <div class="bg-gradient-to-br from-blue-500/10 to-indigo-500/10 border border-blue-500/20 backdrop-blur-xl rounded-3xl p-5 shadow-lg relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-20 h-20 bg-blue-500/10 rounded-full blur-2xl"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-blue-400">Total Masukan</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $stats['total'] }}</h3>
            </div>
            
            <!-- Open Stats -->
            <div class="bg-gradient-to-br from-amber-500/10 to-yellow-500/10 border border-amber-500/20 backdrop-blur-xl rounded-3xl p-5 shadow-lg relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-20 h-20 bg-amber-500/10 rounded-full blur-2xl"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-amber-400">Status Terbuka</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $stats['open'] }}</h3>
            </div>
            
            <!-- In Progress Stats -->
            <div class="bg-gradient-to-br from-purple-500/10 to-indigo-500/10 border border-purple-500/20 backdrop-blur-xl rounded-3xl p-5 shadow-lg relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-20 h-20 bg-purple-500/10 rounded-full blur-2xl"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-purple-400">Diproses</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $stats['in_progress'] }}</h3>
            </div>

            <!-- Fixed Stats -->
            <div class="bg-gradient-to-br from-teal-500/10 to-emerald-500/10 border border-teal-500/20 backdrop-blur-xl rounded-3xl p-5 shadow-lg relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-20 h-20 bg-teal-500/10 rounded-full blur-2xl"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-teal-400">Diperbaiki</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $stats['fixed'] }}</h3>
            </div>

            <!-- Verified Stats -->
            <div class="bg-gradient-to-br from-emerald-500/10 to-green-500/10 border border-emerald-500/20 backdrop-blur-xl rounded-3xl p-5 shadow-lg col-span-2 lg:col-span-1 relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-20 h-20 bg-emerald-500/10 rounded-full blur-2xl"></div>
                <p class="text-[10px] font-black uppercase tracking-wider text-emerald-400">Terverifikasi</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $stats['verified'] }}</h3>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-gray-700/50">
            <form action="{{ route('admin.uat.feedback.dashboard') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label for="search" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Cari Deskripsi / User / URL</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                               placeholder="Cari kata kunci..."
                               class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white">
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label for="category" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Kategori</label>
                        <select id="category" name="category"
                                class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white">
                            <option value="">Semua Kategori</option>
                            <option value="bug" {{ request('category') === 'bug' ? 'selected' : '' }}>Bug / Galat </option>
                            <option value="ui_suggestion" {{ request('category') === 'ui_suggestion' ? 'selected' : '' }}>Saran Tampilan </option>
                            <option value="unclear_feature" {{ request('category') === 'unclear_feature' ? 'selected' : '' }}>Fitur Kurang Jelas </option>
                            <option value="slow" {{ request('category') === 'slow' ? 'selected' : '' }}>Lambat </option>
                            <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Lainnya </option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Status</label>
                        <select id="status" name="status"
                                class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white">
                            <option value="">Semua Status</option>
                            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="fixed" {{ request('status') === 'fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="wont_fix" {{ request('status') === 'wont_fix' ? 'selected' : '' }}>Wont Fix</option>
                        </select>
                    </div>

                    <!-- Priority Filter -->
                    <div>
                        <label for="priority" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Prioritas</label>
                        <select id="priority" name="priority"
                                class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:text-white">
                            <option value="">Semua Prioritas</option>
                            <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical </option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High </option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium </option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low </option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('admin.uat.feedback.dashboard') }}" 
                       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-900 dark:hover:bg-gray-950 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold transition-all">
                        Reset Filter
                    </a>
                    <button type="submit" 
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-all">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('admin.uat.feedback.report') }}" 
                       class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                        Laporan UAT
                    </a>
                </div>
            </form>
        </div>

        <!-- Data List -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-gray-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-[10px] font-black uppercase tracking-wider">
                            <th class="py-3 px-4">Pengirim / Peran</th>
                            <th class="py-3 px-4">Rating</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Deskripsi / URL</th>
                            <th class="py-3 px-4">Prioritas</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800/50 text-xs font-semibold text-gray-700 dark:text-gray-200">
                        @forelse($feedbacks as $feedback)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-all">
                                <td class="py-4 px-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $feedback->user->name }}</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mt-0.5">{{ $feedback->user->role }}</div>
                                </td>
                                <td class="py-4 px-4 text-amber-500 font-black text-sm">
                                    {{ str_repeat('', $feedback->rating) }}{{ str_repeat('', 5 - $feedback->rating) }}
                                </td>
                                <td class="py-4 px-4">
                                    @php
                                        $catLabels = [
                                            'bug' => ['label' => 'Bug ', 'class' => 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20'],
                                            'ui_suggestion' => ['label' => 'UI ', 'class' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20'],
                                            'unclear_feature' => ['label' => 'Fitur ', 'class' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'],
                                            'slow' => ['label' => 'Lambat ', 'class' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20'],
                                            'other' => ['label' => 'Lainnya ', 'class' => 'bg-gray-500/10 text-gray-600 dark:text-gray-400 border border-gray-500/20'],
                                        ];
                                        $cat = $catLabels[$feedback->category] ?? ['label' => $feedback->category, 'class' => 'bg-gray-100 text-gray-600'];
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $cat['class'] }}">
                                        {{ $cat['label'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 max-w-xs">
                                    <div class="line-clamp-2 text-gray-900 dark:text-gray-100 font-semibold">{{ $feedback->description }}</div>
                                    <div class="text-[10px] text-emerald-500 hover:text-emerald-400 transition-colors font-bold truncate mt-1">
                                        <a href="{{ $feedback->page_url }}" target="_blank" title="{{ $feedback->page_url }}">
                                            {{ Str::replace(url('/'), '', $feedback->page_url) }}
                                        </a>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    @php
                                        $prioColors = [
                                            'critical' => 'bg-red-500/15 text-red-600 dark:text-red-400 font-black',
                                            'high' => 'bg-orange-500/15 text-orange-600 dark:text-orange-400 font-black',
                                            'medium' => 'bg-amber-500/15 text-amber-600 dark:text-amber-400 font-semibold',
                                            'low' => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 font-semibold',
                                        ];
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase tracking-wider {{ $prioColors[$feedback->priority] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $feedback->priority }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    @php
                                        $statusStyles = [
                                            'open' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-800/30',
                                            'in_progress' => 'bg-purple-100 text-purple-800 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-800/30',
                                            'fixed' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-800/30',
                                            'verified' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-800/30',
                                            'wont_fix' => 'bg-gray-100 text-gray-800 dark:bg-gray-800/40 dark:text-gray-400 border border-gray-700/30',
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $statusStyles[$feedback->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ str_replace('_', ' ', strtoupper($feedback->status)) }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-gray-500 dark:text-gray-400 text-[11px]">
                                    {{ $feedback->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <!-- Detail Action -->
                                    <button @click="openDetail({{ json_encode($feedback) }})" 
                                            class="px-3.5 py-1.5 bg-emerald-600/10 hover:bg-emerald-600 text-emerald-600 hover:text-white dark:text-emerald-400 rounded-xl text-xs font-bold transition-all border border-emerald-500/20 hover:scale-105">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500 dark:text-gray-400 font-semibold">
                                    Tidak ada feedback UAT ditemukan yang cocok dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 border-t border-gray-100 dark:divide-gray-800/50 pt-4">
                {{ $feedbacks->links() }}
            </div>
        </div>

        <!-- Detail Modal Panel -->
        <div x-show="activeFeedback" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4 overflow-y-auto"
             style="display: none;">
            
            <div @click.away="closeDetail()" 
                 class="bg-gray-900 border border-gray-800 rounded-3xl max-w-4xl w-full shadow-2xl overflow-hidden text-gray-200 max-h-[90vh] flex flex-col">
                
                <!-- Modal Header -->
                <div class="p-5 bg-gradient-to-r from-emerald-950/80 to-teal-950/80 border-b border-gray-800 flex justify-between items-center shrink-0">
                    <h3 class="text-sm font-black tracking-wide text-white uppercase">Detail Feedback UAT #<span x-text="activeFeedback ? activeFeedback.id : ''"></span></h3>
                    <button @click="closeDetail()" class="text-gray-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto flex-1 grid grid-cols-1 md:grid-cols-12 gap-6" x-ref="modalBody">
                    <div class="md:col-span-7 space-y-4">
                        <!-- Description -->
                        <div class="space-y-1.5">
                            <h4 class="text-[10px] font-black uppercase tracking-wider text-gray-400">Deskripsi / Masukan Pengguna</h4>
                            <p class="text-xs bg-white/5 border border-white/5 p-4 rounded-2xl text-gray-200 leading-relaxed font-semibold" x-text="activeFeedback ? activeFeedback.description : ''"></p>
                        </div>

                        <!-- Screenshot -->
                        <div class="space-y-1.5">
                            <h4 class="text-[10px] font-black uppercase tracking-wider text-gray-400">Tangkapan Layar</h4>
                            <div class="bg-gray-950 p-2 rounded-2xl border border-white/5">
                                <template x-if="activeFeedback && activeFeedback.screenshot_path">
                                    <div class="relative group cursor-pointer overflow-hidden rounded-xl" @click="lightboxImage = '/storage/' + activeFeedback.screenshot_path">
                                        <img :src="'/storage/' + activeFeedback.screenshot_path" class="w-full h-auto max-h-60 object-contain hover:scale-[1.02] transition-all">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all">
                                            <span class="px-3 py-1.5 bg-emerald-600 text-white font-bold rounded-lg text-[10px]">Klik untuk Memperbesar</span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="activeFeedback && !activeFeedback.screenshot_path">
                                    <div class="text-center py-8 text-gray-500 font-semibold text-xs">
                                        Tidak ada tangkapan layar dilampirkan.
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Developer Action Form -->
                        <div class="border-t border-gray-800 pt-4">
                            <h4 class="text-[10px] font-black uppercase tracking-wider text-emerald-400 mb-3">Tindak Lanjut Developer</h4>
                            <form :action="activeFeedback ? '/admin/uat/feedback/' + activeFeedback.id + '/status' : ''" method="POST" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="modal-status" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status Penanganan</label>
                                        <select id="modal-status" name="status" :value="activeFeedback ? activeFeedback.status : 'open'"
                                                class="w-full px-3 py-2 bg-gray-950 border border-gray-800 rounded-xl text-xs font-semibold text-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                            <option value="open">Open</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="fixed">Fixed</option>
                                            <option value="verified">Verified</option>
                                            <option value="wont_fix">Wont Fix</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="modal-priority" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Prioritas Dampak</label>
                                        <select id="modal-priority" name="priority" :value="activeFeedback ? activeFeedback.priority : 'medium'"
                                                class="w-full px-3 py-2 bg-gray-950 border border-gray-800 rounded-xl text-xs font-semibold text-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                            <option value="critical">Critical</option>
                                            <option value="high">High</option>
                                            <option value="medium">Medium</option>
                                            <option value="low">Low</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label for="modal-notes" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Catatan Pengembang (Developer Notes)</label>
                                    <textarea id="modal-notes" name="developer_notes" rows="3" :value="activeFeedback ? activeFeedback.developer_notes : ''"
                                              placeholder="Tuliskan catatan teknis perbaikan, kendala, atau referensi git commit..."
                                              class="w-full px-3 py-2 bg-gray-950 border border-gray-800 rounded-xl text-xs font-semibold text-white placeholder-gray-600 focus:ring-1 focus:ring-emerald-500 focus:outline-none resize-none"></textarea>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-600/20 hover:scale-[1.02]">
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Meta Data Sidebar -->
                    <div class="md:col-span-5 space-y-4 bg-gray-950/50 p-4 border border-gray-800 rounded-2xl">
                        <h4 class="text-[10px] font-black uppercase tracking-wider text-gray-400 border-b border-gray-800 pb-2">Informasi Metadata</h4>
                        
                        <div class="space-y-3 text-xs">
                            <div>
                                <span class="text-gray-500 font-bold block">Pengirim:</span>
                                <span class="font-bold text-white" x-text="activeFeedback ? activeFeedback.user.name : ''"></span>
                                <span class="px-2 py-0.5 bg-gray-800 text-gray-400 rounded text-[9px] uppercase tracking-wider ml-1" x-text="activeFeedback ? activeFeedback.user.role : ''"></span>
                            </div>
                            
                            <div>
                                <span class="text-gray-500 font-bold block">Email Pengirim:</span>
                                <span class="text-white break-all" x-text="activeFeedback ? activeFeedback.user.email : ''"></span>
                            </div>

                            <div>
                                <span class="text-gray-500 font-bold block">Halaman Terkait:</span>
                                <a :href="activeFeedback ? activeFeedback.page_url : '#'" target="_blank"
                                   class="text-emerald-400 hover:text-emerald-300 break-all font-semibold inline-block hover:underline"
                                   x-text="activeFeedback ? activeFeedback.page_url : ''"></a>
                            </div>

                            <div>
                                <span class="text-gray-500 font-bold block">Browser Pengguna:</span>
                                <span class="text-white" x-text="activeFeedback && activeFeedback.device_info ? (activeFeedback.device_info.browser || 'Unknown') : 'Unknown'"></span>
                            </div>

                            <div>
                                <span class="text-gray-500 font-bold block">Sistem Operasi (OS):</span>
                                <span class="text-white" x-text="activeFeedback && activeFeedback.device_info ? (activeFeedback.device_info.os || 'Unknown') : 'Unknown'"></span>
                            </div>

                            <div>
                                <span class="text-gray-500 font-bold block">Ukuran Resolusi Viewport:</span>
                                <span class="text-white font-mono" x-text="activeFeedback && activeFeedback.device_info ? (activeFeedback.device_info.screen_size || 'Unknown') : 'Unknown'"></span>
                            </div>

                            <div>
                                <span class="text-gray-500 font-bold block">Dikirim Pada:</span>
                                <span class="text-white" x-text="activeFeedback ? new Date(activeFeedback.created_at).toLocaleString('id-ID', {day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit'}) : ''"></span>
                            </div>

                            <div class="border-t border-gray-800 pt-2">
                                <span class="text-gray-500 font-bold block">User Agent Lengkap:</span>
                                <p class="text-[9px] font-mono text-gray-400 leading-normal select-all bg-gray-950 p-2 rounded-lg border border-gray-850 break-all mt-1" x-text="activeFeedback && activeFeedback.device_info ? (activeFeedback.device_info.user_agent || '-') : '-'"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lightbox Modal for Screenshot Enlarge -->
        <div x-show="lightboxImage" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[200] flex items-center justify-center bg-black/90 p-4"
             style="display: none;"
             @keydown.window.escape="lightboxImage = null">
            <button @click="lightboxImage = null" class="absolute top-6 right-6 text-white hover:text-gray-300 text-3xl focus:outline-none">
                &times;
            </button>
            <img :src="lightboxImage" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl border border-white/10">
        </div>

    </div>
</x-app-layout>
