<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">
                     Dashboard Kurikulum
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Tahun Ajaran {{ $activeYear->year ?? '-' }} — Monitoring Kurikulum & Silabus Digital
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-700/50">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                Aktif
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="relative rounded-xl border border-emerald-200 dark:border-emerald-700/50 bg-emerald-50/80 dark:bg-emerald-900/30 backdrop-blur-sm p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                        </div>
                        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
                        <button @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-600 dark:hover:text-emerald-300 transition-colors">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="relative rounded-xl border border-red-200 dark:border-red-700/50 bg-red-50/80 dark:bg-red-900/30 backdrop-blur-sm p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                        </div>
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
                        <button @click="show = false" class="ml-auto text-red-400 hover:text-red-600 dark:hover:text-red-300 transition-colors">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- ======================================================== --}}
            {{-- KPI STAT CARDS                                            --}}
            {{-- ======================================================== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                {{-- Card 1 : Total RPP --}}
                <div class="group relative overflow-hidden rounded-2xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg hover:shadow-emerald-200/40 dark:hover:shadow-emerald-900/30 transition-all duration-500 hover:-translate-y-1">
                    <div class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-emerald-400/10 dark:bg-emerald-500/10 blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total RPP</p>
                                <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($rppStats['total'] ?? 0) }}</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform duration-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd" /></svg>
                            <span>Rencana Pelaksanaan Pembelajaran</span>
                        </div>
                    </div>
                </div>

                {{-- Card 2 : RPP Disetujui --}}
                <div class="group relative overflow-hidden rounded-2xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg hover:shadow-green-200/40 dark:hover:shadow-green-900/30 transition-all duration-500 hover:-translate-y-1">
                    <div class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-green-400/10 dark:bg-green-500/10 blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">RPP Disetujui</p>
                                <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($rppStats['approved'] ?? 0) }}</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-green-400 to-green-600 text-white shadow-lg shadow-green-500/30 group-hover:scale-110 transition-transform duration-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs text-green-600 dark:text-green-400">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                            <span>Telah divalidasi Waka Kurikulum</span>
                        </div>
                    </div>
                </div>

                {{-- Card 3 : Total Silabus --}}
                <div class="group relative overflow-hidden rounded-2xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg hover:shadow-teal-200/40 dark:hover:shadow-teal-900/30 transition-all duration-500 hover:-translate-y-1">
                    <div class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-teal-400/10 dark:bg-teal-500/10 blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Silabus</p>
                                <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white">{{ number_format($syllabiStats['total'] ?? 0) }}</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-teal-400 to-teal-600 text-white shadow-lg shadow-teal-500/30 group-hover:scale-110 transition-transform duration-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.331 0 4.476.884 6.084 2.34M12 6.042A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18c-2.331 0-4.476.884-6.084 2.34" /></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs text-teal-600 dark:text-teal-400">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.75 16.82A7.462 7.462 0 0115 15.5c.71 0 1.396.098 2.046.282A.75.75 0 0018 15.06V3.06a.75.75 0 00-.546-.721A9.006 9.006 0 0015 2a8.963 8.963 0 00-4.25 1.065V16.82zM9.25 4.065A8.963 8.963 0 005 3a9.006 9.006 0 00-2.454.339A.75.75 0 002 4.06v12a.75.75 0 00.954.721A7.462 7.462 0 015 16.5c1.579 0 3.042.487 4.25 1.32V4.065z" /></svg>
                            <span>Dokumen Silabus Terdaftar</span>
                        </div>
                    </div>
                </div>

                {{-- Card 4 : Persentase Kelengkapan --}}
                @php
                    $completionRate = ($rppStats['total'] ?? 0) > 0
                        ? round((($rppStats['approved'] ?? 0) / $rppStats['total']) * 100, 1)
                        : 0;
                @endphp
                <div class="group relative overflow-hidden rounded-2xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg hover:shadow-amber-200/40 dark:hover:shadow-amber-900/30 transition-all duration-500 hover:-translate-y-1">
                    <div class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-amber-400/10 dark:bg-amber-500/10 blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kelengkapan</p>
                                <p class="mt-2 text-3xl font-extrabold text-gray-900 dark:text-white">{{ $completionRate }}%</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform duration-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="h-1.5 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-emerald-500 transition-all duration-1000 ease-out" style="width: {{ min($completionRate, 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- QUICK ACCESS BUTTONS                                      --}}
            {{-- ======================================================== --}}
            <div class="rounded-2xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Aksi Cepat</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('curriculum.syllabi.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-teal-500 to-emerald-500 text-white shadow-md shadow-emerald-500/25 hover:shadow-lg hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all duration-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.331 0 4.476.884 6.084 2.34M12 6.042A8.967 8.967 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18c-2.331 0-4.476.884-6.084 2.34" /></svg>
                        Bank Silabus
                    </a>
                    <a href="{{ route('curriculum.lesson-plans.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-emerald-500 to-green-500 text-white shadow-md shadow-green-500/25 hover:shadow-lg hover:shadow-green-500/40 hover:-translate-y-0.5 transition-all duration-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                        Daftar RPP
                    </a>
                    <a href="{{ route('curriculum.lesson-plans.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-white dark:bg-gray-700 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700/50 shadow-sm hover:shadow-md hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:-translate-y-0.5 transition-all duration-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Buat RPP Baru
                    </a>
                    @if(in_array(auth()->user()->role ?? '', ['waka_kurikulum', 'admin', 'superadmin', 'kepala_sekolah']))
                        <a href="{{ route('curriculum.report') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-white dark:bg-gray-700 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700/50 shadow-sm hover:shadow-md hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:-translate-y-0.5 transition-all duration-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Unduh Laporan
                        </a>
                    @endif
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- RPP STATUS BREAKDOWN                                      --}}
            {{-- ======================================================== --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $statusCards = [
                        ['label' => 'Draft', 'value' => $rppStats['draft'] ?? 0, 'color' => 'slate', 'icon' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10'],
                        ['label' => 'Diajukan', 'value' => $rppStats['submitted'] ?? 0, 'color' => 'blue', 'icon' => 'M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5'],
                        ['label' => 'Disetujui', 'value' => $rppStats['approved'] ?? 0, 'color' => 'emerald', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => 'Revisi', 'value' => $rppStats['revision'] ?? 0, 'color' => 'red', 'icon' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M21.015 4.356v4.992'],
                    ];
                @endphp
                @foreach($statusCards as $card)
                    <div class="rounded-2xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-md p-5 text-center hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full
                            @if($card['color'] === 'slate') bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400
                            @elseif($card['color'] === 'blue') bg-blue-100 dark:bg-blue-900/40 text-blue-500 dark:text-blue-400
                            @elseif($card['color'] === 'emerald') bg-emerald-100 dark:bg-emerald-900/40 text-emerald-500 dark:text-emerald-400
                            @elseif($card['color'] === 'red') bg-red-100 dark:bg-red-900/40 text-red-500 dark:text-red-400
                            @endif">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" /></svg>
                        </div>
                        <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ $card['value'] }}</p>
                        <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- ======================================================== --}}
            {{-- TEACHER PROGRESS MONITORING TABLE                         --}}
            {{-- ======================================================== --}}
            @if(!in_array(auth()->user()->role ?? '', ['guru']))
                <div class="rounded-2xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200/60 dark:border-gray-700/60">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Monitoring Progress Guru</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelengkapan RPP per guru pada tahun ajaran aktif</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50/80 dark:bg-gray-900/40">
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Guru</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total RPP</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Disetujui</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 min-w-[200px]">Progress</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Rate</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                @forelse($teachersProgress as $teacher)
                                    @php
                                        $rate = $teacher['rate'] ?? 0;
                                        $barColor = $rate >= 80 ? 'from-emerald-400 to-emerald-600' : ($rate >= 50 ? 'from-amber-400 to-amber-500' : 'from-red-400 to-red-500');
                                        $textColor = $rate >= 80 ? 'text-emerald-600 dark:text-emerald-400' : ($rate >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400');
                                    @endphp
                                    <tr class="hover:bg-emerald-50/40 dark:hover:bg-emerald-900/10 transition-colors duration-200">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-xs font-bold text-white shadow-sm">
                                                    {{ strtoupper(substr($teacher['name'] ?? '', 0, 1)) }}
                                                </div>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $teacher['name'] ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-semibold text-gray-700 dark:text-gray-300">{{ $teacher['total'] ?? 0 }}</td>
                                        <td class="px-6 py-4 text-center font-semibold text-gray-700 dark:text-gray-300">{{ $teacher['approved'] ?? 0 }}</td>
                                        <td class="px-6 py-4">
                                            <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                                <div class="h-full rounded-full bg-gradient-to-r {{ $barColor }} transition-all duration-700 ease-out" style="width: {{ min($rate, 100) }}%"></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $textColor }}">{{ $rate }}%</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data progress guru.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
