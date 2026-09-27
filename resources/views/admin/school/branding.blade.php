<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-2xl border border-emerald-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0a2 2 0 002-2V7a2 2 0 00-2-2H9a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    Single-School Enterprise Core & Branding Engine
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Konfigurasi entitas tunggal terpusat {{ school_name() }} (YBWSA).</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            @if(!empty($profile['missing_official_fields']))
                <div class="p-5 rounded-3xl border border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 text-amber-800 dark:text-amber-200 space-y-1">
                    <p class="text-sm font-black">Data resmi belum lengkap: {{ implode(', ', $profile['missing_official_fields']) }}.</p>
                    <p class="text-xs">Kop surat, rapor, dan kuitansi tidak akan mencetak bagian ini sampai diisi. Sistem sengaja tidak memakai nilai contoh untuk data resmi.</p>
                </div>
            @endif

            <!-- Kop Surat Preview Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <h3 class="text-xs font-black uppercase text-gray-400 tracking-wider">Pratinjau Kop Surat Resmi Sekolah (PDF & Dokumen)</h3>
                
                <div class="p-6 bg-slate-50 dark:bg-gray-900 rounded-2xl border-2 border-slate-900 dark:border-gray-700 text-center space-y-1 font-serif">
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-widest block">{{ $headerData['line_1'] }}</span>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $headerData['line_2'] }}</h2>
                    @if($headerData['line_3'])
                        <p class="text-xs text-slate-600 dark:text-gray-300 font-sans font-bold">{{ $headerData['line_3'] }}</p>
                    @endif
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 font-sans">{{ $headerData['line_4'] }}</p>
                </div>
                <div class="text-right text-xs font-serif text-slate-700 dark:text-gray-300 pr-4">
                    <p>{{ $headerData['principal_signature_title'] }},</p>
                    <p class="mt-8 font-bold underline">{{ $headerData['principal_name'] ?: '(nama kepala sekolah belum diisi)' }}</p>
                    <p>NIP: {{ $headerData['principal_nip'] ?: '—' }}</p>
                </div>
            </div>

            <!-- Profile Settings Form -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-6">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-4">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Identitas Lembaga & Kepala Sekolah</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pengaturan ini menjadi sumber rujukan tunggal (Single Source of Truth) seluruh modul aplikasi.</p>
                </div>

                <form action="{{ route('admin.school.branding.update') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Sekolah</label>
                            <input type="text" name="school_name" value="{{ old('school_name', $profile['school_name']) }}" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Singkat Sekolah</label>
                            <input type="text" name="school_short_name" value="{{ old('school_short_name', $profile['school_short_name']) }}" placeholder="{{ config('school.short_name') }}" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold focus:ring-2 focus:ring-emerald-500">
                            <p class="text-[11px] text-gray-500 mt-1">Dipakai di kalimat berjalan dan tanda tangan (tanpa nama kota).</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Yayasan</label>
                            <input type="text" name="foundation_name" value="{{ old('foundation_name', $profile['foundation_name']) }}" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">NPSN</label>
                            <input type="text" name="school_npsn" value="{{ old('school_npsn', $profile['npsn']) }}" required inputmode="numeric" maxlength="8" placeholder="8 digit" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status Akreditasi</label>
                            <input type="text" name="school_akreditasi" value="{{ old('school_akreditasi', $profile['akreditasi']) }}" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Alamat Lengkap Sekolah</label>
                            <input type="text" name="school_address" value="{{ old('school_address', $profile['address']) }}" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Link Tautan Google Maps Sekolah</label>
                            <div class="flex gap-2">
                                <input type="url" name="school_maps_url" value="{{ old('school_maps_url', $profile['maps_url']) }}" placeholder="https://maps.app.goo.gl/…" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono focus:ring-2 focus:ring-emerald-500">
                                <a href="https://www.google.com/maps?q={{ $profile['latitude'] }},{{ $profile['longitude'] }}" target="_blank" rel="noopener" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shrink-0 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    Cek Titik Presensi
                                </a>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">Tombol membuka koordinat presensi di bawah (bukan tautan di atas) agar titiknya bisa dipastikan tepat di sekolah.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Koordinat GPS Latitude</label>
                            <input type="text" name="school_latitude" value="{{ old('school_latitude', $profile['latitude']) }}" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Koordinat GPS Longitude & Radius (Meter)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="school_longitude" value="{{ old('school_longitude', $profile['longitude']) }}" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono focus:ring-2 focus:ring-emerald-500">
                                <input type="number" name="school_geofence_radius" value="{{ old('school_geofence_radius', $profile['geofence_radius']) }}" required min="20" max="5000" placeholder="Radius (Meter)" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">Titik &amp; radius ini dipakai semua presensi GPS (siswa via aplikasi, guru/staf via HRD).</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nomor Telepon</label>
                            <input type="text" name="school_phone" value="{{ old('school_phone', $profile['phone']) }}" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email Resmi Sekolah</label>
                            <input type="email" name="school_email" value="{{ old('school_email', $profile['email']) }}" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">WhatsApp Sekolah</label>
                            <input type="text" name="school_whatsapp" value="{{ old('school_whatsapp', \App\Support\School::whatsapp()) }}" placeholder="+628…" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono focus:ring-2 focus:ring-emerald-500">
                            <p class="text-[11px] text-gray-500 mt-1">Ditampilkan sebagai kontak bantuan di aplikasi HP.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Website Resmi Sekolah</label>
                            <input type="url" name="school_website" value="{{ old('school_website', $profile['website']) }}" placeholder="https://…" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Kepala Sekolah</label>
                            <input type="text" name="principal_name" value="{{ old('principal_name', $profile['principal_name']) }}" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">NIP Kepala Sekolah</label>
                            <input type="text" name="principal_nip" value="{{ old('principal_nip', $profile['principal_nip']) }}" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tahun Ajaran Aktif</label>
                            <div class="w-full bg-gray-100 dark:bg-gray-900/60 text-gray-700 dark:text-gray-300 rounded-xl border border-gray-200 dark:border-gray-700 text-sm px-4 py-2.5 font-bold">
                                {{ $profile['academic_year'] ?? 'Belum ada tahun ajaran aktif' }}
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">Diambil dari data Tahun Ajaran yang aktif, bukan diketik di sini.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition-all">
                            Simpan Konfigurasi Enterprise
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
