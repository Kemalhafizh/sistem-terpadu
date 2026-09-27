<div x-data="uatFeedbackForm()" class="fixed bottom-6 right-24 z-50 font-sans">
    <!-- UAT Toggle Button (FAB) -->
    <button @click="openModal()" 
            id="uat-feedback-fab"
            class="flex items-center justify-center gap-2 px-4 h-14 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-gray-900 group">
        <svg class="w-5 h-5 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <span class="text-xs font-bold uppercase tracking-wider hidden md:inline">UAT Feedback</span>
    </button>

    <!-- UAT Modal Panel -->
    <div x-show="isOpen" 
         id="uat-feedback-modal"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-12 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-12 scale-95"
         x-cloak
         @click.away="closeModal()"
         class="absolute bottom-18 right-[-72px] md:right-0 w-[350px] md:w-[420px] max-h-[580px] flex flex-col bg-gray-900/95 dark:bg-gray-950/98 border border-white/10 rounded-3xl shadow-2xl backdrop-blur-xl overflow-hidden text-gray-200">
        
        <!-- Header -->
        <div class="p-4 bg-gradient-to-r from-emerald-950/80 to-teal-950/80 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-600/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-black tracking-wide text-white">Feedback UAT</h3>
                    <p class="text-[10px] font-semibold text-emerald-400">{{ school_short_name() }}</p>
                </div>
            </div>
            <button @click="closeModal()" class="text-gray-400 hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Form Body -->
        <div class="flex-1 overflow-y-auto p-5 space-y-4">
            <template x-if="successMessage">
                <div class="flex flex-col items-center justify-center py-10 text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-3xl animate-bounce">
                        
                    </div>
                    <h4 class="font-bold text-white text-base">Feedback Terkirim!</h4>
                    <p class="text-xs text-gray-400 max-w-[280px]" x-text="successMessage"></p>
                    <button @click="resetForm()" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all">
                        Kirim Baru
                    </button>
                </div>
            </template>

            <template x-if="!successMessage">
                <form @submit.prevent="submitFeedback()" class="space-y-4">
                    <!-- Rating Stars -->
                    <div class="space-y-1">
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-400">Kepuasan Pengguna</label>
                        <div class="flex items-center gap-2">
                            <template x-for="star in 5">
                                <button type="button" 
                                        @click="rating = star"
                                        @mouseover="hoverRating = star"
                                        @mouseleave="hoverRating = 0"
                                        class="text-2xl transition-all duration-150 focus:outline-none transform hover:scale-125"
                                        :class="(hoverRating || rating) >= star ? 'text-amber-400' : 'text-gray-600'">
                                    
                                </button>
                            </template>
                            <span class="text-xs font-bold text-gray-400 ml-2" x-text="getRatingLabel()"></span>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="space-y-1">
                        <label for="uat-category" class="block text-xs font-black uppercase tracking-wider text-gray-400">Kategori Feedback</label>
                        <select id="uat-category"
                                x-model="category"
                                required
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all">
                            <option value="" class="bg-gray-900">Pilih Kategori...</option>
                            <option value="bug" class="bg-gray-900">Bug / Galat Sistem </option>
                            <option value="ui_suggestion" class="bg-gray-900">Saran Tampilan / UI </option>
                            <option value="unclear_feature" class="bg-gray-900">Fitur Kurang Jelas </option>
                            <option value="slow" class="bg-gray-900">Lambat / Performa Halaman </option>
                            <option value="other" class="bg-gray-900">Lainnya </option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1">
                        <label for="uat-description" class="block text-xs font-black uppercase tracking-wider text-gray-400">Detail Laporan / Masukan</label>
                        <textarea id="uat-description"
                                  x-model="description"
                                  required
                                  rows="3"
                                  placeholder="Jelaskan secara detail kendala atau saran Anda..."
                                  class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all resize-none"></textarea>
                    </div>

                    <!-- Screenshot Capture Switch -->
                    <div class="space-y-3 p-3 bg-white/5 rounded-2xl border border-white/5">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-200">Tangkapan Layar Otomatis</span>
                                <span class="text-[9px] text-gray-400">Melampirkan tampilan halaman saat ini</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="attachScreenshot" @change="handleScreenshotToggle()" class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <!-- Screenshot Loading -->
                        <div x-show="isCapturing" class="flex items-center justify-center py-4 text-xs text-gray-400 gap-2">
                            <span class="w-4 h-4 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></span>
                            Mengambil screenshot halaman...
                        </div>

                        <!-- Screenshot Preview -->
                        <div x-show="screenshotPreview && !isCapturing" class="relative group mt-2 rounded-lg overflow-hidden border border-white/10 max-h-36 bg-gray-950">
                            <img :src="screenshotPreview" alt="Preview Screenshot" class="w-full h-auto object-contain max-h-36">
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all">
                                <button type="button" @click="recaptureScreenshot()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-[10px] font-bold transition-all">
                                    Capture Ulang
                                </button>
                            </div>
                        </div>

                        <!-- Backup Manual File Upload -->
                        <div x-show="!attachScreenshot" class="space-y-1">
                            <label class="block text-[10px] font-bold text-gray-400">Unggah File (Alternatif)</label>
                            <input type="file" 
                                   @change="handleFileUpload($event)"
                                   accept="image/*"
                                   class="w-full text-xs text-gray-400 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-emerald-600/20 file:text-emerald-400 hover:file:bg-emerald-600/30">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            :disabled="isSubmitting"
                            class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 disabled:from-gray-700 disabled:to-gray-800 text-white font-bold rounded-xl text-xs uppercase tracking-widest transition-all duration-300 shadow-lg shadow-emerald-500/20 active:scale-[0.98] flex items-center justify-center gap-2">
                        <template x-if="isSubmitting">
                            <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        </template>
                        <span x-text="isSubmitting ? 'Mengirim...' : 'Kirim Feedback'"></span>
                    </button>
                </form>
            </template>
        </div>
    </div>
