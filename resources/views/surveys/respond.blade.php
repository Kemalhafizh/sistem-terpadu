<x-app-layout>
    <x-slot name="title">Isi Kuesioner: {{ $survey->title }}</x-slot>

    <x-slot name="breadcrumbs">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Survei Digital', 'url' => route('surveys.index')],
            ['label' => 'Isi Kuesioner', 'url' => '#']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('surveys.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <span class="px-3 py-1 text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200/30 rounded-full inline-block mb-1">
                    {{ $survey->is_anonymous ? 'RESPON ANONIM (PDP Protected)' : 'IDENTITAS TERBUKA' }}
                </span>
                <h1 class="text-3xl font-black tracking-tight text-slate-800 dark:text-white leading-tight">
                    {{ $survey->title }}
                </h1>
            </div>
        </div>
    </x-slot>

    <!-- Main Container -->
    <div class="max-w-4xl mx-auto space-y-8" 
         x-data="surveyResponder({
             questions: {{ Js::from($survey->questions->map(fn($q) => [
                 'id' => $q->id,
                 'is_required' => $q->is_required,
                 'type' => $q->type
             ])) }}
         })">
        
        <!-- Welcome & Instructions Card -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl relative overflow-hidden">
            <!-- Background Accent -->
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl"></div>

            <div class="space-y-4">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white">Petunjuk Pengisian:</h2>
                <div class="text-slate-600 dark:text-slate-300 text-sm font-semibold leading-relaxed space-y-2">
                    @if($survey->description)
                        <p>{!! nl2br(e($survey->description)) !!}</p>
                    @else
                        <p>Mohon luangkan waktu Anda untuk mengisi kuesioner ini secara objektif. Umpan balik yang Anda berikan sangat berharga bagi peningkatan mutu pendidikan di sekolah kami.</p>
                    @endif
                </div>

                @if($survey->is_anonymous)
                    <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-4 flex items-start space-x-3 mt-4">
                        <div class="p-1.5 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-black uppercase text-emerald-800 dark:text-emerald-300 tracking-wider">Perlindungan Data & Privasi Terjamin</h4>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-1">
                                Survei ini berstatus <strong>ANONIM</strong>. Seluruh data identitas Anda (Nama, Email, User ID) dienkripsi dan diproteksi di tingkat database serta otomatis dihilangkan pada laporan analisis atau ekspor data.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Alpine Real-time Progress Bar -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-5 shadow-xl space-y-2 sticky top-4 z-40">
            <div class="flex items-center justify-between text-xs font-extrabold text-slate-700 dark:text-slate-300">
                <span class="flex items-center">
                    <svg class="w-4.5 h-4.5 text-emerald-500 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Progres Pengisian
                </span>
                <span x-text="`${answeredCount} dari ${totalRequired} Pertanyaan Wajib Terisi (${Math.round(progressPercent)}%)`"></span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-900 rounded-full h-3 overflow-hidden border border-slate-200/30 dark:border-slate-800">
                <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-full rounded-full transition-all duration-500 ease-out" 
                     :style="`width: ${progressPercent}%`"></div>
            </div>
        </div>

        <!-- Survey Response Form -->
        <form action="{{ route('surveys.respond', $survey->uuid) }}" method="POST" class="space-y-6">
            @csrf

            @if ($errors->any())
                <div class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200/50 dark:border-rose-800/50 rounded-3xl p-5 space-y-2">
                    <h4 class="text-sm font-black text-rose-800 dark:text-rose-400 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Harap perbaiki beberapa kesalahan berikut:
                    </h4>
                    <ul class="list-disc pl-5 text-xs font-semibold text-rose-600 dark:text-rose-400 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Questions -->
            @foreach($survey->questions as $index => $question)
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl space-y-4 hover:shadow-2xl transition-all duration-300">
                    
                    <!-- Question Header -->
                    <div class="flex items-start justify-between">
                        <h3 class="text-base font-black text-slate-800 dark:text-white leading-tight">
                            <span class="text-emerald-500 font-extrabold mr-1">{{ $index + 1 }}.</span>
                            {{ $question->question_text }}
                            @if($question->is_required)
                                <span class="text-rose-500 font-bold" title="Wajib Diisi">*</span>
                            @endif
                        </h3>
                    </div>

                    <!-- Question Input rendering by Type -->
                    <div class="pt-2">
                        
                        <!-- 1. Text Type -->
                        @if($question->type === 'text')
                            <input type="text" 
                                   name="answers[{{ $question->id }}]" 
                                   value="{{ old('answers.' . $question->id) }}"
                                   x-on:input="updateAnswer({{ $question->id }}, $event.target.value)"
                                   placeholder="Ketikkan jawaban Anda di sini..."
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold text-sm placeholder-slate-400">
                        
                        <!-- 2. Textarea Type -->
                        @elseif($question->type === 'textarea')
                            <textarea name="answers[{{ $question->id }}]" 
                                      rows="4"
                                      x-on:input="updateAnswer({{ $question->id }}, $event.target.value)"
                                      placeholder="Berikan jawaban esai lengkap Anda di sini..."
                                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold text-sm placeholder-slate-400">{{ old('answers.' . $question->id) }}</textarea>
                        
                        <!-- 3. Radio Type -->
                        @elseif($question->type === 'radio')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($question->options as $oIndex => $option)
                                    <label class="relative flex items-center p-4 bg-slate-50 dark:bg-slate-900/40 hover:bg-emerald-500/5 border border-slate-200 dark:border-slate-700 rounded-2xl cursor-pointer transition-all duration-200 group">
                                        <input type="radio" 
                                               name="answers[{{ $question->id }}]" 
                                               value="{{ $option }}"
                                               x-on:change="updateAnswer({{ $question->id }}, $event.target.value)"
                                               {{ old('answers.' . $question->id) === $option ? 'checked' : '' }}
                                               class="text-emerald-600 focus:ring-emerald-500 border-slate-300 dark:border-slate-700 w-5 h-5 mr-3 transition-all">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                            {{ $option }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                        <!-- 4. Checkbox Type -->
                        @elseif($question->type === 'checkbox')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" 
                                 x-data="{ selectedValues: {{ Js::from(old('answers.' . $question->id, [])) }} }"
                                 x-init="$watch('selectedValues', val => updateAnswer({{ $question->id }}, val))">
                                @foreach($question->options as $oIndex => $option)
                                    <label class="relative flex items-center p-4 bg-slate-50 dark:bg-slate-900/40 hover:bg-emerald-500/5 border border-slate-200 dark:border-slate-700 rounded-2xl cursor-pointer transition-all duration-200 group">
                                        <input type="checkbox" 
                                               name="answers[{{ $question->id }}][]" 
                                               value="{{ $option }}"
                                               x-model="selectedValues"
                                               class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 dark:border-slate-700 w-5 h-5 mr-3 transition-all">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                            {{ $option }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                        <!-- 5. Select Type -->
                        @elseif($question->type === 'select')
                            <select name="answers[{{ $question->id }}]"
                                    x-on:change="updateAnswer({{ $question->id }}, $event.target.value)"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold text-sm">
                                <option value="">-- Pilih salah satu opsi --</option>
                                @foreach($question->options as $option)
                                    <option value="{{ $option }}" {{ old('answers.' . $question->id) === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>

                        <!-- 6. Scale Likert Type -->
                        @elseif($question->type === 'scale')
                            <div class="space-y-4 py-2" x-data="{ currentVal: '{{ old('answers.' . $question->id) }}' }">
                                @if($question->scale_min_label || $question->scale_max_label)
                                    <div class="flex items-center justify-between text-xs font-black text-slate-500 dark:text-slate-400 px-1">
                                        <span>{{ $question->scale_min_label ?? $question->scale_min }}</span>
                                        <span>{{ $question->scale_max_label ?? $question->scale_max }}</span>
                                    </div>
                                @endif
                                
                                <div class="flex items-center justify-between gap-1 sm:gap-2">
                                    @for($rating = $question->scale_min; $rating <= $question->scale_max; $rating++)
                                        <label class="flex-1 flex flex-col items-center justify-center py-3 bg-slate-50 dark:bg-slate-900/40 border rounded-2xl cursor-pointer hover:bg-emerald-500/5 transition-all"
                                               :class="currentVal == {{ $rating }} ? 'border-emerald-500 bg-emerald-500/10 dark:bg-emerald-950/20' : 'border-slate-200 dark:border-slate-700'">
                                            <input type="radio" 
                                                   name="answers[{{ $question->id }}]" 
                                                   value="{{ $rating }}"
                                                   x-model="currentVal"
                                                   x-on:change="updateAnswer({{ $question->id }}, $event.target.value)"
                                                   class="text-emerald-600 focus:ring-emerald-500 border-slate-300 dark:border-slate-700 w-4.5 h-4.5 mb-1.5 transition-all">
                                            <span class="text-sm font-black" 
                                                  :class="currentVal == {{ $rating }} ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300'">
                                                {{ $rating }}
                                            </span>
                                        </label>
                                    @endfor
                                </div>
                            </div>

                        <!-- 7. Date Type -->
                        @elseif($question->type === 'date')
                            <input type="date" 
                                   name="answers[{{ $question->id }}]" 
                                   value="{{ old('answers.' . $question->id) }}"
                                   x-on:change="updateAnswer({{ $question->id }}, $event.target.value)"
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold text-sm">
                        
                        @endif

                    </div>
                </div>
            @endforeach

            <!-- Form Action Footer -->
            <div class="flex items-center justify-between bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl">
                <a href="{{ route('surveys.index') }}" 
                   class="px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-2xl text-sm font-extrabold hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all">
                    Kembali
                </a>
                <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-2xl text-sm font-black shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transition-all border border-emerald-400/20 flex items-center group">
                    Kirim Jawaban Kuesioner
                    <svg class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </form>
    </div>

    <script>
        function surveyResponder(config) {
            return {
                responses: {},
                questionsMeta: config.questions || [],
                totalRequired: 0,
                answeredCount: 0,
                progressPercent: 0,

                init() {
                    // Populate initial required counts
                    this.totalRequired = this.questionsMeta.filter(q => q.is_required).length;
                    
                    // Initialize empty responses
                    this.questionsMeta.forEach(q => {
                        this.responses[q.id] = null;
                    });
                },

                updateAnswer(questionId, value) {
                    // Handle array response (e.g. checkbox)
                    if (Array.isArray(value)) {
                        this.responses[questionId] = value.length > 0 ? value : null;
                    } else {
                        this.responses[questionId] = (value && value.trim() !== '') ? value : null;
                    }
                    this.recalculateProgress();
                },

                recalculateProgress() {
                    let completedRequired = 0;
                    
                    this.questionsMeta.forEach(q => {
                        if (q.is_required && this.responses[q.id] !== null) {
                            completedRequired++;
                        }
                    });

                    this.answeredCount = completedRequired;
                    
                    if (this.totalRequired > 0) {
                        this.progressPercent = (completedRequired / this.totalRequired) * 100;
                    } else {
                        this.progressPercent = 100;
                    }
                }
            }
        }
    </script>
</x-app-layout>
