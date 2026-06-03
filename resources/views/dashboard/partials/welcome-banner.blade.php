<div class="bg-gradient-to-r from-primary-800 to-primary-600 rounded-[2rem] p-8 shadow-xl shadow-primary-900/20 text-white relative overflow-hidden">
    <!-- Abstract Shape -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-20 -mt-20"></div>
    
    <div class="relative z-10">
        <div class="inline-flex items-center px-3 py-1 bg-white/20 rounded-full text-[10px] font-bold tracking-widest uppercase mb-4 backdrop-blur-md">
            Informasi Sistem • {{ now()->format('d F Y') }}
        </div>
        <h3 class="text-3xl font-extrabold mb-3 leading-tight tracking-tight">Selamat Datang,<br>{{ Auth::user()->name }}!</h3>
        <p class="text-primary-100 opacity-90 max-w-xl font-medium leading-relaxed">
            @switch(Auth::user()->role)
                @case('kepala_sekolah')
                    Pantau kesehatan akademik dan kedisiplinan SMA Islam Sultan Agung 1 dalam satu dasbor eksekutif.
                    @break
                @case('waka_kurikulum')
                    Kelola dinamika kurikulum, jadwal, dan progres akademik siswa secara mandiri dan fleksibel.
                    @break
                @case('waka_kesiswaan')
                    Monitor kedisiplinan dan pembinaan karakter siswa untuk mewujudkan generasi Khaira Ummah.
                    @break
                @default
                    Sistem Terpadu Sultan Agung 1 siap membantu aktivitas pelayanan pendidikan Anda hari ini.
            @endswitch
        </p>
    </div>
</div>
