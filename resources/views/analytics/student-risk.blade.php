<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('analytics.index') }}" class="text-sm font-bold text-gray-500 hover:text-primary-600 flex items-center mb-1 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Dashboard Utama
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-rose-500 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    {{ __('Predictive Early Warning System') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Identifikasi Siswa Berisiko Akademis & Perilaku (At-Risk)</p>
            </div>
            
            <form action="{{ route('analytics.student-risk') }}" method="GET" class="w-64">
                <select name="classroom_id" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold text-gray-700 dark:text-gray-200 shadow-sm focus:border-rose-500 focus:ring-rose-500">
                    <option value="">-- Seluruh Kelas --</option>
                    @foreach($classrooms as $class)
                        <option value="{{ $class->id }}" {{ $classroomId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    <!-- load ApexCharts CDN directly -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="bg-rose-50 dark:bg-rose-900/20 rounded-[2rem] p-6 shadow-sm border border-rose-100 dark:border-rose-800/50 flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-800 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h4 class="font-black text-rose-800 dark:text-rose-300 text-lg">Informasi Sistem Prediktif</h4>
                    <p class="text-sm text-rose-700 dark:text-rose-400 mt-1 leading-relaxed">
                        Sistem ini menggunakan algoritma agregasi multivariabel yang menganalisis <strong>3 pilar utama: Nilai Akademik (40%), Kehadiran (30%), dan Poin Pelanggaran (30%)</strong>. 
                        Siswa yang tampil di bawah ini memiliki Skor Risiko <strong>di atas 40%</strong> dan membutuhkan intervensi segera.
                    </p>
                </div>
            </div>

            @if($atRiskStudents->isEmpty())
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-[2.5rem] p-16 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col items-center justify-center text-center">
                    <div class="w-24 h-24 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 rounded-full flex items-center justify-center mb-6 shadow-sm">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-800 dark:text-white mb-2">Kondisi Aman</h3>
                    <p class="text-gray-500 max-w-md">Tidak ditemukan siswa dengan skor risiko di atas batas ambang batas waspada pada filter ini.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($atRiskStudents as $index => $risk)
                        <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col">
                            <!-- Header Status -->
                            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                                <div>
                                    <h4 class="font-black text-gray-900 dark:text-white text-lg">{{ $risk->student->user->name }}</h4>
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">{{ $risk->student->nis }} • {{ $risk->student->classroom->name }}</p>
                                </div>
                                <div class="w-12 h-12 rounded-full border-4 {{ $risk->color === 'danger' ? 'border-rose-500 text-rose-500' : 'border-amber-500 text-amber-500' }} flex items-center justify-center font-black text-sm">
                                    {{ $risk->total_risk_score }}
                                </div>
                            </div>
                            
                            <!-- Risk Breakdown -->
                            <div class="p-6 flex-grow space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-500 uppercase">Risiko Akademik</span>
                                    <span class="text-sm font-black {{ $risk->academic_risk > 50 ? 'text-rose-500' : 'text-gray-700 dark:text-gray-300' }}">{{ $risk->academic_risk }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="{{ $risk->academic_risk > 50 ? 'bg-rose-500' : 'bg-gray-400' }} h-1.5 rounded-full" style="width: {{ $risk->academic_risk }}%"></div>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-500 uppercase">Risiko Kehadiran</span>
                                    <span class="text-sm font-black {{ $risk->attendance_risk > 50 ? 'text-rose-500' : 'text-gray-700 dark:text-gray-300' }}">{{ $risk->attendance_risk }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="{{ $risk->attendance_risk > 50 ? 'bg-rose-500' : 'bg-gray-400' }} h-1.5 rounded-full" style="width: {{ $risk->attendance_risk }}%"></div>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-500 uppercase">Risiko Perilaku/Poin</span>
                                    <span class="text-sm font-black {{ $risk->behavior_risk > 50 ? 'text-rose-500' : 'text-gray-700 dark:text-gray-300' }}">{{ $risk->behavior_risk }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="{{ $risk->behavior_risk > 50 ? 'bg-rose-500' : 'bg-gray-400' }} h-1.5 rounded-full" style="width: {{ $risk->behavior_risk }}%"></div>
                                </div>

                                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 text-{{ $risk->color === 'danger' ? 'rose' : 'amber' }}-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Rekomendasi Sistem</p>
                                            <p class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $risk->recommendation }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Footer Action -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700">
                                <a href="#" class="w-full py-2 flex items-center justify-center rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 transition-colors shadow-sm">
                                    Lihat Profil Lengkap
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
