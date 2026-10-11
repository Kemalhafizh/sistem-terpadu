@props(['label'])
<div class="pt-4 pb-2" x-show="!sidebarCollapsed">
    <p class="px-3 text-xs font-semibold text-primary-300 uppercase tracking-wider whitespace-nowrap">{{ $label }}</p>
</div>
<div class="pt-4 pb-2 text-center" x-show="sidebarCollapsed" style="display:none;">
    <p class="text-[10px] font-bold text-primary-300 uppercase tracking-widest" title="{{ $label }}">•</p>
</div>
