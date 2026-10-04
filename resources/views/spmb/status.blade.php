<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ __('spmb.check_status') }} | {{ school_name() }}</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('images/logo-kotak.png') }}">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;850;900&family=Inter:wght@300;400;500;600;750&display=swap" rel="stylesheet">

  <!-- Styles & Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
    .font-inter { font-family: 'Inter', sans-serif; }
    .glass-card {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
    }
  </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4" x-data="{ copied: false }">

  <div class="max-w-xl w-full bg-white rounded-[2.5rem] p-8 md:p-10 shadow-2xl border border-slate-200/60 relative glass-card">
    <!-- Back button -->
    <!-- Back button & Language Selector -->
    <div class="absolute top-8 left-8 right-8 flex justify-between items-center z-50">
      <a href="{{ route('spmb.welcome') }}" class="text-slate-400 hover:text-emerald-600 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path></svg>
      </a>
      <!-- Language Selector Dropdown -->
      <div class="relative font-sans" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 hover:bg-slate-200 transition-all text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
          <span>{{ app()->getLocale() === 'id' ? ' ID' : (app()->getLocale() === 'en' ? ' EN' : ' AR') }}</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div x-show="open" @click.away="open = false" 
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="transform opacity-0 scale-95"
           x-transition:enter-end="transform opacity-100 scale-100"
           x-transition:leave="transition ease-in duration-75"
           x-transition:leave-start="transform opacity-100 scale-100"
           x-transition:leave-end="transform opacity-0 scale-95"
           class="absolute right-0 rtl:left-0 rtl:right-auto mt-2 w-36 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-150 dark:border-slate-800 z-50 overflow-hidden" 
           style="display:none">
          <div class="p-1.5 space-y-1">
            <form action="{{ route('preferences.locale', 'id') }}" method="POST" class="block">
              @csrf
              <button type="submit" class="w-full text-left rtl:text-right px-3 py-2 rounded-xl text-xs font-bold flex items-center justify-between {{ app()->getLocale() === 'id' ? 'bg-primary-50 text-primary-600' : 'text-slate-600 hover:bg-slate-50' }}">
                <span> Indonesia</span>
              </button>
            </form>
            <form action="{{ route('preferences.locale', 'en') }}" method="POST" class="block">
              @csrf
              <button type="submit" class="w-full text-left rtl:text-right px-3 py-2 rounded-xl text-xs font-bold flex items-center justify-between {{ app()->getLocale() === 'en' ? 'bg-primary-50 text-primary-600' : 'text-slate-600 hover:bg-slate-50' }}">
                <span> English</span>
              </button>
            </form>
            <form action="{{ route('preferences.locale', 'ar') }}" method="POST" class="block">
              @csrf
              <button type="submit" class="w-full text-left rtl:text-right px-3 py-2 rounded-xl text-xs font-bold flex items-center justify-between {{ app()->getLocale() === 'ar' ? 'bg-primary-50 text-primary-600' : 'text-slate-600 hover:bg-slate-50' }}">
                <span> العربية</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="text-center mb-8 mt-6">
      <div class="mx-auto w-14 h-14 bg-white rounded-2xl flex items-center justify-center p-1.5 shadow-sm border border-slate-100 mb-4">
        <img src="{{ asset('images/logo-kotak.png') }}" alt="{{ school_short_name() }}" class="h-full object-contain">
      </div>
      <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('spmb.status_header') }}</h2>
      <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mt-1">{{ __('spmb.status_subheader') }}</p>
    </div>

    @if(session('error'))
      <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-xs font-bold text-center border border-red-100 uppercase tracking-wide">
        {{ session('error') }}
      </div>
    @endif

    <form action="{{ route('spmb.status') }}" method="GET" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">{{ __('spmb.label_reg_number') }}</label>
          <input 
            type="text" 
            name="registration_number" 
            value="{{ request('registration_number') }}" 
            required 
            placeholder="Contoh: SPMB-2026-0001" 
            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
          >
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">NISN</label>
          <input 
            type="text" 
            name="nisn" 
            value="{{ request('nisn') }}" 
            required 
            placeholder="{{ __('spmb.placeholder_nisn') }}"
            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
          >
        </div>
      </div>

      <button type="submit" class="w-full py-4 bg-slate-900 hover:bg-black text-white rounded-2xl font-black text-xs uppercase tracking-wider shadow-lg hover:shadow-black/25 transition-all">
        {{ __('spmb.btn_track') }}
      </button>
    </form>

    @if(isset($registration))
      <div class="mt-8 pt-8 border-t border-slate-100">
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 text-center">{{ __('spmb.results_header') }}</h3>
        
        <!-- Vertical/Horizontal Status Timeline -->
        <div class="mb-8">
          @php
            $status = $registration->status;
            $step1 = true; // submitted is always done
            $step2 = in_array($status, ['reviewing', 'accepted', 'rejected']);
            $step3 = in_array($status, ['accepted', 'rejected']);
          @endphp
          <div class="flex justify-between items-center relative">
            <!-- Progress Line -->
            <div class="absolute top-4 left-0 w-full h-1 bg-slate-100 -translate-y-1/2 z-0 rounded-full"></div>
            <div class="absolute top-4 left-0 h-1 bg-emerald-500 -translate-y-1/2 z-0 rounded-full transition-all duration-500"
              style="width: {{ $step3 ? '100%' : ($step2 ? '50%' : '0%') }}"></div>

            <!-- Step 1: Submit -->
            <div class="flex flex-col items-center relative z-10">
              <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black bg-emerald-500 text-white border border-emerald-500 ring-4 ring-emerald-50"></div>
              <span class="text-[8px] font-black uppercase tracking-wider mt-2 text-emerald-600 text-center">{{ __('spmb.step_submitted') }}</span>
            </div>

            <!-- Step 2: Review -->
            <div class="flex flex-col items-center relative z-10">
              <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black transition-all duration-300
                @if($step2) bg-emerald-500 text-white border-emerald-500 ring-4 ring-emerald-50
                @else bg-white text-slate-400 border-slate-200
                @endif">
                @if($step2) @else 2 @endif
              </div>
              <span class="text-[8px] font-black uppercase tracking-wider mt-2 text-center
                @if($step2) text-emerald-600 @else text-slate-400 @endif">{{ __('spmb.step_verification') }}</span>
            </div>

            <!-- Step 3: Result -->
            <div class="flex flex-col items-center relative z-10">
              <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black transition-all duration-300
                @if($step3) 
                  @if($status == 'accepted') bg-emerald-500 text-white border-emerald-500 ring-4 ring-emerald-50
                  @else bg-rose-500 text-white border-rose-500 ring-4 ring-rose-50
                  @endif
                @else bg-white text-slate-400 border-slate-200
                @endif">
                @if($step3) @else 3 @endif
              </div>
              <span class="text-[8px] font-black uppercase tracking-wider mt-2 text-center
                @if($step3) 
                  @if($status == 'accepted') text-emerald-600 @else text-rose-500 @endif
                @else text-slate-400 @endif">{{ __('spmb.step_graduation') }}</span>
            </div>
          </div>
        </div>

        <!-- Result Card -->
        <div class="bg-slate-50 rounded-3xl p-6 border border-slate-200/50 text-center">
          <p class="font-black text-slate-900 text-base leading-none">{{ $registration->full_name }}</p>
          <p class="text-[10px] text-slate-400 font-bold mt-1.5 leading-none">{{ $registration->previous_school }}</p>

          <div class="mt-6 inline-flex items-center px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider border
            @if($status == 'pending') bg-slate-100 text-slate-600 border-slate-200
            @elseif($status == 'reviewing') bg-amber-50 text-amber-700 border-amber-200/50
            @elseif($status == 'accepted') bg-emerald-50 text-emerald-700 border-emerald-200/50
            @elseif($status == 'rejected') bg-rose-50 text-rose-700 border-rose-200/50
            @endif">
            
            @if($status == 'pending') {{ __('spmb.status_pending_long') }}
            @elseif($status == 'reviewing') {{ __('spmb.status_reviewing_long') }}
            @elseif($status == 'accepted') {{ __('spmb.status_accepted_long') }}
            @elseif($status == 'rejected') {{ __('spmb.status_rejected_long') }}
            @else {{ strtoupper($status) }}
            @endif
          </div>

          <!-- Credential Box for Accepted Candidates -->
          @if($status == 'accepted' && $registration->user_id)
            @php
              $email = strtolower($registration->registration_number) . '@student.sch.id';
              $password = 'password123'; // Default temporary password
            @endphp
            <div class="mt-6 pt-6 border-t border-slate-200 border-dashed text-left space-y-3 bg-white p-5 rounded-2xl border border-slate-150 shadow-sm relative overflow-hidden">
              <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000008_1px,transparent_1px)] [background-size:12px_12px] opacity-10"></div>
              
              <div class="relative z-10">
                <div class="flex justify-between items-center mb-3">
                  <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">{{ __('spmb.account_info_header') }}</span>
                  <!-- Clipboard Click Copy -->
                  <button 
                    type="button"
                    @click="navigator.clipboard.writeText('Email: {{ $email }}\nPassword: {{ $password }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[9px] font-bold transition-all border border-slate-200 flex items-center gap-1 cursor-pointer"
                  >
                    <span x-text="copied ? ' ' + '{{ __('spmb.btn_copied') }}' : ' ' + '{{ __('spmb.btn_copy_account') }}'"></span>
                  </button>
                </div>
                <div class="text-xs text-slate-650 space-y-1">
                  <div class="flex justify-between">
                    <span class="text-slate-400 font-medium">{{ __('spmb.label_email_address') }}</span>
                    <strong class="text-slate-800">{{ $email }}</strong>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-400 font-medium">{{ __('spmb.label_temp_password') }}</span>
                    <strong class="text-slate-800">{{ $password }}</strong>
                  </div>
                </div>
                <a href="{{ route('login') }}" class="mt-4 block text-center py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-emerald-500/10 transition-all">
                  {{ __('spmb.btn_login_dashboard') }} &rarr;
                </a>
              </div>
            </div>
          @endif
        </div>
      </div>
    @endif
  </div>

</body>
</html>
