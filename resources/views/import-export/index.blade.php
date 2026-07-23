<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-gray-100 leading-tight uppercase tracking-tight flex items-center">
                    <svg class="w-7 h-7 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    Data Import / Export Hub
                </h2>
                <p class="text-xs font-semibold text-gray-400 mt-1 uppercase tracking-wider">
                    Kelola integrasi berkas eksternal dan migrasi data sekolah secara universal
                </p>
            </div>
            <div>
                <a href="{{ route('import-export.history') }}" 
                   class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white rounded-xl text-xs font-bold uppercase tracking-widest shadow-lg shadow-slate-900/10 transition-all border border-slate-700/50 flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Riwayat Import & Rollback
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 w-full space-y-8">
        <!-- Dashboard Alert Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Stat 1 -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-xl flex items-center">
                <div class="p-3 bg-blue-500/10 rounded-xl text-blue-500 mr-4 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Siswa</h4>
                    <p class="text-xl font-black text-gray-800 dark:text-gray-100 mt-0.5">{{ \App\Models\Student::count() }}</p>
                </div>
            </div>
            <!-- Stat 2 -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-xl flex items-center">
                <div class="p-3 bg-indigo-500/10 rounded-xl text-indigo-500 mr-4 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Karyawan / Guru</h4>
                    <p class="text-xl font-black text-gray-800 dark:text-gray-100 mt-0.5">{{ \App\Models\Employee::count() }}</p>
                </div>
            </div>
            <!-- Stat 3 -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-xl flex items-center">
                <div class="p-3 bg-amber-500/10 rounded-xl text-amber-500 mr-4 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Mata Pelajaran</h4>
                    <p class="text-xl font-black text-gray-800 dark:text-gray-100 mt-0.5">{{ \App\Models\Subject::count() }}</p>
                </div>
            </div>
            <!-- Stat 4 -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-xl flex items-center">
                <div class="p-3 bg-emerald-500/10 rounded-xl text-emerald-500 mr-4 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Kelas</h4>
                    <p class="text-xl font-black text-gray-800 dark:text-gray-100 mt-0.5">{{ \App\Models\Classroom::count() }}</p>
                </div>
            </div>
        </div>

        <!-- Modules Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            @foreach($modules as $key => $label)
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl p-6 md:p-8 flex flex-col justify-between hover:shadow-blue-500/5 transition-all duration-300 relative group">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-blue-500/5 to-indigo-500/5 rounded-bl-full pointer-events-none"></div>
                    
                    <div>
                        <!-- Header Card -->
                        <div class="flex items-center space-x-4 mb-6">
                            @if($key === 'students')
                                <div class="p-4 bg-gradient-to-br from-blue-500 to-indigo-500 text-white rounded-2xl shadow-lg shadow-blue-500/20">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                            @elseif($key === 'employees')
                                <div class="p-4 bg-gradient-to-br from-indigo-500 to-purple-500 text-white rounded-2xl shadow-lg shadow-indigo-500/20">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                            @elseif($key === 'subjects')
                                <div class="p-4 bg-gradient-to-br from-amber-500 to-orange-500 text-white rounded-2xl shadow-lg shadow-amber-500/20">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                </div>
                            @elseif($key === 'classrooms')
                                <div class="p-4 bg-gradient-to-br from-emerald-500 to-teal-500 text-white rounded-2xl shadow-lg shadow-emerald-500/20">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                            @endif
                            <div>
                                <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">{{ $label }}</h3>
                                <p class="text-xs text-gray-400 font-semibold uppercase mt-0.5">Operasi migrasi massal berkas modul</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <!-- Section: Import (Upload) -->
                            <div class="border border-gray-100 dark:border-gray-700/60 rounded-2xl p-4 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col justify-between"
                                 x-data="{ fileUploaded: false, fileName: '' }"
                            >
                                <div>
                                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-3">Impor Data (Excel/CSV)</h4>
                                    
                                    <form action="{{ route('import-export.upload') }}" method="POST" enctype="multipart/form-data" id="form-import-{{ $key }}">
                                        @csrf
                                        <input type="hidden" name="module" value="{{ $key }}">
                                        
                                        <!-- Drop zone / Input file -->
                                        <label class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4 flex flex-col items-center justify-center cursor-pointer hover:border-blue-500 transition-colors bg-white dark:bg-gray-800 relative overflow-hidden">
                                            <input type="file" name="file" class="hidden" 
                                                   @change="fileUploaded = true; fileName = $event.target.files[0].name"
                                                   required
                                            >
                                            <div x-show="!fileUploaded" class="text-center">
                                                <svg class="w-7 h-7 mx-auto text-gray-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                                <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 mt-2 uppercase tracking-wide">Pilih Berkas</p>
                                            </div>
                                            <div x-show="fileUploaded" class="text-center" style="display: none;">
                                                <svg class="w-7 h-7 mx-auto text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 mt-2 max-w-[120px] truncate" x-text="fileName"></p>
                                            </div>
                                        </label>
                                    </form>
                                </div>
                                <button type="submit" form="form-import-{{ $key }}" class="w-full mt-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-lg shadow-blue-500/10 hover:shadow-blue-500/20">
                                    Unggah & Petakan Kolom
                                </button>
                            </div>

                            <!-- Section: Export (Download) -->
                            <div class="border border-gray-100 dark:border-gray-700/60 rounded-2xl p-4 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col justify-between"
                                 x-data="{ format: 'xlsx' }"
                            >
                                <div>
                                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-3">Ekspor Data (Unduh)</h4>
                                    <p class="text-[11px] text-gray-400 font-medium mb-4 leading-relaxed">Ekspor basis data aktif secara instan ke format yang Anda butuhkan.</p>
                                    
                                    <!-- Format Selector -->
                                    <div class="grid grid-cols-4 gap-2 mb-4">
                                        <button @click="format = 'xlsx'" :class="format === 'xlsx' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600'" class="py-1 px-1 text-center font-bold text-[10px] rounded-lg border transition-all uppercase">
                                            Excel
                                        </button>
                                        <button @click="format = 'csv'" :class="format === 'csv' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600'" class="py-1 px-1 text-center font-bold text-[10px] rounded-lg border transition-all uppercase">
                                            CSV
                                        </button>
                                        <button @click="format = 'pdf'" :class="format === 'pdf' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600'" class="py-1 px-1 text-center font-bold text-[10px] rounded-lg border transition-all uppercase">
                                            PDF
                                        </button>
                                        <button @click="format = 'json'" :class="format === 'json' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600'" class="py-1 px-1 text-center font-bold text-[10px] rounded-lg border transition-all uppercase">
                                            JSON
                                        </button>
                                    </div>
                                </div>
                                
                                <form action="{{ route('import-export.export') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="module" value="{{ $key }}">
                                    <input type="hidden" name="format" :value="format">
                                    
                                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-md">
                                        Unduh Data (<span x-text="format.toUpperCase()"></span>)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
