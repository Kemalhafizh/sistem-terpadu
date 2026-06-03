<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Mapel -->
        <div class="bg-indigo-600 rounded-[1.5rem] p-6 shadow-lg shadow-indigo-900/20 text-white flex items-center">
            <div class="p-3 bg-white/20 rounded-xl mr-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-indigo-100 uppercase tracking-[0.2em] mb-1">Total Mapel</p>
                <p class="text-3xl font-extrabold tracking-tight">{{ $total_subjects }}</p>
            </div>
        </div>

        <!-- Total Kelas -->
        <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-gray-100 flex items-center">
            <div class="p-3 bg-gray-50 rounded-xl mr-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-1">Kelas Terdaftar</p>
                <p class="text-3xl font-extrabold text-gray-800 tracking-tight">{{ $total_classes }}</p>
            </div>
        </div>

        <!-- Aktifkan Tahun Ajaran Button -->
        <a href="{{ route('academic-years.index') }}" class="group bg-white rounded-[1.5rem] p-6 shadow-sm border-2 border-dashed border-primary-200 flex items-center justify-center hover:bg-primary-50 hover:border-primary-400 transition-all">
            <div class="text-center">
                <p class="text-primary-600 font-bold text-sm group-hover:scale-105 transition-transform">Kelola Tahun Ajaran →</p>
            </div>
        </a>
    </div>

    <!-- Recent Subjects List -->
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
        <h3 class="text-lg font-extrabold text-gray-800 mb-6 flex items-center">
            <span class="w-2 h-6 bg-indigo-500 rounded-full mr-3"></span>
            Mata Pelajaran Baru Ditambahkan
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($recent_subjects ?? [] as $subject)
            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center">
                <div class="w-10 h-10 bg-indigo-100 text-indigo-700 flex items-center justify-center rounded-xl font-bold text-xs mr-3">
                    {{ $subject->code }}
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">{{ $subject->name }}</p>
                    <p class="text-[10px] text-gray-500 uppercase font-semibold">{{ $subject->category }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
