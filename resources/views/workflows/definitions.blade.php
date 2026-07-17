<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between py-2">
            <div>
                <h2 class="font-extrabold text-3xl text-slate-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Manajemen Alur Kerja') }}
                </h2>
                <p class="text-xs font-bold text-teal-600 dark:text-teal-400 tracking-widest uppercase mt-1">
                    SISTA • DEFINISI ALUR PERSETUJUAN DINAMIS
                </p>
            </div>
        </div>
    </x-slot>

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        [x-cloak] { display: none !important; }
    </style>

    @php
        $roleNames = [
            'admin' => 'Administrator',
            'superadmin' => 'Super Administrator',
            'kepala_sekolah' => 'Kepala Sekolah',
            'waka_kurikulum' => 'Waka Kurikulum',
            'waka_kesiswaan' => 'Waka Kesiswaan',
            'waka_sarpras' => 'Waka Sarpras',
            'waka_humas' => 'Waka Humas',
            'staf_tu' => 'Staf Tata Usaha',
            'bk' => 'Bimbingan Konseling (BK)',
            'guru' => 'Guru',
        ];
    @endphp

    <div x-data="{ 
        isOpen: false,
        isEdit: false,
        actionUrl: '{{ route('workflows.definitions.store') }}',
        name: '',
        code: '',
        description: '',
        isActive: true,
        steps: [
            { order: 1, role: 'admin', sla_hours: 48 }
        ],
        openCreate() {
            this.isEdit = false;
            this.actionUrl = '{{ route('workflows.definitions.store') }}';
            this.name = '';
            this.code = '';
            this.description = '';
            this.isActive = true;
            this.steps = [{ order: 1, role: 'admin', sla_hours: 48 }];
            this.isOpen = true;
        },
        openEdit(def) {
            this.isEdit = true;
            this.actionUrl = '{{ url('/workflows/definitions') }}/' + def.id;
            this.name = def.name;
            this.code = def.code;
            this.description = def.description || '';
            this.isActive = def.is_active ? true : false;
            
            if (Array.isArray(def.steps)) {
                this.steps = def.steps.map(s => ({
                    order: s.order,
                    role: s.role,
                    sla_hours: s.sla_hours || 48
                }));
            } else {
                this.steps = [{ order: 1, role: 'admin', sla_hours: 48 }];
            }
            this.isOpen = true;
        },
        addStep() {
            let nextOrder = this.steps.length > 0 ? Math.max(...this.steps.map(s => s.order)) + 1 : 1;
            this.steps.push({ order: nextOrder, role: 'admin', sla_hours: 48 });
        },
        removeStep(index) {
            this.steps.splice(index, 1);
            this.steps.forEach((s, i) => {
                s.order = i + 1;
            });
        }
    }" x-cloak class="py-8 bg-slate-50 dark:bg-slate-900 min-h-screen text-slate-900 dark:text-slate-100">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8 animate-fade-in-up">
            
            <!-- Navigation Bar -->
            <nav class="flex flex-wrap gap-2 p-1.5 bg-white dark:bg-slate-800 rounded-[1.5rem] border border-slate-200/50 dark:border-slate-700/50 shadow-sm">
                <a href="{{ route('workflows.dashboard') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    Dashboard
                </a>
                <a href="{{ route('workflows.pending') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    Pending Approvals
                </a>
                <a href="{{ route('workflows.my-requests') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    My Requests
                </a>
                <a href="{{ route('workflows.definitions') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 bg-teal-500 text-white shadow-lg shadow-teal-500/25">
                    Alur Dinamis
                </a>
                <a href="{{ route('workflows.history') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    Riwayat
                </a>
                <a href="{{ route('workflows.analytics') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    Analitik
                </a>
            </nav>

            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="p-4 bg-teal-500/10 border border-teal-500/30 rounded-[1.5rem] text-teal-600 dark:text-teal-400 text-sm flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-[1.5rem] text-rose-600 dark:text-rose-400 text-sm flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Main Panel Container -->
            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border border-slate-100 dark:border-slate-700/50 shadow-xl overflow-hidden">
                <div class="p-8 border-b border-slate-100 dark:border-slate-700/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900 dark:text-white">Daftar Alur Persetujuan</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Kelola langkah dan SLA persetujuan berdasarkan peran jabatan.</p>
                    </div>
                    <button @click="openCreate()" class="inline-flex items-center px-5 py-3 bg-teal-500 hover:bg-teal-600 text-white text-sm font-bold rounded-2xl transition-all duration-300 shadow-lg shadow-teal-500/20 hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Alur Baru
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/50">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Nama Alur</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Kode Unik</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Deskripsi</th>
                                <th class="px-6 py-4 text-center text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Jumlah Langkah</th>
                                <th class="px-6 py-4 text-center text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            @forelse($definitions as $def)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors duration-200">
                                    <td class="px-6 py-5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $def->name }}</div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="font-mono text-xs bg-slate-100 dark:bg-slate-900 px-2 py-1 rounded text-slate-600 dark:text-slate-400">
                                            {{ $def->code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs truncate">{{ $def->description ?: '-' }}</p>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center px-3 py-1 bg-teal-500/10 text-teal-600 dark:text-teal-400 text-xs font-bold rounded-full">
                                            {{ is_array($def->steps) ? count($def->steps) : 0 }} Langkah
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        @if($def->is_active)
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-full">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-slate-100 dark:bg-slate-900 text-slate-400 rounded-full">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-right whitespace-nowrap text-sm font-semibold space-x-2">
                                        <button @click="openEdit({{ $def->toJson() }})" class="text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 transition-colors mr-2">
                                            Edit
                                        </button>
                                        <form action="{{ route('workflows.definitions.delete', $def->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus alur kerja ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 transition-colors {{ $def->requests_count > 0 ? 'opacity-25 cursor-not-allowed' : '' }}" {{ $def->requests_count > 0 ? 'disabled' : '' }} title="{{ $def->requests_count > 0 ? 'Tidak dapat dihapus karena sudah ada data pengajuan' : '' }}">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                            <p class="font-bold">Belum ada definisi alur persetujuan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create & Edit Modal Dialog -->
        <div x-show="isOpen" 
             class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
             
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isOpen = false"></div>

            <!-- Modal Content Card -->
            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] max-w-2xl w-full mx-4 overflow-hidden shadow-2xl border border-slate-100 dark:border-slate-700/50 z-10 max-h-[90vh] flex flex-col"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="scale-95 translate-y-4"
                 x-transition:enter-end="scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="scale-100 translate-y-0"
                 x-transition:leave-end="scale-95 translate-y-4">
                 
                <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                    <div>
                        <h4 class="text-xl font-extrabold text-slate-900 dark:text-white" x-text="isEdit ? 'Perbarui Alur Kerja' : 'Tambah Alur Baru'"></h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Konfigurasi info utama dan urutan langkah persetujuan.</p>
                    </div>
                    <button @click="isOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l18 18"></path></svg>
                    </button>
                </div>

                <!-- Scrollable Form Container -->
                <form :action="actionUrl" method="POST" class="flex-1 overflow-y-auto p-8 space-y-6">
                    @csrf
                    <!-- Dynamic method for edit/put -->
                    <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">

                    <!-- Info fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[0.65rem] font-extrabold tracking-widest text-slate-400 uppercase mb-2">Nama Alur Kerja *</label>
                            <input type="text" name="name" x-model="name" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm text-slate-900 dark:text-white font-medium" placeholder="Contoh: Pengajuan RABS Sekolah">
                        </div>

                        <div>
                            <label class="block text-[0.65rem] font-extrabold tracking-widest text-slate-400 uppercase mb-2">Kode Unik (Hanya Create) *</label>
                            <input type="text" name="code" x-model="code" :disabled="isEdit" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm text-slate-900 dark:text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed" placeholder="Contoh: RABS_APPROVAL">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[0.65rem] font-extrabold tracking-widest text-slate-400 uppercase mb-2">Deskripsi</label>
                        <textarea name="description" x-model="description" rows="2" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm text-slate-900 dark:text-white" placeholder="Penjelasan singkat mengenai peruntukan alur persetujuan ini..."></textarea>
                    </div>

                    <!-- Steps Dynamic List -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="block text-[0.65rem] font-extrabold tracking-widest text-slate-400 uppercase">Langkah Persetujuan *</span>
                            <button type="button" @click="addStep()" class="inline-flex items-center text-xs font-bold text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                Tambah Langkah
                            </button>
                        </div>

                        <div class="space-y-3 max-h-[30vh] overflow-y-auto pr-2">
                            <template x-for="(step, index) in steps" :key="index">
                                <div class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-700/50">
                                    <div class="w-8 h-8 rounded-full bg-teal-500/10 text-teal-600 dark:text-teal-400 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                        <span x-text="step.order"></span>
                                        <input type="hidden" :name="`steps[${index}][order]`" :value="step.order">
                                    </div>

                                    <!-- Role Select -->
                                    <div class="flex-1">
                                        <select :name="`steps[${index}][role]`" x-model="step.role" class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-1 focus:ring-teal-500 text-xs font-semibold text-slate-800 dark:text-slate-200">
                                            @foreach($roles as $role)
                                                <option value="{{ $role }}">{{ $roleNames[$role] ?? Str::headline($role) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- SLA Input -->
                                    <div class="w-32 flex-shrink-0 flex items-center space-x-2">
                                        <input type="number" :name="`steps[${index}][sla_hours]`" x-model.number="step.sla_hours" min="1" placeholder="SLA" class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-1 focus:ring-teal-500 text-xs text-center font-bold text-slate-800 dark:text-slate-200">
                                        <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">Jam</span>
                                    </div>

                                    <!-- Remove Button -->
                                    <button type="button" @click="removeStep(index)" :disabled="steps.length === 1" class="text-rose-500 hover:text-rose-700 disabled:opacity-20 transition-colors p-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Status Toggle -->
                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-700/50">
                        <div>
                            <span class="block text-sm font-bold text-slate-800 dark:text-slate-200">Aktifkan Alur Kerja</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">Hanya alur aktif yang dapat digunakan untuk pengajuan baru.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" x-model="isActive" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-teal-500"></div>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-700/50">
                        <button type="button" @click="isOpen = false" class="px-5 py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-bold rounded-2xl transition-all duration-300">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white text-sm font-bold rounded-2xl transition-all duration-300 shadow-lg shadow-teal-500/20 hover:scale-105">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
