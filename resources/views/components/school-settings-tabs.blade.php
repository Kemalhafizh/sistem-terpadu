{{-- School identity and appearance share one menu with two tabs. --}}
<nav class="flex gap-2 border-b border-gray-200 dark:border-gray-700 mb-6" aria-label="{{ __('navigation.school_settings') }}">
    @foreach (['admin.school.branding.index' => __('navigation.school_identity'), 'admin.appearance.index' => __('navigation.school_appearance')] as $tabRoute => $tabLabel)
        <a href="{{ route($tabRoute) }}"
           class="px-4 py-2 text-sm font-semibold border-b-2 -mb-px {{ request()->routeIs($tabRoute) ? 'border-primary-600 text-primary-700 dark:text-primary-300' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}"
           @if(request()->routeIs($tabRoute)) aria-current="page" @endif>{{ $tabLabel }}</a>
    @endforeach
</nav>
