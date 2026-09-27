@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'hint' => null])
@php($id = $attributes->get('id', str_replace(['[', ']', '.'], '_', $name)))
@php($selected = (string) old(str_replace(['[', ']'], ['.', ''], $name), $value))
<div>
    <label for="{{ $id }}" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $name }}"
        {{ $attributes->except('id')->class(['w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm focus:border-indigo-500 focus:ring-indigo-500']) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($hint)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif
    <x-input-error :messages="$errors->get(str_replace(['[', ']'], ['.', ''], $name))" class="mt-1" />
</div>
