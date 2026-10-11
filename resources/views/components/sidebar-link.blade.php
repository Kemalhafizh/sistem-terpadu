{{--
    One sidebar entry, shown only to users the route itself admits (RouteAccess,
    the same rule as @canVisit). `active` takes the route-name patterns that
    highlight it; `href` overrides the link when the entry picks its page;
    `show` adds a check the controller makes itself (a policy), so the menu
    never offers a page that answers 403.
--}}
@props(['route', 'label', 'icon', 'active' => null, 'href' => null, 'show' => true])
@if($show && \App\Support\Access\RouteAccess::allowsRoute(auth()->user(), $route))
@php($isActive = request()->routeIs(...(array) ($active ?? $route)))
<a href="{{ $href ?? route($route) }}" class="block px-3 py-2.5 rounded-lg font-medium transition-colors relative group sidebar-item {{ $isActive ? 'bg-primary-800 text-white shadow-sm border border-primary-700/50' : 'text-primary-100 hover:bg-primary-800/60 hover:text-white' }}" title="{{ $label }}">
    <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
        <svg class="w-5 h-5 shrink-0" :class="!sidebarCollapsed ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path></svg>
        <span x-show="!sidebarCollapsed" class="whitespace-nowrap">{{ $label }}</span>
    </div>
    <div x-show="sidebarCollapsed" class="absolute left-14 top-1/2 -translate-y-1/2 ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 whitespace-nowrap transition-opacity duration-200" style="display:none;">{{ $label }}</div>
</a>
@endif
