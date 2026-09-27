<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Detail Presensi Kegiatan') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Detail & Status Kehadiran Acara
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('event-attendance.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    ⬅ KEMBALI
                </a>
                
                @if(in_array(auth()->user()->role, ['guru', 'admin', 'superadmin']))
                    <a href="{{ route('event-attendance.export-pdf', $event->id) }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-amber-500/20 hover:scale-[1.02] transition-all">
                         CETAK PDF
                    </a>
                    <a href="{{ route('event-attendance.input', $event->id) }}" class="px-5 py-2 bg-primary-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-primary-900/20 hover:scale-[1.04] transition-all">
                         EDIT ABSENSI
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-5 py-4 rounded-2xl relative" role="alert">
                    <span class="block text-xs font-bold">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Summary Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Card: Event Details --}}
                <div class="bg-white p-6 sm:p-8 rounded-[2.5rem] shadow-sm border border-gray-100 space-y-5">
                    <div class="border-b border-gray-50 pb-3">
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-primary-50 text-primary-600 border border-primary-100">
                            {{ str_replace('_', ' ', $event->type) }}
                        </span>
                        <h3 class="font-black text-lg text-gray-800 mt-2 tracking-tight">{{ $event->name }}</h3>
                    </div>

                    <div class="space-y-4 text-xs font-bold text-gray-700">
                        <div>
                            <span class="text-gray-400 block text-[9px] font-black uppercase tracking-widest">Tanggal Pelaksanaan</span>
                            <span class="text-gray-800 text-sm mt-0.5 block">{{ $event->event_date?->format('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[9px] font-black uppercase tracking-widest">Waktu Acara</span>
                            <span class="text-gray-800 mt-0.5 block">
                                @if($event->start_time)
                                    {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }}{{ $event->end_time ? ' - ' . \Carbon\Carbon::parse($event->end_time)->format('H:i') : '' }} WIB
                                @else
                                    Waktu Fleksibel
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[9px] font-black uppercase tracking-widest">Sasaran / Target Peserta</span>
                            <span class="text-gray-800 mt-0.5 block">
                                @if($event->target === 'all_students')
                                    Semua Murid
                                @elseif($event->target === 'all_teachers')
                                    Semua Guru
                                @elseif($event->target === 'specific_class')
                                    Kelas Tertentu
                                @else
                                    Grup Kustom
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[9px] font-black uppercase tracking-widest">Dibuat Oleh</span>
                            <span class="text-gray-800 mt-0.5 block">{{ $event->creator->name ?? 'Sistem' }}</span>
                        </div>
                        @if($event->notes)
                            <div class="border-t border-gray-50 pt-3">
                                <span class="text-gray-400 block text-[9px] font-black uppercase tracking-widest">Catatan Tambahan</span>
                                <span class="text-gray-600 font-medium block mt-1 leading-relaxed">{{ $event->notes }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right Card: Attendance Charts & Statistics --}}
                <div class="lg:col-span-2 bg-white p-6 sm:p-8 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col md:flex-row gap-8">
                    
                    {{-- Stats Counters --}}
                    <div class="flex-1 flex flex-col justify-between space-y-6">
                        <div>
                            <h3 class="font-black text-gray-800 text-lg tracking-tight">Metrik Kehadiran</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Ringkasan status kehadiran seluruh sasaran peserta</p>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <div class="bg-gray-50 p-4 rounded-2xl text-center">
                                <span class="text-[9px] text-gray-400 block font-black uppercase tracking-widest">Total Peserta</span>
                                <span class="text-2xl font-black text-gray-800 block mt-1">{{ count($records) }}</span>
                            </div>
                            <div class="bg-emerald-50 p-4 rounded-2xl text-center border border-emerald-100">
                                <span class="text-[9px] text-emerald-600 block font-black uppercase tracking-widest">Hadir + Late</span>
                                <span class="text-2xl font-black text-emerald-700 block mt-1">{{ $stats['hadir'] + $stats['terlambat'] }}</span>
                            </div>
                            <div class="bg-rose-50 p-4 rounded-2xl text-center border border-rose-100 col-span-2 sm:col-span-1">
                                <span class="text-[9px] text-rose-600 block font-black uppercase tracking-widest">Mangkir (Alpa)</span>
                                <span class="text-2xl font-black text-rose-700 block mt-1">{{ $stats['alpa'] }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-amber-50/50 p-3 rounded-xl border border-amber-100 flex items-center justify-between">
                                <span class="text-[10px] text-amber-700 font-black uppercase tracking-widest">Izin</span>
                                <span class="text-sm font-black text-amber-800">{{ $stats['izin'] }}</span>
                            </div>
                            <div class="bg-red-50/50 p-3 rounded-xl border border-red-100 flex items-center justify-between">
                                <span class="text-[10px] text-red-600 font-black uppercase tracking-widest">Sakit</span>
                                <span class="text-sm font-black text-red-700">{{ $stats['sakit'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Pie Chart Column --}}
                    <div class="w-full md:w-72 flex flex-col items-center justify-center border-t md:border-t-0 md:border-l border-gray-50 pt-6 md:pt-0 md:pl-8">
                        <div id="attendance-ratio-chart" class="w-full"></div>
                    </div>
                </div>
            </div>

            {{-- Attendees Detailed Table --}}
            <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 overflow-x-auto">
                <div class="flex items-center justify-between border-b border-gray-50 pb-4 mb-4">
                    <h3 class="font-black text-lg text-gray-800">Daftar Kehadiran Peserta</h3>
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Jumlah: {{ count($records) }} Peserta</span>
                </div>
                
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                            <th class="pb-3 w-16 text-center">No</th>
                            <th class="pb-3">Peserta</th>
                            <th class="pb-3">Kelas / Peran</th>
                            <th class="pb-3 text-center">Status Kehadiran</th>
                            <th class="pb-3 text-center">Jam Masuk</th>
                            <th class="pb-3">Catatan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-xs font-bold text-gray-700">
                        @forelse($records as $index => $record)
                            @php
                                $statusLabels = [
                                    'hadir' => ' Hadir',
                                    'terlambat' => ' Terlambat',
                                    'izin' => ' Izin',
                                    'sakit' => ' Sakit',
                                    'alpa' => ' Alpa'
                                ];

                                $statusColors = [
                                    'hadir' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                                    'terlambat' => 'bg-teal-50 text-teal-600 border border-teal-100',
                                    'izin' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                    'sakit' => 'bg-rose-50 text-rose-600 border border-rose-100',
                                    'alpa' => 'bg-gray-50 text-gray-500 border border-gray-100'
                                ];

                                $userTypeLabel = $record->user?->role === 'student' ? ($record->user?->student?->classroom?->name ?? 'Siswa') : 'Guru/Staf';
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-4 text-center text-gray-400 font-black">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-primary-50 text-primary-700 border border-primary-100 flex items-center justify-center text-[10px] font-black uppercase">
                                            {{ substr($record->user?->name ?? 'X', 0, 2) }}
                                        </div>
                                        <div class="text-left">
                                            <p class="font-black text-gray-800">{{ $record->user?->name }}</p>
                                            <p class="text-[9px] text-gray-400 uppercase font-black">ID: #{{ $record->user_id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 text-gray-500">
                                    {{ $userTypeLabel }}
                                </td>
                                <td class="py-4 text-center">
                                    <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider {{ $statusColors[$record->status] ?? 'bg-gray-100' }}">
                                        {{ $statusLabels[$record->status] ?? $record->status }}
                                    </span>
                                </td>
                                <td class="py-4 text-center text-gray-600">
                                    {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') . ' WIB' : '-' }}
                                </td>
                                <td class="py-4 text-gray-500 font-medium max-w-xs truncate" title="{{ $record->notes }}">
                                    {{ $record->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400 font-bold">
                                    Tidak ada data presensi siswa/staf.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- ApexCharts & Initializer --}}
    <x-vendor.script lib="apexcharts" />
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stats = {
                hadir: {{ $stats['hadir'] }},
                terlambat: {{ $stats['terlambat'] }},
                izin: {{ $stats['izin'] }},
                sakit: {{ $stats['sakit'] }},
                alpa: {{ $stats['alpa'] }}
            };

            const total = stats.hadir + stats.terlambat + stats.izin + stats.sakit + stats.alpa;

            const options = {
                chart: {
                    type: 'donut',
                    height: 250
                },
                series: [stats.hadir, stats.terlambat, stats.izin, stats.sakit, stats.alpa],
                labels: ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa'],
                colors: ['#10b981', '#14b8a6', '#f59e0b', '#f43f5e', '#6b7280'],
                legend: {
                    position: 'bottom',
                    fontSize: '10px',
                    fontFamily: 'Inter',
                    fontWeight: 600,
                    labels: {
                        colors: '#6b7280'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Kehadiran',
                                    formatter: function (w) {
                                        const present = stats.hadir + stats.terlambat;
                                        if (total === 0) return '0%';
                                        return Math.round((present / total) * 100) + '%';
                                    }
                                }
                            }
                        }
                    }
                }
            };

            if (total > 0) {
                const chart = new ApexCharts(document.querySelector("#attendance-ratio-chart"), options);
                chart.render();
            } else {
                document.querySelector("#attendance-ratio-chart").innerHTML = '<div class="text-sm text-gray-400 font-bold">Belum ada data presensi</div>';
            }
        });
    </script>
</x-app-layout>
