{{-- Premium Reusable Form Group Component --}}
@props([
    'name', // Name parameter (used to match @error and default ID)
    'label' => null, // Label text
    'id' => null, // Element ID (defaults to name)
    'helperText' => null, // Optional description below input
    'required' => false, // Adds a red asterisk marker next to label
    'class' => 'mb-5' // Wrapper margin-bottom class
])

@php
    $id = $id ?? $name;
@endphp

<div class="{{ $class }}">
    @if($label)
        <label for="{{ $id }}" class="block font-semibold text-sm text-gray-700 dark:text-slate-350 mb-1.5 flex items-center">
            {{ $label }}
            @if($required)
                <span class="text-red-500 ml-1 font-bold" title="Wajib diisi">*</span>
            @endif
        </label>
    @endif

    {{-- Input/Select/Textarea Element Slot --}}
    <div class="relative">
        {{ $slot }}
    </div>

    {{-- Helper/Description Text --}}
    @if($helperText)
        <p class="mt-1.5 text-xs text-gray-400 dark:text-slate-400 leading-relaxed font-medium">
            {{ $helperText }}
        </p>
    @endif

    {{-- Validation Error Message --}}
    @error($name)
        <p class="text-xs text-red-600 dark:text-red-400 mt-1.5 font-bold flex items-center animate-pulse">
            <svg class="w-3.5 h-3.5 mr-1 text-red-500 dark:text-red-450 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
