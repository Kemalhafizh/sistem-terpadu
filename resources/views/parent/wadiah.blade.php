<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h2 class="font-black text-3xl text-gray-900 dark:text-white leading-tight tracking-tight">
          Portal Orang Tua <span class="bg-gradient-to-r from-primary-600 to-indigo-600 bg-clip-text text-transparent">Premium</span>
        </h2>
        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
      </div>
      
      <!-- Child Switcher Tab -->
      @php
        $children = \App\Models\Student::where('parent_id', auth()->id())->with('user')->get();
      @endphp
      @if(count($children) > 0)
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-1.5 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 flex gap-2 shadow-sm">
          @foreach($children as $child)
            <a href="{{ route('parent-portal.wallet', ['student' => $child->id]) }}" 
              class="px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-300 flex items-center gap-2 {{ $student->id === $child->id ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
              <span class="w-2.5 h-2.5 rounded-full {{ $student->id === $child->id ? 'bg-white' : 'bg-primary-505' }}"></span>
              {{ $child->user->name }}
            </a>
          @endforeach
        </div>
      @endif
    </div>
  </x-slot>

  <!-- Content Area -->
  <div class="py-8 bg-gray-50/50 dark:bg-slate-900/20 min-h-screen">
    <div class="w-full px-4 sm:px-6 lg:px-8 space-y-10">
      
      @if(session('success'))
        <div class="bg-emerald-600 text-white p-4 rounded-2xl shadow-xl flex items-center animate-fade-in">
          <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          <span class="font-bold">{{ session('success') }}</span>
        </div>
      @endif

      @if($errors->any())
        <div class="bg-rose-600 text-white p-4 rounded-2xl shadow-xl space-y-1">
          @foreach($errors->all() as $error)
            <p class="font-bold flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
              {{ $error }}
            </p>
          @endforeach
        </div>
      @endif

      <!-- Profile & Quick Links Header Section -->
      @php
        $classroom = $student->classroom;
        $teacher = $classroom?->teacher;
      @endphp
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
            <a href="{{ route('parent-portal.dashboard', ['child_id' => $student->id]) }}" class="flex-1 lg:flex-none text-center px-6 py-3.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/10 hover:border-white/20 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300">
               Dashboard
            </a>
            <a href="{{ route('parent-portal.report-card', $student->id) }}" class="flex-1 lg:flex-none text-center px-6 py-3.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/10 hover:border-white/20 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300">
               Rapor Detail
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
            <a href="{{ route('parent-portal.wallet', $student->id) }}" class="flex-1 lg:flex-none text-center px-6 py-3.5 bg-primary-600 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300">
               Wadiah Wallet
            </a>
            <a href="{{ route('parent-portal.messages', ['child_id' => $student->id]) }}" class="w-full lg:w-auto text-center px-6 py-3.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/10 hover:border-white/20 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300 flex items-center justify-center gap-2">
               Chat Wali Kelas
            </a>
          </div>
        </div>
      </div>

      <!-- Dashboard Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Grid: Wallet Card (lg:col-span-7) -->
        <div class="lg:col-span-7 space-y-8">
          
          <!-- Wallet Display -->
          <div class="bg-gradient-to-br from-slate-900 to-indigo-950 rounded-[2.5rem] p-8 md:p-10 shadow-2xl text-white relative overflow-hidden group border border-white/10">
            <div class="absolute -right-24 -bottom-24 w-96 h-96 bg-primary-600/10 rounded-full blur-3xl"></div>
            <div class="absolute -left-20 -top-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 flex flex-col justify-between h-full min-h-[220px]">
              <div class="flex justify-between items-start">
                <div>
                  <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full">
                    Tabungan Wadiah Anak
                  </span>
                  <h3 class="text-xs font-semibold text-slate-400 mt-4 uppercase tracking-wider">Nomor Rekening</h3>
                  <p class="text-lg font-mono font-bold tracking-widest text-slate-200 mt-1">
                    {{ implode(' ', str_split($account->account_number, 4)) }}
                  </p>
                </div>
                <div class="w-12 h-12 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center backdrop-blur-md">
                  <svg class="w-6 h-6 text-primary-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75H21m0 0v-.375c0-.621-.504-1.125-1.125-1.125H3.75m16.5 0h.008v.008h-.008V4.5zm0 12h.008v.008h-.008V16.5zm0-6h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                  </svg>
                </div>
              </div>
              
              <div class="my-6">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Saldo E-Wallet</span>
                <h2 class="text-4xl md:text-5xl font-black tracking-tight text-white mt-1 flex items-baseline gap-1">
                  <span class="text-xl font-bold text-slate-400">Rp</span>
                  <span>{{ number_format($account->balance, 0, ',', '.') }}</span>
                </h2>
              </div>
              
              <div class="border-t border-white/10 pt-4 flex justify-between items-center text-xs">
                <div>
                  <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Status Akun</span>
                  <span class="inline-flex items-center gap-1 mt-1 text-emerald-400 font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ $account->is_active ? 'AKTIF' : 'NONAKTIF' }}
                  </span>
                </div>
                <div class="text-right">
                  <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Siswa</span>
                  <span class="font-bold text-white block mt-0.5">{{ $student->user->name }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Grid: Spending Limit Form (lg:col-span-5) -->
        <div class="lg:col-span-5">
          <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-xl border border-slate-100 dark:border-gray-700/60 flex flex-col justify-between h-full">
            <div>
              <span class="text-[10px] font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest block">Kontrol Orang Tua</span>
              <h3 class="text-xl font-black text-gray-900 dark:text-white mt-1">Batasan Belanja Harian</h3>
              <p class="text-xs text-gray-400 dark:text-gray-400 mt-1 mb-6">Atur limit pengeluaran harian jajan cashless anak Anda</p>
              
              @php
                $limit = (float) $account->daily_spending_limit;
                $remaining = (float) $account->remaining_daily_limit;
                $spentToday = max(0, $limit - $remaining);
                $percentSpent = $limit > 0 ? min(100, ($spentToday / $limit) * 100) : 0;
              @endphp
              
              <!-- Spending Limit Visual Progress -->
              <div class="mb-6">
                @if($limit > 0)
                  <div class="bg-slate-50 dark:bg-gray-900/40 border border-slate-100 dark:border-gray-700/60 p-4 rounded-2xl space-y-3">
                    <div class="flex justify-between items-center text-[10px]">
                      <span class="font-black text-slate-400 uppercase tracking-wider">Pemakaian Hari Ini</span>
                      <span class="font-black text-primary-600 dark:text-primary-400">{{ round($percentSpent) }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                      <div class="bg-gradient-to-r from-primary-500 to-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ $percentSpent }}%"></div>
                    </div>
                    <div class="flex justify-between items-center text-[10px] font-bold text-gray-500 dark:text-gray-400">
                      <span>Terpakai: Rp {{ number_format($spentToday, 0, ',', '.') }}</span>
                      <span>Sisa: Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                    </div>
                  </div>
                @else
                  <div class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200/50 dark:border-emerald-900/30 p-4 rounded-2xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div class="text-[11px]">
                      <h4 class="font-black text-emerald-800 dark:text-emerald-400 uppercase tracking-widest">Bebas Tanpa Batasan</h4>
                      <p class="text-gray-500 dark:text-emerald-500 font-semibold mt-0.5">Siswa dapat bertransaksi tanpa batasan limit harian.</p>
                    </div>
                  </div>
                @endif
              </div>

              <!-- Limit Update Form -->
              <form action="{{ route('parent-portal.wallet.limit', $student->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                  <label for="daily_spending_limit" class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2">Atur Limit Baru</label>
                  <div class="relative rounded-2xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                      <span class="text-gray-500 dark:text-gray-400 text-sm font-bold">Rp</span>
                    </div>
                    <input type="number" 
                        name="daily_spending_limit" 
                        id="daily_spending_limit" 
                        value="{{ old('daily_spending_limit', (int) $account->daily_spending_limit) }}" 
                        class="block w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-gray-900 border border-slate-200 dark:border-gray-700/80 rounded-2xl text-sm font-bold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" 
                        placeholder="Contoh: 30000" 
                        min="0">
                  </div>
                  <p class="mt-2 text-[10px] font-semibold text-gray-400 dark:text-gray-500 leading-normal">
                    Setel ke 0 jika ingin menonaktifkan batasan jajan harian siswa.
                  </p>
                </div>
                <button type="submit" class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-lg shadow-primary-900/15 transition-all flex items-center justify-center gap-2">
                  Simpan Batasan
                </button>
              </form>
            </div>
          </div>
        </div>

      </div>

      <!-- Full Width Bottom Grid: Transaction Table with Order Breakdown -->
      <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-slate-150/60 dark:border-gray-700/60 animate-fade-in-up">
        <div class="flex justify-between items-center mb-6">
          <div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Mutasi Rekening Anak</span>
            <h3 class="text-xl font-black text-gray-900 dark:text-white mt-1">Riwayat Transaksi</h3>
          </div>
          <span class="px-4 py-1.5 bg-slate-50 dark:bg-gray-900 text-gray-500 dark:text-slate-400 rounded-xl text-xs font-bold border border-slate-100 dark:border-gray-800">
            {{ $transactions->total() }} Transaksi Terdaftar
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-100 dark:border-gray-700/80 text-[10px] font-black uppercase tracking-widest text-slate-400">
                <th class="pb-4 pl-4">Tanggal & Waktu</th>
                <th class="pb-4">No. Referensi</th>
                <th class="pb-4">Deskripsi</th>
                <th class="pb-4">Detail Belanja (Kantin)</th>
                <th class="pb-4">Tipe</th>
                <th class="pb-4 pr-4 text-right">Jumlah</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/60 dark:divide-gray-700/50">
              @forelse($transactions as $transaction)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/30 transition-all">
                  <!-- Date -->
                  <td class="py-4 pl-4">
                    <p class="text-sm font-black text-gray-800 dark:text-slate-100">
                      {{ $transaction->created_at->translatedFormat('d M Y') }}
                    </p>
                    <p class="text-[11px] font-semibold text-gray-400 dark:text-slate-500">
                      {{ $transaction->created_at->format('H:i') }}
                    </p>
                  </td>
                  <!-- Reference -->
                  <td class="py-4">
                    <span class="text-xs font-mono font-bold text-gray-500 dark:text-slate-400 bg-slate-100 dark:bg-gray-900 px-2.5 py-1 rounded-lg">
                      {{ $transaction->reference_number }}
                    </span>
                  </td>
                  <!-- Description -->
                  <td class="py-4 text-sm font-semibold text-gray-600 dark:text-slate-300">
                    {{ $transaction->description }}
                  </td>
                  <!-- Order Items Breakdown -->
                  <td class="py-4">
                    @if($transaction->type === 'purchase' && $transaction->canteenTransaction)
                      @php
                        $itemsOrdered = $transaction->canteenTransaction->items_ordered;
                      @endphp
                      @if(is_array($itemsOrdered) && count($itemsOrdered) > 0)
                        <div class="flex flex-col gap-1 max-w-[200px]">
                          @foreach($itemsOrdered as $item)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 dark:text-slate-400">
                              <span class="w-1.5 h-1.5 rounded-full bg-primary-400"></span>
                              {{ $item['name'] ?? '' }} 
                              <strong class="text-gray-800 dark:text-slate-200">x{{ $item['qty'] ?? 1 }}</strong>
                            </span>
                          @endforeach
                        </div>
                      @else
                        <span class="text-xs italic text-gray-400 dark:text-slate-500">Item tidak tercatat</span>
                      @endif
                    @else
                      <span class="text-xs text-gray-400 dark:text-slate-500">-</span>
                    @endif
                  </td>
                  <!-- Type -->
                  <td class="py-4">
                    @if($transaction->type === 'deposit')
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/30">
                        SETORAN
                      </span>
                    @elseif($transaction->type === 'withdrawal')
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/30">
                        PENARIKAN
                      </span>
                    @elseif($transaction->type === 'purchase')
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-900/30">
                        BELANJA
                      </span>
                    @else
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-50 text-slate-700 dark:bg-slate-900 dark:text-slate-400 border border-slate-200/50">
                        {{ strtoupper($transaction->type) }}
                      </span>
                    @endif
                  </td>
                  <!-- Amount -->
                  <td class="py-4 pr-4 text-right">
                    <strong class="text-base font-black {{ $transaction->type === 'deposit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                      {{ $transaction->type === 'deposit' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </strong>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="py-12 text-center text-gray-400 dark:text-gray-500 italic font-bold">
                    Belum ada riwayat transaksi pada rekening ini.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination Links -->
        <div class="mt-6">
          {{ $transactions->links() }}
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
