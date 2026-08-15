<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ __('spmb.spmb_title') }} | SMA Islam Sultan Agung 1 Semarang</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('images/logo-kotak.png') }}">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;850;900&family=Inter:wght@300;400;500;600;750&display=swap" rel="stylesheet">

  <!-- Styles & Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
    body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
    .font-inter { font-family: 'Inter', sans-serif; }
    .glass-nav {
      background: rgba(255, 255, 255, 0.75);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
    }
  </style>
</head>
<body class="antialiased text-slate-800 min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white" x-data="registrationForm()">

  <!-- Navbar -->
  <nav class="glass-nav border-b border-slate-200/50 py-4 shadow-sm fixed w-full z-50">
    <div class="w-full px-4 sm:px-6 lg:px-8 flex justify-between items-center h-12">
      <div class="flex items-center">
        <a href="{{ route('spmb.welcome') }}" class="flex items-center text-slate-400 hover:text-emerald-600 transition-colors mr-6">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path></svg>
        </a>
        <div class="h-10 w-10 flex items-center justify-center p-1 bg-white rounded-xl shadow-sm border border-slate-200/60 mr-3 shrink-0">
          <img src="{{ asset('images/logo-kotak.png') }}" alt="Sultan Agung 1" class="h-full object-contain">
        </div>
        <div>
          <h1 class="font-black text-lg text-slate-900 tracking-tight leading-none bg-gradient-to-r from-emerald-600 to-teal-700 bg-clip-text text-transparent">Sultan Agung 1</h1>
          <p class="text-[9px] text-slate-400 font-bold tracking-widest uppercase leading-none mt-1">{{ __('spmb.register_title') }}</p>
        </div>
      </div>
      <div class="flex items-center space-x-3 rtl:space-x-reverse">
        <!-- Language Selector Dropdown -->
        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" class="flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white/40 dark:bg-slate-950/40 border border-slate-200/50 dark:border-slate-800/50 hover:bg-white/80 transition-all text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
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
             class="absolute right-0 rtl:left-0 rtl:right-auto mt-2 w-36 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-800 z-50 overflow-hidden" 
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
        <a href="{{ route('spmb.status') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs border border-slate-200 transition-all">{{ __('spmb.check_status') }}</a>
      </div>
    </div>
  </nav>

  <!-- Main Container -->
  <main class="flex-grow flex items-center justify-center pt-32 pb-16 px-4">
    <div class="max-w-3xl w-full">
      
      <div class="text-center mb-8">
        <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ __('spmb.form_title_online') }}</h2>
        <p class="text-xs text-slate-500 max-w-md mx-auto mt-2 leading-relaxed font-inter font-normal">
          {{ __('spmb.form_desc_online') }}
        </p>
      </div>

      @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl shadow-sm text-center text-xs font-bold uppercase tracking-wider">
          {{ session('error') }}
        </div>
      @endif

      @if(isset($activeYear) && $activeYear)
        <div class="bg-white/80 backdrop-blur-md py-8 px-6 md:px-10 shadow-xl rounded-3xl border border-slate-200/60 relative">
          
          <!-- Wave design on card header -->
          <div class="mb-8 border-b border-slate-100 pb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
              <span class="inline-flex px-3 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-full border border-emerald-100 uppercase tracking-widest mb-1.5">
                {{ __('spmb.active_wave') }}
              </span>
              <h3 class="text-sm font-black text-slate-800 leading-none">{{ __('spmb.wave_academic_year', ['year' => $activeYear->year, 'semester' => ucfirst($activeYear->semester)]) }}</h3>
            </div>
            <div class="text-xs font-black text-slate-400 uppercase tracking-widest">
              {{ app()->getLocale() === 'en' ? 'Step' : (app()->getLocale() === 'ar' ? 'الخطوة' : 'Langkah') }} <span class="text-emerald-600" x-text="step"></span> {{ app()->getLocale() === 'en' ? 'of 5' : (app()->getLocale() === 'ar' ? 'من 5' : 'Dari 5') }}
            </div>
          </div>

          <!-- Progress Indicator Bar -->
          <div class="mb-10 relative">
            <!-- Background Progress Line -->
            <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-100 -translate-y-1/2 z-0 rounded-full"></div>
            <!-- Active Progress Line -->
            <div class="absolute top-1/2 left-0 h-1 bg-emerald-500 -translate-y-1/2 z-0 rounded-full transition-all duration-500" :style="'width: ' + ((step - 1) / 4 * 100) + '%'"></div>

            <!-- Step Circles -->
            <div class="relative z-10 flex justify-between">
              <template x-for="s in [1, 2, 3, 4, 5]">
                <div class="flex flex-col items-center">
                  <div 
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black border transition-all duration-500 cursor-pointer shadow-sm"
                    :class="step >= s 
                      ? 'bg-emerald-500 border-emerald-500 text-white ring-4 ring-emerald-50 dark:ring-emerald-950/20' 
                      : 'bg-white border-slate-200 text-slate-400'"
                    @click="goToStep(s)"
                  >
                    <span x-text="s"></span>
                  </div>
                  <span 
                    class="text-[9px] font-black uppercase tracking-wider mt-2 transition-colors duration-300"
                    :class="step >= s ? 'text-emerald-600' : 'text-slate-400'"
                    x-text="getStepLabel(s)"
                  ></span>
                </div>
              </template>
            </div>
          </div>

          <!-- Main Form -->
          <form method="POST" action="{{ route('spmb.store') }}" enctype="multipart/form-data" class="space-y-6" @submit="handleSubmit($event)">
            @csrf

            <!-- Honeypot Anti-Bot -->
            <div class="hidden" aria-hidden="true">
              <input type="text" name="secondary_email" tabindex="-1" autocomplete="off">
            </div>

            <!-- STEP 1: PILIH GELOMBANG & JALUR -->
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">
              <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Langkah 1: Jalur & Gelombang Masuk</h4>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="spmb_wave_id" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Gelombang Pendaftaran <span class="text-red-500">*</span></label>
                  <select 
                    name="spmb_wave_id" 
                    id="spmb_wave_id" 
                    x-model="formData.spmb_wave_id" 
                    required 
                    class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  >
                    <option value="">-- Pilih Gelombang --</option>
                    @foreach($openWaves ?? [] as $wave)
                      <option value="{{ $wave->id }}">{{ $wave->name }} (Kuota: {{ $wave->quota }} - Biaya: Rp {{ number_format($wave->registration_fee, 0, ',', '.') }})</option>
                    @endforeach
                  </select>
                  <span class="text-xs text-red-500 font-medium" x-show="errors.spmb_wave_id" x-text="errors.spmb_wave_id"></span>
                </div>
                <div>
                  <label for="spmb_track_id" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Jalur Seleksi <span class="text-red-500">*</span></label>
                  <select 
                    name="spmb_track_id" 
                    id="spmb_track_id" 
                    x-model="formData.spmb_track_id" 
                    required 
                    class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  >
                    <option value="">-- Pilih Jalur --</option>
                    @foreach($tracks ?? [] as $track)
                      <option value="{{ $track->id }}">{{ $track->name }} (Kuota: {{ $track->quota }})</option>
                    @endforeach
                  </select>
                  <span class="text-xs text-red-500 font-medium" x-show="errors.spmb_track_id" x-text="errors.spmb_track_id"></span>
                </div>
              </div>

              <hr class="border-slate-100 my-4" />

              <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">{{ __('spmb.step_biodata') }}</h4>
              
              <div>
                <label for="full_name" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">{{ __('spmb.label_full_name') }} <span class="text-red-500">*</span></label>
                <input 
                  type="text" 
                  name="full_name" 
                  id="full_name" 
                  x-model="formData.full_name" 
                  required 
                  class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                  placeholder="{{ __('spmb.placeholder_name') }}"
                >
                <span class="text-xs text-red-500 font-medium" x-show="errors.full_name" x-text="errors.full_name"></span>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="nisn" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">{{ __('spmb.label_nisn_optional') }}</label>
                  <input 
                    type="text" 
                    name="nisn" 
                    id="nisn" 
                    x-model="formData.nisn" 
                    class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                    placeholder="{{ __('spmb.placeholder_nisn') }}"
                  >
                  <span class="text-xs text-red-500 font-medium" x-show="errors.nisn" x-text="errors.nisn"></span>
                </div>
                <div>
                  <label for="gender" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                  <select 
                    name="gender" 
                    id="gender" 
                    x-model="formData.gender" 
                    required 
                    class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  >
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                  </select>
                  <span class="text-xs text-red-500 font-medium" x-show="errors.gender" x-text="errors.gender"></span>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="birth_place" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Tempat Lahir <span class="text-red-500">*</span></label>
                  <input 
                    type="text" 
                    name="birth_place" 
                    id="birth_place" 
                    x-model="formData.birth_place" 
                    required 
                    class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                    placeholder="Kota tempat lahir"
                  >
                  <span class="text-xs text-red-500 font-medium" x-show="errors.birth_place" x-text="errors.birth_place"></span>
                </div>
                <div>
                  <label for="birth_date" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
                  <input 
                    type="date" 
                    name="birth_date" 
                    id="birth_date" 
                    x-model="formData.birth_date" 
                    required 
                    class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                  >
                  <span class="text-xs text-red-500 font-medium" x-show="errors.birth_date" x-text="errors.birth_date"></span>
                </div>
              </div>

              <div>
                <label for="previous_school" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">{{ __('spmb.label_previous_school') }} <span class="text-red-500">*</span></label>
                <input 
                  type="text" 
                  name="previous_school" 
                  id="previous_school" 
                  x-model="formData.previous_school" 
                  required 
                  class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                  placeholder="{{ __('spmb.placeholder_school') }}"
                >
                <span class="text-xs text-red-500 font-medium" x-show="errors.previous_school" x-text="errors.previous_school"></span>
              </div>
            </div>

            <!-- STEP 2: DATA ORANG TUA & KONTAK -->
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">
              <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Langkah 2: Data Orang Tua & Kontak</h4>
              
              <div>
                <label for="parent_name" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Nama Orang Tua / Wali <span class="text-red-500">*</span></label>
                <input 
                  type="text" 
                  name="parent_name" 
                  id="parent_name" 
                  x-model="formData.parent_name" 
                  required 
                  class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                  placeholder="Nama lengkap Ayah/Ibu/Wali"
                >
                <span class="text-xs text-red-500 font-medium" x-show="errors.parent_name" x-text="errors.parent_name"></span>
              </div>

              <div>
                <label for="email" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">{{ __('spmb.label_email') }} <span class="text-red-500">*</span></label>
                <input 
                  type="email" 
                  name="email" 
                  id="email" 
                  x-model="formData.email" 
                  required 
                  class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                  placeholder="{{ __('spmb.placeholder_email') }}"
                >
                <p class="mt-1 text-[10px] text-slate-400">{{ __('spmb.email_info') }}</p>
                <span class="text-xs text-red-500 font-medium" x-show="errors.email" x-text="errors.email"></span>
              </div>

              <div>
                <label for="parent_phone" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">{{ __('spmb.label_phone') }} <span class="text-red-500">*</span></label>
                <input 
                  type="text" 
                  name="parent_phone" 
                  id="parent_phone" 
                  x-model="formData.parent_phone" 
                  required 
                  class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                  placeholder="{{ __('spmb.placeholder_phone') }}"
                >
                <p class="mt-1 text-[10px] text-slate-400">{{ __('spmb.phone_info') }}</p>
                <span class="text-xs text-red-500 font-medium" x-show="errors.parent_phone" x-text="errors.parent_phone"></span>
              </div>

              <div>
                <label for="address" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Alamat Tempat Tinggal <span class="text-red-500">*</span></label>
                <textarea 
                  name="address" 
                  id="address" 
                  x-model="formData.address" 
                  required 
                  rows="3"
                  class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                  placeholder="Alamat lengkap beserta RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten"
                ></textarea>
                <span class="text-xs text-red-500 font-medium" x-show="errors.address" x-text="errors.address"></span>
              </div>
            </div>

            <!-- STEP 3: NILAI RAPOR & DATA PENDUKUNG -->
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">
              <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Langkah 3: Nilai Rapor & Data Pendukung</h4>
              
              <p class="text-xs text-slate-500 mb-4 bg-amber-50 border border-amber-200 p-3 rounded-xl">Masukkan rata-rata nilai semester 1-5 SMP/MTs Anda untuk mata pelajaran inti berikut:</p>

              <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div>
                  <label class="block font-bold text-xs text-slate-400 mb-1">Matematika <span class="text-red-500">*</span></label>
                  <input type="number" step="0.01" name="rapor_scores[matematika]" x-model="formData.rapor_scores.matematika" required class="block w-full bg-slate-50 border border-slate-250 rounded-xl px-3 py-2 text-xs font-semibold text-center" placeholder="85">
                </div>
                <div>
                  <label class="block font-bold text-xs text-slate-400 mb-1">IPA <span class="text-red-500">*</span></label>
                  <input type="number" step="0.01" name="rapor_scores[ipa]" x-model="formData.rapor_scores.ipa" required class="block w-full bg-slate-50 border border-slate-250 rounded-xl px-3 py-2 text-xs font-semibold text-center" placeholder="85">
                </div>
                <div>
                  <label class="block font-bold text-xs text-slate-400 mb-1">IPS <span class="text-red-500">*</span></label>
                  <input type="number" step="0.01" name="rapor_scores[ips]" x-model="formData.rapor_scores.ips" required class="block w-full bg-slate-50 border border-slate-250 rounded-xl px-3 py-2 text-xs font-semibold text-center" placeholder="85">
                </div>
                <div>
                  <label class="block font-bold text-xs text-slate-400 mb-1">B. Indo <span class="text-red-500">*</span></label>
                  <input type="number" step="0.01" name="rapor_scores[indonesia]" x-model="formData.rapor_scores.indonesia" required class="block w-full bg-slate-50 border border-slate-250 rounded-xl px-3 py-2 text-xs font-semibold text-center" placeholder="85">
                </div>
                <div>
                  <label class="block font-bold text-xs text-slate-400 mb-1">B. Inggris <span class="text-red-500">*</span></label>
                  <input type="number" step="0.01" name="rapor_scores[inggris]" x-model="formData.rapor_scores.inggris" required class="block w-full bg-slate-50 border border-slate-250 rounded-xl px-3 py-2 text-xs font-semibold text-center" placeholder="85">
                </div>
              </div>
              <span class="text-xs text-red-500 font-medium" x-show="errors.rapor_scores" x-text="errors.rapor_scores"></span>

              <hr class="border-slate-100 my-4" />

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="tahfidz_count" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Jumlah Juz Hafalan (Khusus Jalur Tahfidz)</label>
                  <input 
                    type="number" 
                    name="tahfidz_data[juz_count]" 
                    id="tahfidz_count" 
                    x-model="formData.tahfidz_data.juz_count" 
                    class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                    placeholder="Contoh: 2"
                  >
                </div>
                <div>
                  <label for="achievement_title" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Prestasi Tertinggi (Khusus Jalur Prestasi)</label>
                  <div class="grid grid-cols-2 gap-2">
                    <input 
                      type="text" 
                      name="achievement_data[0][title]" 
                      id="achievement_title" 
                      x-model="formData.achievement_data[0].title" 
                      class="block w-full bg-slate-50 border border-slate-250 rounded-xl px-3 py-2.5 text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                      placeholder="Nama lomba/prestasi"
                    >
                    <select 
                      name="achievement_data[0][level]" 
                      x-model="formData.achievement_data[0].level" 
                      class="block w-full bg-slate-50 border border-slate-250 rounded-xl px-3 py-2.5 text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    >
                      <option value="">-- Tingkat --</option>
                      <option value="sekolah">Sekolah</option>
                      <option value="kota">Kabupaten/Kota</option>
                      <option value="provinsi">Provinsi</option>
                      <option value="nasional">Nasional</option>
                      <option value="internasional">Internasional</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- STEP 4: UNGGAH BERKAS PERSYARATAN -->
            <div x-show="step === 4" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">
              <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">{{ __('spmb.step_files') }}</h4>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-200/60 shadow-inner">
                <!-- KK File -->
                <div>
                  <label class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Scan Kartu Keluarga <span class="text-red-500">*</span></label>
                  <input 
                    type="file" 
                    name="document_kk" 
                    id="document_kk" 
                    accept=".pdf,.jpg,.jpeg,.png" 
                    required 
                    @change="handleFileChange($event, 'document_kk')"
                    class="file-input block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:bg-white file:text-slate-700 hover:file:bg-slate-50 border border-slate-250 bg-white rounded-xl shadow-sm cursor-pointer"
                  >
                  <span class="text-xs text-red-500 font-medium mt-1 block" x-show="errors.document_kk" x-text="errors.document_kk"></span>
                </div>

                <!-- Certificate File -->
                <div>
                  <label class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Scan Ijazah / SKL <span class="text-red-500">*</span></label>
                  <input 
                    type="file" 
                    name="document_certificate" 
                    id="document_certificate" 
                    accept=".pdf,.jpg,.jpeg,.png" 
                    required 
                    @change="handleFileChange($event, 'document_certificate')"
                    class="file-input block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:bg-white file:text-slate-700 hover:file:bg-slate-50 border border-slate-250 bg-white rounded-xl shadow-sm cursor-pointer"
                  >
                  <span class="text-xs text-red-500 font-medium mt-1 block" x-show="errors.document_certificate" x-text="errors.document_certificate"></span>
                </div>

                <!-- SKHUN File -->
                <div>
                  <label class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Scan SKHUN / Rapor Semester 5</label>
                  <input 
                    type="file" 
                    name="document_skhun" 
                    id="document_skhun" 
                    accept=".pdf,.jpg,.jpeg,.png" 
                    @change="handleFileChange($event, 'document_skhun')"
                    class="file-input block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:bg-white file:text-slate-700 hover:file:bg-slate-50 border border-slate-250 bg-white rounded-xl shadow-sm cursor-pointer"
                  >
                  <span class="text-xs text-red-500 font-medium mt-1 block" x-show="errors.document_skhun" x-text="errors.document_skhun"></span>
                </div>

                <!-- Akta Kelahiran -->
                <div>
                  <label class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Scan Akta Kelahiran</label>
                  <input 
                    type="file" 
                    name="document_akta" 
                    id="document_akta" 
                    accept=".pdf,.jpg,.jpeg,.png" 
                    @change="handleFileChange($event, 'document_akta')"
                    class="file-input block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:bg-white file:text-slate-700 hover:file:bg-slate-50 border border-slate-250 bg-white rounded-xl shadow-sm cursor-pointer"
                  >
                  <span class="text-xs text-red-500 font-medium mt-1 block" x-show="errors.document_akta" x-text="errors.document_akta"></span>
                </div>

                <!-- Photo Pas Foto 3x4 -->
                <div class="col-span-1 md:col-span-2">
                  <label class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Pas Foto Calon Siswa (3x4) <span class="text-red-500">*</span></label>
                  <input 
                    type="file" 
                    name="photo" 
                    id="photo" 
                    accept=".jpg,.jpeg,.png" 
                    required 
                    @change="handleFileChange($event, 'photo')"
                    class="file-input block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:bg-white file:text-slate-700 hover:file:bg-slate-50 border border-slate-250 bg-white rounded-xl shadow-sm cursor-pointer"
                  >
                  <span class="text-xs text-red-500 font-medium mt-1 block" x-show="errors.photo" x-text="errors.photo"></span>
                </div>

                <div class="col-span-1 md:col-span-2">
                  <p class="text-[10px] text-slate-400 text-center">{{ __('spmb.file_requirements') }}</p>
                </div>
              </div>
            </div>

            <!-- STEP 5: REVIEW & KONFIRMASI -->
            <div x-show="step === 5" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">
              <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">{{ __('spmb.step_review') }}</h4>
              
              <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200/60 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('spmb.label_full_name') }}</span>
                    <strong class="text-xs text-slate-800" x-text="formData.full_name"></strong>
                  </div>
                  <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('spmb.label_previous_school') }}</span>
                    <strong class="text-xs text-slate-800" x-text="formData.previous_school"></strong>
                  </div>
                  <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">NISN</span>
                    <strong class="text-xs text-slate-800" x-text="formData.nisn || '-'"></strong>
                  </div>
                  <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jenis Kelamin</span>
                    <strong class="text-xs text-slate-800" x-text="formData.gender === 'L' ? 'Laki-laki' : 'Perempuan'"></strong>
                  </div>
                  <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('spmb.label_email') }}</span>
                    <strong class="text-xs text-slate-800" x-text="formData.email"></strong>
                  </div>
                  <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('spmb.label_phone') }}</span>
                    <strong class="text-xs text-slate-800" x-text="formData.parent_phone"></strong>
                  </div>
                  <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Orang Tua</span>
                    <strong class="text-xs text-slate-800" x-text="formData.parent_name"></strong>
                  </div>
                  <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Lengkap</span>
                    <strong class="text-xs text-slate-800" x-text="formData.address"></strong>
                  </div>
                  <div class="col-span-1 md:col-span-2">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata Nilai Rapor</span>
                    <strong class="text-xs text-slate-800">
                      Matematika: <span x-text="formData.rapor_scores.matematika"></span>, 
                      IPA: <span x-text="formData.rapor_scores.ipa"></span>, 
                      IPS: <span x-text="formData.rapor_scores.ips"></span>, 
                      B. Indo: <span x-text="formData.rapor_scores.indonesia"></span>, 
                      B. Inggris: <span x-text="formData.rapor_scores.inggris"></span>
                    </strong>
                  </div>
                </div>

                <div class="bg-blue-50 border border-blue-200/50 p-4 rounded-xl text-blue-900 text-xs leading-relaxed font-inter font-medium">
                   {{ __('spmb.review_warning') }}
                </div>
              </div>
            </div>

            <!-- Wizard Navigational Buttons -->
            <div class="flex justify-between items-center pt-8 border-t border-slate-100">
              <button 
                type="button" 
                @click="prevStep()" 
                x-show="step > 1" 
                class="px-5 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold tracking-wide transition-all"
              >
                &larr; {{ __('spmb.btn_previous') }}
              </button>
              <div x-show="step === 1"></div> <!-- placeholder to keep align right -->

              <button 
                type="button" 
                @click="nextStep()" 
                x-show="step < 5" 
                class="px-6 py-2.5 bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all"
              >
                {{ __('spmb.btn_next') }} &rarr;
              </button>

              <button 
                type="submit" 
                x-show="step === 5" 
                :disabled="isSubmitting"
                class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 disabled:opacity-50 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-1.5"
              >
                <span x-show="isSubmitting" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                <span x-text="isSubmitting ? '{{ __('spmb.btn_submitting') }}' : '{{ __('spmb.btn_submit') }}'"></span>
              </button>
            </div>
          </form>
        </div>
      @else
        <div class="bg-white py-12 px-6 text-center shadow-xl rounded-3xl border border-slate-200/60">
          <span class="text-5xl"></span>
          <h3 class="text-lg font-black text-slate-800 mt-4">{{ __('spmb.closed_title') }}</h3>
          <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto leading-relaxed">{{ __('spmb.closed_desc') }}</p>
        </div>
      @endif
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-slate-50 border-t border-slate-200/50 py-6 text-center mt-auto">
    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">&copy; {{ date('Y') }} {{ __('public.footer_copyright') }}</p>
  </footer>

  <!-- AlpineJS Handler Script -->
  <script>
    function registrationForm() {
      return {
        step: 1,
        isSubmitting: false,
        formData: {
          spmb_wave_id: '',
          spmb_track_id: '',
          full_name: '',
          nisn: '',
          gender: 'L',
          birth_place: '',
          birth_date: '',
          previous_school: '',
          parent_name: '',
          email: '',
          parent_phone: '',
          address: '',
          rapor_scores: {
            matematika: '',
            ipa: '',
            ips: '',
            indonesia: '',
            inggris: ''
          },
          tahfidz_data: {
            juz_count: 0
          },
          achievement_data: [
            {
              title: '',
              level: ''
            }
          ],
          document_kk: null,
          document_certificate: null,
          document_skhun: null,
          document_akta: null,
          photo: null
        },
        errors: {
          spmb_wave_id: '',
          spmb_track_id: '',
          full_name: '',
          nisn: '',
          gender: '',
          birth_place: '',
          birth_date: '',
          previous_school: '',
          parent_name: '',
          email: '',
          parent_phone: '',
          address: '',
          rapor_scores: '',
          document_kk: '',
          document_certificate: '',
          document_skhun: '',
          document_akta: '',
          photo: ''
        },
        getStepLabel(s) {
          const labels = {
            1: "Jalur & Gelombang",
            2: "Biodata & Kontak",
            3: "Rapor & Prestasi",
            4: "Berkas",
            5: "{{ __('spmb.step_review') }}"
          };
          return labels[s];
        },
        goToStep(s) {
          if (s < this.step) {
            this.step = s;
          } else if (s > this.step) {
            while (this.step < s) {
              if (!this.validateCurrentStep()) break;
              this.step++;
            }
          }
        },
        prevStep() {
          if (this.step > 1) this.step--;
        },
        nextStep() {
          if (this.validateCurrentStep()) {
            this.step++;
          }
        },
        validateCurrentStep() {
          // Reset errors
          this.errors = {
            spmb_wave_id: '',
            spmb_track_id: '',
            full_name: '',
            nisn: '',
            gender: '',
            birth_place: '',
            birth_date: '',
            previous_school: '',
            parent_name: '',
            email: '',
            parent_phone: '',
            address: '',
            rapor_scores: '',
            document_kk: '',
            document_certificate: '',
            document_skhun: '',
            document_akta: '',
            photo: ''
          };

          let isValid = true;

          if (this.step === 1) {
            if (!this.formData.spmb_wave_id) {
              this.errors.spmb_wave_id = 'Gelombang pendaftaran wajib dipilih.';
              isValid = false;
            }
            if (!this.formData.spmb_track_id) {
              this.errors.spmb_track_id = 'Jalur masuk wajib dipilih.';
              isValid = false;
            }
            if (!this.formData.full_name.trim()) {
              this.errors.full_name = "{{ __('spmb.validation_name_required') }}";
              isValid = false;
            }
            if (!this.formData.birth_place.trim()) {
              this.errors.birth_place = 'Tempat lahir wajib diisi.';
              isValid = false;
            }
            if (!this.formData.birth_date) {
              this.errors.birth_date = 'Tanggal lahir wajib diisi.';
              isValid = false;
            }
            if (!this.formData.previous_school.trim()) {
              this.errors.previous_school = "{{ __('spmb.validation_school_required') }}";
              isValid = false;
            }
          }

          if (this.step === 2) {
            if (!this.formData.parent_name.trim()) {
              this.errors.parent_name = 'Nama orang tua/wali wajib diisi.';
              isValid = false;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!this.formData.email.trim()) {
              this.errors.email = "{{ __('spmb.validation_email_required') }}";
              isValid = false;
            } else if (!emailRegex.test(this.formData.email)) {
              this.errors.email = "{{ __('spmb.validation_email_invalid') }}";
              isValid = false;
            }
            if (!this.formData.parent_phone.trim()) {
              this.errors.parent_phone = "{{ __('spmb.validation_phone_required') }}";
              isValid = false;
            }
            if (!this.formData.address.trim()) {
              this.errors.address = 'Alamat lengkap wajib diisi.';
              isValid = false;
            }
          }

          if (this.step === 3) {
            const scoreFields = ['matematika', 'ipa', 'ips', 'indonesia', 'inggris'];
            let scoreValid = true;
            scoreFields.forEach(f => {
              const val = parseFloat(this.formData.rapor_scores[f]);
              if (isNaN(val) || val < 0 || val > 100) {
                scoreValid = false;
              }
            });
            if (!scoreValid) {
              this.errors.rapor_scores = 'Seluruh nilai rapor wajib diisi dengan rentang 0-100.';
              isValid = false;
            }
          }

          if (this.step === 4) {
            const kkInput = document.getElementById('document_kk');
            const certInput = document.getElementById('document_certificate');
            const photoInput = document.getElementById('photo');

            if (!kkInput || kkInput.files.length === 0) {
              this.errors.document_kk = "{{ __('spmb.validation_kk_required') }}";
              isValid = false;
            }

            if (!certInput || certInput.files.length === 0) {
              this.errors.document_certificate = "{{ __('spmb.validation_cert_required') }}";
              isValid = false;
            }

            if (!photoInput || photoInput.files.length === 0) {
              this.errors.photo = 'Mohon unggah pas foto 3x4.';
              isValid = false;
            }
          }

          return isValid;
        },
        handleFileChange(e, key) {
          const file = e.target.files[0];
          this.errors[key] = '';

          if (file) {
            const maxSize = 2 * 1024 * 1024; // 2MB
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];

            if (file.size > maxSize) {
              this.errors[key] = "{{ __('spmb.validation_file_size') }}";
              e.target.value = ''; // clear
              return;
            }

            if (!allowedTypes.includes(file.type)) {
              this.errors[key] = "{{ __('spmb.validation_file_type') }}";
              e.target.value = ''; // clear
              return;
            }

            this.formData[key] = file;
          }
        },
        handleSubmit(e) {
          if (this.step !== 5) {
            e.preventDefault();
            this.goToStep(5);
            return;
          }

          this.isSubmitting = true;
        }
      };
    }
  </script>
</body>
</html>
