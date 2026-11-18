<x-app-layout>
    <x-slot name="title">Kelola Template Surat</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Kelola Template Surat Resmi
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Buat dan konfigurasikan template surat dengan placeholders dinamis, otorisasi penandatangan, dan scan TTD digital.
                </p>
            </div>
            <a href="{{ route('letters.index') }}" class="inline-flex items-center justify-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Kembali ke Surat
            </a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
        
        <!-- PANEL KIRI: LIST TEMPLATE AKTIF -->
        <div class="xl:col-span-2 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
            <h4 class="text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700/50 pb-2">
                 Daftar Template Surat
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($templates as $template)
                    <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200/60 dark:border-gray-700 p-5 rounded-2xl flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start gap-2">
                                <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400 rounded-md text-[10px] font-black tracking-widest uppercase">
                                    {{ $template->code }}
                                </span>
                                @if($template->is_active)
                                    <span class="text-[10px] font-black uppercase text-emerald-500 bg-emerald-500/10 px-2 py-0.5 rounded">AKTIF</span>
                                @endif
                            </div>
                            <h5 class="font-extrabold text-gray-800 dark:text-white text-base mt-2">{{ $template->name }}</h5>
                            
                            <!-- Placeholder Tags -->
                            <div class="flex flex-wrap gap-1 mt-3">
                                @foreach($template->required_fields as $field)
                                    <span class="text-[9px] font-bold bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded-full border border-gray-300/40 dark:border-gray-700/40">
                                        {{ $field }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-200/40 dark:border-gray-700/30 flex justify-between items-center text-xs">
                            <div class="text-gray-400 font-semibold">
                                Penandatangan: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $template->signer_name }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-2 text-center py-10 text-gray-400 font-semibold">
                        Belum ada template surat yang dibuat. Silakan tambahkan template baru di panel kanan.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- PANEL KANAN: FORM TAMBAH TEMPLATE -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
            <h4 class="text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700/50 pb-2">
                 Tambah Template Baru
            </h4>

            <form action="{{ route('letters.templates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- name -->
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Nama Surat</label>
                    <input type="text" name="name" id="name" required placeholder="Misal: Surat Keterangan Aktif Siswa" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                </div>

                <!-- code -->
                <div>
                    <label for="code" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Kode Surat (Max 10 Karakter)</label>
                    <input type="text" name="code" id="code" required placeholder="Misal: SKA, STG, SDM" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                </div>

                <!-- body_template -->
                <div>
                    <label for="body_template" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Badan Surat (HTML / Text)</label>
                    <textarea name="body_template" id="body_template" rows="8" required placeholder="Tuliskan badan surat di sini... Gunakan placeholder @{{nama_siswa}}, @{{nisn}}, @{{purpose}}, dsb." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500 font-mono"></textarea>
                    
                    <div class="mt-2 bg-gray-50 dark:bg-gray-900 border border-gray-200/40 dark:border-gray-700/30 p-3 rounded-xl">
                        <span class="block text-[10px] font-black uppercase text-gray-400 tracking-wider"> PANDUAN PLACEHOLDER:</span>
                        <div class="grid grid-cols-2 gap-1 text-[10px] text-gray-500 font-semibold mt-1">
                            <div><code>&#123;&#123;nama_siswa&#125;&#125;</code>: Nama Siswa</div>
                            <div><code>&#123;&#123;nisn&#125;&#125;</code>: NISN Siswa</div>
                            <div><code>&#123;&#123;kelas&#125;&#125;</code>: Kelas Siswa</div>
                            <div><code>&#123;&#123;nama_pegawai&#125;&#125;</code>: Nama Guru</div>
                            <div><code>&#123;&#123;nip&#125;&#125;</code>: NIP Guru</div>
                            <div><code>&#123;&#123;jabatan&#125;&#125;</code>: Jabatan Guru</div>
                        </div>
                    </div>
                </div>

                <!-- required_fields -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Kolom Isian yang Diperlukan</label>
                    <div class="grid grid-cols-2 gap-2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="required_fields[]" value="student_id" class="rounded text-primary-600 border-gray-300 dark:border-gray-700">
                            <span>Dropdown Siswa</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="required_fields[]" value="employee_id" class="rounded text-primary-600 border-gray-300 dark:border-gray-700">
                            <span>Dropdown Guru</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="required_fields[]" value="purpose" class="rounded text-primary-600 border-gray-300 dark:border-gray-700">
                            <span>Keperluan (purpose)</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="required_fields[]" value="start_date" class="rounded text-primary-600 border-gray-300 dark:border-gray-700">
                            <span>Tgl Mulai (start_date)</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="required_fields[]" value="end_date" class="rounded text-primary-600 border-gray-300 dark:border-gray-700">
                            <span>Tgl Selesai (end_date)</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="required_fields[]" value="location" class="rounded text-primary-600 border-gray-300 dark:border-gray-700">
                            <span>Tempat (location)</span>
                        </label>
                    </div>
                </div>

                <!-- signer_name -->
                <div>
                    <label for="signer_name" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Nama Penandatangan</label>
                    <input type="text" name="signer_name" id="signer_name" required placeholder="Nama lengkap &amp; gelar penanda tangan" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                </div>

                <!-- signer_title -->
                <div>
                    <label for="signer_title" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Jabatan Penandatangan</label>
                    <input type="text" name="signer_title" id="signer_title" required placeholder="Kepala Sekolah" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                </div>

                <!-- signature image file -->
                <div>
                    <label for="signature" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Scan Tanda Tangan Scan (PNG Transparan)</label>
                    <input type="file" name="signature" id="signature" accept="image/*" class="w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gray-100 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-200 hover:file:bg-gray-200 dark:hover:file:bg-gray-600">
                    <p class="text-[10px] text-gray-400 font-semibold mt-1">Unggahan akan disimpan di private storage lokal untuk keamanan.</p>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-4 rounded-xl text-xs uppercase tracking-wider text-center transition-all shadow-md shadow-primary-500/20">
                        Buat Template
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
