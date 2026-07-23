<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('academic.copilot.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-black text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('CBT Smart Question Creator') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        provider: '{{ old('provider', 'local_fallback') }}',
        showApiKey: false,
        showEndpoint: false,
        init() {
            this.showApiKey = this.provider !== 'local_fallback';
            this.showEndpoint = this.provider === 'ollama';
            this.$watch('provider', value => {
                this.showApiKey = value !== 'local_fallback';
                this.showEndpoint = value === 'ollama';
            });
        }
    }">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-950/30 border-l-4 border-red-500 p-4 rounded-r-2xl shadow-sm mb-6">
                    <div class="flex">
                        <div class="shrink-0 text-red-500">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-bold text-red-800 dark:text-red-300">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- Form Column --}}
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-xl border border-gray-100 dark:border-gray-700/50">
                        <div class="mb-6">
                            <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1 rounded-full">
                                Parameter Input
                            </span>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white mt-3">Rancang Soal Ujian</h3>
                            <p class="text-xs text-gray-400 mt-1">Masukkan materi ajar dan atur setelan soal yang ingin dibuat.</p>
                        </div>

                        <form action="{{ route('academic.copilot.cbt.generate') }}" method="POST" class="space-y-5">
                            @csrf
                            
                            {{-- Subject input --}}
                            <div>
                                <label for="subject_id" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Mata Pelajaran</label>
                                <select name="subject_id" id="subject_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white">
                                    <option value="">Pilih Mata Pelajaran</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id', request('subject_id')) == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }} ({{ $subject->category }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <p class="text-xs text-red-500 font-bold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Material Textarea --}}
                            <div>
                                <label for="material_text" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Kutipan Materi Ajar (Min 50 Karakter)</label>
                                <textarea name="material_text" id="material_text" rows="5" placeholder="Salin kutipan materi ajar, artikel, atau buku paket di sini..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white">{{ old('material_text', request('material_text')) }}</textarea>
                                @error('material_text')
                                    <p class="text-xs text-red-500 font-bold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                {{-- Question Type --}}
                                <div>
                                    <label for="type" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Tipe Soal</label>
                                    <select name="type" id="type" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white">
                                        <option value="multiple_choice" {{ old('type', request('type')) == 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda</option>
                                        <option value="essay" {{ old('type', request('type')) == 'essay' ? 'selected' : '' }}>Essay</option>
                                    </select>
                                    @error('type')
                                        <p class="text-xs text-red-500 font-bold mt-1.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Question Count --}}
                                <div>
                                    <label for="count" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Jumlah Soal</label>
                                    <input type="number" name="count" id="count" min="1" max="15" value="{{ old('count', request('count', 5)) }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white">
                                    @error('count')
                                        <p class="text-xs text-red-500 font-bold mt-1.5">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Difficulty Level --}}
                            <div>
                                <label for="difficulty" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Tingkat Kesulitan</label>
                                <select name="difficulty" id="difficulty" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white">
                                    <option value="easy" {{ old('difficulty', request('difficulty')) == 'easy' ? 'selected' : '' }}>Mudah (Easy)</option>
                                    <option value="medium" {{ old('difficulty', request('difficulty', 'medium')) == 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                                    <option value="hard" {{ old('difficulty', request('difficulty')) == 'hard' ? 'selected' : '' }}>Sulit (Hard)</option>
                                </select>
                                @error('difficulty')
                                    <p class="text-xs text-red-500 font-bold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Model Provider --}}
                            <div>
                                <label for="provider_select" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">AI Model Provider</label>
                                <select id="provider_select" x-model="provider" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white">
                                    <option value="local_fallback">Local Fallback Template (Offline)</option>
                                    <option value="openai">OpenAI API (GPT-4o-mini)</option>
                                    <option value="gemini">Gemini API (Gemini 1.5 Flash)</option>
                                    <option value="ollama">Ollama / Custom Endpoint</option>
                                </select>
                                <input type="hidden" name="provider" :value="provider === 'ollama' ? 'openai' : provider">
                                @error('provider')
                                    <p class="text-xs text-red-500 font-bold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- API Key Input --}}
                            <div x-show="showApiKey" x-cloak x-transition class="space-y-1">
                                <label for="api_key" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">API Key</label>
                                <div class="relative rounded-2xl" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" name="api_key" id="api_key" value="{{ old('api_key', request('api_key')) }}" placeholder="Masukkan Kunci API Anda..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 pr-10 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white">
                                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors focus:outline-none">
                                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 5.656m0-5.656l-3.329-3.329m1.883-1.883L3 3m15.364 6.364l-1.391-1.391m0 0l-3.757-3.757M13.732 4a9.972 9.972 0 013.414.832m1.91 1.91A9.97 9.97 0 0121 12a9.97 9.97 0 01-1.42 4.87m-2.13 2.13A9.972 9.972 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-4.59m3.19-3.19a9.97 9.97 0 014.288-1.22"></path></svg>
                                    </button>
                                </div>
                                @error('api_key')
                                    <p class="text-xs text-red-500 font-bold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Custom Endpoint Input --}}
                            <div x-show="showEndpoint" x-cloak x-transition>
                                <label for="custom_endpoint" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Custom Endpoint URL</label>
                                <input type="url" name="custom_endpoint" id="custom_endpoint" value="{{ old('custom_endpoint', request('custom_endpoint', 'http://localhost:11434/v1/chat/completions')) }}" placeholder="Contoh: http://localhost:11434/v1/chat/completions" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white">
                                @error('custom_endpoint')
                                    <p class="text-xs text-red-500 font-bold mt-1.5">{{ $message }}</p>
                                @enderror
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-1">Gunakan endpoint kompatibel OpenAI milik Ollama atau layanan lainnya.</p>
                            </div>

                            <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-lg shadow-indigo-600/20 hover:shadow-xl hover:shadow-indigo-600/30 transition-all duration-300">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Generate CBT Questions
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Preview/Save Column --}}
                <div class="lg:col-span-7 flex flex-col relative">
                    @if(empty($questions))
                        {{-- Empty State --}}
                        <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-12 shadow-xl border border-gray-100 dark:border-gray-700/50 flex flex-col items-center justify-center text-center grow min-h-[500px]">
                            <div class="w-20 h-20 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-[1.5rem] flex items-center justify-center mb-6 shadow-inner animate-pulse">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Pratinjau Butir Soal</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm leading-relaxed">
                                Masukkan kutipan materi untuk merumuskan butir soal, lalu klik <strong>Generate CBT Questions</strong>.
                            </p>
                        </div>
                    @else
                        {{-- Save Form wrapper --}}
                        <form action="{{ route('academic.copilot.cbt.save') }}" method="POST" class="grow flex flex-col space-y-6">
                            @csrf
                            <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">

                            {{-- Question List Container --}}
                            <div class="space-y-6 pb-24 overflow-y-auto max-h-[650px] pr-2">
                                @foreach($questions as $index => $q)
                                    <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-md border border-gray-100 dark:border-gray-700/50 hover:shadow-lg transition-all relative overflow-hidden" 
                                         x-data="{ selected: true }">
                                        
                                        {{-- Visual selection status indicator background --}}
                                        <div :class="selected ? 'opacity-100' : 'opacity-0'" class="absolute top-0 left-0 w-1.5 h-full bg-indigo-600 transition-opacity"></div>
                                        
                                        {{-- Header row --}}
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex items-center space-x-3">
                                                <input type="checkbox" name="selected_questions[]" value="{{ $index }}" x-model="selected" checked class="w-5 h-5 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 transition-all">
                                                <span class="text-sm font-black text-gray-900 dark:text-white">
                                                    Soal #{{ $index + 1 }}
                                                </span>
                                                
                                                {{-- Difficulty badge --}}
                                                @php
                                                    $difficulty = strtolower($q['difficulty'] ?? 'medium');
                                                    $badgeColor = [
                                                        'easy' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400',
                                                        'medium' => 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400',
                                                        'hard' => 'bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400',
                                                    ][$difficulty] ?? 'bg-gray-50 text-gray-600';
                                                @endphp
                                                <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md {{ $badgeColor }}">
                                                    {{ $q['difficulty'] ?? 'Medium' }}
                                                </span>
                                            </div>
                                            
                                            <span class="text-[9px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2.5 py-0.5 rounded-full">
                                                {{ $q['type'] === 'essay' ? 'Essay' : 'Pilihan Ganda' }}
                                            </span>
                                        </div>

                                        {{-- Question Content --}}
                                        <div class="space-y-4">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 leading-relaxed">
                                                {{ $q['question_text'] }}
                                            </p>

                                            {{-- Hidden Inputs for form submit --}}
                                            <input type="hidden" name="questions[{{ $index }}][question_text]" value="{{ $q['question_text'] }}">
                                            <input type="hidden" name="questions[{{ $index }}][type]" value="{{ $q['type'] }}">
                                            <input type="hidden" name="questions[{{ $index }}][correct_answer]" value="{{ $q['correct_answer'] }}">
                                            <input type="hidden" name="questions[{{ $index }}][explanation]" value="{{ $q['explanation'] ?? '' }}">
                                            <input type="hidden" name="questions[{{ $index }}][difficulty]" value="{{ $q['difficulty'] }}">

                                            {{-- If type is multiple choice --}}
                                            @if($q['type'] === 'multiple_choice' || !empty($q['options']))
                                                <div class="grid grid-cols-1 gap-2.5 mt-2">
                                                    @foreach(['A', 'B', 'C', 'D', 'E'] as $letter)
                                                        @if(isset($q['options'][$letter]))
                                                            @php
                                                                $isCorrect = strtoupper(trim($q['correct_answer'])) === $letter;
                                                            @endphp
                                                            <div class="flex items-start p-3 rounded-xl border text-xs font-semibold transition-colors {{ $isCorrect ? 'bg-emerald-50/50 dark:bg-emerald-950/10 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300' : 'bg-gray-50/40 dark:bg-gray-900/40 border-gray-100 dark:border-gray-800/80 text-gray-600 dark:text-gray-400' }}">
                                                                <span class="w-5 h-5 rounded-lg flex items-center justify-center text-[10px] font-black mr-3 shrink-0 {{ $isCorrect ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                                                                    {{ $letter }}
                                                                </span>
                                                                <span class="flex-1 mt-0.5">{{ $q['options'][$letter] }}</span>
                                                                
                                                                <input type="hidden" name="questions[{{ $index }}][options][{{ $letter }}]" value="{{ $q['options'][$letter] }}">
                                                                
                                                                @if($isCorrect)
                                                                    <span class="text-[9px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 self-center shrink-0">Kunci</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                {{-- Essay Key --}}
                                                <div class="p-4 rounded-xl bg-amber-50/40 dark:bg-amber-950/10 border border-amber-100/50 dark:border-amber-900/20 text-xs mt-2">
                                                    <span class="text-[9px] font-black uppercase tracking-widest text-amber-600 dark:text-amber-400 block mb-1">Jawaban Kunci / Kriteria Jawaban</span>
                                                    <p class="font-semibold text-gray-700 dark:text-gray-300 leading-relaxed">{{ $q['correct_answer'] }}</p>
                                                </div>
                                            @endif

                                            {{-- Explanation --}}
                                            @if(!empty($q['explanation']))
                                                <div class="text-[11px] text-gray-400 font-semibold border-t border-gray-100 dark:border-gray-700/60 pt-3">
                                                    <span class="font-black text-gray-600 dark:text-gray-300">Penjelasan Cerdas:</span> {{ $q['explanation'] }}
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Sticky Action Footer --}}
                            <div class="absolute bottom-0 left-0 right-0 bg-white/90 dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700 backdrop-blur-md p-4 rounded-[2rem] flex items-center justify-between shadow-2xl z-20">
                                <div class="hidden sm:block">
                                    <h5 class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-wider">Aksi Impor Massal</h5>
                                    <p class="text-[10px] text-gray-400 font-medium">Impor soal bertanda centang di atas.</p>
                                </div>
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-indigo-600/20 transition-all duration-300">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"></path></svg>
                                    Impor Soal Terpilih ke Bank Soal SISTA
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
