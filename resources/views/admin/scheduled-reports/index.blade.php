<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Manajemen Laporan Terjadwal & Digest') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        showCreateModal: false,
        showEditModal: false,
        editReport: { id: null, name: '', report_type: 'daily_admin', cron_expression: '0 7 * * *', delivery_method: 'email', attach_pdf: true, is_active: true, recipients: [] },
        newRecipients: [{ type: 'email', value: '' }],
        editRecipients: [],
        
        addRecipientRow(mode) {
            if (mode === 'create') {
                this.newRecipients.push({ type: 'email', value: '' });
            } else {
                this.editRecipients.push({ type: 'email', value: '' });
            }
        },
        removeRecipientRow(mode, idx) {
            if (mode === 'create') {
                this.newRecipients.splice(idx, 1);
            } else {
                this.editRecipients.splice(idx, 1);
            }
        },
        setCronPreset(mode, val) {
            if (mode === 'create') {
                document.getElementById('create_cron').value = val;
            } else {
                this.editReport.cron_expression = val;
            }
        },
        openEdit(report) {
            this.editReport = Object.assign({}, report);
            this.editRecipients = Array.isArray(report.recipients) ? JSON.parse(JSON.stringify(report.recipients)) : [];
            this.showEditModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            
            <!-- Banner Dashboard -->
            <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-[2rem] p-8 border border-indigo-500/10 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 hover:shadow-indigo-500/5 transition-all duration-300">
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-indigo-300"> Pusat Laporan Otomatis & Email Digest</h3>
                    <p class="text-xs text-slate-300 font-medium leading-relaxed max-w-2xl">
                        Konfigurasikan jadwal pengiriman rekapitulasi data sekolah. Kepala sekolah, staf keuangan, administrator, dan orang tua murid akan menerima rangkuman performa secara otomatis melalui integrasi Email Mailable dan notifikasi WhatsApp Gateway.
                    </p>
                </div>
                <button @click="showCreateModal = true" class="w-full md:w-auto px-5 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-600/25 active:scale-95 flex items-center justify-center space-x-2">
                    <span>+ Buat Jadwal Baru</span>
                </button>
            </div>

            <!-- List of Schedules -->
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100">
                <h4 class="text-lg font-black text-gray-900 mb-6 flex items-center">
                    <span class="w-2.5 h-6 bg-indigo-600 rounded-full mr-3"></span>
                    Jadwal Laporan Terdaftar
                </h4>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="text-left text-xs font-black uppercase tracking-wider text-gray-400">
                                <th class="pb-4">Nama Laporan</th>
                                <th class="pb-4">Jenis Rangkuman</th>
                                <th class="pb-4">Jadwal (Cron)</th>
                                <th class="pb-4">Penerima</th>
                                <th class="pb-4">Metode</th>
                                <th class="pb-4">Status</th>
                                <th class="pb-4 text-center">Aksi Pengujian</th>
                                <th class="pb-4 text-right">Opsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm font-bold text-gray-700">
                            @forelse($reports as $report)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 text-gray-900 font-black">
                                        {{ $report->name }}
                                        <p class="text-[10px] text-gray-400 font-bold mt-1">Dibuat: {{ $report->created_at->translatedFormat('d M Y') }}</p>
                                    </td>
                                    <td class="py-4 text-slate-500">
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-800 rounded text-xs">
                                            {{ $types[$report->report_type] ?? $report->report_type }}
                                        </span>
                                    </td>
                                    <td class="py-4 font-mono text-xs text-indigo-600">{{ $report->cron_expression }}</td>
                                    <td class="py-4 text-xs font-medium max-w-xs truncate">
                                        @foreach($report->recipients as $rec)
                                            <span class="inline-block px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-md border border-indigo-100/50 mr-1 mb-1">
                                                {{ $rec['type'] === 'role' ? 'Role: ' : '' }}{{ $rec['value'] }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="py-4 text-xs font-black uppercase tracking-widest text-slate-500">
                                        {{ $report->delivery_method }}
                                    </td>
                                    <td class="py-4">
                                        @if($report->is_active)
                                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded text-xs font-black">Aktif</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded text-xs font-black">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-center">
                                        <!-- Test Trigger Button -->
                                        <form method="POST" action="{{ route('admin.scheduled-reports.trigger', $report->id) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" class="px-3.5 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                                                 Kirim Sekarang
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-4 text-right space-x-2">
                                        <button @click="openEdit({{ json_encode($report) }})" class="text-indigo-600 hover:underline">Edit</button>
                                        <form method="POST" action="{{ route('admin.scheduled-reports.destroy', $report->id) }}" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal laporan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-gray-400 italic">
                                        Belum ada jadwal laporan otomatis yang terdaftar. Klik tombol "+ Buat Jadwal Baru" untuk memulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- MODAL: BUAT BARU -->
        <!-- ========================================== -->
        <div x-show="showCreateModal" class="fixed inset-0 z-[400] overflow-y-auto" style="display: none;">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showCreateModal = false"></div>
                
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div class="inline-block transform overflow-hidden rounded-[2.5rem] bg-white text-left align-bottom shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl sm:align-middle border border-gray-100">
                    <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">+ Buat Jadwal Laporan Baru</h3>
                        <button @click="showCreateModal = false" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 transition-all">
                            
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.scheduled-reports.store') }}" class="px-8 py-6 space-y-5">
                        @csrf
                        
                        <!-- Name -->
                        <div class="space-y-1">
                            <label class="text-xs font-black uppercase tracking-widest text-gray-500">Nama Laporan:</label>
                            <input type="text" name="name" required placeholder="Contoh: Digest Harian Admin Sekolah" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Report Type -->
                        <div class="space-y-1">
                            <label class="text-xs font-black uppercase tracking-widest text-gray-500">Jenis Rangkuman:</label>
                            <select name="report_type" required class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="daily_admin">Harian Administrator (Daily Admin)</option>
                                <option value="weekly_kepsek">Mingguan Kepala Sekolah (Weekly Headmaster)</option>
                                <option value="monthly_finance">Bulanan Keuangan (Monthly Finance)</option>
                                <option value="weekly_parent">Mingguan Orang Tua (Weekly Parents)</option>
                            </select>
                        </div>

                        <!-- Cron Expression & Presets -->
                        <div class="space-y-1">
                            <label class="text-xs font-black uppercase tracking-widest text-gray-500">Jadwal Pengiriman (Cron Expression):</label>
                            <input type="text" name="cron_expression" id="create_cron" required value="0 7 * * *" placeholder="Contoh: 0 7 * * *" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 text-sm font-bold font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            
                            <!-- Presets -->
                            <div class="flex flex-wrap gap-2 pt-2">
                                <button type="button" @click="setCronPreset('create', '0 7 * * *')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-md text-[10px] font-black uppercase tracking-wider">Harian 07:00</button>
                                <button type="button" @click="setCronPreset('create', '0 8 * * 1')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-md text-[10px] font-black uppercase tracking-wider">Tiap Senin 08:00</button>
                                <button type="button" @click="setCronPreset('create', '0 8 1 * *')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-md text-[10px] font-black uppercase tracking-wider">Awal Bulan 08:00</button>
                                <button type="button" @click="setCronPreset('create', '* * * * *')" class="px-2.5 py-1 bg-orange-50 hover:bg-orange-100 text-orange-700 rounded-md text-[10px] font-black uppercase tracking-wider">Tiap Menit (Test)</button>
                            </div>
                        </div>

                        <!-- Dynamic Recipients Rows -->
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-black uppercase tracking-widest text-gray-500">Daftar Penerima:</label>
                                <button type="button" @click="addRecipientRow('create')" class="text-[10px] font-black text-indigo-600 hover:underline uppercase">+ Tambah Penerima</button>
                            </div>
                            
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(recipient, idx) in newRecipients" :key="idx">
                                    <div class="flex gap-2 items-center">
                                        <select :name="'recipients['+idx+'][type]'" class="bg-gray-50 border border-gray-200 rounded-xl py-2 px-3 text-xs font-bold w-1/3 focus:outline-none">
                                            <option value="email">Email Langsung</option>
                                            <option value="role">Role Pengguna</option>
                                        </select>
                                        <input type="text" :name="'recipients['+idx+'][value]'" required placeholder="Alamat email / Nama role" class="bg-gray-50 border border-gray-200 rounded-xl py-2 px-3 text-xs font-bold flex-1 focus:outline-none">
                                        <button type="button" @click="removeRecipientRow('create', idx)" class="text-rose-500 text-xs font-black px-2 hover:bg-rose-50 py-1.5 rounded-lg"></button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Method & Options -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-black uppercase tracking-widest text-gray-500">Metode Pengiriman:</label>
                                <select name="delivery_method" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 text-sm font-bold focus:outline-none">
                                    <option value="email">Email</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="both">Keduanya (Email & WA)</option>
                                </select>
                            </div>
                            <div class="flex flex-col justify-end space-y-3 pb-1">
                                <label class="inline-flex items-center text-xs font-bold text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="attach_pdf" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4 mr-2">
                                    Lampirkan Laporan PDF
                                </label>
                                <label class="inline-flex items-center text-xs font-bold text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4 mr-2">
                                    Jadwal Aktif
                                </label>
                            </div>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="pt-6 border-t border-gray-50 flex gap-3">
                            <button type="button" @click="showCreateModal = false" class="flex-1 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all">Batal</button>
                            <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-lg shadow-indigo-600/20">Simpan Jadwal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: EDIT -->
        <!-- ========================================== -->
        <div x-show="showEditModal" class="fixed inset-0 z-[400] overflow-y-auto" style="display: none;">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>
                
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div class="inline-block transform overflow-hidden rounded-[2.5rem] bg-white text-left align-bottom shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl sm:align-middle border border-gray-100">
                    <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Edit Jadwal Laporan</h3>
                        <button @click="showEditModal = false" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 transition-all">
                            
                        </button>
                    </div>

                    <form method="POST" :action="'{{ route('admin.scheduled-reports.index') }}/' + editReport.id" class="px-8 py-6 space-y-5">
                        @csrf
                        @method('PUT')
                        
                        <!-- Name -->
                        <div class="space-y-1">
                            <label class="text-xs font-black uppercase tracking-widest text-gray-500">Nama Laporan:</label>
                            <input type="text" name="name" x-model="editReport.name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Report Type -->
                        <div class="space-y-1">
                            <label class="text-xs font-black uppercase tracking-widest text-gray-500">Jenis Rangkuman:</label>
                            <select name="report_type" x-model="editReport.report_type" required class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="daily_admin">Harian Administrator (Daily Admin)</option>
                                <option value="weekly_kepsek">Mingguan Kepala Sekolah (Weekly Headmaster)</option>
                                <option value="monthly_finance">Bulanan Keuangan (Monthly Finance)</option>
                                <option value="weekly_parent">Mingguan Orang Tua (Weekly Parents)</option>
                            </select>
                        </div>

                        <!-- Cron Expression & Presets -->
                        <div class="space-y-1">
                            <label class="text-xs font-black uppercase tracking-widest text-gray-500">Jadwal Pengiriman (Cron Expression):</label>
                            <input type="text" name="cron_expression" x-model="editReport.cron_expression" required class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 text-sm font-bold font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            
                            <!-- Presets -->
                            <div class="flex flex-wrap gap-2 pt-2">
                                <button type="button" @click="setCronPreset('edit', '0 7 * * *')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-md text-[10px] font-black uppercase tracking-wider">Harian 07:00</button>
                                <button type="button" @click="setCronPreset('edit', '0 8 * * 1')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-md text-[10px] font-black uppercase tracking-wider">Tiap Senin 08:00</button>
                                <button type="button" @click="setCronPreset('edit', '0 8 1 * *')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-md text-[10px] font-black uppercase tracking-wider">Awal Bulan 08:00</button>
                                <button type="button" @click="setCronPreset('edit', '* * * * *')" class="px-2.5 py-1 bg-orange-50 hover:bg-orange-100 text-orange-700 rounded-md text-[10px] font-black uppercase tracking-wider">Tiap Menit (Test)</button>
                            </div>
                        </div>

                        <!-- Dynamic Recipients Rows -->
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-black uppercase tracking-widest text-gray-500">Daftar Penerima:</label>
                                <button type="button" @click="addRecipientRow('edit')" class="text-[10px] font-black text-indigo-600 hover:underline uppercase">+ Tambah Penerima</button>
                            </div>
                            
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(recipient, idx) in editRecipients" :key="idx">
                                    <div class="flex gap-2 items-center">
                                        <select :name="'recipients['+idx+'][type]'" x-model="recipient.type" class="bg-gray-50 border border-gray-200 rounded-xl py-2 px-3 text-xs font-bold w-1/3 focus:outline-none">
                                            <option value="email">Email Langsung</option>
                                            <option value="role">Role Pengguna</option>
                                        </select>
                                        <input type="text" :name="'recipients['+idx+'][value]'" x-model="recipient.value" required placeholder="Alamat email / Nama role" class="bg-gray-50 border border-gray-200 rounded-xl py-2 px-3 text-xs font-bold flex-1 focus:outline-none">
                                        <button type="button" @click="removeRecipientRow('edit', idx)" class="text-rose-500 text-xs font-black px-2 hover:bg-rose-50 py-1.5 rounded-lg"></button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Method & Options -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-black uppercase tracking-widest text-gray-500">Metode Pengiriman:</label>
                                <select name="delivery_method" x-model="editReport.delivery_method" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 text-sm font-bold focus:outline-none">
                                    <option value="email">Email</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="both">Keduanya (Email & WA)</option>
                                </select>
                            </div>
                            <div class="flex flex-col justify-end space-y-3 pb-1">
                                <label class="inline-flex items-center text-xs font-bold text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="attach_pdf" value="1" x-model="editReport.attach_pdf" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4 mr-2">
                                    Lampirkan Laporan PDF
                                </label>
                                <label class="inline-flex items-center text-xs font-bold text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="editReport.is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4 mr-2">
                                    Jadwal Aktif
                                </label>
                            </div>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="pt-6 border-t border-gray-50 flex gap-3">
                            <button type="button" @click="showEditModal = false" class="flex-1 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all">Batal</button>
                            <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-lg shadow-indigo-600/20">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
