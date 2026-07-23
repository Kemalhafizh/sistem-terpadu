<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-800 dark:text-white leading-tight tracking-tight uppercase">
                    {{ __('WhatsApp Automations') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kelola aturan pengiriman pesan otomatis berdasarkan pemicu kejadian di sistem sekolah.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            
            <!-- Navigation Tabs -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-2 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-wrap gap-2">
                <a href="{{ route('admin.whatsapp.dashboard') }}" class="px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    Dashboard
                </a>
                <a href="{{ route('admin.whatsapp.templates.index') }}" class="px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    Templates
                </a>
                <a href="{{ route('admin.whatsapp.broadcasts.index') }}" class="px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    Broadcasts
                </a>
                <a href="{{ route('admin.whatsapp.automations.index') }}" class="px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all bg-teal-600 text-white shadow-sm hover:scale-102">
                    Automations
                </a>
                <a href="{{ route('admin.whatsapp.index') }}" class="px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    Settings
                </a>
                <a href="{{ route('admin.whatsapp.logs') }}" class="px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    Logs
                </a>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/30 text-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-xs font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/30 text-red-800 dark:text-red-300 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-xs font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- List of automation triggers -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Aturan Pemicu Otomasi WhatsApp</h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-teal-100 dark:bg-teal-900/30 text-teal-850 dark:text-teal-350">
                        {{ $automations->count() }} Aturan
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30 text-gray-400 uppercase tracking-widest font-black">
                                <th class="py-4 px-6">Nama Otomasi</th>
                                <th class="py-4 px-6">Event Pemicu (Trigger)</th>
                                <th class="py-4 px-6">Template Aktif</th>
                                <th class="py-4 px-6">Status Toggle</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($automations as $automation)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/10 transition-colors">
                                    <td class="py-4 px-6 font-bold text-gray-900 dark:text-white">
                                        {{ $automation->name }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <code class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-mono text-[10px]">
                                            {{ $automation->trigger }}
                                        </code>
                                    </td>
                                    <td class="py-4 px-6">
                                        <form action="{{ route('admin.whatsapp.automations.store') }}" method="POST" id="form-automation-{{ $automation->id }}" class="flex items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="trigger" value="{{ $automation->trigger }}">
                                            <input type="hidden" name="name" value="{{ $automation->name }}">
                                            <select name="template_id" class="text-[11px] bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-2.5 py-1.5 focus:ring-teal-500 focus:border-teal-500 text-gray-800 dark:text-white font-medium min-w-[200px]">
                                                @foreach($templates as $template)
                                                    <option value="{{ $template->id }}" {{ $automation->template_id == $template->id ? 'selected' : '' }}>
                                                        {{ $template->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                    <td class="py-4 px-6">
                                        <form action="{{ route('admin.whatsapp.automations.toggle', $automation->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="relative inline-flex items-center cursor-pointer group">
                                                <div class="relative w-10 h-5 bg-gray-200 group-hover:bg-gray-300 dark:bg-gray-700 rounded-full transition-colors {{ $automation->is_active ? 'bg-teal-600' : '' }}">
                                                    <div class="absolute top-[2px] start-[2px] bg-white border border-gray-300 dark:border-gray-650 rounded-full h-4 w-4 transition-transform {{ $automation->is_active ? 'translate-x-5' : '' }}"></div>
                                                </div>
                                                <span class="ml-2 font-bold uppercase text-[9px] tracking-wider {{ $automation->is_active ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400' }}">
                                                    {{ $automation->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <button type="submit" form="form-automation-{{ $automation->id }}" class="inline-flex items-center px-3 py-1.5 bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 hover:bg-teal-600 hover:text-white rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all focus:outline-none hover:-translate-y-0.5">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2v-9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                            Simpan
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-16 text-center text-gray-400 dark:text-gray-500 italic">
                                        Tidak ada aturan otomasi yang dikonfigurasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Automation Trigger Details Reference Section -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                <h3 class="font-bold text-sm text-gray-800 dark:text-white uppercase tracking-wider border-b border-gray-50 dark:border-gray-700 pb-3 mb-4">Panduan & Referensi Event Pemicu (Trigger)</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Billing Reminder H-3 -->
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="p-1.5 rounded-lg bg-teal-50 dark:bg-teal-950/30 text-teal-650 dark:text-teal-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-850 dark:text-white uppercase tracking-wider">Remind Tagihan H-3</span>
                        </div>
                        <code class="block text-[9px] text-gray-400 dark:text-gray-500 font-mono mb-2">billing_due_3_days</code>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                            Dikirim ke wali murid tepat 3 hari sebelum jatuh tempo tagihan SPP atau iuran bulanan untuk menghindari keterlambatan.
                        </p>
                    </div>

                    <!-- Overdue Billing -->
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="p-1.5 rounded-lg bg-red-50 dark:bg-red-950/30 text-red-650 dark:text-red-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-850 dark:text-white uppercase tracking-wider">Tagihan Terlambat</span>
                        </div>
                        <code class="block text-[9px] text-gray-400 dark:text-gray-500 font-mono mb-2">billing_overdue</code>
                        <p class="text-[11px] text-gray-550 dark:text-gray-400 leading-relaxed font-medium">
                            Pemberitahuan kepada orang tua jika pembayaran SPP/iuran belum diselesaikan melewati tanggal jatuh tempo yang ditentukan.
                        </p>
                    </div>

                    <!-- Attendance Absent -->
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/30 text-amber-655 dark:text-amber-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-855 dark:text-white uppercase tracking-wider">Absensi Siswa Alpa</span>
                        </div>
                        <code class="block text-[9px] text-gray-400 dark:text-gray-500 font-mono mb-2">attendance_absent</code>
                        <p class="text-[11px] text-gray-550 dark:text-gray-400 leading-relaxed font-medium">
                            Kirim pesan instan secara otomatis kepada orang tua di pagi hari ketika siswa tidak hadir tanpa keterangan (Alpa).
                        </p>
                    </div>

                    <!-- CBT Exam Reminder -->
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/30 text-blue-650 dark:text-blue-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-855 dark:text-white uppercase tracking-wider">Ujian CBT Besok</span>
                        </div>
                        <code class="block text-[9px] text-gray-400 dark:text-gray-500 font-mono mb-2">exam_upcoming_1_day</code>
                        <p class="text-[11px] text-gray-550 dark:text-gray-400 leading-relaxed font-medium">
                            Pemberitahuan terjadwal sehari sebelum pelaksanaan ujian online CBT agar siswa mempersiapkan diri.
                        </p>
                    </div>

                    <!-- UKS Emergency -->
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/30 text-rose-650 dark:text-rose-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-855 dark:text-white uppercase tracking-wider">Darurat UKS</span>
                        </div>
                        <code class="block text-[9px] text-gray-400 dark:text-gray-500 font-mono mb-2">uks_visit_severe</code>
                        <p class="text-[11px] text-gray-550 dark:text-gray-400 leading-relaxed font-medium">
                            Kirim notifikasi mendesak/darurat ketika siswa dirujuk ke UKS dengan indikasi gejala atau sakit yang parah/severe.
                        </p>
                    </div>

                    <!-- Grade Below KKM -->
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 text-indigo-650 dark:text-indigo-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-855 dark:text-white uppercase tracking-wider">Nilai di Bawah KKM</span>
                        </div>
                        <code class="block text-[9px] text-gray-400 dark:text-gray-500 font-mono mb-2">grade_below_kkm</code>
                        <p class="text-[11px] text-gray-550 dark:text-gray-400 leading-relaxed font-medium">
                            Kirim pemberitahuan nilai hasil ujian kepada orang tua apabila nilai siswa di bawah kriteria kelulusan minimum (KKM).
                        </p>
                    </div>

                    <!-- Mutaba'ah Grade D -->
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="p-1.5 rounded-lg bg-orange-50 dark:bg-orange-950/30 text-orange-650 dark:text-orange-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-855 dark:text-white uppercase tracking-wider">Mutaba'ah Kurang</span>
                        </div>
                        <code class="block text-[9px] text-gray-400 dark:text-gray-500 font-mono mb-2">mutabaah_grade_d</code>
                        <p class="text-[11px] text-gray-550 dark:text-gray-400 leading-relaxed font-medium">
                            Kirim laporan khusus jika mutaba'ah yaumiyah (ibadah & karakter harian) siswa mendapat predikat D (kurang).
                        </p>
                    </div>

                    <!-- Tahfidz Behind Target -->
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-300">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-650 dark:text-emerald-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-855 dark:text-white uppercase tracking-wider">Tahfidz Tertinggal</span>
                        </div>
                        <code class="block text-[9px] text-gray-400 dark:text-gray-500 font-mono mb-2">tahfidz_behind_target</code>
                        <p class="text-[11px] text-gray-550 dark:text-gray-400 leading-relaxed font-medium">
                            Kirim pengingat jika progres setoran hafalan Al-Qur'an siswa belum mencapai target berkala yang ditentukan.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
