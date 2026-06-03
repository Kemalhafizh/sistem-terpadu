<x-app-layout>
    <x-slot name="title">Survei & Kuesioner Digital</x-slot>

    <x-slot name="breadcrumbs">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Survei Digital', 'url' => '#']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-800 dark:text-white">
                    Survei & Kuesioner Digital
                </h1>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1">
                    Umpan balik stakeholder untuk evaluasi dan peningkatan mutu pendidikan.
                </p>
            </div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('surveys.create') }}" 
                   class="inline-flex items-center justify-center px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-2xl text-sm font-bold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transition-all border border-emerald-400/20 group">
                    <svg class="w-5 h-5 mr-2 transition-transform group-hover:rotate-95" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Survei Baru
                </a>
            @endif
        </div>
    </x-slot>

    <div class="space-y-8" x-data="{ activeTab: '{{ auth()->user()->isAdmin() ? 'active' : 'all' }}' }">
        
        <!-- Admin Dashboard Categories -->
        @if(auth()->user()->isAdmin())
            <div class="flex border-b border-slate-200 dark:border-slate-800 space-x-6">
                <button @click="activeTab = 'active'" 
                        :class="activeTab === 'active' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="pb-4 px-1 border-b-2 text-sm font-medium transition-all focus:outline-none">
                    Survei Aktif ({{ $activeSurveys->count() }})
                </button>
                <button @click="activeTab = 'draft'" 
                        :class="activeTab === 'draft' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="pb-4 px-1 border-b-2 text-sm font-medium transition-all focus:outline-none">
                    Draf Kuesioner ({{ $draftSurveys->count() }})
                </button>
                <button @click="activeTab = 'closed'" 
                        :class="activeTab === 'closed' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="pb-4 px-1 border-b-2 text-sm font-medium transition-all focus:outline-none">
                    Selesai / Ditutup ({{ $closedSurveys->count() }})
                </button>
            </div>

            <!-- TAB: ACTIVE SURVEYS -->
            <div x-show="activeTab === 'active'" x-transition class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($activeSurveys as $survey)
                    @include('surveys.partials.survey_card', ['survey' => $survey, 'badgeColor' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200/50'])
                @empty
                    @include('surveys.partials.empty_state', ['message' => 'Tidak ada survei aktif saat ini.'])
                @endforelse
            </div>

            <!-- TAB: DRAFT SURVEYS -->
            <div x-show="activeTab === 'draft'" x-transition class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" style="display: none;">
                @forelse($draftSurveys as $survey)
                    @include('surveys.partials.survey_card', ['survey' => $survey, 'badgeColor' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200/50'])
                @empty
                    @include('surveys.partials.empty_state', ['message' => 'Tidak ada draf kuesioner.'])
                @endforelse
            </div>

            <!-- TAB: CLOSED SURVEYS -->
            <div x-show="activeTab === 'closed'" x-transition class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" style="display: none;">
                @forelse($closedSurveys as $survey)
                    @include('surveys.partials.survey_card', ['survey' => $survey, 'badgeColor' => 'bg-slate-100 text-slate-800 dark:bg-slate-800/60 dark:text-slate-300 border-slate-200/50'])
                @empty
                    @include('surveys.partials.empty_state', ['message' => 'Tidak ada survei yang ditutup.'])
                @endforelse
            </div>

        <!-- User view -->
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($surveys as $survey)
                    <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-3 py-1 text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200/30 rounded-full">
                                    {{ $survey->is_anonymous ? 'ANONIM (PDP Protected)' : 'IDENTITAS TERBUKA' }}
                                </span>
                                @if($survey->end_date)
                                    <span class="text-xs font-semibold text-rose-500 dark:text-rose-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Hingga {{ $survey->end_date->format('d M') }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-lg font-black tracking-tight text-slate-800 dark:text-white mb-2 leading-tight">
                                {{ $survey->title }}
                            </h3>
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 line-clamp-3 mb-6">
                                {{ $survey->description ?? 'Tidak ada deskripsi.' }}
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('surveys.show', $survey->uuid) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md hover:shadow-lg shadow-emerald-500/10 transition-all border border-emerald-400/20">
                                Mulai Mengisi Kuesioner
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        @include('surveys.partials.empty_state', ['message' => 'Luar biasa! Tidak ada kuesioner tertunda untuk Anda saat ini.'])
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</x-app-layout>
