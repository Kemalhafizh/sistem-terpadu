<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold leading-tight text-gray-800 dark:text-white">
            {{ __('Kustomisasi & Branding Tampilan Sekolah') }}
        </h2>
    </x-slot>

    <x-slot name="breadcrumbs">
        <nav class="flex text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Dashboard</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 md:ml-2 text-gray-400 dark:text-gray-500">Sistem</span>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 md:ml-2 text-gray-700 dark:text-gray-300">Branding Sekolah</span>
                    </div>
                </li>
            </ol>
        </nav>
    </x-slot>

    <!-- Google Fonts Preload dynamically in preview panel -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Montserrat:wght@400;600;700&family=Outfit:wght@400;600;700&family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;600;700&family=Poppins:wght@400;600;700&family=Roboto:wght@400;600;700&display=swap" rel="stylesheet">

    <div x-data="{
        primary_color: '{{ old('primary_color', $appearance->primary_color) }}',
        secondary_color: '{{ old('secondary_color', $appearance->secondary_color) }}',
        accent_color: '{{ old('accent_color', $appearance->accent_color) }}',
        sidebar_color: '{{ old('sidebar_color', $appearance->sidebar_color) }}',
        font_family: '{{ old('font_family', $appearance->font_family) }}',
        app_name: '{{ old('app_name', $appearance->app_name) }}',
        footer_text: '{{ old('footer_text', $appearance->footer_text) }}',
        applyPreset(p, s, a, sb) {
            this.primary_color = p;
            this.secondary_color = s;
            this.accent_color = a;
            this.sidebar_color = sb;
        },
        resetConfirm: false
    }" class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">

        <!-- Editor Form Panel (Left) -->
        <div class="xl:col-span-7 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 md:p-8 shadow-xl border border-white/20 dark:border-gray-700/50">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pengaturan Identitas & Tema</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ubah penampilan visual platform agar sesuai dengan identitas resmi sekolah Anda.</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="resetConfirm = true" type="button" class="px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 rounded-xl text-xs font-bold transition-all border border-red-500/20">
                        Reset Default
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.appearance.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Application Identity -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="app_name" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Nama Sekolah / Aplikasi</label>
                        <input type="text" id="app_name" name="app_name" x-model="app_name" 
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm font-semibold focus:ring-2 focus:ring-primary-500 transition-all dark:text-white"
                            required>
                        @error('app_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="footer_text" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Teks Hak Cipta Footer</label>
                        <input type="text" id="footer_text" name="footer_text" x-model="footer_text" 
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm font-semibold focus:ring-2 focus:ring-primary-500 transition-all dark:text-white"
                            required>
                        @error('footer_text') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Theme Color Palette Preset Buttons -->
                <div class="p-5 bg-primary-900/5 dark:bg-slate-900/40 border border-primary-500/10 dark:border-slate-800 rounded-3xl">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300 mb-3">Rekomendasi Preset Warna Instan</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <button type="button" @click="applyPreset('#10b981', '#047857', '#34d399', '#064e3b')" 
                            class="flex flex-col items-center p-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 hover:scale-[1.03] transition-all shadow-sm">
                            <div class="flex space-x-1 mb-2">
                                <span class="w-4 h-4 rounded-full bg-[#10b981]"></span>
                                <span class="w-4 h-4 rounded-full bg-[#047857]"></span>
                                <span class="w-4 h-4 rounded-full bg-[#064e3b]"></span>
                            </div>
                            <span class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400">Emerald Islam</span>
                        </button>

                        <button type="button" @click="applyPreset('#2563eb', '#1d4ed8', '#60a5fa', '#1e3a8a')" 
                            class="flex flex-col items-center p-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 hover:scale-[1.03] transition-all shadow-sm">
                            <div class="flex space-x-1 mb-2">
                                <span class="w-4 h-4 rounded-full bg-[#2563eb]"></span>
                                <span class="w-4 h-4 rounded-full bg-[#1d4ed8]"></span>
                                <span class="w-4 h-4 rounded-full bg-[#1e3a8a]"></span>
                            </div>
                            <span class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400">Ocean Blue</span>
                        </button>

                        <button type="button" @click="applyPreset('#8b5cf6', '#6d28d9', '#a78bfa', '#2e1065')" 
                            class="flex flex-col items-center p-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 hover:scale-[1.03] transition-all shadow-sm">
                            <div class="flex space-x-1 mb-2">
                                <span class="w-4 h-4 rounded-full bg-[#8b5cf6]"></span>
                                <span class="w-4 h-4 rounded-full bg-[#6d28d9]"></span>
                                <span class="w-4 h-4 rounded-full bg-[#2e1065]"></span>
                            </div>
                            <span class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400">Royal Purple</span>
                        </button>

                        <button type="button" @click="applyPreset('#3b82f6', '#8b5cf6', '#06b6d4', '#0f172a')" 
                            class="flex flex-col items-center p-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 hover:scale-[1.03] transition-all shadow-sm">
                            <div class="flex space-x-1 mb-2">
                                <span class="w-4 h-4 rounded-full bg-[#3b82f6]"></span>
                                <span class="w-4 h-4 rounded-full bg-[#8b5cf6]"></span>
                                <span class="w-4 h-4 rounded-full bg-[#0f172a]"></span>
                            </div>
                            <span class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400">Default SISTA</span>
                        </button>
                    </div>
                </div>

                <!-- Custom Color Pickers -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Primary Color -->
                    <div>
                        <label for="primary_color" class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Warna Utama</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" id="primary_color_picker" x-model="primary_color" 
                                class="w-10 h-10 border-0 rounded-xl cursor-pointer p-0 overflow-hidden shadow-inner">
                            <input type="text" name="primary_color" x-model="primary_color" 
                                class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-semibold uppercase text-gray-800 dark:text-white"
                                required>
                        </div>
                        @error('primary_color') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Secondary Color -->
                    <div>
                        <label for="secondary_color" class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Warna Sekunder</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" id="secondary_color_picker" x-model="secondary_color" 
                                class="w-10 h-10 border-0 rounded-xl cursor-pointer p-0 overflow-hidden shadow-inner">
                            <input type="text" name="secondary_color" x-model="secondary_color" 
                                class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-semibold uppercase text-gray-800 dark:text-white"
                                required>
                        </div>
                        @error('secondary_color') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Accent Color -->
                    <div>
                        <label for="accent_color" class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Warna Aksen</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" id="accent_color_picker" x-model="accent_color" 
                                class="w-10 h-10 border-0 rounded-xl cursor-pointer p-0 overflow-hidden shadow-inner">
                            <input type="text" name="accent_color" x-model="accent_color" 
                                class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-semibold uppercase text-gray-800 dark:text-white"
                                required>
                        </div>
                        @error('accent_color') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Sidebar Color -->
                    <div>
                        <label for="sidebar_color" class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Warna Sidebar</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" id="sidebar_color_picker" x-model="sidebar_color" 
                                class="w-10 h-10 border-0 rounded-xl cursor-pointer p-0 overflow-hidden shadow-inner">
                            <input type="text" name="sidebar_color" x-model="sidebar_color" 
                                class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-semibold uppercase text-gray-800 dark:text-white"
                                required>
                        </div>
                        @error('sidebar_color') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Font Family Selection -->
                <div>
                    <label for="font_family" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Keluarga Font Teks (Google Fonts)</label>
                    <select id="font_family" name="font_family" x-model="font_family" 
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm font-semibold focus:ring-2 focus:ring-primary-500 transition-all dark:text-white">
                        @foreach($fonts as $fontKey => $fontName)
                            <option value="{{ $fontKey }}" style="font-family: '{{ $fontKey }}', sans-serif;">{{ $fontName }}</option>
                        @endforeach
                    </select>
                    @error('font_family') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- File Uploads Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="text-xs font-black uppercase tracking-widest text-primary-600 dark:text-primary-400 mb-4">Aset Gambar & Logo Sekolah</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Light Logo -->
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Logo Utama (Tema Terang)</label>
                            @if($appearance->logo_path)
                                <div class="mb-3 h-16 w-32 bg-white rounded-xl border border-gray-200 flex items-center justify-center p-2 overflow-hidden shadow-sm">
                                    <img src="{{ asset('storage/' . $appearance->logo_path) }}" class="h-full object-contain" alt="Current Logo Light">
                                </div>
                            @endif
                            <input type="file" name="logo" class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary-500/10 file:text-primary-600 dark:file:bg-primary-500/20 dark:file:text-primary-400 hover:file:bg-primary-500/20 cursor-pointer">
                            <p class="text-[10px] text-gray-400 mt-2">Format: PNG, JPG, SVG. Rekomendasi: 200x50px.</p>
                            @error('logo') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Dark Logo -->
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Logo Utama (Tema Gelap)</label>
                            @if($appearance->logo_dark_path)
                                <div class="mb-3 h-16 w-32 bg-gray-950 rounded-xl border border-gray-800 flex items-center justify-center p-2 overflow-hidden shadow-sm">
                                    <img src="{{ asset('storage/' . $appearance->logo_dark_path) }}" class="h-full object-contain" alt="Current Logo Dark">
                                </div>
                            @endif
                            <input type="file" name="logo_dark" class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary-500/10 file:text-primary-600 dark:file:bg-primary-500/20 dark:file:text-primary-400 hover:file:bg-primary-500/20 cursor-pointer">
                            <p class="text-[10px] text-gray-400 mt-2">Digunakan untuk latar belakang gelap. Format: PNG, JPG, SVG.</p>
                            @error('logo_dark') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Favicon -->
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Favicon (Ikon Browser)</label>
                            @if($appearance->favicon_path)
                                <div class="mb-3 h-12 w-12 bg-white rounded-xl border border-gray-200 flex items-center justify-center p-1.5 overflow-hidden shadow-sm">
                                    <img src="{{ asset('storage/' . $appearance->favicon_path) }}" class="h-full object-contain" alt="Current Favicon">
                                </div>
                            @endif
                            <input type="file" name="favicon" class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary-500/10 file:text-primary-600 dark:file:bg-primary-500/20 dark:file:text-primary-400 hover:file:bg-primary-500/20 cursor-pointer">
                            <p class="text-[10px] text-gray-400 mt-2">Format: PNG, ICO. Rekomendasi: 32x32px.</p>
                            @error('favicon') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Login Background -->
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Latar Belakang Login Kustom</label>
                            @if($appearance->login_background_path)
                                <div class="mb-3 h-12 w-32 bg-white rounded-xl border border-gray-200 flex items-center justify-center p-1 overflow-hidden shadow-sm">
                                    <img src="{{ asset('storage/' . $appearance->login_background_path) }}" class="h-full w-full object-cover rounded" alt="Current Background">
                                </div>
                            @endif
                            <input type="file" name="login_background" class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary-500/10 file:text-primary-600 dark:file:bg-primary-500/20 dark:file:text-primary-400 hover:file:bg-primary-500/20 cursor-pointer">
                            <p class="text-[10px] text-gray-400 mt-2">Latar belakang halaman login portal. Max: 3MB.</p>
                            @error('login_background') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Custom Additional CSS -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <label for="custom_css" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Kode CSS Kustom Tambahan (Lanjutan)</label>
                    <textarea id="custom_css" name="custom_css" rows="5" 
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl text-xs font-mono focus:ring-2 focus:ring-primary-500 transition-all dark:text-white"
                        placeholder="/* Masukkan CSS tambahan di sini untuk override layout */\n.custom-class {\n    border-radius: 50px;\n}">{{ old('custom_css', $appearance->custom_css) }}</textarea>
                    <p class="text-[10px] text-gray-400 mt-2">Gunakan fitur ini hanya jika Anda memahami sintaks CSS. Aturan ini akan disuntikkan di bagian akhir file dokumen.</p>
                    @error('custom_css') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Action Button -->
                <div class="flex justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-2xl text-xs font-bold shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transition-all border border-blue-400/20 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Visual Real-time Mockup Live Preview (Right Panel) -->
        <div class="xl:col-span-5 sticky top-8 space-y-6">
            <div class="bg-gray-900 text-white rounded-3xl p-6 shadow-2xl border border-gray-800 flex flex-col relative overflow-hidden min-h-[500px]">
                <div class="absolute -right-16 -top-16 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl"></div>
                <div class="absolute -left-16 -bottom-16 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl"></div>

                <div class="flex items-center justify-between mb-6 border-b border-gray-800 pb-3">
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
                    </div>
                    <span class="text-[10px] font-black uppercase text-gray-500 tracking-widest">Visual Live Preview</span>
                </div>

                <!-- Embedded Dynamic Styles for Live Preview -->
                <div class="flex-grow flex flex-col border border-gray-800 rounded-2xl overflow-hidden bg-gray-950/70"
                    :style="'--color-primary: ' + primary_color + '; --color-secondary: ' + secondary_color + '; --color-accent: ' + accent_color + '; --color-sidebar: ' + sidebar_color + '; font-family: \'' + font_family + '\', sans-serif;'">
                    
                    <!-- App Mockup Header -->
                    <div class="px-4 py-3 flex items-center justify-between border-b border-gray-800" :style="'background-color: ' + sidebar_color">
                        <div class="flex items-center space-x-2">
                            <div class="h-6 w-6 bg-white rounded flex items-center justify-center p-0.5 shrink-0 overflow-hidden">
                                @if($appearance->logo_path)
                                    <img src="{{ asset('storage/' . $appearance->logo_path) }}" class="h-full object-contain">
                                @else
                                    <span class="text-[8px] font-black text-blue-700">SA1</span>
                                @endif
                            </div>
                            <span class="text-xs font-black text-white" x-text="app_name"></span>
                        </div>
                        <div class="h-5 w-5 bg-white/10 rounded-full flex items-center justify-center shrink-0">
                            <span class="w-2.5 h-2.5 rounded-full inline-block" :style="'background-color: ' + primary_color"></span>
                        </div>
                    </div>

                    <!-- App Mockup Body -->
                    <div class="flex flex-1 min-h-[300px]">
                        <!-- Sidebar Mockup -->
                        <div class="w-16 md:w-20 px-2 py-4 flex flex-col items-center space-y-3 shrink-0" :style="'background-color: ' + sidebar_color">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-white/10 text-white hover:bg-white/20 transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            </div>
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white/50 cursor-pointer" :style="'background-color: ' + primary_color + '15; color: ' + primary_color">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                            </div>
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white/30 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white/30 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>

                        <!-- Content Mockup -->
                        <div class="flex-1 p-4 bg-slate-900/90 text-slate-100 flex flex-col justify-between">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h5 class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Dashboard Utama</h5>
                                    <span class="px-2 py-0.5 rounded-full text-[8px] font-bold text-white shadow-sm" :style="'background-color: ' + secondary_color">Tahun Ajaran Aktif</span>
                                </div>

                                <!-- Cards Layout in Mockup -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="p-3 bg-white/5 border border-white/5 rounded-xl shadow-md">
                                        <p class="text-[8px] text-slate-400 font-semibold leading-tight">Total Siswa Aktif</p>
                                        <h4 class="text-sm font-black mt-1" :style="'color: ' + primary_color">1.240 Siswa</h4>
                                    </div>
                                    <div class="p-3 bg-white/5 border border-white/5 rounded-xl shadow-md">
                                        <p class="text-[8px] text-slate-400 font-semibold leading-tight">Kehadiran Hari Ini</p>
                                        <h4 class="text-sm font-black mt-1 text-emerald-400">98,2%</h4>
                                    </div>
                                </div>

                                <!-- Button & Badges in Mockup -->
                                <div class="p-3 bg-white/5 border border-white/5 rounded-xl flex items-center justify-between">
                                    <span class="text-[8px] font-bold text-slate-400">Pusat Pembayaran SPP</span>
                                    <button class="px-3 py-1 text-[8px] font-black text-white rounded-lg transition-all"
                                        :style="'background-color: ' + primary_color">
                                        Bayar Tagihan
                                    </button>
                                </div>
                            </div>

                            <!-- Footer Mockup -->
                            <div class="text-[8px] text-slate-500 font-semibold text-center border-t border-white/5 pt-2" x-text="footer_text"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tip Card -->
            <div class="p-5 bg-blue-500/10 border border-blue-500/20 text-blue-300 rounded-3xl backdrop-blur-md">
                <h4 class="text-xs font-bold flex items-center uppercase tracking-wider text-blue-200 mb-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Informasi Penyelarasan Font
                </h4>
                <p class="text-[11px] leading-relaxed">
                    Sistem akan menyinkronkan seluruh tampilan aplikasi secara instan begitu tombol simpan diklik. Font teks yang dipilih akan dimuat secara dinamis via Google Fonts API untuk performa yang optimal dan visual premium.
                </p>
            </div>
        </div>

        <!-- Reset Factory Default Modal Confirmation -->
        <div x-show="resetConfirm" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/60 backdrop-blur-sm px-4" style="display: none;">
            <div @click.away="resetConfirm = false" class="bg-gray-900 border border-gray-800 rounded-3xl p-6 max-w-md w-full shadow-2xl relative">
                <div class="h-12 w-12 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-500 mb-4 mx-auto">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h4 class="text-base font-black text-white text-center uppercase tracking-wide">Konfirmasi Reset Tampilan</h4>
                <p class="text-xs text-gray-400 mt-2 text-center leading-relaxed">
                    Apakah Anda yakin ingin menghapus semua branding kustom (Logo, favicon, warna kustom, dll) dan mengembalikannya ke setelan bawaan default?
                </p>
                <div class="mt-6 flex space-x-3 justify-center">
                    <button @click="resetConfirm = false" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-xs font-bold transition-all">
                        Batalkan
                    </button>
                    <form action="{{ route('admin.appearance.reset') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-red-600/20">
                            Reset ke Bawaan
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
