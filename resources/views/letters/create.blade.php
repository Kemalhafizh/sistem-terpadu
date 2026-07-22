<x-app-layout>
    <x-slot name="title">Buat Draf Surat Resmi</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Buat Draf: {{ $template->name }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Gunakan formulir di kiri untuk mengisi data secara asinkron. Tinjauan surat fisik akan ter-update secara real-time di kanan.</p>
            </div>
            <a href="{{ route('letters.index') }}" class="inline-flex items-center justify-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Kembali
            </a>
        </div>
    </x-slot>

    <div class="h-full flex flex-col lg:flex-row gap-6 overflow-hidden pb-10" 
         x-data="{
             issue_date: '{{ now()->format('Y-m-d') }}',
             student_id: '',
             employee_id: '',
             field_values: {},
             notes: '',
             students: @json($students),
             employees: @json($employees),
             bodyTemplate: @json($template->body_template),
             requiredFields: @json($template->required_fields),
             
             init() {
                 // Initialize dynamic field_values keys from required_fields excluding student_id and employee_id
                 this.requiredFields.forEach(field => {
                     if (field !== 'student_id' && field !== 'employee_id') {
                         this.field_values[field] = '';
                     }
                 });
             },

             get formattedIssueDate() {
                 if (!this.issue_date) return '...................';
                 const options = { day: 'numeric', month: 'long', year: 'numeric' };
                 return new Date(this.issue_date).toLocaleDateString('id-ID', options);
             },

             get renderedBody() {
                 let body = this.bodyTemplate;
                 
                 // Student Replacements
                 if (this.student_id) {
                     let s = this.students.find(x => x.id == this.student_id);
                     if (s) {
                         body = body.replace(/\{\{nama_siswa\}\}/g, s.name || '-');
                         body = body.replace(/\{\{nis\}\}/g, s.nis || '-');
                         body = body.replace(/\{\{nisn\}\}/g, s.nisn || '-');
                         body = body.replace(/\{\{kelas\}\}/g, s.classroom ? s.classroom.name : '-');
                         body = body.replace(/\{\{tempat_lahir\}\}/g, s.birth_place || '-');
                         body = body.replace(/\{\{tanggal_lahir\}\}/g, s.birth_date ? new Date(s.birth_date).toLocaleDateString('id-ID', {day:'numeric', month:'numeric', year:'numeric'}) : '-');
                     }
                 } else {
                     body = body.replace(/\{\{nama_siswa\}\}/g, '<span class="text-amber-500 font-extrabold underline">[Nama Siswa]</span>');
                     body = body.replace(/\{\{nis\}\}/g, '<span class="text-amber-500 font-extrabold underline">[NIS]</span>');
                     body = body.replace(/\{\{nisn\}\}/g, '<span class="text-amber-500 font-extrabold underline">[NISN]</span>');
                     body = body.replace(/\{\{kelas\}\}/g, '<span class="text-amber-500 font-extrabold underline">[Kelas]</span>');
                     body = body.replace(/\{\{tempat_lahir\}\}/g, '<span class="text-amber-500 font-extrabold underline">[Tempat Lahir]</span>');
                     body = body.replace(/\{\{tanggal_lahir\}\}/g, '<span class="text-amber-500 font-extrabold underline">[Tanggal Lahir]</span>');
                 }

                 // Employee Replacements
                 if (this.employee_id) {
                     let e = this.employees.find(x => x.id == this.employee_id);
                     if (e) {
                         body = body.replace(/\{\{nama_pegawai\}\}/g, e.name || '-');
                         body = body.replace(/\{\{nip\}\}/g, e.nip || '-');
                         body = body.replace(/\{\{jabatan\}\}/g, e.position || '-');
                     }
                 } else {
                     body = body.replace(/\{\{nama_pegawai\}\}/g, '<span class="text-amber-500 font-extrabold underline">[Nama Pegawai]</span>');
                     body = body.replace(/\{\{nip\}\}/g, '<span class="text-amber-500 font-extrabold underline">[NIP]</span>');
                     body = body.replace(/\{\{jabatan\}\}/g, '<span class="text-amber-500 font-extrabold underline">[Jabatan]</span>');
                 }

                 // Custom values replacements
                 for (let key in this.field_values) {
                     let val = this.field_values[key];
                     let replacement = val ? val : `<span class="text-amber-500 font-extrabold underline">[${key}]</span>`;
                     let re = new RegExp(`\\{\\{${key}\\}\\}`, 'g');
                     body = body.replace(re, replacement);
                 }
                 
                 return body;
             }
         }"
    >
        <!-- PANEL KIRI: FORM ENTRI (40%) -->
        <div class="w-full lg:w-[40%] bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-y-auto max-h-[85vh]">
            <h4 class="text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4 border-b border-gray-100 dark:border-gray-700/50 pb-2">
                 Pengisian Data Surat
            </h4>

            <form action="{{ route('letters.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="template_id" value="{{ $template->id }}">

                <!-- issue_date -->
                <div>
                    <label for="issue_date" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Tanggal Surat</label>
                    <input type="date" name="issue_date" id="issue_date" x-model="issue_date" required class="w-full bg-gray-55 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                </div>

                <!-- student_id -->
                @if(in_array('student_id', $template->required_fields))
                <div>
                    <label for="student_id" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Pilih Siswa</label>
                    <select name="student_id" id="student_id" x-model="student_id" required class="w-full bg-gray-55 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} (Kelas: {{ $s->classroom?->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- employee_id -->
                @if(in_array('employee_id', $template->required_fields))
                <div>
                    <label for="employee_id" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Pilih Pegawai / Guru</label>
                    <select name="employee_id" id="employee_id" x-model="employee_id" required class="w-full bg-gray-55 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} (NIP: {{ $e->nip ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Custom Fields -->
                @foreach($template->required_fields as $field)
                    @if($field !== 'student_id' && $field !== 'employee_id')
                        <div>
                            <label for="field_{{ $field }}" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">{{ ucwords(str_replace('_', ' ', $field)) }}</label>
                            @if(str_contains($field, 'date') || str_contains($field, 'tanggal'))
                                <input type="date" name="field_values[{{ $field }}]" id="field_{{ $field }}" x-model="field_values['{{ $field }}']" required class="w-full bg-gray-55 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                            @else
                                <input type="text" name="field_values[{{ $field }}]" id="field_{{ $field }}" x-model="field_values['{{ $field }}']" required placeholder="Masukkan data untuk {{ $field }}..." class="w-full bg-gray-55 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                            @endif
                        </div>
                    @endif
                @endforeach

                <!-- Notes / Keterangan Tambahan -->
                <div>
                    <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Catatan Internal (Opsional)</label>
                    <textarea name="notes" id="notes" x-model="notes" rows="3" placeholder="Misal: Draf dibuat atas permohonan wali kelas..." class="w-full bg-gray-55 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500"></textarea>
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50 flex gap-2">
                    <a href="{{ route('letters.index') }}" class="w-1/2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-bold py-3 px-4 rounded-xl text-xs uppercase tracking-wider text-center transition-all">
                        Batal
                    </a>
                    <button type="submit" class="w-1/2 bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-4 rounded-xl text-xs uppercase tracking-wider text-center transition-all shadow-md shadow-primary-500/20">
                        Simpan Draf
                    </button>
                </div>
            </form>
        </div>

        <!-- PANEL KANAN: LIVE PREVIEW (60%) -->
        <div class="w-full lg:w-[60%] flex flex-col h-[85vh]">
            <div class="flex justify-between items-center mb-2 px-2">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400"> Tinjauan Cetak (A4 Live Preview)</span>
                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500 bg-emerald-500/10 px-2 py-0.5 rounded">Asinkron & Aktif</span>
            </div>
            
            <div class="flex-grow bg-gray-200 dark:bg-gray-900 border border-gray-300 dark:border-gray-750 rounded-3xl shadow-inner overflow-y-auto p-8 flex justify-center items-start">
                <div class="w-[21cm] min-h-[29.7cm] bg-white text-black p-[2cm] shadow-2xl border border-gray-300 rounded font-serif text-[12pt] leading-relaxed relative flex flex-col justify-between">
                    
                    <!-- Paper Content Top -->
                    <div>
                        <!-- Kop Surat Resmi -->
                        @if($template->header_html)
                            {!! $template->header_html !!}
                        @else
                            <div class="flex border-b-4 border-double border-black pb-3 mb-6 items-center">
                                <div class="w-[80px] text-center pr-4">
                                    <!-- Embedded system logo -->
                                    <div class="w-[60px] h-[60px] bg-gray-150 border border-gray-400 rounded-full flex items-center justify-center text-[10px] font-sans font-bold">LOGO</div>
                                </div>
                                <div class="flex-grow text-center">
                                    <h2 class="m-0 text-[14pt] uppercase font-bold">Yayasan Badan Wakaf Sultan Agung</h2>
                                    <h1 class="m-0 text-[16pt] uppercase font-bold tracking-wide">SMA Islam Sultan Agung 1</h1>
                                    <p class="m-0 text-[9pt] italic leading-tight">Terakreditasi A (Unggul)</p>
                                    <p class="m-0 text-[9pt] italic leading-tight">Jl. Mataram No.657, Wonodri, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50242, Telp: (024) 8313922 | Email: info@smaislam-sa1.sch.id</p>
                                </div>
                            </div>
                        @endif

                        <!-- Judul & Nomor Surat -->
                        <div class="text-center mb-8">
                            <h3 class="text-[13pt] uppercase font-bold underline m-0">{{ $template->name }}</h3>
                            <p class="text-[11px] font-sans text-gray-500 m-0 mt-1">[Nomor Surat otomatis digenerate saat diterbitkan]</p>
                        </div>

                        <!-- Badan Surat Hasil Render -->
                        <div class="text-justify font-serif text-[12pt] leading-relaxed" x-html="renderedBody"></div>
                    </div>

                    <!-- Paper Content Bottom (Tanda Tangan) -->
                    <div class="mt-12 flex justify-between">
                        <div class="w-1/2"></div>
                        <div class="w-1/2 text-left pl-10">
                            <p class="m-0">Semarang, <span x-text="formattedIssueDate"></span></p>
                            <p class="m-0 mt-1">{{ $template->signer_title ?? 'Kepala Sekolah' }},</p>
                            
                            <!-- Digital Signature Scan Placeholder -->
                            <div class="h-[90px] relative my-2">
                                @if($template->signature_path)
                                    <!-- Fetch private file via controller or render a secure preview placeholder -->
                                    <div class="absolute inset-0 flex items-center justify-start opacity-70">
                                        <span class="text-[10px] font-sans bg-emerald-500/20 text-emerald-700 px-2 py-1 rounded font-bold border border-emerald-500/30 uppercase tracking-widest">TTD DIGITAL TERSEMAT</span>
                                    </div>
                                @else
                                    <div class="absolute inset-0 flex items-center justify-start text-xs italic text-gray-400 font-sans">
                                        [Scan Tanda Tangan Kosong]
                                    </div>
                                @endif
                            </div>

                            <p class="m-0 font-bold underline">{{ $template->signer_name ?? 'Drs. H. Soetarto, M.Pd.' }}</p>
                            <p class="m-0 text-sm">NIP. [NIP Pegawai terkait]</p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
