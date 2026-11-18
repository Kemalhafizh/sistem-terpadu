{{-- Premium Reusable Confirmation Modal Component --}}
@props([
    'name', // Unique modal name used for Alpine trigger
    'title' => 'Konfirmasi Tindakan',
    'type' => 'danger', // danger, success, warning, info
    'action' => '', // Form action route/url
    'method' => 'POST', // POST, DELETE, PUT, PATCH
    'submitText' => 'Ya, Lanjutkan',
    'cancelText' => 'Batal',
])

<x-modal :name="$name" maxWidth="md" focusable>
    <form method="POST" action="{{ $action }}" class="p-6">
        @csrf
        @if(in_array(strtoupper($method), ['DELETE', 'PUT', 'PATCH']))
            @method($method)
        @endif

        {{-- Icon & Content --}}
        <div class="flex items-start gap-4">
            {{-- Modal Type Icon --}}
            <div class="shrink-0">
                @if($type === 'danger')
                    <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center border border-red-100 dark:border-red-900/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                @elseif($type === 'success')
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-emerald-900/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                @elseif($type === 'warning')
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 flex items-center justify-center border border-amber-100 dark:border-amber-900/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                @else {{-- info --}}
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-100 dark:border-blue-900/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Text Content --}}
            <div class="grow">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">
                    {{ $title }}
                </h2>
                <div class="mt-2 text-sm text-gray-700 dark:text-slate-300 leading-relaxed">
                    {{ $slot }}
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 dark:border-slate-700/60 pt-4">
            <button type="button" 
                    x-on:click="$dispatch('close-modal', '{{ $name }}')" 
                    class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none transition-colors">
                {{ $cancelText }}
            </button>
            <button type="submit" 
                    class="px-4 py-2 rounded-lg text-sm font-bold text-white shadow-xs transition-all uppercase tracking-wider
                        {{ $type === 'danger' ? 'bg-red-700 hover:bg-red-700 focus:ring-red-500' : '' }}
                        {{ $type === 'success' ? 'bg-emerald-700 hover:bg-emerald-700 focus:ring-emerald-500' : '' }}
                        {{ $type === 'warning' ? 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-500 text-gray-950' : '' }}
                        {{ $type === 'info' ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' : '' }}">
                {{ $submitText }}
            </button>
        </div>
    </form>
</x-modal>
