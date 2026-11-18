<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Laporkan Kerusakan Aset') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-lg mx-auto space-y-6">
        <!-- Notifications -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/50 rounded-2xl flex items-center space-x-3 shadow-md animate-fade-in-up">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-100 dark:border-rose-900/50 rounded-2xl flex items-center space-x-3 shadow-md animate-fade-in-up">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="text-sm font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        <!-- Asset Profile Card -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden relative">
            <div class="h-3 bg-gradient-to-r from-teal-500 to-emerald-600"></div>
            <div class="p-6">
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400 block mb-1">PROFIL ASET</span>
                <h3 class="text-xl font-black text-gray-950 dark:text-white leading-tight mb-2">{{ $asset->name }}</h3>
                
                <div class="grid grid-cols-2 gap-4 mt-4 border-t border-gray-100 dark:border-gray-700 pt-4">
                    <div>
                        <span class="block text-[9px] font-black text-gray-400 uppercase tracking-wider">KODE ASET</span>
                        <span class="font-mono text-xs font-bold text-gray-800 dark:text-gray-200">{{ $asset->asset_code }}</span>
                    </div>
                    <div>
                        <span class="block text-[9px] font-black text-gray-400 uppercase tracking-wider">LOKASI</span>
                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $asset->room->name ?? 'N/A' }}</span>
                        @if($asset->room && $asset->room->location)
                            <span class="block text-[9px] text-gray-400 font-medium mt-0.5">{{ $asset->room->location }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="block text-[9px] font-black text-gray-400 uppercase tracking-wider">KONDISI SAAT INI</span>
                        <div class="mt-1">
                            @if($asset->condition === 'good')
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[9px] font-black bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50">BAIK</span>
                            @elseif($asset->condition === 'damaged')
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[9px] font-black bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50">RUSAK RINGAN</span>
                            @else
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[9px] font-black bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50">RUSAK BERAT</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <span class="block text-[9px] font-black text-gray-400 uppercase tracking-wider">STATUS</span>
                        <div class="mt-1">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[9px] font-black bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700">
                                {{ strtoupper($asset->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reporting Form -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-xl border border-gray-100 dark:border-gray-700 p-6 md:p-8">
            <h4 class="text-lg font-black text-gray-950 dark:text-white mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Buat Laporan Kerusakan
            </h4>

            <form action="{{ route('admin.assets.maintenance.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="asset_id" value="{{ $asset->id }}">

                <!-- Priority Selector -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">TINGKAT PRIORITAS</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <!-- Low -->
                        <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all select-none">
                            <input type="radio" name="priority" value="low" class="sr-only peer" checked>
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400 mb-1"></span>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Low</span>
                            <span class="absolute inset-0 border-2 border-transparent peer-checked:border-slate-500 dark:peer-checked:border-slate-400 rounded-2xl pointer-events-none"></span>
                        </label>

                        <!-- Medium -->
                        <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all select-none">
                            <input type="radio" name="priority" value="medium" class="sr-only peer">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 mb-1"></span>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Medium</span>
                            <span class="absolute inset-0 border-2 border-transparent peer-checked:border-amber-400 dark:peer-checked:border-amber-500 rounded-2xl pointer-events-none"></span>
                        </label>

                        <!-- High -->
                        <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all select-none">
                            <input type="radio" name="priority" value="high" class="sr-only peer">
                            <span class="w-2.5 h-2.5 rounded-full bg-orange-500 mb-1"></span>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">High</span>
                            <span class="absolute inset-0 border-2 border-transparent peer-checked:border-orange-500 rounded-2xl pointer-events-none"></span>
                        </label>

                        <!-- Critical -->
                        <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all select-none">
                            <input type="radio" name="priority" value="critical" class="sr-only peer">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-600 mb-1"></span>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Critical</span>
                            <span class="absolute inset-0 border-2 border-transparent peer-checked:border-rose-600 rounded-2xl pointer-events-none"></span>
                        </label>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="issue_description" class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">DESKRIPSI KERUSAKAN</label>
                    <textarea id="issue_description" name="issue_description" rows="4" minlength="10" required 
                        class="w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm focus:border-teal-500 focus:ring-teal-500 text-gray-800 dark:text-gray-200 placeholder-gray-400"
                        placeholder="Jelaskan detail kerusakan sarana prasarana yang Anda temukan (min. 10 karakter)...">{{ old('issue_description') }}</textarea>
                    @error('issue_description')
                        <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- File Upload -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">FOTO BUKTI KERUSAKAN (OPSIONAL)</label>
                    
                    <div x-data="{ fileName: '' }" class="relative">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-2 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold px-4 text-center" x-text="fileName ? fileName : 'Ambil Foto / Pilih File Gambar'"></p>
                                <p class="text-[10px] text-gray-400 mt-1">Format: JPG, PNG, WEBP (Maks. 2MB)</p>
                            </div>
                            <input type="file" name="photo" accept="image/*" class="hidden" 
                                @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                        </label>
                    </div>
                    @error('photo')
                        <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-sm shadow-xl shadow-emerald-600/20 hover:shadow-emerald-600/30 transition-all transform active:scale-[0.98] flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <span>Kirim Laporan Kerusakan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
