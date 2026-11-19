<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight">Log & Kalender Presensi</h2>
                <p class="text-sm font-semibold text-gray-500 mt-1">Riwayat Kehadiran Siswa Secara Detail</p>
            </div>
            <a href="{{ route('parent-portal.dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Attendance Stats Widget (1/3 Column) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-200/60 flex flex-col items-center justify-center text-center">
                        <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-6">Persentase Kehadiran Berjalan</p>
                        
                        <div class="relative w-40 h-40 flex items-center justify-center mb-8">
                            <!-- Circular Ring Visual -->
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-gray-100" stroke-width="2.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-emerald-500" stroke-dasharray="{{ $hadirPercentage }}, 100" stroke-width="3" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="absolute text-3xl font-black text-gray-900 tracking-tighter">{{ $hadirPercentage }}%</span>
                        </div>

                        <div class="w-full space-y-3.5 text-sm text-gray-600 border-t border-gray-100 pt-6">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 bg-emerald-500 rounded-full"></span>
                                    <span>Hadir</span>
                                </div>
                                <strong class="text-gray-900 font-extrabold">{{ $hadir }} Hari</strong>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 bg-amber-500 rounded-full"></span>
                                    <span>Sakit</span>
                                </div>
                                <strong class="text-gray-900 font-extrabold">{{ $sakit }} Hari</strong>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                                    <span>Izin</span>
                                </div>
                                <strong class="text-gray-900 font-extrabold">{{ $izin }} Hari</strong>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                                    <span>Alpa</span>
                                </div>
                                <strong class="{{ $alpa > 0 ? 'text-red-600 font-black' : 'text-gray-900' }} font-extrabold">{{ $alpa }} Hari</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Logs List (2/3 Column) -->
                <div class="lg:col-span-2 space-y-4">
                    <h3 class="font-black text-gray-800 uppercase text-xs tracking-widest px-2">Log Riwayat Harian Presensi</h3>
                    
                    @forelse($attendances as $att)
                        @php
                            $statusMap = [
                                'H' => ['label' => 'Hadir', 'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
                                'Hadir' => ['label' => 'Hadir', 'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
                                'S' => ['label' => 'Sakit', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
                                'Sakit' => ['label' => 'Sakit', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
                                'I' => ['label' => 'Izin', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
                                'Izin' => ['label' => 'Izin', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
                                'A' => ['label' => 'Alpa', 'class' => 'bg-red-100 text-red-700 border-red-200'],
                                'Alpa' => ['label' => 'Alpa', 'class' => 'bg-red-100 text-red-700 border-red-200'],
                            ];
                            $statusMeta = $statusMap[$att->status] ?? ['label' => $att->status, 'class' => 'bg-gray-100 text-gray-700 border-gray-200'];
                        @endphp
                        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-200/60 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <span class="px-4 py-2 border rounded-xl text-xs font-black uppercase tracking-wider {{ $statusMeta['class'] }}">
                                    {{ $statusMeta['label'] }}
                                </span>
                                <div>
                                    <h4 class="font-extrabold text-gray-900 text-base">
                                        {{ \Carbon\Carbon::parse($att->date)->isoFormat('D MMMM YYYY') }}
                                    </h4>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-0.5">
                                        Hari: {{ \Carbon\Carbon::parse($att->date)->isoFormat('dddd') }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-sm font-semibold text-gray-600">
                                    {{ $att->notes ?? 'Tanpa Keterangan Tambahan' }}
                                </p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">
                                    Dicatat oleh: {{ $att->recorder->name ?? 'Sistem' }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-[2.5rem] p-20 text-center border-2 border-dashed border-gray-200">
                            <p class="text-gray-400 italic font-bold">Belum ada riwayat catatan presensi untuk semester ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
