<div class="bg-linear-to-r from-primary-800 to-primary-600 rounded-2xl p-6 sm:p-8 shadow-md text-white relative overflow-hidden">
    <div class="relative z-10">
        <div class="inline-flex items-center px-3 py-1 bg-white/15 rounded-full text-[10px] font-bold tracking-widest uppercase mb-4">
            {{ __('dashboard.system_info') }} • {{ now()->format('d F Y') }}
        </div>
        <h3 class="text-3xl font-extrabold mb-3 leading-tight tracking-tight">{{ __('dashboard.welcome') }},<br>{{ Auth::user()->name }}!</h3>
        <p class="text-primary-100 opacity-90 max-w-xl font-medium leading-relaxed">
            @switch(Auth::user()->role)
                @case('kepala_sekolah')
                    {{ __('dashboard.welcome_desc_kepala_sekolah', school_placeholders()) }}
                    @break
                @case('waka_kurikulum')
                    {{ __('dashboard.welcome_desc_waka_kurikulum') }}
                    @break
                @case('waka_kesiswaan')
                    {{ __('dashboard.welcome_desc_waka_kesiswaan') }}
                    @break
                @default
                    {{ __('dashboard.welcome_desc_default', school_placeholders()) }}
            @endswitch
        </p>
    </div>
</div>
