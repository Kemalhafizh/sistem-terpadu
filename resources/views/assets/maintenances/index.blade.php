<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-3xl text-gray-900 leading-tight">
                    {{ __('Perawatan & Servis Berkala Aset') }}
                </h2>
                <p class="text-xs text-gray-500 font-bold mt-1">Pemeliharaan preventif, korektif, dan peningkatan kapasitas sarana sekolah</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('assets.loans.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs transition-all flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>Peminjaman Aset</span>
                </a>
                @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'waka_sarpras']))
                    <button onclick="toggleModal('addMaintenanceModal')" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-black text-xs shadow-lg shadow-primary-200 hover:shadow-xl transition-all flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        <span>Jadwalkan Perawatan</span>
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center space-x-2 text-sm font-bold shadow-sm">
                    <span class="p-1 bg-emerald-100 rounded-lg text-emerald-600"></span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Glassmorphic Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Card 1 -->
                <div class="bg-white/75 backdrop-blur-md p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-4 bg-primary-50 rounded-2xl text-primary-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Servis</div>
                        <div class="text-3xl font-black text-gray-900 mt-1">{{ $maintenances->total() }}</div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white/75 backdrop-blur-md p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-4 bg-amber-50 rounded-2xl text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Servis Aktif</div>
                        <div class="text-3xl font-black text-gray-900 mt-1">
                            {{ $maintenances->whereIn('status', ['scheduled', 'in_progress'])->count() }}
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white/75 backdrop-blur-md p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-4 bg-emerald-50 rounded-2xl text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Selesai Servis</div>
                        <div class="text-3xl font-black text-gray-900 mt-1">
                            {{ $maintenances->where('status', 'completed')->count() }}
                        </div>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="bg-white/75 backdrop-blur-md p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-4 bg-blue-50 rounded-2xl text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V7"></path></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Biaya Total</div>
                        <div class="text-xl font-black text-gray-900 mt-2">
                            Rp {{ number_format($maintenances->sum('cost'), 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Aset / Kode</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Jenis & Jadwal</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Deskripsi Servis</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Biaya / Vendor</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Petugas / Status</th>
                            @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'waka_sarpras']))
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($maintenances as $m)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-5">
                                    <div class="font-black text-gray-900">{{ $m->asset->name }}</div>
                                    <div class="text-[10px] font-bold text-primary-600 font-mono tracking-tighter">{{ $m->asset->asset_code }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-md text-[9px] font-black uppercase tracking-wider">{{ $m->type }}</span>
                                    <div class="text-xs font-bold text-gray-700 mt-1">{{ $m->maintenance_date->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-5 max-w-xs">
                                    <div class="text-xs text-gray-700 line-clamp-2 italic font-medium">"{{ $m->description }}"</div>
                                    @if($m->next_maintenance_date)
                                        <div class="text-[9px] text-primary-600 font-bold uppercase mt-1">Servis Berikutnya: {{ $m->next_maintenance_date->format('d M Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-xs font-bold text-gray-900">Rp {{ number_format($m->cost, 0, ',', '.') }}</div>
                                    <div class="text-[10px] text-gray-400 font-bold">{{ $m->vendor ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-[10px] font-bold text-gray-700">{{ $m->handler->name }}</div>
                                    <div class="mt-1">
                                        @if($m->status === 'scheduled')
                                            <span class="px-2.5 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded-full text-[9px] font-black uppercase">DIJADWALKAN</span>
                                        @elseif($m->status === 'in_progress')
                                            <span class="px-2.5 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-full text-[9px] font-black uppercase">DIPROSES</span>
                                        @elseif($m->status === 'completed')
                                            <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full text-[9px] font-black uppercase">SELESAI</span>
                                        @endif
                                    </div>
                                </td>
                                @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'waka_sarpras']))
                                    <td class="px-6 py-5 text-right">
                                        <button onclick="openEditModal({{ json_encode($m) }})" class="px-3.5 py-1.5 bg-gray-100 hover:bg-primary-50 text-gray-600 hover:text-primary-600 rounded-xl text-[10px] font-black transition-all">
                                            Update Status
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic text-sm">Belum ada riwayat pemeliharaan aset.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($maintenances->hasPages())
                    <div class="px-6 py-4 border-t border-gray-50">
                        {{ $maintenances->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Modal 1: Add Maintenance -->
    <div id="addMaintenanceModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" onclick="toggleModal('addMaintenanceModal')"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                <div class="h-2 bg-gradient-to-r from-primary-600 to-emerald-600"></div>
                <div class="p-6">
                    <h3 class="text-base font-black text-gray-900 uppercase tracking-widest border-b border-gray-100 pb-3">Jadwalkan Perawatan Baru</h3>
                    
                    <form action="{{ route('assets.maintenances.store') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        
                        <div>
                            <label for="asset_id" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Aset Inventaris</label>
                            <select name="asset_id" id="asset_id" required class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5">
                                <option value="">-- Pilih Aset --</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->asset_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="maintenance_date" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Tanggal Perawatan</label>
                                <input type="date" name="maintenance_date" id="maintenance_date" required class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5">
                            </div>
                            <div>
                                <label for="next_maintenance_date" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Servis Berikutnya (Opsional)</label>
                                <input type="date" name="next_maintenance_date" id="next_maintenance_date" class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="type" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Jenis Perawatan</label>
                                <select name="type" id="type" required class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5">
                                    <option value="preventif">Preventif (Pencegahan)</option>
                                    <option value="korektif">Korektif (Perbaikan Rusak)</option>
                                    <option value="upgrade">Upgrade Kapasitas</option>
                                </select>
                            </div>
                            <div>
                                <label for="status" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Status Perawatan</label>
                                <select name="status" id="status" required class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5">
                                    <option value="scheduled">Dijadwalkan</option>
                                    <option value="in_progress">Sedang Diproses</option>
                                    <option value="completed">Selesai / Tuntas</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="cost" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Biaya Perawatan (Rupiah)</label>
                                <input type="number" name="cost" id="cost" required min="0" value="0" class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5">
                            </div>
                            <div>
                                <label for="vendor" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Pihak Ketiga / Vendor</label>
                                <input type="text" name="vendor" id="vendor" placeholder="Nama tukang/toko servis..." class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5">
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Deskripsi & Keluhan</label>
                            <textarea name="description" id="description" required rows="3" placeholder="Tuliskan keluhan atau rincian pemeliharaan..." class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5"></textarea>
                        </div>

                        <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                            <button type="button" onclick="toggleModal('addMaintenanceModal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-all">Batal</button>
                            <button type="submit" class="px-6 py-2 bg-gradient-to-r from-primary-600 to-emerald-600 hover:from-primary-700 hover:to-emerald-700 text-white rounded-xl text-xs font-black transition-all">Simpan Jadwal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: Edit Maintenance -->
    <div id="editMaintenanceModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" onclick="toggleModal('editMaintenanceModal')"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                <div class="h-2 bg-gradient-to-r from-blue-600 to-indigo-600"></div>
                <div class="p-6">
                    <h3 class="text-base font-black text-gray-900 uppercase tracking-widest border-b border-gray-100 pb-3">Update Status Perawatan</h3>
                    
                    <form id="editForm" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Aset Terpilih</label>
                            <input type="text" id="edit_asset_name" disabled class="w-full rounded-xl border-gray-200 text-xs bg-gray-100 p-2.5 font-bold text-gray-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="edit_status" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Status Perawatan</label>
                                <select name="status" id="edit_status" required class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5">
                                    <option value="scheduled">Dijadwalkan</option>
                                    <option value="in_progress">Sedang Diproses</option>
                                    <option value="completed">Selesai / Tuntas</option>
                                </select>
                            </div>
                            <div>
                                <label for="edit_next_maintenance_date" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Servis Berikutnya (Opsional)</label>
                                <input type="date" name="next_maintenance_date" id="edit_next_maintenance_date" class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5">
                            </div>
                        </div>

                        <div>
                            <label for="edit_cost" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Biaya Perawatan (Rupiah)</label>
                            <input type="number" name="cost" id="edit_cost" required min="0" class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5">
                        </div>

                        <div>
                            <label for="edit_description" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Deskripsi & Keluhan</label>
                            <textarea name="description" id="edit_description" required rows="3" class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5"></textarea>
                        </div>

                        <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                            <button type="button" onclick="toggleModal('editMaintenanceModal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-all">Batal</button>
                            <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-xs font-black transition-all">Simpan Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleModal(id) {
            const el = document.getElementById(id);
            if (el.classList.contains('hidden')) {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        }

        function openEditModal(maintenance) {
            document.getElementById('edit_asset_name').value = maintenance.asset.name + ' (' + maintenance.asset.asset_code + ')';
            document.getElementById('edit_status').value = maintenance.status;
            document.getElementById('edit_cost').value = parseInt(maintenance.cost);
            document.getElementById('edit_description').value = maintenance.description;
            
            if (maintenance.next_maintenance_date) {
                // convert from Y-m-d H:i:s or date format to Y-m-d
                const d = new Date(maintenance.next_maintenance_date);
                const day = ("0" + d.getDate()).slice(-2);
                const month = ("0" + (d.getMonth() + 1)).slice(-2);
                const dateStr = d.getFullYear() + "-" + month + "-" + day;
                document.getElementById('edit_next_maintenance_date').value = dateStr;
            } else {
                document.getElementById('edit_next_maintenance_date').value = '';
            }

            const actionUrl = "{{ route('assets.maintenances.index') }}/" + maintenance.id;
            document.getElementById('editForm').setAttribute('action', actionUrl);

            toggleModal('editMaintenanceModal');
        }
    </script>
</x-app-layout>