</div>

<!-- Load html2canvas from CDN dynamically -->
<script>
    function uatFeedbackForm() {
        return {
            isOpen: false,
            rating: 4,
            hoverRating: 0,
            category: '',
            description: '',
            attachScreenshot: true,
            screenshotData: null,
            screenshotPreview: null,
            isCapturing: false,
            isSubmitting: false,
            successMessage: '',
            selectedFile: null,

            openModal() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.successMessage = '';
                    this.recaptureScreenshot();
                }
            },

            closeModal() {
                this.isOpen = false;
            },

            getRatingLabel() {
                const labels = {
                    1: 'Sangat Buruk ',
                    2: 'Buruk ',
                    3: 'Cukup ',
                    4: 'Baik ',
                    5: 'Sangat Baik '
                };
                return labels[this.rating] || '';
            },

            loadHtml2Canvas() {
                return new Promise((resolve, reject) => {
                    if (window.html2canvas) {
                        resolve(window.html2canvas);
                        return;
                    }
                    const script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                    script.async = true;
                    script.onload = () => resolve(window.html2canvas);
                    script.onerror = (err) => reject(err);
                    document.head.appendChild(script);
                });
            },

            recaptureScreenshot() {
                if (!this.attachScreenshot) return;
                this.isCapturing = true;
                
                // Hide floating widgets from screen capture to avoid recursive overlays
                const fab = document.getElementById('uat-feedback-fab');
                const modal = document.getElementById('uat-feedback-modal');
                const chatbot = document.querySelector('[x-data="chatbotWidget()"]');
                
                if (fab) fab.style.visibility = 'hidden';
                if (modal) modal.style.visibility = 'hidden';
                if (chatbot) chatbot.style.visibility = 'hidden';

                setTimeout(() => {
                    this.loadHtml2Canvas()
                        .then(html2canvas => {
                            return html2canvas(document.body, {
                                useCORS: true,
                                allowTaint: true,
                                scrollY: -window.scrollY
                            });
                        })
                        .then(canvas => {
                            const dataUrl = canvas.toDataURL('image/png');
                            this.screenshotData = dataUrl;
                            this.screenshotPreview = dataUrl;
                            this.isCapturing = false;
                            
                            // Restore visibility
                            if (fab) fab.style.visibility = 'visible';
                            if (modal) modal.style.visibility = 'visible';
                            if (chatbot) chatbot.style.visibility = 'visible';
                        })
                        .catch(err => {
                            console.error('Error capturing screen:', err);
                            this.isCapturing = false;
                            
                            // Restore visibility
                            if (fab) fab.style.visibility = 'visible';
                            if (modal) modal.style.visibility = 'visible';
                            if (chatbot) chatbot.style.visibility = 'visible';
                        });
                }, 300); // Small delay to let modal adjust
            },

            handleScreenshotToggle() {
                if (this.attachScreenshot) {
                    this.selectedFile = null;
                    this.recaptureScreenshot();
                } else {
                    this.screenshotData = null;
                    this.screenshotPreview = null;
                }
            },

            handleFileUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    this.selectedFile = file;
                    
                    // Show a local preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.screenshotPreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            getDeviceInfo() {
                const ua = navigator.userAgent;
                let browser = 'Unknown';
                if (ua.indexOf('Firefox') > -1) browser = 'Firefox';
                else if (ua.indexOf('SamsungBrowser') > -1) browser = 'Samsung Browser';
                else if (ua.indexOf('Opera') > -1 || ua.indexOf('OPR') > -1) browser = 'Opera';
                else if (ua.indexOf('Trident') > -1) browser = 'Internet Explorer';
                else if (ua.indexOf('Edge') > -1 || ua.indexOf('Edg') > -1) browser = 'Microsoft Edge';
                else if (ua.indexOf('Chrome') > -1) browser = 'Google Chrome';
                else if (ua.indexOf('Safari') > -1) browser = 'Safari';

                let os = 'Unknown';
                if (ua.indexOf('Win') > -1) os = 'Windows';
                else if (ua.indexOf('Mac') > -1) os = 'macOS';
                else if (ua.indexOf('Linux') > -1) os = 'Linux';
                else if (ua.indexOf('Android') > -1) os = 'Android';
                else if (ua.indexOf('like Mac') > -1) os = 'iOS';

                return {
                    browser: browser,
                    os: os,
                    screen_size: window.innerWidth + 'x' + window.innerHeight,
                    role: '{{ auth()->user()->role ?? "guest" }}',
                    user_agent: ua
                };
            },

            submitFeedback() {
                if (!this.category || !this.description) return;
                
                this.isSubmitting = true;
                
                const formData = new FormData();
                formData.append('rating', this.rating);
                formData.append('category', this.category);
                formData.append('description', this.description);
                formData.append('page_url', window.location.href);
                formData.append('device_info', JSON.stringify(this.getDeviceInfo()));
                
                if (this.attachScreenshot && this.screenshotData) {
                    formData.append('screenshot', this.screenshotData);
                } else if (this.selectedFile) {
                    formData.append('screenshot_file', this.selectedFile);
                }

                axios.post('{{ route("uat.feedback.store") }}', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    this.isSubmitting = false;
                    if (response.data.success) {
                        this.successMessage = response.data.message;
                        
                        // Dispatch custom event for global toast if it exists
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                type: 'success',
                                message: 'Terima kasih atas feedback Anda!'
                            }
                        }));
                    } else {
                        alert('Terjadi kesalahan saat mengirim feedback. Silakan coba kembali.');
                    }
                })
                .catch(error => {
                    this.isSubmitting = false;
                    console.error('Error submitting UAT feedback:', error);
                    alert('Terjadi kesalahan koneksi. Pastikan Anda terhubung ke internet.');
                });
            },

            resetForm() {
                this.rating = 4;
                this.category = '';
                this.description = '';
                this.attachScreenshot = true;
                this.screenshotData = null;
                this.screenshotPreview = null;
                this.selectedFile = null;
                this.successMessage = '';
                this.recaptureScreenshot();
            }
        };
    }
</script>
