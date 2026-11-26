<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Pengaturan Sistem') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-xl shadow-sm animate-fade-in">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-bold">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Sidebar Navigation -->
                <div class="lg:col-span-1 space-y-2">
                    <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                        @foreach($settings as $group => $items)
                            <button onclick="showSection('{{ $group }}')" id="btn-{{ $group }}" 
                                    class="section-btn w-full text-left px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 mb-1 flex items-center {{ $loop->first ? 'bg-primary-50 text-primary-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50' }}">
                                <span class="capitalize">{{ $group }}</span>
                                <svg class="w-4 h-4 ml-auto opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        @endforeach
                    </div>
                    
                    <div class="bg-gray-900 p-6 rounded-2xl shadow-xl text-white overflow-hidden relative group">
                        <div class="relative z-10">
                            <h4 class="text-xs font-black uppercase tracking-widest text-primary-400 mb-2">Pusat Bantuan</h4>
                            <p class="text-[11px] text-gray-400 leading-relaxed mb-4">Butuh bantuan konfigurasi tingkat lanjut? Hubungi tim teknis kami.</p>
                            <a href="#" class="inline-flex items-center text-[10px] font-black bg-white/10 px-3 py-2 rounded-lg hover:bg-white/20 transition-all">
                                DOKUMENTASI SISTEM
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="lg:col-span-3">
                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @foreach($settings as $group => $items)
                            <div id="section-{{ $group }}" class="setting-section {{ $loop->first ? '' : 'hidden' }} bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in">
                                <div class="p-8 border-b border-gray-50">
                                    <h3 class="text-xl font-black text-gray-800 capitalize tracking-tight">{{ $group }} Settings</h3>
                                    <p class="text-sm text-gray-400 mt-1">Kelola preferensi dan identitas untuk modul {{ $group }}.</p>
                                </div>

                                <div class="p-8 space-y-8">
                                    @foreach($items as $item)
                                        <div class="group relative">
                                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-primary-500 transition-colors">
                                                {{ str_replace('_', ' ', str_replace($group.'_', '', $item->key)) }}
                                            </label>

                                            @if($item->type === 'text')
                                                <input type="text" name="{{ $item->key }}" value="{{ $item->value }}" 
                                                       class="w-full bg-gray-50 border-gray-100 rounded-2xl px-5 py-3 text-sm font-medium focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">
                                            @elseif($item->type === 'textarea')
                                                <textarea name="{{ $item->key }}" rows="4" 
                                                          class="w-full bg-gray-50 border-gray-100 rounded-2xl px-5 py-3 text-sm font-medium focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all">{{ $item->value }}</textarea>
                                            @elseif($item->type === 'image')
                                                <div class="flex items-center space-x-6">
                                                    @if($item->value)
                                                        <div class="relative group/img">
                                                            <img src="{{ Storage::url($item->value) }}" class="w-20 h-20 rounded-2xl object-cover border-4 border-gray-50 shadow-sm transition-transform group-hover/img:scale-110">
                                                            <div class="absolute inset-0 bg-black/40 rounded-2xl opacity-0 group-hover/img:opacity-100 flex items-center justify-center transition-opacity">
                                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="w-20 h-20 rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 flex items-center justify-center">
                                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                        </div>
                                                    @endif
                                                    <div class="flex-1">
                                                        <input type="file" name="{{ $item->key }}" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition-all">
                                                        <p class="mt-2 text-[10px] text-gray-400 font-medium">Recommended: PNG or SVG, max 2MB.</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-8 flex items-center justify-end space-x-4">
                            <button type="reset" class="px-8 py-3 rounded-2xl text-sm font-bold text-gray-500 hover:bg-gray-100 transition-all">
                                RESET
                            </button>
                            <button type="submit" class="px-10 py-4 bg-gray-900 text-white rounded-2xl text-sm font-black shadow-xl shadow-gray-200 hover:shadow-2xl hover:-translate-y-1 transition-all">
                                SIMPAN PERUBAHAN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(group) {
            // Hide all sections
            document.querySelectorAll('.setting-section').forEach(s => s.classList.add('hidden'));
            // Show selected
            document.getElementById('section-' + group).classList.remove('hidden');
            
            // Update buttons
            document.querySelectorAll('.section-btn').forEach(b => {
                b.classList.remove('bg-primary-50', 'text-primary-700', 'shadow-sm');
                b.classList.add('text-gray-500');
            });
            document.getElementById('btn-' + group).classList.add('bg-primary-50', 'text-primary-700', 'shadow-sm');
            document.getElementById('btn-' + group).classList.remove('text-gray-500');
        }
    </script>

    <style>
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</x-app-layout>
