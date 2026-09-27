<x-app-layout>
    <x-slot name="title">Analitik Survei: {{ $survey->title }}</x-slot>

    <x-slot name="breadcrumbs">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Survei Digital', 'url' => route('surveys.index')],
            ['label' => 'Analitik Hasil', 'url' => '#']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex items-center space-x-4">
                <a href="{{ route('surveys.index') }}" 
                   class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <span class="px-3 py-1 text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200/30 rounded-full inline-block mb-1">
                        {{ $survey->is_anonymous ? 'Survei Anonim (PDP Protected)' : 'Survei Terbuka' }}
                    </span>
                    <h1 class="text-3xl font-black tracking-tight text-slate-800 dark:text-white leading-tight">
                        Analitik Hasil & Umpan Balik
                    </h1>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <a href="{{ route('surveys.export.pdf', $survey->uuid) }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-rose-500/10 border border-rose-400/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Unduh PDF
                </a>
                <a href="{{ route('surveys.export.excel', $survey->uuid) }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-600/10 border border-emerald-500/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Ekspor Excel
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Top KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- 1. Total Submissions -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl relative overflow-hidden flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Responden</p>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $survey->responses()->count() }}</h3>
                @if($survey->max_responses)
                    <p class="text-[10px] text-slate-400 font-bold">Dari kuota {{ $survey->max_responses }}</p>
                @else
                    <p class="text-[10px] text-slate-400 font-bold">Tanpa Batas Kuota</p>
                @endif
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

        <!-- 2. Participation Rate -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl relative overflow-hidden flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tingkat Partisipasi</p>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $responseRate }}%</h3>
                <p class="text-[10px] text-emerald-500 font-bold">Terhadap Target Sekolah</p>
            </div>
            <div class="p-3 bg-teal-50 dark:bg-teal-950/20 text-teal-600 dark:text-teal-400 rounded-2xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
        </div>

        <!-- 3. Target Audience -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl relative overflow-hidden flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Target Responden</p>
                <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase">
                    @if($survey->target_role)
                        {{ $survey->target_role === 'guru' ? 'Guru' : ($survey->target_role === 'student' ? 'Siswa' : ($survey->target_role === 'parent' ? 'Orang Tua' : $survey->target_role)) }}
                    @else
                        Semua Stakeholder
                    @endif
                </h3>
                @if($survey->classroom)
                    <p class="text-[10px] text-slate-400 font-bold">Kelas: {{ $survey->classroom->name }}</p>
                @else
                    <p class="text-[10px] text-slate-400 font-bold">Skala Seluruh Sekolah</p>
                @endif
            </div>
            <div class="p-3 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-2xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
        </div>

        <!-- 4. Survey Status -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl relative overflow-hidden flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status Pengisian</p>
                <h3 class="text-2xl font-black uppercase text-emerald-500 flex items-center">
                    <span class="inline-block w-2.5 h-2.5 rounded-full mr-2 {{ $survey->status === 'active' ? 'bg-emerald-500 animate-pulse' : ($survey->status === 'closed' ? 'bg-slate-500' : 'bg-amber-500') }}"></span>
                    {{ $survey->status === 'active' ? 'Aktif/Buka' : ($survey->status === 'closed' ? 'Tutup' : 'Draf') }}
                </h3>
                @if($survey->end_date)
                    <p class="text-[10px] text-slate-400 font-bold">Batas: {{ $survey->end_date->format('d M Y') }}</p>
                @else
                    <p class="text-[10px] text-slate-400 font-bold">Tanpa Batas Waktu</p>
                @endif
            </div>
            <div class="p-3 bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 rounded-2xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Main Content Tab layout: Graphs and Tables -->
    <div class="space-y-8" x-data="{ viewMode: 'charts' }">
        <!-- Tabs Buttons -->
        <div class="flex border-b border-slate-200 dark:border-slate-800 space-x-6">
            <button @click="viewMode = 'charts'" 
                    :class="viewMode === 'charts' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="pb-4 px-1 border-b-2 text-sm font-medium transition-all focus:outline-none">
                Visualisasi Grafik & Ringkasan
            </button>
            <button @click="viewMode = 'responses'" 
                    :class="viewMode === 'responses' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="pb-4 px-1 border-b-2 text-sm font-medium transition-all focus:outline-none">
                Daftar Respons Mentah ({{ $survey->responses()->count() }})
            </button>
        </div>

        <!-- 1. TAB: CHARTS & SUMMARIES -->
        <div x-show="viewMode === 'charts'" x-transition class="space-y-8">
            @foreach($survey->questions as $index => $question)
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl space-y-6">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200/50 dark:border-slate-700/50 space-y-2 sm:space-y-0">
                        <div>
                            <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-750 text-slate-600 dark:text-slate-300 border border-slate-200/50 rounded-md">
                                Tipe: {{ $question->type }}
                            </span>
                            <h3 class="text-base font-extrabold text-slate-800 dark:text-white mt-1 leading-snug">
                                <span class="text-emerald-500 mr-0.5">{{ $index + 1 }}.</span>
                                {{ $question->question_text }}
                            </h3>
                        </div>
                        <div class="text-xs font-bold text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 px-3 py-1.5 rounded-xl border border-slate-200/50 dark:border-slate-700/50">
                            {{ $analytics[$question->id]['response_count'] }} Respon Masuk
                        </div>
                    </div>

                    <!-- Rendering per Question Type -->
                    @if(in_array($question->type, ['radio', 'select', 'checkbox']))
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                            <!-- Left Table Data -->
                            <div class="space-y-4">
                                <table class="w-full text-left text-xs font-bold text-slate-700 dark:text-slate-300">
                                    <thead>
                                        <tr class="border-b border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 uppercase">
                                            <th class="py-2">Pilihan Opsi</th>
                                            <th class="py-2 text-right">Frekuensi</th>
                                            <th class="py-2 text-right">Persentase</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100/50 dark:divide-slate-800/50">
                                        @foreach($analytics[$question->id]['data'] as $breakdown)
                                            <tr>
                                                <td class="py-3 flex items-center">
                                                    <span class="w-2.5 h-2.5 rounded-full mr-2 bg-emerald-500"></span>
                                                    {{ $breakdown['label'] }}
                                                </td>
                                                <td class="py-3 text-right text-slate-800 dark:text-white">{{ $breakdown['count'] }}</td>
                                                <td class="py-3 text-right text-emerald-600 dark:text-emerald-400 font-extrabold">{{ $breakdown['percentage'] }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Right ApexChart container -->
                            <div class="flex justify-center">
                                <div id="chart-q-{{ $question->id }}" class="w-full max-w-sm"></div>
                            </div>
                        </div>

                    @elseif($question->type === 'scale')
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                            <!-- Key Metrics Indicators -->
                            <div class="bg-slate-50 dark:bg-slate-900/40 rounded-2xl p-5 border border-slate-200/50 dark:border-slate-800/50 space-y-4">
                                <div class="text-center py-2 border-b border-slate-200 dark:border-slate-800">
                                    <p class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Skor Rata-rata (Mean)</p>
                                    <h4 class="text-4xl font-black text-emerald-500 mt-1">{{ $analytics[$question->id]['data']['average'] ?? 0 }}</h4>
                                    <p class="text-[10px] text-slate-400 font-bold mt-1">Skala ({{ $question->scale_min }} s/d {{ $question->scale_max }})</p>
                                </div>
                                <div class="text-center py-2">
                                    <p class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Nilai Tengah (Median)</p>
                                    <h4 class="text-3xl font-black text-teal-500 mt-1">{{ $analytics[$question->id]['data']['median'] ?? 0 }}</h4>
                                </div>
                            </div>

                            <!-- Distribution graph -->
                            <div class="lg:col-span-2">
                                <div id="chart-q-{{ $question->id }}" class="w-full"></div>
                            </div>
                        </div>

                    @elseif(in_array($question->type, ['text', 'textarea', 'date']))
                        <div class="max-h-60 overflow-y-auto border border-slate-100 dark:border-slate-800 rounded-2xl bg-slate-50 dark:bg-slate-900/30 p-4 space-y-3">
                            @forelse($analytics[$question->id]['data'] as $ansText)
                                <div class="p-3 bg-white dark:bg-slate-850 rounded-xl border border-slate-200/40 dark:border-slate-700/30 text-xs font-bold text-slate-700 dark:text-slate-300 leading-normal flex items-start">
                                    <svg class="w-4 h-4 text-slate-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    <p>{{ $ansText }}</p>
                                </div>
                            @empty
                                <div class="text-center py-6 text-xs text-slate-400">Tidak ada respon berupa teks.</div>
                            @endforelse
                        </div>

                    @endif
                </div>
            @endforeach
        </div>

        <!-- 2. TAB: RAW RESPONSES TABLE -->
        <div x-show="viewMode === 'responses'" x-transition class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl space-y-6" style="display: none;">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200/50 dark:border-slate-700/50">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Semua Tanggapan Masuk</h3>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 px-3 py-1.5 rounded-xl border border-slate-200/50 dark:border-slate-700/50">
                    Total {{ $rawResponses->total() }} baris respon
                </span>
            </div>

            <!-- Responsive Table -->
            <div class="overflow-x-auto rounded-2xl border border-slate-200/50 dark:border-slate-700/50">
                <table class="w-full text-left text-xs font-bold text-slate-700 dark:text-slate-300">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900 text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <th class="px-6 py-4">#</th>
                            <th class="px-6 py-4">Responden</th>
                            <th class="px-6 py-4">IP Address</th>
                            <th class="px-6 py-4">Waktu Mengisi</th>
                            <th class="px-6 py-4 text-center">Detail Jawaban</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/30 dark:divide-slate-800/30">
                        @forelse($rawResponses as $response)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-750/30 transition-all">
                                <td class="px-6 py-4">{{ ($rawResponses->currentPage() - 1) * $rawResponses->perPage() + $loop->iteration }}</td>
                                <td class="px-6 py-4 font-black">
                                    @if($survey->is_anonymous)
                                        <div class="flex items-center text-emerald-600 dark:text-emerald-400">
                                            <svg class="w-4.5 h-4.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.952 11.952 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                            Responden Anonim #{{ ($rawResponses->currentPage() - 1) * $rawResponses->perPage() + $loop->iteration }}
                                        </div>
                                    @else
                                        <div class="flex items-center space-x-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                                            <span>{{ $response->respondent ? $response->respondent->name : 'Responden Luar' }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500 font-semibold">{{ $response->ip_address ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-slate-500 font-semibold">{{ $response->created_at->format('d M Y H:i') }} WIB</td>
                                <td class="px-6 py-4 text-center">
                                    <!-- Simple dialog response triggers -->
                                    <button type="button" 
                                            @click="$dispatch('open-modal', {
                                                modalId: 'response-modal-{{ $response->id }}'
                                            })"
                                            class="inline-flex items-center px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-emerald-500 hover:text-white transition-all text-[10px] font-black uppercase tracking-wider">
                                        Lihat Jawaban
                                    </button>

                                    <!-- Response Modal Dialog overlay -->
                                    <x-modal name="response-modal-{{ $response->id }}" focusable>
                                        <div class="p-6 space-y-6">
                                            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                                                <h3 class="text-lg font-black text-slate-800 dark:text-white">
                                                    @if($survey->is_anonymous)
                                                        Detail Jawaban: Responden Anonim #{{ ($rawResponses->currentPage() - 1) * $rawResponses->perPage() + $loop->iteration }}
                                                    @else
                                                        Detail Jawaban: {{ $response->respondent ? $response->respondent->name : 'Responden' }}
                                                    @endif
                                                </h3>
                                                <button @click="$dispatch('close-modal', 'response-modal-{{ $response->id }}')" 
                                                        class="p-1 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>

                                            <div class="space-y-4 max-h-[60vh] overflow-y-auto text-left pr-2">
                                                @foreach($survey->questions as $q)
                                                    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4 border border-slate-200/50 dark:border-slate-800/50">
                                                        <h4 class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pertanyaan:</h4>
                                                        <p class="text-sm font-extrabold text-slate-800 dark:text-white mt-1 leading-snug">{{ $q->question_text }}</p>
                                                        
                                                        <h4 class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mt-3">Jawaban:</h4>
                                                        <div class="text-sm font-bold text-slate-700 dark:text-slate-300 mt-1 pl-1">
                                                            @php
                                                                $ans = $response->answers[$q->id] ?? null;
                                                            @endphp
                                                            @if($ans === null)
                                                                <span class="text-slate-400 italic">Tidak dijawab</span>
                                                            @elseif(is_array($ans))
                                                                <ul class="list-disc pl-4 space-y-0.5">
                                                                    @foreach($ans as $subAns)
                                                                        <li>{{ $subAns }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <p class="whitespace-pre-line">{{ $ans }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </x-modal>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">Belum ada respon masuk untuk survei ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination links -->
            <div class="pt-4">
                {{ $rawResponses->links() }}
            </div>
        </div>
    </div>

    <!-- Script loading ApexCharts and drawing visual widgets -->
    <x-vendor.script lib="apexcharts" />
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Options helper for dark/light themes
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#cbd5e1' : '#334155';
            const gridColor = isDark ? '#334155' : '#f1f5f9';

            @foreach($survey->questions as $question)
                @if(in_array($question->type, ['radio', 'select', 'checkbox']))
                    // Draw Donut chart for choice-based questions
                    (function() {
                        const labels = {!! json_encode(array_column($analytics[$question->id]['data'], 'label')) !!};
                        const series = {!! json_encode(array_column($analytics[$question->id]['data'], 'count')) !!};

                        const options = {
                            chart: {
                                type: 'donut',
                                height: 280,
                                fontFamily: 'Inter, sans-serif'
                            },
                            stroke: {
                                show: true,
                                width: 2,
                                colors: [isDark ? '#1e293b' : '#ffffff']
                            },
                            series: series,
                            labels: labels,
                            colors: ['#10b981', '#14b8a6', '#6366f1', '#f59e0b', '#ec4899', '#8b5cf6', '#3b82f6'],
                            legend: {
                                position: 'bottom',
                                labels: {
                                    colors: textColor
                                }
                            },
                            dataLabels: {
                                enabled: true,
                                formatter: function (val) {
                                    return Math.round(val) + "%"
                                }
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '65%',
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                label: 'Respon',
                                                color: textColor,
                                                formatter: function (w) {
                                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        };

                        const chart = new ApexCharts(document.querySelector("#chart-q-{{ $question->id }}"), options);
                        chart.render();
                    })();

                @elseif($question->type === 'scale')
                    // Draw Column bar distribution chart for Scale questions
                    (function() {
                        const distribution = @json($analytics[$question->id]['data']['distribution'] ?? []);
                        const categories = Object.keys(distribution);
                        const data = Object.values(distribution).map(item => item.count);

                        const options = {
                            chart: {
                                type: 'bar',
                                height: 250,
                                toolbar: { show: false },
                                fontFamily: 'Inter, sans-serif'
                            },
                            series: [{
                                name: 'Jumlah Pilihan',
                                data: data
                            }],
                            plotOptions: {
                                bar: {
                                    borderRadius: 8,
                                    columnWidth: '50%',
                                    distributed: true,
                                    dataLabels: {
                                        position: 'top',
                                    },
                                }
                            },
                            colors: ['#ef4444', '#f97316', '#f59e0b', '#10b981', '#059669', '#14b8a6', '#06b6d4', '#3b82f6', '#4f46e5', '#6366f1'],
                            dataLabels: {
                                enabled: true,
                                formatter: function (val) {
                                    return val;
                                },
                                offsetY: -20,
                                style: {
                                    fontSize: '11px',
                                    fontWeight: '900',
                                    colors: [textColor]
                                }
                            },
                            xaxis: {
                                categories: categories,
                                labels: {
                                    style: {
                                        colors: textColor,
                                        fontWeight: 800
                                    }
                                },
                                axisBorder: { show: false },
                                axisTicks: { show: false }
                            },
                            yaxis: {
                                show: false
                            },
                            grid: {
                                show: false
                            },
                            legend: {
                                show: false
                            },
                            tooltip: {
                                enabled: true,
                                theme: isDark ? 'dark' : 'light',
                                y: {
                                    formatter: function(val) {
                                        return val + " Responden"
                                    }
                                }
                            }
                        };

                        const chart = new ApexCharts(document.querySelector("#chart-q-{{ $question->id }}"), options);
                        chart.render();
                    })();
                @endif
            @endforeach
        });
    </script>
</x-app-layout>
