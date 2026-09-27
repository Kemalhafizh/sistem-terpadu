<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('academic.copilot.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-black text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('RPP Kurikulum Merdeka Writer') }}
            </h2>
        </div>
    </x-slot>

    {{-- Markdown Compiler --}}
    <x-vendor.script lib="marked" />

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
                            <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30 px-3 py-1 rounded-full">
                                Parameter Input
                            </span>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white mt-3">Rancang Detail RPP</h3>
                            <p class="text-xs text-gray-400 mt-1">Lengkapi data di bawah ini untuk menghasilkan draf RPP.</p>
                        </div>

                        <form action="{{ route('academic.copilot.rpp.generate') }}" method="POST" class="space-y-5">
                            @csrf
                            
                            {{-- Subject input --}}
                            <div>
                                <label for="subject_id" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Mata Pelajaran</label>
                                <select name="subject_id" id="subject_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all dark:text-white">
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

                            {{-- Classroom Input --}}
                            <div>
                                <label for="classroom_id" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Kelas / Tingkat</label>
                                <select name="classroom_id" id="classroom_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all dark:text-white">
                                    <option value="">Pilih Kelas</option>
                                    @foreach($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}" {{ old('classroom_id', request('classroom_id')) == $classroom->id ? 'selected' : '' }}>
                                            {{ $classroom->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('classroom_id')
                                    <p class="text-xs text-red-500 font-bold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Topic Input --}}
                            <div>
                                <label for="topic" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Topik / Tema Pembelajaran</label>
                                <input type="text" name="topic" id="topic" value="{{ old('topic', request('topic')) }}" placeholder="Contoh: Persamaan Kuadrat, Ekosistem Hutan" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all dark:text-white">
                                @error('topic')
                                    <p class="text-xs text-red-500 font-bold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Competencies Textarea --}}
                            <div>
                                <label for="competencies" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Tujuan Pembelajaran / Kompetensi Dasar</label>
                                <textarea name="competencies" id="competencies" rows="4" placeholder="Uraikan tujuan pembelajaran utama yang ingin dicapai..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all dark:text-white">{{ old('competencies', request('competencies')) }}</textarea>
                                @error('competencies')
                                    <p class="text-xs text-red-500 font-bold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Model Provider --}}
                            <div>
                                <label for="provider_select" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">AI Model Provider</label>
                                <select id="provider_select" x-model="provider" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all dark:text-white">
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
                                    <input :type="show ? 'text' : 'password'" name="api_key" id="api_key" value="{{ old('api_key', request('api_key')) }}" placeholder="Masukkan Kunci API Anda..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 pr-10 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all dark:text-white">
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
                                <input type="url" name="custom_endpoint" id="custom_endpoint" value="{{ old('custom_endpoint', request('custom_endpoint', 'http://localhost:11434/v1/chat/completions')) }}" placeholder="Contoh: http://localhost:11434/v1/chat/completions" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-3 px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all dark:text-white">
                                @error('custom_endpoint')
                                    <p class="text-xs text-red-500 font-bold mt-1.5">{{ $message }}</p>
                                @enderror
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-1">Gunakan endpoint kompatibel OpenAI milik Ollama atau layanan lainnya.</p>
                            </div>

                            <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-4 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-lg shadow-teal-600/20 hover:shadow-xl hover:shadow-teal-600/30 transition-all duration-300">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                Generate RPP
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Preview/Editor Column --}}
                <div class="lg:col-span-7 flex flex-col">
                    @if(empty($rppContent))
                        {{-- Empty State --}}
                        <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-12 shadow-xl border border-gray-100 dark:border-gray-700/50 flex flex-col items-center justify-center text-center grow min-h-[500px]">
                            <div class="w-20 h-20 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-[1.5rem] flex items-center justify-center mb-6 shadow-inner animate-pulse">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Pratinjau Hasil RPP</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm leading-relaxed">
                                Isi form parameter di samping, lalu klik tombol <strong>Generate RPP</strong> untuk merancang draf Kurikulum Merdeka secara otomatis.
                            </p>
                        </div>
                    @else
                        {{-- RPP Present & Editable --}}
                        <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-xl border border-gray-100 dark:border-gray-700/50 flex flex-col grow overflow-hidden" 
                             x-data="{
                                 rppContentText: @js($rppContent),
                                 activeTab: 'preview',
                                 get parsedMarkdown() {
                                     return typeof marked !== 'undefined' ? marked.parse(this.rppContentText) : this.rppContentText;
                                 }
                             }">
                            
                            {{-- Tab header --}}
                            <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">
                                        Hasil Generasi AI
                                    </span>
                                    <h4 class="text-sm font-black text-gray-800 dark:text-white mt-1">Draf Modul Ajar RPP</h4>
                                </div>
                                <div class="flex bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
                                    <button type="button" @click="activeTab = 'preview'" :class="activeTab === 'preview' ? 'bg-white dark:bg-gray-700 text-teal-600 dark:text-teal-300 shadow-sm font-black' : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 font-bold'" class="px-4 py-1.5 rounded-lg text-xs uppercase tracking-wider transition-all">
                                        Pratinjau
                                    </button>
                                    <button type="button" @click="activeTab = 'edit'" :class="activeTab === 'edit' ? 'bg-white dark:bg-gray-700 text-teal-600 dark:text-teal-300 shadow-sm font-black' : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 font-bold'" class="px-4 py-1.5 rounded-lg text-xs uppercase tracking-wider transition-all">
                                        Edit Kode
                                    </button>
                                </div>
                            </div>

                            {{-- Render content area --}}
                            <div class="p-6 grow flex flex-col overflow-y-auto max-h-[600px] min-h-[500px]">
                                {{-- Preview Tab --}}
                                <div x-show="activeTab === 'preview'" class="grow flex flex-col">
                                    <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed space-y-4" x-html="parsedMarkdown">
                                        {{-- Compiled Markdown Rendered here --}}
                                    </div>
                                    
                                    {{-- Inline styling details to make markdown lists, headers look good if prose is missing --}}
                                    <style>
                                        .prose h1 { font-size: 1.5rem; font-weight: 800; color: #111827; margin-top: 1.5rem; margin-bottom: 0.75rem; border-bottom: 2px solid #f3f4f6; padding-bottom: 0.5rem; }
                                        .dark .prose h1 { color: #f9fafb; border-color: #374151; }
                                        .prose h2 { font-size: 1.25rem; font-weight: 800; color: #1f2937; margin-top: 1.25rem; margin-bottom: 0.5rem; }
                                        .dark .prose h2 { color: #f3f4f6; }
                                        .prose h3 { font-size: 1.1rem; font-weight: 700; color: #374151; margin-top: 1rem; }
                                        .dark .prose h3 { color: #e5e7eb; }
                                        .prose ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
                                        .prose ol { list-style-type: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
                                        .prose li { margin-bottom: 0.25rem; }
                                        .prose p { margin-bottom: 1rem; }
                                        .prose hr { margin: 1.5rem 0; border-top: 1px solid #e5e7eb; }
                                        .dark .prose hr { border-color: #374151; }
                                    </style>
                                </div>

                                {{-- Edit Tab --}}
                                <div x-show="activeTab === 'edit'" class="grow flex flex-col" style="display: none;">
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Markdown Editor</p>
                                    <textarea x-model="rppContentText" class="w-full grow bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl py-4 px-5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all dark:text-white min-h-[400px] resize-y"></textarea>
                                </div>
                            </div>

                            {{-- Hidden form targeted to Save --}}
                            <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                                <form action="{{ route('academic.copilot.rpp.save') }}" method="POST" class="w-full sm:w-auto">
                                    @csrf
                                    <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                                    <input type="hidden" name="classroom_id" value="{{ request('classroom_id') }}">
                                    <input type="hidden" name="topic" value="{{ request('topic') }}">
                                    <input type="hidden" name="competencies" value="{{ request('competencies') }}">
                                    
                                    {{-- Bind text area to Alpine text model --}}
                                    <textarea name="rpp_content" x-model="rppContentText" class="hidden"></textarea>

                                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-teal-600/10 hover:shadow-lg transition-all">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                        Simpan ke Draft Kurikulum SISTA
                                    </button>
                                </form>
                            </div>

                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
