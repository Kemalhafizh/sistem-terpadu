<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Dashboard SPMB (Sistem Penerimaan Murid Baru)') }}
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="w-full px-4 sm:px-6 lg:px-8">

      @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
          <span class="block sm:inline font-medium">{{ session('success') }}</span>
        </div>
      @endif

      <!-- Navigation bar quick links -->
      <div class="mb-8 flex flex-wrap gap-2">
        <a href="{{ route('spmb.admin.periods') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-sm transition-all"> Kelola Periode</a>
        <a href="{{ route('spmb.admin.waves') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-sm transition-all"> Kelola Gelombang</a>
        <a href="{{ route('spmb.admin.tracks') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-sm transition-all"> Kelola Jalur</a>
        <a href="{{ route('spmb.admin.index') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-sm transition-all"> Verifikasi Dokumen (Kanban)</a>
      </div>

      <!-- Stats grid -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Stat 1 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
          <div>
            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pendaftar</span>
            <strong class="text-3xl font-black text-slate-900 mt-1 block">{{ $applicants->count() }}</strong>
          </div>
          <span class="text-3xl bg-slate-50 p-3 rounded-2xl border border-slate-100"></span>
        </div>
        <!-- Stat 2 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
          <div>
            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Diterima</span>
            <strong class="text-3xl font-black text-emerald-600 mt-1 block">{{ $applicants->where('status', 'accepted')->count() }}</strong>
          </div>
          <span class="text-3xl bg-emerald-50 p-3 rounded-2xl border border-emerald-100"></span>
        </div>
        <!-- Stat 3 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
          <div>
            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Pending / Review</span>
            <strong class="text-3xl font-black text-blue-600 mt-1 block">{{ $applicants->where('status', 'pending')->count() + $applicants->where('status', 'reviewing')->count() }}</strong>
          </div>
          <span class="text-3xl bg-blue-50 p-3 rounded-2xl border border-blue-100"></span>
        </div>
        <!-- Stat 4 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
          <div>
            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Daftar Ulang</span>
            <strong class="text-3xl font-black text-indigo-600 mt-1 block">{{ $applicants->where('has_reregistered', true)->count() }}</strong>
          </div>
          <span class="text-3xl bg-indigo-50 p-3 rounded-2xl border border-indigo-100"></span>
        </div>
      </div>

      <!-- Waves Status Table / Panel -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Column Left: Waves status and actions -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="p-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <h3 class="font-black text-slate-800 text-sm">Status Gelombang Aktif</h3>
          </div>
          <div class="p-6">
            @forelse($waves ?? [] as $wave)
              <div class="p-4 rounded-xl border border-slate-200/60 shadow-sm mb-4 last:mb-0 flex justify-between items-center flex-wrap gap-4">
                <div>
                  <div class="flex items-center gap-2">
                    <h4 class="font-bold text-slate-900 text-sm">{{ $wave->name }}</h4>
                    @if($wave->status === 'open')
                      <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[9px] font-black rounded-full border border-emerald-200">OPEN</span>
                    @elseif($wave->status === 'closed')
                      <span class="px-2 py-0.5 bg-red-50 text-red-700 text-[9px] font-black rounded-full border border-red-200">CLOSED</span>
                    @else
                      <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[9px] font-black rounded-full border border-slate-200">{{ strtoupper($wave->status) }}</span>
                    @endif
                  </div>
                  <p class="text-xs text-slate-400 mt-1">Registrasi: {{ $wave->registration_start->format('d M') }} s/d {{ $wave->registration_end->format('d M Y') }}</p>
                  <p class="text-xs text-slate-500 font-medium mt-1">Sisa Kuota: {{ $wave->quota - $applicants->where('spmb_wave_id', $wave->id)->count() }} dari {{ $wave->quota }} kursi</p>
                </div>
                <div class="flex gap-2">
                  <a href="{{ route('spmb.admin.scoring', $wave->id) }}" class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-bold hover:bg-black transition-all"> Input Nilai</a>
                  <a href="{{ route('spmb.admin.selection', $wave->id) }}" class="px-3 py-1.5 bg-emerald-650 text-emerald-800 bg-emerald-50 border border-emerald-250 rounded-lg text-xs font-bold hover:bg-emerald-100 transition-all"> Jalankan Seleksi</a>
                  <a href="{{ route('spmb.admin.reregistration', $wave->id) }}" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-bold hover:bg-indigo-100 transition-all"> Daftar Ulang</a>
                </div>
              </div>
            @empty
              <div class="text-center text-xs text-slate-400 py-8">Belum ada gelombang pendaftaran yang dibuat.</div>
            @endforelse
          </div>
        </div>

        <!-- Column Right: Active Period Info -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
          <div>
            <div class="p-5 border-b border-slate-100 bg-slate-50">
              <h3 class="font-black text-slate-800 text-sm">Periode Tahun Ajaran Aktif</h3>
            </div>
            <div class="p-6 text-center">
              <span class="text-4xl bg-emerald-50 p-4 rounded-full border border-emerald-150 inline-block mb-3"></span>
              @if($activeYear)
                <h4 class="font-black text-slate-800 text-lg leading-tight">{{ $activeYear->year }}</h4>
                <p class="text-xs text-slate-400 font-semibold uppercase mt-1">Semester {{ $activeYear->semester }}</p>
                <hr class="border-slate-100 my-4" />
                <div class="text-xs text-slate-500 space-y-1 text-left font-inter">
                  <div class="flex justify-between"><span>Total Kuota SPMB:</span> <strong>{{ $periods->where('academic_year_id', $activeYear->id)->first()->total_quota ?? '-' }} Siswa</strong></div>
                  <div class="flex justify-between"><span>Status Portal:</span> <strong class="text-emerald-600">{{ strtoupper($periods->where('academic_year_id', $activeYear->id)->first()->status ?? 'N/A') }}</strong></div>
                </div>
              @else
                <h4 class="font-black text-slate-400 text-base leading-tight">Tidak Ada TA Aktif</h4>
                <p class="text-[10px] text-slate-400 mt-1">Silakan aktifkan tahun ajaran baru terlebih dahulu.</p>
              @endif
            </div>
          </div>
          <div class="p-4 bg-slate-50 border-t border-slate-100 text-center">
            <a href="{{ route('spmb.admin.periods') }}" class="text-xs font-black text-emerald-600 hover:text-emerald-700">Setup Tahun Ajaran & Kuota &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Recent Applicants Table -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50">
          <h3 class="font-black text-slate-800 text-sm">Pendaftar Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-150 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                <th class="p-4">No. Pendaftaran</th>
                <th class="p-4">Nama Lengkap</th>
                <th class="p-4">Sekolah Asal</th>
                <th class="p-4">Jalur / Gelombang</th>
                <th class="p-4">Skor Ujian / Seleksi</th>
                <th class="p-4">Status</th>
                <th class="p-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="text-xs divide-y divide-slate-100">
              @forelse($applicants->take(10) ?? [] as $app)
                <tr class="hover:bg-slate-50 transition-colors">
                  <td class="p-4 font-bold text-slate-500">{{ $app->registration_number }}</td>
                  <td class="p-4 font-black text-slate-900">
                    <div class="flex items-center gap-2">
                      @if($app->photo_path)
                        <img src="{{ asset('storage/' . $app->photo_path) }}" alt="Photo" class="h-6 w-6 rounded-full object-cover">
                      @endif
                      {{ $app->full_name }}
                    </div>
                  </td>
                  <td class="p-4 font-inter text-slate-500">{{ $app->previous_school }}</td>
                  <td class="p-4">
                    <div class="font-semibold text-slate-700">{{ $app->spmbTrack->name ?? 'Reguler' }}</div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $app->spmbWave->name ?? 'Gelombang 1' }}</div>
                  </td>
                  <td class="p-4">
                    @if($app->total_score)
                      <span class="font-black text-slate-800">{{ $app->total_score }}</span>
                      <span class="text-[10px] text-slate-400"> (Rank #{{ $app->rank ?? '-' }})</span>
                    @else
                      <span class="text-slate-300">Belum Dinilai</span>
                    @endif
                  </td>
                  <td class="p-4">
                    @if($app->status === 'accepted')
                      <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-lg border border-emerald-150">DITERIMA</span>
                    @elseif($app->status === 'rejected')
                      <span class="px-2.5 py-1 bg-red-50 text-red-700 text-[10px] font-black rounded-lg border border-red-150">DITOLAK</span>
                    @else
                      <span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-[10px] font-black rounded-lg border border-amber-150">{{ strtoupper($app->status) }}</span>
                    @endif
                  </td>
                  <td class="p-4 text-center">
                    <a href="{{ route('spmb.admin.show', $app->id) }}" class="px-3 py-1 bg-white hover:bg-slate-50 border border-slate-200 text-[11px] font-bold rounded-lg transition-all">Detail</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="p-8 text-center text-slate-400">Belum ada calon murid yang mendaftar.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
