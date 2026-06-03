<div class="bg-gradient-to-r from-primary-800 to-primary-600 rounded-[2rem] p-8 shadow-xl shadow-primary-900/20 text-white relative overflow-hidden">
    <!-- Abstract Shape -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-20 -mt-20"></div>
    
    <div class="relative z-10">
        <div class="inline-flex items-center px-3 py-1 bg-white/20 rounded-full text-[10px] font-bold tracking-widest uppercase mb-4 backdrop-blur-md">
            {{ __('dashboard.system_info') }} • {{ now()->format('d F Y') }}
        </div>
        <h3 class="text-3xl font-extrabold mb-3 leading-tight tracking-tight">{{ __('dashboard.welcome') }},<br>{{ Auth::user()->name }}!</h3>
        <p class="text-primary-100 opacity-90 max-w-xl font-medium leading-relaxed">
            @switch(Auth::user()->role)
                @case('kepala_sekolah')
                    {{ __('dashboard.welcome_desc_kepala_sekolah') }}
                    @break
                @case('waka_kurikulum')
                    {{ __('dashboard.welcome_desc_waka_kurikulum') }}
                    @break
                @case('waka_kesiswaan')
                    {{ __('dashboard.welcome_desc_waka_kesiswaan') }}
                    @break
                @default
                    {{ __('dashboard.welcome_desc_default') }}
            @endswitch
        </p>
    </div>
</div>
