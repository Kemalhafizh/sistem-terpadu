<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-3xl text-gray-900 dark:text-white leading-tight tracking-tight">
                    Obrolan & <span class="text-primary-600 dark:text-primary-400">Konsultasi Wali Kelas</span>
                </h2>
                <p class="text-sm font-semibold text-gray-500 mt-1">{{ school_name() }}</p>
            </div>
            
            <!-- Child Switcher Tab -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-1.5 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 flex gap-2 shadow-sm">
                @foreach($children as $child)
                    <a href="{{ route('parent-portal.messages', ['child_id' => $child->id]) }}" 
                       class="px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-300 flex items-center gap-2 {{ $student->id === $child->id ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="w-2.5 h-2.5 rounded-full {{ $student->id === $child->id ? 'bg-white' : 'bg-primary-500' }}"></span>
                        {{ $child->user->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </x-slot>

    <!-- Content Area -->
    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-emerald-600 text-white p-4 rounded-2xl shadow-xl flex items-center mb-6 animate-fade-in">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-rose-600 text-white p-4 rounded-2xl shadow-xl flex items-center mb-6 animate-fade-in">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                
                <!-- 1. Left Panel: Teacher Card info -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-gray-200/60 dark:border-slate-700">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Wali Kelas Terkait</h4>
                        
                        @if($teacher)
                            <div class="text-center relative z-10">
                                <div class="w-20 h-20 bg-primary-600 rounded-3xl flex items-center justify-center font-black text-3xl text-white shadow-md mx-auto mb-4">
                                    {{ substr($teacher->name, 0, 1) }}
                                </div>
                                <h3 class="font-black text-gray-900 dark:text-white text-lg leading-tight tracking-tight">{{ $teacher->name }}</h3>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-1.5">{{ $student->classroom->name }}</p>
                                
                                <div class="mt-6 pt-6 border-t border-gray-100 dark:border-slate-700 text-start space-y-4 text-xs font-semibold text-gray-600 dark:text-slate-300">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <div>
                                            <span class="text-[9px] text-gray-400 font-black block uppercase tracking-wider">Email</span>
                                            <strong class="text-gray-800 dark:text-slate-100">{{ $teacher->email }}</strong>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        </div>
                                        <div>
                                            <span class="text-[9px] text-gray-400 font-black block uppercase tracking-wider">Nomor Telepon</span>
                                            <strong class="text-gray-800 dark:text-slate-100">{{ $teacher->phone ?? 'Belum Ada' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="py-6 text-center text-gray-400 italic text-xs">
                                 Wali kelas tidak ditemukan untuk kelas anak ini.
                            </div>
                        @endif
                    </div>

                    <!-- Guidance Info -->
                    <div class="bg-slate-900 rounded-3xl p-6 shadow-md text-white">
                        <h4 class="text-xs font-black text-indigo-300 uppercase tracking-widest mb-3">Panduan Obrolan</h4>
                        <p class="text-[11px] text-gray-300 leading-relaxed font-semibold">
                            Portal Wali Murid memfasilitasi komunikasi asinkron dengan Wali Kelas untuk konsultasi hasil belajar, kehadiran siswa, bimbingan konseling, dan pengajuan izin.
                        </p>
                    </div>
                </div>

                <!-- 2. Right Panel: Modern Chat Interface Box -->
                <div class="lg:col-span-3">
                    <div x-data="{
                        studentId: {{ $student->id }},
                        authId: {{ auth()->id() }},
                        newMessage: '',
                        isTyping: false,
                        typingUser: '',
                        typingTimeout: null,
                        messages: {{ json_encode($messages->map(function($msg) {
                            return [
                                'id' => $msg->id,
                                'sender_id' => (int) $msg->sender_id,
                                'message' => $msg->message,
                                'created_at_human' => $msg->created_at->diffForHumans(),
                                'is_read' => (bool) $msg->is_read
                            ];
                        })) }},
                        init() {
                            this.scrollToBottom();
                            if (window.Echo) {
                                window.Echo.private('chat.' + this.studentId)
                                    .listen('.message-sent', (e) => {
                                        this.messages.push({
                                            id: e.message.id,
                                            sender_id: parseInt(e.message.sender_id),
                                            message: e.message.message,
                                            created_at_human: 'Baru saja',
                                            is_read: e.message.is_read
                                        });
                                        this.scrollToBottom();
                                    })
                                    .listenForWhisper('typing', (e) => {
                                        this.isTyping = true;
                                        this.typingUser = e.name;
                                        clearTimeout(this.typingTimeout);
                                        this.typingTimeout = setTimeout(() => {
                                            this.isTyping = false;
                                        }, 3000);
                                    });
                            }
                        },
                        scrollToBottom() {
                            this.$nextTick(() => {
                                const container = document.getElementById('chat-messages-container');
                                if (container) {
                                    container.scrollTop = container.scrollHeight;
                                }
                            });
                        },
                        sendTyping() {
                            if (window.Echo) {
                                window.Echo.private('chat.' + this.studentId)
                                    .whisper('typing', {
                                        name: '{{ auth()->user()->name }}'
                                    });
                            }
                        },
                        async sendMessage() {
                            if (!this.newMessage.trim()) return;
                            
                            const messageText = this.newMessage;
                            this.newMessage = '';
                            
                            // Instant feedback
                            const tempId = 'temp-' + Date.now();
                            this.messages.push({
                                id: tempId,
                                sender_id: this.authId,
                                message: messageText,
                                created_at_human: 'Baru saja',
                                is_read: false
                            });
                            this.scrollToBottom();
                            
                            try {
                                const response = await fetch('{{ route('parent-portal.messages.send') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        student_id: this.studentId,
                                        message: messageText
                                    })
                                });
                                
                                if (response.ok) {
                                    const data = await response.json();
                                    const idx = this.messages.findIndex(m => m.id === tempId);
                                    if (idx !== -1 && data.message) {
                                        this.messages[idx].id = data.message.id;
                                    }
                                }
                            } catch (err) {
                                console.error(err);
                            }
                        }
                    }" class="bg-white rounded-[2rem] shadow-sm border border-gray-200/60 flex flex-col h-[650px] overflow-hidden">
                        <!-- Chat Header -->
                        <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center font-black text-indigo-600 text-lg">
                                    {{ $teacher ? substr($teacher->name, 0, 1) : '?' }}
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-gray-800 text-sm tracking-tight">{{ $teacher->name ?? 'Wali Kelas' }}</h4>
                                    <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                        Saluran Konsultasi Aktif
                                    </span>
                                </div>
                            </div>
                            
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                Anak: <strong class="text-indigo-600">{{ $student->user->name }}</strong>
                            </span>
                        </div>

                        <!-- Chat Messages Area -->
                        <div id="chat-messages-container" class="flex-1 p-8 overflow-y-auto space-y-6 bg-slate-50/50 scroll-smooth">
                            <template x-for="msg in messages" :key="msg.id">
                                <div class="flex" :class="msg.sender_id === authId ? 'justify-end animate-fade-in' : 'justify-start animate-fade-in'">
                                    <div class="max-w-[70%] space-y-1">
                                        <div class="px-5 py-3.5 rounded-3xl text-xs font-semibold leading-relaxed shadow-sm"
                                            :class="msg.sender_id === authId 
                                                ? 'bg-gradient-to-r from-primary-600 to-indigo-600 text-white rounded-tr-none shadow-indigo-900/10' 
                                                : 'bg-white text-gray-800 border border-gray-200/60 rounded-tl-none' 
                                            ">
                                            <p class="whitespace-pre-wrap" x-text="msg.message"></p>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[9px] font-bold text-gray-400 px-2" :class="msg.sender_id === authId ? 'justify-end' : 'justify-start'">
                                            <span x-text="msg.created_at_human"></span>
                                            <template x-if="msg.sender_id === authId">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="opacity-40">•</span>
                                                    <span :class="msg.is_read ? 'text-primary-500' : 'text-gray-400'" x-text="msg.is_read ? 'Dibaca' : 'Terkirim'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            
                            <!-- Typing Indicator bubble -->
                            <template x-if="isTyping">
                                <div class="flex justify-start animate-pulse">
                                    <div class="max-w-[70%] space-y-1">
                                        <div class="px-5 py-3 rounded-3xl text-xs font-semibold leading-relaxed bg-white text-gray-500 border border-gray-200/60 rounded-tl-none flex items-center gap-1">
                                            <span x-text="typingUser" class="font-bold"></span> sedang mengetik
                                            <span class="flex gap-0.5 ml-1">
                                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            
                            <template x-if="messages.length === 0">
                                <div class="h-full flex flex-col items-center justify-center text-center text-gray-400 space-y-3">
                                    <span class="text-4xl"></span>
                                    <div>
                                        <p class="font-black text-sm text-gray-500">Belum Ada Riwayat Konsultasi</p>
                                        <p class="text-xs text-gray-400 mt-1 max-w-[280px] leading-relaxed mx-auto">
                                            Ketik pesan pertama Anda di bawah untuk memulai konsultasi langsung dengan wali kelas.
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Chat Input Box -->
                        @if($teacher)
                            <div class="p-6 border-t border-gray-100 bg-white">
                                <form @submit.prevent="sendMessage" class="flex items-center gap-3">
                                    <input type="text" name="message" x-model="newMessage" @input.debounce.500ms="sendTyping()" required autocomplete="off" placeholder="Tulis pesan konsultasi Anda di sini..." 
                                           class="flex-1 px-5 py-4 border border-gray-200 hover:border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-500/10 rounded-2xl text-xs font-semibold placeholder-gray-400 transition-all duration-200">
                                    
                                    <button type="submit" class="px-6 py-4 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-lg shadow-indigo-900/10 transition-all duration-300 flex items-center gap-1.5 active:scale-[0.98]">
                                        <span>Kirim</span>
                                        <svg class="w-4 h-4 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="p-6 border-t border-gray-100 bg-gray-50 text-center font-bold text-xs text-gray-400">
                                Tombol kirim dinonaktifkan karena wali kelas tidak terdeteksi.
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Script to keep the scroll bottom active -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('chat-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    </script>
</x-app-layout>
