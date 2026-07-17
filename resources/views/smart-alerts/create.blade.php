<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight flex items-center space-x-2">
                    <span class="p-2 bg-teal-500/10 text-teal-500 rounded-xl">✍️</span>
                    <span>{{ __('Buat Manual Smart Alert') }}</span>
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
                    Terbitkan peringatan insiden atau kasus secara manual di luar deteksi otomatis sistem
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8"
         x-data="{
             targetType: 'student',
             search: '',
             selectedId: '',
             selectedName: '',
             isOpen: false,
             students: @js($students->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'info' => $s->classroom?->name ?? 'Tanpa Kelas'])),
             teachers: @js($teachers->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'info' => ucwords(str_replace('_', ' ', $t->role))])),
             
             get filteredItems() {
                 let list = this.targetType === 'student' ? this.students : this.teachers;
                 if (!this.search.trim()) return list.slice(0, 10);
                 return list.filter(item => 
                     item.name.toLowerCase().includes(this.search.toLowerCase()) ||
                     item.info.toLowerCase().includes(this.search.toLowerCase())
                 ).slice(0, 10);
             },
             
             selectItem(item) {
                 this.selectedId = item.id;
                 this.selectedName = item.name;
                 this.search = item.name;
                 this.isOpen = false;
             },
             
             resetSelection() {
                 this.selectedId = '';
                 this.selectedName = '';
                 this.search = '';
             }
         }">

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="p-4 bg-rose-50/80 dark:bg-rose-950/30 backdrop-blur-sm border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 rounded-2xl shadow-sm animate-fade-in">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-black uppercase tracking-wider">Periksa Kembali Input Anda:</span>
                </div>
                <ul class="list-disc list-inside text-xs font-semibold space-y-1 pl-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Card --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 md:p-8 shadow-xl">
            <form action="{{ route('smart-alerts.manual-create') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Target Type select --}}
                <div class="space-y-2">
                    <label for="target_type" class="block text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
                        Tipe Target Alert
                    </label>
                    <select name="target_type" 
                            id="target_type" 
                            x-model="targetType"
                            @change="resetSelection()"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all font-semibold">
                        <option value="student">Siswa (Student)</option>
                        <option value="user">Guru / Staf (User)</option>
                    </select>
                </div>

                {{-- Interactive Target Selector via AlpineJS --}}
                <div class="space-y-2 relative">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
                        Pilih Target (Nama Siswa / Guru / Staf)
                    </label>
                    
                    <div class="relative">
                        <input type="text" 
                               x-model="search" 
                               @focus="isOpen = true"
                               @click.away="isOpen = false"
                               placeholder="Ketik nama untuk mencari target..."
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all font-semibold pr-10">
                        
                        {{-- Hidden Input for actual target_id --}}
                        <input type="hidden" name="target_id" :value="selectedId">
                        
                        {{-- Status Check Icon --}}
                        <div class="absolute right-4 top-3.5 text-gray-400">
                            <template x-if="selectedId">
                                <span class="text-emerald-500">✓</span>
                            </template>
                            <template x-if="!selectedId">
                                <span class="text-xs">🔍</span>
                            </template>
                        </div>
                    </div>

                    {{-- Search Dropdown Result list --}}
                    <div x-show="isOpen && filteredItems.length > 0" 
                         x-transition
                         class="absolute z-10 w-full mt-1 bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl shadow-2xl max-h-60 overflow-y-auto p-2 space-y-1">
                        <template x-for="item in filteredItems" :key="item.id">
                            <button type="button" 
                                    @mousedown="selectItem(item)"
                                    class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-slate-800 rounded-xl transition flex items-center justify-between text-sm">
                                <span class="font-bold text-gray-800 dark:text-gray-200" x-text="item.name"></span>
                                <span class="text-[9px] font-black uppercase tracking-wider bg-gray-100 dark:bg-slate-800 px-2 py-0.5 rounded text-gray-500 dark:text-gray-400" x-text="item.info"></span>
                            </button>
                        </template>
                    </div>

                    <div x-show="isOpen && filteredItems.length === 0" 
                         class="absolute z-10 w-full mt-1 bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl shadow-2xl p-4 text-center text-xs font-semibold text-gray-400">
                        Tidak ditemukan hasil pencarian.
                    </div>
                </div>

                {{-- Severity Select --}}
                <div class="space-y-2">
                    <label for="severity" class="block text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
                        Tingkat Keparahan (Severity)
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:bg-amber-50/50 dark:hover:bg-amber-950/10"
                               :class="severity === 'warning' ? 'border-amber-500 bg-amber-500/5' : 'border-gray-200 dark:border-slate-700'"
                               x-data="{ severity: 'warning' }">
                            <input type="radio" name="severity" value="warning" class="sr-only" checked @change="severity = 'warning'">
                            <span class="text-xl mb-1">⚠️</span>
                            <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Warning</span>
                        </label>

                        <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:bg-rose-50/50 dark:hover:bg-rose-950/10"
                               :class="severity === 'critical' ? 'border-rose-500 bg-rose-500/5' : 'border-gray-200 dark:border-slate-700'"
                               x-data="{ severity: 'warning' }">
                            <input type="radio" name="severity" value="critical" class="sr-only" @change="severity = 'critical'">
                            <span class="text-xl mb-1">🚨</span>
                            <span class="text-xs font-black uppercase tracking-wider text-rose-600 dark:text-rose-400">Critical</span>
                        </label>
                    </div>
                </div>

                {{-- Title Input --}}
                <div class="space-y-2">
                    <label for="title" class="block text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
                        Judul Peringatan (Title)
                    </label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           required 
                           value="{{ old('title') }}" 
                           placeholder="Contoh: Pelanggaran Tata Tertib Berulang Kelas XII MIPA 1"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all font-semibold">
                </div>

                {{-- Description textarea --}}
                <div class="space-y-2">
                    <label for="description" class="block text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
                        Deskripsi Masalah / Kasus Lengkap
                    </label>
                    <textarea name="description" 
                              id="description" 
                              required 
                              rows="5" 
                              placeholder="Jelaskan detail temuan masalah, kronologi, dampak, atau saran tindakan awal..."
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all font-medium leading-relaxed">{{ old('description') }}</textarea>
                </div>

                {{-- Submit Button --}}
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-slate-800">
                    <a href="{{ route('smart-alerts.index') }}" 
                       class="px-6 py-3 bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-700 rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-200 active:scale-95">
                        Batal
                    </a>
                    <button type="submit" 
                            ::disabled="!selectedId"
                            class="px-8 py-3 bg-gradient-to-r from-teal-500 to-indigo-600 hover:from-teal-600 hover:to-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all duration-200 active:scale-95 disabled:opacity-50 shadow-lg shadow-teal-500/15">
                        Terbitkan Manual Alert
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
