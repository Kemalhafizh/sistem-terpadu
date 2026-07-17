<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.whatsapp.broadcasts.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-xl transition-all hover:scale-105">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-black text-xl md:text-2xl text-gray-800 dark:text-white leading-tight tracking-tight uppercase">
                    {{ __('Create WhatsApp Broadcast') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Buat siaran baru untuk disebarkan secara instan atau terjadwal ke target audiens Anda.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div x-data="{
                title: '',
                targetType: 'all_parents',
                templateId: '',
                messageText: '',
                scheduledAt: '',
                templates: @json($templates->keyBy('id')),
                search: '',
                users: @json($users),
                selectedUsers: [],
                get filteredUsers() {
                    if (this.search === '') return [];
                    return this.users.filter(u => 
                        u.name.toLowerCase().includes(this.search.toLowerCase()) || 
                        u.phone_number.includes(this.search)
                    ).slice(0, 5);
                },
                addUser(user) {
                    if (!this.selectedUsers.some(u => u.id === user.id)) {
                        this.selectedUsers.push(user);
                    }
                    this.search = '';
                },
                removeUser(userId) {
                    this.selectedUsers = this.selectedUsers.filter(u => u.id !== userId);
                },
                updateTemplateContent() {
                    if (this.templateId && this.templates[this.templateId]) {
                        this.messageText = this.templates[this.templateId].content;
                    } else {
                        this.messageText = '';
                    }
                }
            }" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Form Panel (2 Columns Wide on large screens) -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 md:p-8">
                    <form action="{{ route('admin.whatsapp.broadcasts.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Judul Siaran -->
                        <div>
                            <label for="title" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Judul Siaran / Kampanye</label>
                            <input type="text" id="title" name="title" x-model="title" required
                                   class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm text-gray-800 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all"
                                   placeholder="Contoh: Pengumuman Pembagian Rapor Semester 2">
                            @error('title')
                                <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Target Penerima -->
                        <div>
                            <label for="target_type" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Target Penerima</label>
                            <select id="target_type" name="target_type" x-model="targetType" required
                                    class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm text-gray-800 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                                <option value="all_parents">Semua Orang Tua / Wali Murid</option>
                                <option value="all_teachers">Semua Guru & Staf Akademik</option>
                                <option value="specific_class">Kelas Spesifik (Rombel)</option>
                                <option value="custom">Kustom (Pilih Pengguna Tertentu)</option>
                            </select>
                            @error('target_type')
                                <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Specific Class Selector (Hidden by default unless target_type = specific_class) -->
                        <div x-show="targetType === 'specific_class'" x-transition class="space-y-3 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pilih Rombongan Belajar (Kelas)</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-2">
                                @foreach($classrooms as $classroom)
                                    <label class="flex items-center space-x-3 p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-150 dark:border-gray-700 hover:border-teal-500 dark:hover:border-teal-500 cursor-pointer transition-all">
                                        <input type="checkbox" name="target_ids[]" value="{{ $classroom->id }}" class="h-4.5 w-4.5 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                        <div>
                                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">{{ $classroom->name }}</span>
                                            <span class="text-[9px] text-gray-400 uppercase">{{ $classroom->grade_level }} {{ $classroom->major ?? '' }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-[10px] text-gray-400">Anda dapat memilih lebih dari satu kelas.</p>
                            @error('target_ids')
                                <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Custom Target Users (Hidden unless target_type = custom) -->
                        <div x-show="targetType === 'custom'" x-transition class="space-y-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <!-- Alpine user search component -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Cari & Pilih Pengguna Penerima</label>
                                <div class="relative">
                                    <input type="text" x-model="search" placeholder="Ketik nama atau nomor telepon..."
                                           class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-xs text-gray-800 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                                    
                                    <div x-show="filteredUsers.length > 0" class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto divide-y divide-gray-150 dark:divide-gray-700">
                                        <template x-for="user in filteredUsers" :key="user.id">
                                            <button type="button" @click="addUser(user)" class="w-full text-left px-4 py-2.5 hover:bg-teal-50 dark:hover:bg-teal-950/20 text-xs font-semibold text-gray-700 dark:text-gray-200 flex justify-between items-center transition-colors">
                                                <div>
                                                    <span x-text="user.name" class="font-bold"></span>
                                                    <span x-text="' (' + user.role + ')'" class="text-[9px] text-gray-400 capitalize"></span>
                                                </div>
                                                <span x-text="user.phone_number" class="text-teal-600 dark:text-teal-400 font-bold"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Selected Users Chips -->
                                <div class="flex flex-wrap gap-2 mt-3">
                                    <template x-for="user in selectedUsers" :key="user.id">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border border-teal-150 dark:border-teal-800/30 rounded-xl text-xs font-bold transition-all">
                                            <span x-text="user.name"></span>
                                            <button type="button" @click="removeUser(user.id)" class="text-teal-600 hover:text-teal-800 dark:hover:text-teal-300 font-bold">&times;</button>
                                            <input type="hidden" name="target_ids[]" :value="user.id">
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Manual Phone Number Textarea as Fallback/Option -->
                            <hr class="border-gray-200 dark:border-gray-700 my-2">
                            <div>
                                <label for="custom_phone_numbers" class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Input Manual Nomor Telepon (Opsional)</label>
                                <textarea id="custom_phone_numbers" name="custom_phone_numbers" rows="3"
                                          class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-xs text-gray-800 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all"
                                          placeholder="Masukkan nomor telepon dipisah dengan koma atau baris baru...&#10;Contoh: 628123456789, 628987654321"></textarea>
                                <span class="text-[9px] text-gray-400 mt-1 block">Catatan: Pastikan nomor diawali kode negara (62).</span>
                            </div>
                            @error('target_ids')
                                <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Template Select (optional, preloaded templates) -->
                        <div>
                            <label for="template_id" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Pilih Template Pesan (Opsional)</label>
                            <select id="template_id" name="template_id" x-model="templateId" @change="updateTemplateContent()"
                                    class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm text-gray-800 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                                <option value="">-- Tanpa Template (Tulis Kustom) --</option>
                                @foreach($templates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ strtoupper($tpl->category) }})</option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Isi Pesan Siaran -->
                        <div>
                            <label for="message" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Isi Pesan Siaran</label>
                            <textarea id="message" name="message" rows="6" x-model="messageText" :readonly="templateId !== ''"
                                      :class="templateId !== '' ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed opacity-75' : 'bg-gray-50 dark:bg-gray-900'"
                                      class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm text-gray-800 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all"
                                      placeholder="Tulis isi pesan siaran Anda di sini..."></textarea>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1" x-show="templateId !== ''">
                                Pesan menggunakan template. Konten terkunci dan tidak dapat diedit secara manual.
                            </p>
                            @error('message')
                                <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Waktu Penjadwalan (datetime-local, optional) -->
                        <div>
                            <label for="scheduled_at" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Jadwalkan Pengiriman (Opsional)</label>
                            <input type="datetime-local" id="scheduled_at" name="scheduled_at" x-model="scheduledAt"
                                   class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm text-gray-800 dark:text-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">Kosongkan jika ingin langsung dikirimkan sekarang secara instan.</p>
                            @error('scheduled_at')
                                <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('admin.whatsapp.broadcasts.index') }}" 
                               class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold uppercase tracking-wider rounded-xl transition-all">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all hover:scale-102 shadow-sm focus:outline-none">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                Kirim Sekarang / Jadwalkan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Live Preview Mockup Column -->
                <div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-3xl border border-gray-100 dark:border-gray-700 p-6 flex flex-col justify-between h-full min-h-[450px]">
                        <div>
                            <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Pratinjau Pesan Terkirim</span>
                            
                            <!-- Mock Phone Screen -->
                            <div class="bg-[#efeae2] dark:bg-gray-950 rounded-2xl p-4 shadow-inner border border-gray-200 dark:border-gray-900 flex flex-col space-y-3 relative overflow-hidden" style="min-height: 340px; background-image: radial-gradient(circle, #e5ddd5 10%, transparent 11%), radial-gradient(circle, #e5ddd5 10%, transparent 11%); background-size: 20px 20px;">
                                <!-- WhatsApp Header Mock -->
                                <div class="absolute top-0 left-0 right-0 bg-[#075e54] dark:bg-teal-900 px-4 py-2.5 flex items-center gap-2 text-white shadow-sm z-10">
                                    <div class="w-8 h-8 rounded-full bg-teal-600 dark:bg-teal-800 flex items-center justify-center font-bold text-xs uppercase text-teal-100">
                                        S
                                    </div>
                                    <div>
                                        <span class="block text-xs font-bold leading-tight">SISTA Gateway</span>
                                        <span class="text-[9px] text-teal-100/80 leading-none">Online</span>
                                    </div>
                                </div>
                                
                                <!-- Message Bubble -->
                                <div class="pt-8 flex flex-col space-y-2 z-0">
                                    <!-- System Notification Bubble -->
                                    <div class="self-center bg-[#d1f4cc] dark:bg-teal-950/50 text-gray-800 dark:text-gray-200 text-[10px] py-1 px-3 rounded-lg shadow-sm border border-emerald-100 dark:border-emerald-900/20 text-center max-w-[85%] font-medium">
                                        Siaran: <span class="font-bold text-teal-800 dark:text-teal-400" x-text="title || 'Belum Berjudul'"></span>
                                    </div>
                                    
                                    <!-- Main Message Bubble -->
                                    <div class="self-start bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-xs py-2.5 px-3 rounded-lg rounded-tl-none shadow-sm max-w-[85%] relative border border-gray-150 dark:border-gray-750/30">
                                        <div class="whitespace-pre-line font-medium leading-relaxed" x-text="messageText || 'Tulis pesan atau pilih template untuk melihat pratinjau...' "></div>
                                        <span class="block text-[8px] text-gray-400 text-right mt-1.5 font-bold uppercase tracking-wider">
                                            <span x-show="scheduledAt">Terjadwal</span>
                                            <span x-show="!scheduledAt">Instan</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-150 dark:border-gray-800 text-xs text-gray-400 dark:text-gray-500 font-medium">
                            <p class="leading-relaxed">⚠️ Catatan: Variabel dynamic placeholder seperti <code class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-teal-600 dark:text-teal-400 font-bold">{nama_siswa}</code> akan otomatis diubah menjadi data riil saat dikirim.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
