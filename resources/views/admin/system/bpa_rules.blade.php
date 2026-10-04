<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.601 15.1a2 2 0 00-1.118 3.866l2.387.477a8 8 0 005.147-.69l.318-.158a8 8 0 015.147-.69l2.387.477a2 2 0 001.022-3.866z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v8m0 0l-3-3m3 3l3-3"></path>
                        </svg>
                    </span>
                    No-Code Business Process Automation (BPA) Rule Engine
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Konfigurasi alur kerja otomatis berbasis kondisi tanpa pengodean ulang aplikasi.</p>
            </div>
            <form action="{{ route('admin.bpa.rules.test-execute') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    </svg>
                    Simulasikan Evaluasi Aturan
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Summary Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Aturan Bisnis</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $totalRules }}</span>
                        <span class="text-xs text-gray-500">Rules Configured</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Aturan Aktif</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $activeRules }}</span>
                        <span class="text-xs text-gray-500">Active Pipeline</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Tingkat Kesesuaian (Match Rate)</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $matchRate }}%</span>
                        <span class="text-xs text-gray-500">Trigger Match</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Eksekusi Otomatisasi</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $totalExecutions }}</span>
                        <span class="text-xs text-gray-500">Evaluations</span>
                    </div>
                </div>
            </div>

            <!-- Business Rule Builder Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Buat Aturan Otomatisasi Bisnis Baru (No-Code Rule Builder)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tentukan event pemicu, kondisi kriteria (IF), dan tindakan otomatisasi (THEN).</p>
                </div>

                <form action="{{ route('admin.bpa.rules.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Aturan Bisnis</label>
                            <input type="text" name="name" required placeholder="Tiket BK Otomatis Siswa Presensi Rendah" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Event Pemicu (Trigger Event)</label>
                            <select name="trigger_event" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold focus:ring-2 focus:ring-indigo-500">
                                <option value="attendance.checked">attendance.checked (Presensi Siswa Dikeluarkan)</option>
                                <option value="employee.lateness">employee.lateness (Keterlambatan Pegawai Logged)</option>
                                <option value="student.point_logged">student.point_logged (Poin Pelanggaran Siswa)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Condition & Action Sample Inputs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-slate-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 text-xs">
                        <div class="space-y-2">
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider block">JIKA (IF Conditions):</span>
                            <div class="grid grid-cols-3 gap-2">
                                <input type="text" name="conditions[0][field]" value="attendance_percentage" required placeholder="Field" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700 p-2 font-mono text-xs">
                                <select name="conditions[0][operator]" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700 p-2 font-mono text-xs">
                                    <option value="<">&lt; (Kurang Dari)</option>
                                    <option value=">">&gt; (Lebih Dari)</option>
                                    <option value="==">== (Sama Dengan)</option>
                                </select>
                                <input type="text" name="conditions[0][value]" value="80" required placeholder="Value" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700 p-2 font-mono text-xs">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">MAKA (THEN Actions):</span>
                            <div class="grid grid-cols-2 gap-2">
                                <select name="actions[0][action_type]" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700 p-2 font-mono text-xs">
                                    <option value="issue_counseling_ticket">issue_counseling_ticket (Terbitkan Tiket BK)</option>
                                    <option value="apply_payroll_deduction">apply_payroll_deduction (Terap Potongan Payroll)</option>
                                    <option value="issue_warning_letter_sp1">issue_warning_letter_sp1 (Terbitkan SP-1)</option>
                                    <option value="send_whatsapp_alert">send_whatsapp_alert (Kirim WA Alert)</option>
                                </select>
                                <input type="text" name="actions[0][params][recipient]" value="parent" placeholder="Param (Opsional)" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700 p-2 font-mono text-xs">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all">
                            Simpan Aturan Otomatisasi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Active Business Rules List Cards -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Daftar Aturan Bisnis Terkonfigurasi</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Aturan bisnis aktif yang secara otomatis dievaluasi saat event sekolah terjadi.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @forelse($rules as $rule)
                        <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700/80 space-y-3 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start gap-2">
                                    <h4 class="text-sm font-black text-gray-900 dark:text-white">{{ $rule->name }}</h4>
                                    <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold uppercase {{ $rule->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-gray-200 text-gray-600' }}">
                                        {{ $rule->is_active ? 'AKTIF' : 'NON-AKTIF' }}
                                    </span>
                                </div>
                                <span class="text-[10px] font-mono text-indigo-600 dark:text-indigo-400 font-bold block mt-1">Event: {{ $rule->trigger_event }}</span>

                                <div class="mt-3 space-y-1 font-mono text-[11px] text-gray-600 dark:text-gray-300">
                                    <span class="font-bold text-gray-400 block text-[10px] uppercase">Syarat Conditions:</span>
                                    @foreach($rule->conditions ?? [] as $c)
                                        <div class="bg-white dark:bg-gray-800 p-1.5 rounded border border-gray-200 dark:border-gray-700">
                                            IF {{ $c['field'] ?? '-' }} {{ $c['operator'] ?? '==' }} {{ $c['value'] ?? '-' }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <form action="{{ route('admin.bpa.rules.toggle', $rule->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                        {{ $rule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.bpa.rules.delete', $rule->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus aturan bisnis ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-rose-500 hover:text-rose-700">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-8 text-gray-400 italic">
                            Belum ada aturan bisnis terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Rule Executions Log Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden space-y-4">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Log Eksekusi Otomatisasi Aturan Bisnis</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Riwayat evaluasi kondisi dan eksekusi tindakan otomatisasi bisnis.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Nama Aturan</th>
                                <th class="px-6 py-4">Event Pemicu</th>
                                <th class="px-6 py-4">Evaluasi Condition</th>
                                <th class="px-6 py-4">Tindakan Dieksekusi</th>
                                <th class="px-6 py-4">Latensi (ms)</th>
                                <th class="px-6 py-4">Waktu Eksekusi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($recentExecutions as $exec)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-bold">{{ optional($exec->rule)->name ?? 'Aturan Dihapus' }}</td>
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $exec->trigger_event }}</td>
                                    <td class="px-6 py-4">
                                        @if($exec->matched)
                                            <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 rounded font-mono text-[10px] font-bold">MATCHED (COCOK)</span>
                                        @else
                                            <span class="px-2.5 py-0.5 bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded font-mono text-[10px] font-bold">SKIPPED</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-mono">
                                        @if($exec->status === 'NOT_EXECUTED')
                                            <span class="text-amber-600 dark:text-amber-400 font-bold">{{ __(':count belum dijalankan', ['count' => count($exec->actions_executed ?? [])]) }}</span>
                                            <span class="block text-[10px] text-gray-400">{{ __('Aksi belum memiliki pelaksana') }}</span>
                                        @else
                                            0 Tindakan
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $exec->execution_time_ms }} ms</td>
                                    <td class="px-6 py-4 font-mono text-gray-500">{{ $exec->created_at->format('d M Y H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">Belum ada riwayat eksekusi aturan bisnis.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
