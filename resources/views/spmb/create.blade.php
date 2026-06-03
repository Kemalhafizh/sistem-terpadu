<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formulir Pendaftaran SPMB | SMA Islam Sultan Agung 1 Semarang</title>
    
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-12">
            <div class="flex items-center">
                <a href="{{ route('spmb.welcome') }}" class="flex items-center text-slate-400 hover:text-emerald-600 transition-colors mr-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path></svg>
                </a>
                <div class="h-10 w-10 flex items-center justify-center p-1 bg-white rounded-xl shadow-sm border border-slate-200/60 mr-3 shrink-0">
                    <img src="{{ asset('images/logo-kotak.png') }}" alt="Sultan Agung 1" class="h-full object-contain">
                </div>
                <div>
                    <h1 class="font-black text-lg text-slate-900 tracking-tight leading-none bg-gradient-to-r from-emerald-600 to-teal-700 bg-clip-text text-transparent">Sultan Agung 1</h1>
                    <p class="text-[9px] text-slate-400 font-bold tracking-widest uppercase leading-none mt-1">Formulir Pendaftaran</p>
                </div>
            </div>
            <div>
                <a href="{{ route('spmb.status') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs border border-slate-200 transition-all">Cek Status</a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="flex-grow flex items-center justify-center pt-32 pb-16 px-4">
        <div class="max-w-3xl w-full">
            
            <div class="text-center mb-8">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Formulir SPMB Online</h2>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-2 leading-relaxed font-inter font-normal">
                    Silakan isi data dengan teliti. Proses pengisian dibagi menjadi 4 tahapan ringkas demi kenyamanan Anda.
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
                                Gelombang Aktif
                            </span>
                            <h3 class="text-sm font-black text-slate-800 leading-none">Tahun Ajaran {{ $activeYear->year }} - Semester {{ ucfirst($activeYear->semester) }}</h3>
                        </div>
                        <div class="text-xs font-black text-slate-400 uppercase tracking-widest">
                            Langkah <span class="text-emerald-600" x-text="step"></span> Dari 4
                        </div>
                    </div>

                    <!-- Progress Indicator Bar -->
                    <div class="mb-10 relative">
                        <!-- Background Progress Line -->
                        <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-100 -translate-y-1/2 z-0 rounded-full"></div>
                        <!-- Active Progress Line -->
                        <div class="absolute top-1/2 left-0 h-1 bg-emerald-500 -translate-y-1/2 z-0 rounded-full transition-all duration-500" :style="'width: ' + ((step - 1) / 3 * 100) + '%'"></div>

                        <!-- Step Circles -->
                        <div class="relative z-10 flex justify-between">
                            <template x-for="s in [1, 2, 3, 4]">
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

                        <!-- STEP 1: BIODATA CALON SISWA -->
                        <div x-show="step === 1" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Langkah 1: Biodata Calon Siswa</h4>
                            
                            <div>
                                <label for="full_name" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap Sesuai Ijazah <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    name="full_name" 
                                    id="full_name" 
                                    x-model="formData.full_name" 
                                    required 
                                    class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                                    placeholder="Contoh: Budi Santoso"
                                >
                                <span class="text-xs text-red-500 font-medium" x-show="errors.full_name" x-text="errors.full_name"></span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="nisn" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">NISN (10 Digit, Opsional)</label>
                                    <input 
                                        type="text" 
                                        name="nisn" 
                                        id="nisn" 
                                        x-model="formData.nisn" 
                                        class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                                        placeholder="Nomor Induk Siswa Nasional"
                                    >
                                    <span class="text-xs text-red-500 font-medium" x-show="errors.nisn" x-text="errors.nisn"></span>
                                </div>
                                <div>
                                    <label for="previous_school" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Asal Sekolah (SMP/MTs) <span class="text-red-500">*</span></label>
                                    <input 
                                        type="text" 
                                        name="previous_school" 
                                        id="previous_school" 
                                        x-model="formData.previous_school" 
                                        required 
                                        class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                                        placeholder="Contoh: SMP Negeri 1 Semarang"
                                    >
                                    <span class="text-xs text-red-500 font-medium" x-show="errors.previous_school" x-text="errors.previous_school"></span>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2: KONTAK & WALI -->
                        <div x-show="step === 2" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Langkah 2: Kontak & Wali</h4>
                            
                            <div>
                                <label for="email" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Alamat Email Aktif <span class="text-red-500">*</span></label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email" 
                                    x-model="formData.email" 
                                    required 
                                    class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                                    placeholder="Contoh: budi@gmail.com"
                                >
                                <p class="mt-1 text-[10px] text-slate-400">Informasi pengumuman juga akan dikirimkan ke email ini.</p>
                                <span class="text-xs text-red-500 font-medium" x-show="errors.email" x-text="errors.email"></span>
                            </div>

                            <div>
                                <label for="parent_phone" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">Nomor WhatsApp Wali / Orang Tua <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    name="parent_phone" 
                                    id="parent_phone" 
                                    x-model="formData.parent_phone" 
                                    required 
                                    class="block w-full bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none" 
                                    placeholder="Contoh: 081234567890"
                                >
                                <p class="mt-1 text-[10px] text-slate-400">Pastikan nomor ini aktif WhatsApp untuk menerima notifikasi berkas dari sekolah.</p>
                                <span class="text-xs text-red-500 font-medium" x-show="errors.parent_phone" x-text="errors.parent_phone"></span>
                            </div>
                        </div>

                        <!-- STEP 3: UNGGAH BERKAS PERSYARATAN -->
                        <div x-show="step === 3" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Langkah 3: Unggah Berkas Persyaratan</h4>
                            
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

                                <div class="col-span-1 md:col-span-2">
                                    <p class="text-[10px] text-slate-400 text-center">Format berkas yang didukung: <strong class="text-slate-650">PDF, JPG, PNG</strong> dengan ukuran maksimal <strong class="text-slate-650">2MB</strong> per file.</p>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 4: REVIEW & KONFIRMASI -->
                        <div x-show="step === 4" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Langkah 4: Tinjauan Ulang & Konfirmasi</h4>
                            
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200/60 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap Calon Siswa</span>
                                        <strong class="text-xs text-slate-800" x-text="formData.full_name"></strong>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Asal Sekolah</span>
                                        <strong class="text-xs text-slate-800" x-text="formData.previous_school"></strong>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">NISN</span>
                                        <strong class="text-xs text-slate-800" x-text="formData.nisn || '-'"></strong>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Email</span>
                                        <strong class="text-xs text-slate-800" x-text="formData.email"></strong>
                                    </div>
                                    <div class="col-span-1 md:col-span-2">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor WhatsApp Orang Tua / Wali</span>
                                        <strong class="text-xs text-slate-800" x-text="formData.parent_phone"></strong>
                                    </div>
                                </div>

                                <div class="bg-blue-50 border border-blue-200/50 p-4 rounded-xl text-blue-900 text-xs leading-relaxed font-inter font-medium">
                                    🔔 <strong>Perhatian:</strong> Pastikan seluruh data di atas sudah benar sesuai dengan dokumen asli Anda. Berkas fisik KK dan Ijazah akan diverifikasi secara manual oleh Panitia.
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
                                &larr; Sebelumnya
                            </button>
                            <div x-show="step === 1"></div> <!-- placeholder to keep align right -->

                            <button 
                                type="button" 
                                @click="nextStep()" 
                                x-show="step < 4" 
                                class="px-6 py-2.5 bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all"
                            >
                                Selanjutnya &rarr;
                            </button>

                            <button 
                                type="submit" 
                                x-show="step === 4" 
                                :disabled="isSubmitting"
                                class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 disabled:opacity-50 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-1.5"
                            >
                                <span x-show="isSubmitting" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                                <span x-text="isSubmitting ? 'Sedang Memproses...' : 'Kirim Pendaftaran'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-white py-12 px-6 text-center shadow-xl rounded-3xl border border-slate-200/60">
                    <span class="text-5xl">🔒</span>
                    <h3 class="text-lg font-black text-slate-800 mt-4">Pendaftaran Ditutup</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto leading-relaxed">Pendaftaran SPMB belum dibuka karena belum ada Tahun Ajaran aktif di database. Silakan hubungi sekretariat sekolah.</p>
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-50 border-t border-slate-200/50 py-6 text-center mt-auto">
        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">&copy; {{ date('Y') }} SMA Islam Sultan Agung 1 Semarang. All rights reserved.</p>
    </footer>

    <!-- AlpineJS Handler Script -->
    <script>
        function registrationForm() {
            return {
                step: 1,
                isSubmitting: false,
                formData: {
                    full_name: '',
                    nisn: '',
                    previous_school: '',
                    email: '',
                    parent_phone: '',
                    document_kk: null,
                    document_certificate: null
                },
                errors: {
                    full_name: '',
                    nisn: '',
                    previous_school: '',
                    email: '',
                    parent_phone: '',
                    document_kk: '',
                    document_certificate: ''
                },
                getStepLabel(s) {
                    const labels = {
                        1: 'Biodata',
                        2: 'Kontak',
                        3: 'Berkas',
                        4: 'Review'
                    };
                    return labels[s];
                },
                goToStep(s) {
                    if (s < this.step) {
                        this.step = s;
                    } else if (s > this.step) {
                        // Allow moving forward if validation passes for intermediate steps
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
                    // Clear previous errors
                    this.errors = {
                        full_name: '',
                        nisn: '',
                        previous_school: '',
                        email: '',
                        parent_phone: '',
                        document_kk: '',
                        document_certificate: ''
                    };

                    let isValid = true;

                    if (this.step === 1) {
                        if (!this.formData.full_name.trim()) {
                            this.errors.full_name = 'Nama lengkap wajib diisi.';
                            isValid = false;
                        }
                        if (!this.formData.previous_school.trim()) {
                            this.errors.previous_school = 'Asal sekolah wajib diisi.';
                            isValid = false;
                        }
                    }

                    if (this.step === 2) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!this.formData.email.trim()) {
                            this.errors.email = 'Alamat email wajib diisi.';
                            isValid = false;
                        } else if (!emailRegex.test(this.formData.email)) {
                            this.errors.email = 'Format alamat email tidak valid.';
                            isValid = false;
                        }
                        if (!this.formData.parent_phone.trim()) {
                            this.errors.parent_phone = 'Nomor WhatsApp wajib diisi.';
                            isValid = false;
                        }
                    }

                    if (this.step === 3) {
                        const kkInput = document.getElementById('document_kk');
                        const certInput = document.getElementById('document_certificate');

                        if (!kkInput || kkInput.files.length === 0) {
                            this.errors.document_kk = 'Mohon unggah Kartu Keluarga (KK).';
                            isValid = false;
                        }

                        if (!certInput || certInput.files.length === 0) {
                            this.errors.document_certificate = 'Mohon unggah Ijazah / SKL.';
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
                            this.errors[key] = 'Ukuran berkas melebihi 2MB.';
                            e.target.value = ''; // clear
                            return;
                        }

                        if (!allowedTypes.includes(file.type)) {
                            this.errors[key] = 'Format berkas wajib PDF, JPG, atau PNG.';
                            e.target.value = ''; // clear
                            return;
                        }

                        this.formData[key] = file;
                    }
                },
                handleSubmit(e) {
                    if (this.step !== 4) {
                        e.preventDefault();
                        this.goToStep(4);
                        return;
                    }

                    this.isSubmitting = true;
                }
            };
        }
    </script>
</body>
</html>
