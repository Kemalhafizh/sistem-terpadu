<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 lg:gap-6">
    <!-- Pendaftar SPMB (New) -->
    @canVisit('spmb.admin.index')
    <a href="{{ route('spmb.admin.index') }}" class="bg-linear-to-br from-primary-900 to-primary-800 rounded-4xl p-6 shadow-xl shadow-primary-900/20 border border-primary-700/50 flex items-center hover:-translate-y-1 transition-all duration-300 group overflow-hidden relative text-white">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-10 -mt-10 blur-2xl group-hover:bg-white/10 transition-colors"></div>
        <div class="p-4 bg-white/10 backdrop-blur-md rounded-2xl mr-4 relative z-10 border border-white/10 group-hover:scale-110 transition-transform">
            <svg class="w-8 h-8 text-primary-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
        </div>
        <div class="relative z-10">
            <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest mb-1">{{ __('dashboard.new_applicants') }}</p>
            <p class="text-3xl font-black tracking-tight">{{ $pending_registrations ?? 0 }}</p>
        </div>
        @if(($pending_registrations ?? 0) > 0)
            <div class="absolute top-4 right-4 flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 shadow-sm shadow-red-500/50"></span>
            </div>
        @endif
    </a>
    @endcanVisit

    <!-- Total Siswa -->
    <div class="bg-white rounded-4xl p-6 shadow-sm hover:shadow-xl hover:shadow-gray-200/50 border border-gray-100 flex items-center hover:-translate-y-1 transition-all duration-300 group overflow-hidden relative">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-linear-to-br from-primary-50 to-primary-100 rounded-full blur-2xl opacity-50 group-hover:opacity-100 transition-opacity"></div>
        <div class="p-4 bg-linear-to-br from-primary-50 to-primary-100 rounded-2xl mr-4 group-hover:scale-110 transition-transform shadow-inner border border-primary-100/50 relative z-10">
            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div class="relative z-10">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ __('dashboard.total_students') }}</p>
            <p class="text-3xl font-black text-gray-900 tracking-tight">{{ $total_students }}</p>
        </div>
    </div>

    <!-- Total Guru -->
    <div class="bg-white rounded-4xl p-6 shadow-sm hover:shadow-xl hover:shadow-blue-200/40 border border-gray-100 flex items-center hover:-translate-y-1 transition-all duration-300 group overflow-hidden relative">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-linear-to-br from-blue-50 to-blue-100 rounded-full blur-2xl opacity-50 group-hover:opacity-100 transition-opacity"></div>
        <div class="p-4 bg-linear-to-br from-blue-50 to-blue-100 rounded-2xl mr-4 group-hover:scale-110 transition-transform shadow-inner border border-blue-100/50 relative z-10">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        </div>
        <div class="relative z-10">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ __('dashboard.active_teachers') }}</p>
            <p class="text-3xl font-black text-gray-900 tracking-tight">{{ $total_teachers }}</p>
        </div>
    </div>

    <!-- Total Kelas -->
    <div class="bg-white rounded-4xl p-6 shadow-sm hover:shadow-xl hover:shadow-amber-200/40 border border-gray-100 flex items-center hover:-translate-y-1 transition-all duration-300 group overflow-hidden relative">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-linear-to-br from-amber-50 to-amber-100 rounded-full blur-2xl opacity-50 group-hover:opacity-100 transition-opacity"></div>
        <div class="p-4 bg-linear-to-br from-amber-50 to-amber-100 rounded-2xl mr-4 group-hover:scale-110 transition-transform shadow-inner border border-amber-100/50 relative z-10">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </div>
        <div class="relative z-10">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ __('dashboard.total_classrooms') }}</p>
            <p class="text-3xl font-black text-gray-900 tracking-tight">{{ $total_classes }}</p>
        </div>
    </div>

    <!-- Tahun Ajaran -->
    <div class="bg-white rounded-4xl p-6 shadow-sm hover:shadow-xl hover:shadow-emerald-200/40 border border-gray-100 flex items-center hover:-translate-y-1 transition-all duration-300 group overflow-hidden relative">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-linear-to-br from-emerald-50 to-emerald-100 rounded-full blur-2xl opacity-50 group-hover:opacity-100 transition-opacity"></div>
        <div class="p-4 bg-linear-to-br from-emerald-50 to-emerald-100 rounded-2xl mr-4 group-hover:scale-110 transition-transform shadow-inner border border-emerald-100/50 relative z-10">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
        <div class="relative z-10">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ __('dashboard.active_period') }}</p>
            <p class="text-lg font-black text-gray-900 tracking-tight leading-tight">
                {{ $active_year ? $active_year->year : 'N/A' }}
                <span class="block text-[10px] font-bold text-emerald-600 mt-0.5">{{ $active_year ? $active_year->semester : __('dashboard.not_set') }}</span>
            </p>
        </div>
    </div>
</div>
