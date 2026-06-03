{{-- Toast Notification System --}}
{{-- Auto-detects session flash messages and displays animated toast notifications --}}
<div
    x-data="toastNotification()"
    x-on:toast.window="addToast($event.detail)"
    data-success="{{ session('success') }}"
    data-error="{{ session('error') }}"
    data-warning="{{ session('warning') }}"
    data-info="{{ session('info') }}"
    data-status="{{ session('status') }}"
    class="fixed top-4 right-4 z-[200] flex flex-col items-end space-y-3 pointer-events-none"
    role="alert"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-8 scale-95"
            class="pointer-events-auto max-w-sm w-full shadow-2xl rounded-2xl overflow-hidden border backdrop-blur-xl"
            :class="{
                'bg-emerald-50/95 border-emerald-200 dark:bg-emerald-950/95 dark:border-emerald-800': toast.type === 'success',
                'bg-red-50/95 border-red-200 dark:bg-red-950/95 dark:border-red-800': toast.type === 'error',
                'bg-amber-50/95 border-amber-200 dark:bg-amber-950/95 dark:border-amber-800': toast.type === 'warning',
                'bg-blue-50/95 border-blue-200 dark:bg-blue-950/95 dark:border-blue-800': toast.type === 'info',
            }"
        >
            <div class="p-4 flex items-start gap-3">
                {{-- Icon --}}
                <div class="shrink-0 mt-0.5">
                    {{-- Success Icon --}}
                    <template x-if="toast.type === 'success'">
                        <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </template>
                    {{-- Error Icon --}}
                    <template x-if="toast.type === 'error'">
                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </template>
                    {{-- Warning Icon --}}
                    <template x-if="toast.type === 'warning'">
                        <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </template>
                    {{-- Info Icon --}}
                    <template x-if="toast.type === 'info'">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </template>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold"
                       :class="{
                            'text-emerald-800 dark:text-emerald-200': toast.type === 'success',
                            'text-red-800 dark:text-red-200': toast.type === 'error',
                            'text-amber-800 dark:text-amber-200': toast.type === 'warning',
                            'text-blue-800 dark:text-blue-200': toast.type === 'info',
                       }"
                       x-text="toast.message">
                    </p>
                </div>

                {{-- Close Button --}}
                <button @click="removeToast(toast.id)"
                        class="shrink-0 p-1 rounded-lg opacity-50 hover:opacity-100 transition-opacity"
                        :class="{
                            'text-emerald-600 hover:bg-emerald-100 dark:text-emerald-400 dark:hover:bg-emerald-900': toast.type === 'success',
                            'text-red-600 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-900': toast.type === 'error',
                            'text-amber-600 hover:bg-amber-100 dark:text-amber-400 dark:hover:bg-amber-900': toast.type === 'warning',
                            'text-blue-600 hover:bg-blue-100 dark:text-blue-400 dark:hover:bg-blue-900': toast.type === 'info',
                        }"
                        aria-label="Tutup notifikasi">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Progress bar --}}
            <div class="h-1 w-full"
                 :class="{
                    'bg-emerald-100 dark:bg-emerald-900': toast.type === 'success',
                    'bg-red-100 dark:bg-red-900': toast.type === 'error',
                    'bg-amber-100 dark:bg-amber-900': toast.type === 'warning',
                    'bg-blue-100 dark:bg-blue-900': toast.type === 'info',
                 }">
                <div class="h-full transition-all duration-100 ease-linear rounded-full"
                     :class="{
                        'bg-emerald-500': toast.type === 'success',
                        'bg-red-500': toast.type === 'error',
                        'bg-amber-500': toast.type === 'warning',
                        'bg-blue-500': toast.type === 'info',
                     }"
                     :style="'width: ' + toast.progress + '%'">
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    function toastNotification() {
        return {
            toasts: [],
            nextId: 0,

            init() {
                // Auto-detect Laravel session flash messages via data attributes
                const success = this.$el.getAttribute('data-success');
                const error = this.$el.getAttribute('data-error');
                const warning = this.$el.getAttribute('data-warning');
                const info = this.$el.getAttribute('data-info');
                const status = this.$el.getAttribute('data-status');

                if (success) {
                    this.addToast({ type: 'success', message: success });
                }
                if (error) {
                    this.addToast({ type: 'error', message: error });
                }
                if (warning) {
                    this.addToast({ type: 'warning', message: warning });
                }
                if (info) {
                    this.addToast({ type: 'info', message: info });
                }
                if (status) {
                    this.addToast({ type: 'success', message: status });
                }
            },

            addToast({ type = 'info', message = '', duration = 5000 }) {
                const id = this.nextId++;
                const toast = {
                    id,
                    type,
                    message,
                    visible: true,
                    progress: 100,
                    duration,
                };

                this.toasts.push(toast);

                // Progress bar countdown
                const startTime = Date.now();
                const interval = setInterval(() => {
                    const elapsed = Date.now() - startTime;
                    toast.progress = Math.max(0, 100 - (elapsed / duration) * 100);
                    if (toast.progress <= 0) {
                        clearInterval(interval);
                        this.removeToast(id);
                    }
                }, 50);
            },

            removeToast(id) {
                const toast = this.toasts.find(t => t.id === id);
                if (toast) {
                    toast.visible = false;
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 300);
                }
            }
        }
    }
</script>
