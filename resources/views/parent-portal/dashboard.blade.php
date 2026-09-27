<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h2 class="font-black text-3xl text-gray-900 leading-tight tracking-tight">
          Portal Orang Tua <span class="bg-gradient-to-r from-primary-600 to-indigo-600 bg-clip-text text-transparent">Premium</span>
        </h2>
        <p class="text-sm font-semibold text-gray-500 mt-1">{{ school_name() }}</p>
      </div>
      
      <!-- Child Switcher Tab -->
      <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-1.5 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 flex gap-2 shadow-sm">
        @foreach($children as $child)
          <a href="{{ route('parent-portal.dashboard', ['child_id' => $child->id]) }}" 
            class="px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-300 flex items-center gap-2 {{ $student->id === $child->id ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <span class="w-2.5 h-2.5 rounded-full {{ $student->id === $child->id ? 'bg-white' : 'bg-primary-500' }}"></span>
            {{ $child->user->name }}
          </a>
        @endforeach
      </div>
    </div>
  </x-slot>

  <!-- Content Area -->
  <div class="py-8 bg-gray-50/50 min-h-screen">
    <div class="w-full px-4 sm:px-6 lg:px-8 space-y-10">
      
      <!-- Welcome Alert & Info -->
      @if(session('success'))
        <div class="bg-emerald-600 text-white p-4 rounded-2xl shadow-xl flex items-center animate-fade-in">
          <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          <span class="font-bold">{{ session('success') }}</span>
        </div>
      @endif

      <!-- 1. Profile & Quick Links Header Section -->
      <div class="bg-gradient-to-r from-gray-900 via-indigo-950 to-slate-900 rounded-[2.5rem] p-8 md:p-10 shadow-2xl text-white relative overflow-hidden group">
        <div class="absolute -right-24 -bottom-24 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700"></div>
        <div class="absolute -left-20 -top-20 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
          <!-- Profile Widget -->
          <div class="flex items-center gap-6">
            <div class="w-20 h-20 bg-gradient-to-br from-primary-500 to-indigo-600 rounded-3xl flex items-center justify-center font-black text-3xl text-white shadow-xl shadow-indigo-900/40">
              {{ substr($student->user->name, 0, 1) }}
            </div>
            <div>
              <span class="px-3 py-1 bg-white/10 backdrop-blur-md border border-white/15 text-[10px] font-black uppercase tracking-widest rounded-full text-indigo-200">
                {{ $classroom->name ?? 'Belum Ada Kelas' }}
              </span>
              <h3 class="text-3xl font-black mt-2 tracking-tight">{{ $student->user->name }}</h3>
              <p class="text-sm font-semibold text-gray-300 mt-1 flex items-center gap-4">
                <span>NIS: <strong class="text-white">{{ $student->nis }}</strong></span>
                <span class="opacity-30">|</span>
                <span>NISN: <strong class="text-white">{{ $student->nisn }}</strong></span>
              </p>
              @if($teacher)
                <p class="text-xs text-gray-400 mt-2 flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                  Wali Kelas: <strong class="text-gray-200 font-bold">{{ $teacher->name }}</strong>
                </p>
              @endif
            </div>
          </div>
          
          <!-- Quick Menu Buttons -->
          <div class="flex flex-wrap gap-3 w-full lg:w-auto">
            <a href="{{ route('parent-portal.report-card', $student->id) }}" class="flex-1 lg:flex-none text-center px-6 py-3.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/10 hover:border-white/20 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300">
               Rapor Akademik
            </a>
            <a href="{{ route('parent-portal.daily-assessments', $student->id) }}" class="flex-1 lg:flex-none text-center px-6 py-3.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/10 hover:border-white/20 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300">
               Nilai Harian
            </a>
            <a href="{{ route('parent-portal.schedule', $student->id) }}" class="flex-1 lg:flex-none text-center px-6 py-3.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/10 hover:border-white/20 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300">
               Jadwal Mingguan
            </a>
            <a href="{{ route('parent-portal.attendance', $student->id) }}" class="flex-1 lg:flex-none text-center px-6 py-3.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/10 hover:border-white/20 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300">
               Log Presensi
            </a>
            <a href="{{ route('parent-portal.billings', $student->id) }}" class="flex-1 lg:flex-none text-center px-6 py-3.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/10 hover:border-white/20 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300">
               Tagihan SPP
            </a>
            <a href="{{ route('parent-portal.messages', ['child_id' => $student->id]) }}" class="w-full lg:w-auto text-center px-6 py-3.5 bg-primary-600 hover:bg-primary-500 text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-lg shadow-primary-900/20 transition-all duration-300 flex items-center justify-center gap-2">
               Chat Wali Kelas
            </a>
          </div>
        </div>
      </div>

      <!-- Dashboard Grid: Widgets & Timeline -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Left 2 Columns: 5 Integrated Widgets -->
        <div class="xl:col-span-2 space-y-8">
          
          <!-- 2. Academic & 3. Attendance Widgets Side-by-Side -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Academic Widget -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-150/60 flex flex-col justify-between group hover:shadow-md transition-all duration-300">
              <div>
                <div class="flex justify-between items-center mb-6">
                  <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">Akademik & Perkembangan</h4>
                  <span class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                  </span>
                </div>
                <div class="flex items-baseline gap-2 mb-2">
                  <span class="text-5xl font-black text-gray-900 tracking-tight">{{ $avgScore }}</span>
                  <span class="text-sm font-bold text-gray-400">/ 100</span>
                </div>
                <p class="text-xs font-bold text-gray-500">Nilai Rata-Rata Akademik Siswa</p>
                
                <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-center text-sm">
                  <span class="font-bold text-gray-600">Peringkat Kelas</span>
                  <span class="font-black text-indigo-600 bg-indigo-50 px-3 py-1 rounded-lg text-xs">
                    Rangking {{ $rank }} dari {{ $totalClassStudents }} Siswa
                  </span>
                </div>
              </div>
              <div class="mt-6 flex flex-wrap gap-4 items-center">
                <a href="{{ route('parent-portal.report-card', $student->id) }}" class="text-xs font-black uppercase text-indigo-600 hover:text-indigo-700 tracking-wider flex items-center gap-1">
                  Lihat Rapor Detail
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('parent-portal.daily-assessments', $student->id) }}" class="text-xs font-black uppercase text-indigo-600 hover:text-indigo-700 tracking-wider flex items-center gap-1">
                  Nilai Harian & Remedial
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
              </div>
            </div>

            <!-- Attendance Widget -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-150/60 flex flex-col justify-between group hover:shadow-md transition-all duration-300">
              <div>
                <div class="flex justify-between items-center mb-6">
                  <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">Persentase Kehadiran</h4>
                  <span class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                  </span>
                </div>
                <div class="flex items-center gap-6">
                  <div class="relative w-24 h-24 flex items-center justify-center">
                    <!-- Ring Donut Visual -->
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                      <path class="text-gray-100" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                      <path class="text-emerald-500" stroke-dasharray="{{ $hadirPercentage }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span class="absolute text-lg font-black text-gray-800">{{ $hadirPercentage }}%</span>
                  </div>
                  <div class="space-y-1.5 text-xs text-gray-600">
                    <div class="flex items-center gap-2">
                      <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                      <span>Hadir: <strong>{{ $hadir }} Hari</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                      <span class="w-2.5 h-2.5 bg-amber-500 rounded-full"></span>
                      <span>Sakit/Izin: <strong>{{ $sakit + $izin }} Hari</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                      <span class="w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                      <span>Alpa: <strong class="{{ $alpa > 0 ? 'text-red-600 font-black' : '' }}">{{ $alpa }} Hari</strong></span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="mt-6">
                <a href="{{ route('parent-portal.attendance', $student->id) }}" class="text-xs font-black uppercase text-emerald-600 hover:text-emerald-700 tracking-wider flex items-center gap-1">
                  Detail Kalender Kehadiran
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
              </div>
            </div>

          </div>

          <!-- ApexCharts running grades trend -->
          @if(count($chartData) > 0)
            <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-gray-150/60">
              <h4 class="text-sm font-black text-gray-800 mb-6 uppercase tracking-wider">Grafik Tren Nilai Mata Pelajaran</h4>
              <div id="gradesTrendChart" class="w-full h-72"></div>
            </div>
          @endif

          <!-- 4. Finance Widget -->
          <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-gray-150/60">
            <div class="flex justify-between items-center mb-6">
              <div>
                <h4 class="text-sm font-black text-gray-800 uppercase tracking-wider">Keuangan & SPP Siswa</h4>
                <p class="text-xs text-gray-400 font-semibold mt-1">Daftar tagihan yang harus diselesaikan</p>
              </div>
              <div class="text-right">
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Tunggakan</p>
                <p class="text-2xl font-black {{ $totalUnpaidAmount > 0 ? 'text-red-500' : 'text-emerald-500' }} mt-0.5">
                  Rp {{ number_format($totalUnpaidAmount, 0, ',', '.') }}
                </p>
              </div>
            </div>

            <div class="divide-y divide-gray-100">
              @forelse($unpaidBillings ?? [] as $bill)
                <div class="py-4 flex justify-between items-center group">
                  <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-red-50 text-red-500 rounded-xl flex items-center justify-center">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                      <h5 class="font-extrabold text-sm text-gray-800">{{ $bill->category->name }}</h5>
                      <p class="text-xs text-gray-400 mt-1">Jatuh Tempo: <strong>{{ \Carbon\Carbon::parse($bill->due_date)->isoFormat('D MMMM YYYY') }}</strong></p>
                    </div>
                  </div>
                  <div class="flex items-center gap-4">
                    <span class="text-sm font-black text-gray-800">Rp {{ number_format($bill->remaining_balance, 0, ',', '.') }}</span>
                    <a href="{{ route('parent-portal.billings.checkout', $bill->id) }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-primary-900/10 transition-all duration-300">
                      Bayar
                    </a>
                  </div>
                </div>
              @empty
                <div class="py-6 text-center text-gray-400 italic font-bold">
                   Semua tagihan anak telah lunas diselesaikan.
                </div>
              @endforelse
            </div>
          </div>

          <!-- 5. BK Points & 6. UKS Widgets Side-by-Side -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- BK Points Behavior Widget -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-150/60 flex flex-col justify-between group hover:shadow-md transition-all duration-300">
              <div>
                <div class="flex justify-between items-center mb-6">
                  <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">Catatan Perilaku & BK</h4>
                  <span class="w-8 h-8 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                  </span>
                </div>
                <div class="flex items-baseline gap-2 mb-4">
                  <span class="text-5xl font-black tracking-tight {{ $totalPoints > 50 ? 'text-red-500' : 'text-primary-600' }}">{{ $totalPoints }}</span>
                  <span class="text-sm font-bold text-gray-400">Poin Pelanggaran</span>
                </div>
                
                <div class="space-y-3">
                  <p class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Log Perilaku Terakhir</p>
                  @forelse($recentPointLogs as $log)
                    <div class="flex justify-between items-start gap-3 text-xs border-b border-gray-50 pb-2">
                      <span class="text-gray-600 font-semibold truncate">{{ $log->description }}</span>
                      <span class="font-black whitespace-nowrap {{ $log->points > 0 ? 'text-red-500' : 'text-emerald-500' }}">{{ $log->points > 0 ? "+{$log->points}" : $log->points }} Poin</span>
                    </div>
                  @empty
                    <p class="text-xs text-gray-400 italic">Belum ada catatan pelanggaran.</p>
                  @endforelse
                </div>
              </div>
            </div>

            <!-- UKS Health logs Widget -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-150/60 flex flex-col justify-between group hover:shadow-md transition-all duration-300">
              <div>
                <div class="flex justify-between items-center mb-6">
                  <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">Catatan Kesehatan UKS</h4>
                  <span class="w-8 h-8 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                  </span>
                </div>

                @if($latestUksVisit)
                  <div class="space-y-4">
                    <div>
                      <p class="text-[10px] font-black text-teal-600 uppercase tracking-widest">Kunjungan Terakhir</p>
                      <p class="text-sm font-extrabold text-gray-800 mt-1">{{ \Carbon\Carbon::parse($latestUksVisit->visit_time)->isoFormat('D MMMM YYYY HH:mm') }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-xs">
                      <div class="bg-teal-50/50 p-2.5 rounded-xl border border-teal-100/50">
                        <span class="text-gray-400 font-semibold block uppercase text-[9px]">Keluhan</span>
                        <strong class="text-teal-800 font-black block mt-0.5">{{ $latestUksVisit->complaint }}</strong>
                      </div>
                      <div class="bg-teal-50/50 p-2.5 rounded-xl border border-teal-100/50">
                        <span class="text-gray-400 font-semibold block uppercase text-[9px]">Tindakan</span>
                        <strong class="text-teal-800 font-black block mt-0.5">{{ $latestUksVisit->treatment }}</strong>
                      </div>
                    </div>
                    @if(count($latestUksVisit->medicines) > 0)
                      <div>
                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Obat Diberikan:</span>
                        <div class="flex flex-wrap gap-1.5 mt-1.5">
                          @foreach($latestUksVisit->medicines as $med)
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-700 text-[10px] font-bold rounded-lg border border-gray-200">
                              {{ $med->name }} ({{ $med->pivot->quantity }} Pcs)
                            </span>
                          @endforeach
                        </div>
                      </div>
                    @endif
                  </div>
                @else
                  <div class="py-8 text-center text-gray-400 italic">
                    Siswa tidak pernah dicatat masuk UKS.
                  </div>
                @endif
              </div>
            </div>

          </div>

          <!-- Counseling Sessions (BK Terjadwal/Selesai) -->
          <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-gray-150/60">
            <div class="flex justify-between items-center mb-6">
              <div>
                <h4 class="text-sm font-black text-gray-800 uppercase tracking-wider">Jadwal Bimbingan Konseling</h4>
                <p class="text-xs text-gray-400 font-semibold mt-1">Sesi bimbingan terjadwal & non-konfidensial</p>
              </div>
              <span class="px-2.5 py-1 bg-primary-50 text-primary-600 rounded-lg text-xs font-black uppercase tracking-wider">
                General
              </span>
            </div>

            <div class="space-y-4">
              @forelse($counselingSessions as $session)
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex justify-between items-center">
                  <div>
                    <h5 class="text-sm font-extrabold text-gray-800 flex items-center gap-2">
                      <span>Sesi {{ $session->category }}</span>
                      <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest {{ $session->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $session->status === 'completed' ? 'Selesai' : 'Terjadwal' }}
                      </span>
                    </h5>
                    <p class="text-xs text-gray-500 mt-1">
                      Konselor: <strong class="text-gray-700 font-bold">{{ $session->counselor->name }}</strong>
                    </p>
                    @if($session->scheduled_at)
                      <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ $session->scheduled_at->isoFormat('D MMMM YYYY HH:mm') }}
                      </p>
                    @endif
                  </div>
                </div>
              @empty
                <div class="py-6 text-center text-gray-400 italic">
                  Tidak ada jadwal bimbingan konseling saat ini.
                </div>
              @endforelse
            </div>
          </div>

        </div>

        <!-- Right 1 Column: Integrated Chronological Activity Timeline Feed -->
        <div class="xl:col-span-1">
          <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-150/60 sticky top-6">
            <h4 class="text-lg font-black text-gray-800 tracking-tight flex items-center gap-2.5">
              <span class="w-2.5 h-6 bg-primary-600 rounded-full"></span>
              Feed Aktivitas Siswa
            </h4>
            <p class="text-xs text-gray-400 font-semibold mt-1 mb-8">Riwayat gabungan aktivitas kronologis real-time</p>

            <!-- Timeline Body -->
            <div class="flow-root">
              <ul role="list" class="-mb-8">
                @forelse($sortedTimeline as $item)
                  <li>
                    <div class="relative pb-8">
                      @if(!$loop->last)
                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-100" aria-hidden="true"></span>
                      @endif
                      <div class="relative flex space-x-3 items-start">
                        <div>
                          <span class="h-8 w-8 rounded-xl bg-{{ $item['color'] }}-50 flex items-center justify-center ring-8 ring-white">
                            {!! $item['icon'] !!}
                          </span>
                        </div>
                        <div class="flex-1 min-w-0 pt-0.5">
                          <div class="flex justify-between items-start gap-4">
                            <h5 class="text-sm font-extrabold text-gray-800 tracking-tight leading-tight">{{ $item['title'] }}</h5>
                            <span class="text-[10px] font-bold text-gray-400 whitespace-nowrap">
                              {{ \Carbon\Carbon::parse($item['date'])->diffForHumans() }}
                            </span>
                          </div>
                          <p class="text-xs text-gray-500 leading-relaxed mt-1.5">{{ $item['description'] }}</p>
                          <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest block mt-2">
                            {{ \Carbon\Carbon::parse($item['date'])->isoFormat('D MMM YYYY HH:mm') }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </li>
                @empty
                  <div class="py-12 text-center text-gray-400 italic">
                    Belum ada aktivitas terekam.
                  </div>
                @endforelse
              </ul>
            </div>
          </div>
        </div>

      </div>
      
    </div>
  </div>

  <!-- Scripts for ApexCharts -->
  @if(count($chartData) > 0)
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var options = {
          series: [{
            name: 'Nilai Akademik',
            data: @json($chartData)
          }],
          chart: {
            type: 'area',
            height: 280,
            fontFamily: 'Inter, sans-serif',
            toolbar: {
              show: false
            },
            zoom: {
              enabled: false
            }
          },
          colors: ['#4f46e5'],
          fill: {
            type: 'gradient',
            gradient: {
              shadeIntensity: 1,
              opacityFrom: 0.45,
              opacityTo: 0.05,
              stops: [0, 100]
            }
          },
          dataLabels: {
            enabled: true,
            style: {
              fontSize: '10px',
              fontFamily: 'Inter, sans-serif',
              fontWeight: 'bold',
              colors: ['#4f46e5']
            },
            background: {
              enabled: true,
              foreColor: '#ffffff',
              borderRadius: 6,
              padding: 4,
              borderWidth: 1,
              borderColor: '#4f46e5'
            }
          },
          stroke: {
            curve: 'smooth',
            width: 3
          },
          grid: {
            borderColor: '#f3f4f6',
            strokeDashArray: 4,
            padding: {
              left: 10,
              right: 10
            }
          },
          xaxis: {
            categories: @json($chartLabels),
            labels: {
              style: {
                colors: '#9ca3af',
                fontSize: '10px',
                fontWeight: 'bold'
              }
            },
            axisBorder: {
              show: false
            },
            axisTicks: {
              show: false
            }
          },
          yaxis: {
            min: 0,
            max: 100,
            labels: {
              style: {
                colors: '#9ca3af',
                fontSize: '10px',
                fontWeight: 'bold'
              }
            }
          }
        };

        var chart = new ApexCharts(document.querySelector("#gradesTrendChart"), options);
        chart.render();
      });
    </script>
  @endif
</x-app-layout>
