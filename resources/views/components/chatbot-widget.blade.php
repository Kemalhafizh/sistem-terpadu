<div x-data="chatbotWidget()" class="fixed bottom-6 right-6 z-50 font-sans">
    <!-- Chat Toggle Button (Floating Bubble) -->
    <button @click="toggle()" 
            class="flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white shadow-2xl hover:scale-110 active:scale-95 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:ring-offset-2 focus:ring-offset-gray-900 group">
        <!-- Chat Icon (Default) -->
        <svg x-show="!isOpen" class="w-6 h-6 animate-pulse group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
        </svg>
        <!-- Close Icon (When open) -->
        <svg x-show="isOpen" x-cloak class="w-6 h-6 transition-transform hover:rotate-90 duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <!-- Chat Window Panel -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-12 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-12 scale-95"
         x-cloak
         class="absolute bottom-18 right-0 w-96 h-[500px] flex flex-col bg-gray-950/90 dark:bg-gray-950/95 border border-white/10 rounded-3xl shadow-2xl backdrop-blur-xl overflow-hidden">
        
        <!-- Header -->
        <div class="p-4 bg-gradient-to-r from-blue-900/60 to-purple-900/60 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <!-- Glowing avatar -->
                <div class="relative w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <span class="absolute top-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-gray-950 rounded-full"></span>
                    <span class="text-xs"></span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white tracking-wide">Asisten SISTA</h3>
                    <p class="text-[10px] text-gray-400">Offline Chatbot & Navigator</p>
                </div>
            </div>
            <!-- Settings / Minimize -->
            <button @click="toggle()" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>

        <!-- Chat Bubble Container -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages" x-ref="messageContainer">
            <!-- Welcome message -->
            <div class="flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-[10px]"></div>
                <div class="p-3 bg-white/5 border border-white/5 rounded-2xl rounded-tl-none max-w-[80%]">
                    <p class="text-xs text-gray-200 leading-relaxed">
                        Halo **{{ auth()->user()->name }}**! Saya adalah asisten sekolah cerdas Anda. Anda dapat berkonsultasi tentang:
                    </p>
                    <ul class="mt-2 space-y-1 text-[11px] text-gray-400">
                        <li>•  *Jumlah alpa hari ini*</li>
                        <li>•  *Total tunggakan tagihan SPP*</li>
                        <li>•  *Buka halaman E-Rapor atau Jadwal*</li>
                        <li>•  Informasi administrasi lainnya.</li>
                    </ul>
                </div>
            </div>

            <!-- Messages Loop -->
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.isBot ? 'flex items-start gap-2.5' : 'flex items-start justify-end gap-2.5'">
                    <!-- Bot Avatar -->
                    <template x-if="msg.isBot">
                        <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-[10px]"></div>
                    </template>

                    <!-- Bubble Content -->
                    <div class="flex flex-col max-w-[80%]">
                        <div :class="msg.isBot 
                                     ? 'p-3 bg-white/5 border border-white/5 rounded-2xl rounded-tl-none text-xs text-gray-200 leading-relaxed' 
                                     : 'p-3 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-2xl rounded-tr-none text-xs text-white leading-relaxed'"
                             x-html="msg.text">
                        </div>

                        <!-- Bot message extra (like feedback) -->
                        <template x-if="msg.isBot && msg.conversation_id && !msg.feedbackGiven">
                            <div class="flex items-center gap-2 mt-1.5 px-1">
                                <span class="text-[10px] text-gray-500">Membantu?</span>
                                <button @click="sendFeedback(msg, true)" class="text-gray-400 hover:text-green-400 transition text-[11px]"></button>
                                <button @click="sendFeedback(msg, false)" class="text-gray-400 hover:text-red-400 transition text-[11px]"></button>
                            </div>
                        </template>
                        <template x-if="msg.isBot && msg.feedbackGiven">
                            <span class="text-[9px] text-gray-600 mt-1 px-1 italic">Feedback terekam. Terima kasih!</span>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Loading indicator -->
            <div x-show="isLoading" x-cloak class="flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-[10px]"></div>
                <div class="p-3 bg-white/5 border border-white/5 rounded-2xl rounded-tl-none flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce [animation-delay:0.2s]"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce [animation-delay:0.4s]"></span>
                </div>
            </div>
        </div>

        <!-- Input Footer -->
        <div class="p-3 bg-gray-950 border-t border-white/5">
            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                @csrf
                <input x-model="userInput" 
                       type="text" 
                       placeholder="Tanya asisten sekolah..." 
                       class="flex-1 bg-white/5 border border-white/10 rounded-2xl px-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all duration-300">
                <button type="submit" 
                        class="p-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white transition duration-300 shadow-lg shadow-indigo-500/20 active:scale-95 focus:outline-none">
                    <svg class="w-4 h-4 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function chatbotWidget() {
        return {
            isOpen: false,
            userInput: '',
            isLoading: false,
            messages: [],

            toggle() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                }
            },

            sendMessage() {
                if (this.userInput.trim() === '') return;

                const text = this.userInput;
                this.messages.push({
                    isBot: false,
                    text: text
                });
                this.userInput = '';
                this.isLoading = true;

                this.$nextTick(() => {
                    this.scrollToBottom();
                });

                fetch('{{ route("chatbot.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: text })
                })
                .then(res => res.json())
                .then(data => {
                    this.isLoading = false;
                    if (data.success) {
                        this.messages.push({
                            isBot: true,
                            text: data.reply,
                            conversation_id: data.conversation_id,
                            feedbackGiven: false
                        });
                    } else {
                        this.messages.push({
                            isBot: true,
                            text: 'Maaf, terjadi kendala teknis saat memproses pesan Anda. Coba lagi nanti.'
                        });
                    }
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                })
                .catch(err => {
                    this.isLoading = false;
                    this.messages.push({
                        isBot: true,
                        text: 'Koneksi gagal. Pastikan jaringan internet atau server Anda aktif.'
                    });
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                });
            },

            sendFeedback(msg, isHelpful) {
                fetch('{{ route("chatbot.feedback") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        conversation_id: msg.conversation_id,
                        was_helpful: isHelpful
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        msg.feedbackGiven = true;
                    }
                });
            },

            scrollToBottom() {
                const container = this.$refs.messageContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }
        }
    }
</script>
