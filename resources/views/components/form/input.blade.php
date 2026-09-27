@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null])
@php($id = $attributes->get('id', str_replace(['[', ']', '.'], '_', $name)))
<div>
    <label for="{{ $id }}" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">{{ $label }}</label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old(str_replace(['[', ']'], ['.', ''], $name), $value) }}"
        {{ $attributes->except('id')->class(['w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm focus:border-indigo-500 focus:ring-indigo-500']) }}>
    @if ($hint)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif
    <x-input-error :messages="$errors->get(str_replace(['[', ']'], ['.', ''], $name))" class="mt-1" />
</div>
