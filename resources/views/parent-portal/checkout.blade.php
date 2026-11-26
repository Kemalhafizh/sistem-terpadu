<x-app-layout>
  <x-slot name="header">
    <h2 class="font-black text-2xl text-gray-900 dark:text-white leading-tight">
       Portal Pembayaran <span class="text-primary-600 dark:text-primary-400">Snap Checkout</span>
    </h2>
  </x-slot>

  <div class="py-12 bg-gray-50/50 dark:bg-slate-900/50 min-h-[calc(100vh-4rem)]">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
      <!-- Info Gateway Pembayaran -->
      <div class="bg-emerald-700 rounded-2xl p-6 text-white shadow-md">
        <div class="flex items-start space-x-4">
          <div class="p-3 bg-white/15 rounded-xl text-white shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div>
            <h4 class="font-bold text-lg tracking-tight">Mode Simulasi Pembayaran Lokal</h4>
            <p class="text-emerald-50 font-medium text-xs mt-1.5 leading-relaxed">
              Portal terintegrasi dengan Midtrans Snap SDK apabila server key tersedia dan aktif. Untuk pengembangan luring atau token simulasi, pembayaran diarahkan ke halaman Mock Payment Gateway.
            </p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
        <!-- Rincian Pembayaran Card -->
        <div class="md:col-span-3 bg-white rounded-[2.5rem] p-8 shadow-2xl border border-gray-200/60 relative overflow-hidden flex flex-col justify-between group hover:shadow-md transition-all duration-300">
          <div class="absolute top-0 right-0 w-40 h-40 bg-indigo-50/30 rounded-bl-[6rem] -mr-16 -mt-16 -z-0"></div>
          
          <div class="relative z-10">
            <div class="flex justify-between items-center mb-8">
              <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-4 py-1.5 rounded-full">Detail Tagihan Anak</span>
              <span class="text-xs font-bold text-gray-400">Tahun Ajaran Aktif</span>
            </div>
            
            <h4 class="text-3xl font-black text-gray-800 tracking-tight mb-2">{{ $billing?->category?->name }}</h4>
            <p class="text-gray-400 text-xs font-semibold mb-6 flex items-center">
              <svg class="w-4.5 h-4.5 mr-1.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              Jatuh Tempo: {{ \Carbon\Carbon::parse($billing->due_date)->isoFormat('D MMMM YYYY') }}
            </p>

            <div class="space-y-4 border-t border-gray-100 pt-6">
              <div class="flex justify-between items-center py-1">
                <span class="text-gray-400 font-bold text-xs uppercase tracking-wider">Nama Anak</span>
                <span class="font-extrabold text-gray-800 text-sm">{{ $student->user->name }}</span>
              </div>
              <div class="flex justify-between items-center py-1">
                <span class="text-gray-400 font-bold text-xs uppercase tracking-wider">Kelas</span>
                <span class="font-extrabold text-gray-700 text-sm">{{ $student->classroom->name ?? '-' }}</span>
              </div>
              <div class="flex justify-between items-center py-1">
                <span class="text-gray-400 font-bold text-xs uppercase tracking-wider">Jumlah Awal</span>
                <span class="font-extrabold text-gray-700 text-sm">Rp {{ number_format($billing->amount, 0, ',', '.') }}</span>
              </div>
              <div class="flex justify-between items-center py-1 border-b border-gray-50 pb-4">
                <span class="text-gray-400 font-bold text-xs uppercase tracking-wider">Sisa Tunggakan</span>
                <span class="font-extrabold text-indigo-600 text-sm">Rp {{ number_format($billing->remaining_balance, 0, ',', '.') }}</span>
              </div>
              <div class="flex justify-between items-center pt-4">
                <span class="text-gray-800 font-black text-sm uppercase tracking-wider">Total Pembayaran</span>
                <span class="text-3xl font-black text-gray-900 tracking-tight">Rp {{ number_format($billing->remaining_balance, 0, ',', '.') }}</span>
              </div>
            </div>
          </div>

          <div class="mt-8 relative z-10">
            <button id="pay-button" class="w-full bg-gradient-to-r from-primary-600 to-indigo-600 text-white text-center py-4 rounded-2xl font-black text-sm shadow-xl shadow-indigo-900/10 hover:from-primary-500 hover:to-indigo-500 active:scale-[0.99] transition-all flex items-center justify-center space-x-2">
              <span>PROSES PEMBAYARAN ONLINE</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
            <p class="text-[10px] text-center text-gray-400 mt-4 font-bold uppercase tracking-wider flex items-center justify-center">
              <svg class="w-4 h-4 mr-1.5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
              </svg>
              Koneksi Sandbox Terenkripsi SSL
            </p>
          </div>
        </div>

        <!-- Guide / Billing Status Card -->
        <div class="md:col-span-2 bg-slate-900 text-white rounded-3xl p-8 shadow-lg border border-slate-800 flex flex-col justify-between relative overflow-hidden">
          <div class="relative z-10 space-y-6">
            <div class="flex items-center space-x-2">
              <span class="w-2.5 h-2.5 bg-indigo-400 rounded-full"></span>
              <span class="text-[10px] font-black text-indigo-300 uppercase tracking-widest bg-indigo-950/60 border border-indigo-800/40 px-3 py-1 rounded-full">INFO TRANSAKSI</span>
            </div>

            <div>
              <h4 class="text-xl font-extrabold tracking-tight">Metode Instan</h4>
              <p class="text-slate-400 text-xs mt-1.5 leading-relaxed font-medium">
                Dengan menekan tombol proses, sistem akan memicu popup Midtrans Snap. Anda dapat membayar menggunakan QRIS (Gopay/ShopeePay), Virtual Account Bank (BCA, Mandiri, BNI, BRI), atau Alfamart/Indomaret.
              </p>
            </div>

            <div class="bg-slate-950/80 rounded-2xl p-5 border border-slate-800/50 space-y-3 font-mono text-[10px] text-slate-300">
              <div class="flex justify-between">
                <span class="text-slate-500">Order ID:</span>
                <span class="text-indigo-400 font-bold">BILL-{{ $billing->id }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Snap Token:</span>
                <span class="text-cyan-400 truncate max-w-[120px]">{{ $billing->snap_token }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Status Awal:</span>
                <span class="text-amber-400 uppercase font-black text-[9px]">{{ $billing->status }}</span>
              </div>
            </div>

            <div class="pt-2 text-xs text-slate-400 italic">
               *Pastikan Anda tidak menutup halaman checkout sebelum konfirmasi transaksi sukses dikirimkan ke server sekolah.*
            </div>
          </div>

          <div class="text-[9px] text-slate-500 mt-8 font-semibold tracking-widest uppercase border-t border-slate-800/40 pt-4 relative z-10 text-center">
            Secure Checkout System
          </div>
        </div>
      </div>

      <div class="text-center">
        <a href="{{ route('parent-portal.billings', $student) }}" class="text-xs font-bold text-gray-400 hover:text-indigo-600 uppercase tracking-widest transition-colors">← Kembali ke Rincian Keuangan</a>
      </div>
    </div>
  </div>

  @push('scripts')
  <!-- Integrasi SDK Snap JS Midtrans -->
  <x-midtrans-snap />
  <!-- SweetAlert2 untuk Notifikasi -->
  <x-vendor.script lib="sweetalert2" />
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const payButton = document.getElementById('pay-button');
      const snapToken = "{{ $billing->snap_token }}";
      const isDemoToken = snapToken.startsWith('TKN-DEMO-');

      payButton.addEventListener('click', function (e) {
        e.preventDefault();

        if (isDemoToken) {
          Swal.fire({
            title: 'Mode Simulasi Pembayaran ',
            html: `Anda berada dalam mode pengembangan lokal offline.<br><br>Sistem akan mengalihkan Anda ke <strong>Mock Payment Gateway</strong> untuk merampungkan transaksi simulasi.`,
            icon: 'info',
            confirmButtonText: 'Lanjutkan ke Mock Gateway',
            confirmButtonColor: '#4f46e5',
            showCancelButton: true,
            cancelButtonText: 'Batal',
            customClass: {
              popup: 'rounded-[2rem]',
              confirmButton: 'rounded-xl font-bold py-3 px-6 mr-2',
              cancelButton: 'rounded-xl font-bold py-3 px-6'
            }
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = "{{ route('parent-portal.billings.mock-gateway', $billing->id) }}";
            }
          });
          return;
        }

        // Jika token asli, panggil Snap JS SDK
        window.snap.pay(snapToken, {
          onSuccess: function (result) {
            Swal.fire({
              title: 'Pembayaran Berhasil! ',
              text: 'Sistem sedang memproses konfirmasi pembayaran.',
              icon: 'success',
              showConfirmButton: false,
              timer: 2000,
              customClass: { popup: 'rounded-[2rem]' }
            }).then(() => {
              window.location.href = "{{ route('parent-portal.billings', $student) }}";
            });
          },
          onPending: function (result) {
            Swal.fire({
              title: 'Pembayaran Tertunda ',
              text: 'Silakan selesaikan pembayaran Anda di gerbang pembayaran Midtrans.',
              icon: 'warning',
              confirmButtonText: 'OK',
              confirmButtonColor: '#4f46e5',
              customClass: { popup: 'rounded-[2rem]' }
            });
          },
          onError: function (result) {
            Swal.fire({
              title: 'Pembayaran Gagal ',
              text: 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.',
              icon: 'error',
              confirmButtonText: 'Tutup',
              confirmButtonColor: '#ef4444',
              customClass: { popup: 'rounded-[2rem]' }
            });
          },
          onClose: function () {
            console.log('Customer closed the popup without finishing the payment');
          }
        });
      });
    });
  </script>
  @endpush
</x-app-layout>
