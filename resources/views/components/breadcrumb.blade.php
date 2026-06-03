{{-- Breadcrumb Navigation Component --}}
{{-- Usage: <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'Finance']]" /> --}}
@props(['items' => []])

@if(count($items) > 0)
<nav aria-label="Breadcrumb" class="flex items-center text-sm">
    <ol class="flex items-center space-x-1 flex-wrap">
        {{-- Home icon --}}
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}"
               class="text-gray-400 hover:text-primary-600 dark:text-gray-500 dark:hover:text-primary-400 transition-colors"
               aria-label="Dashboard">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </a>
        </li>

        @foreach($items as $index => $item)
            {{-- Separator --}}
            <li class="flex items-center" aria-hidden="true">
                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </li>

            {{-- Breadcrumb item --}}
            <li class="flex items-center">
                @if(isset($item['url']) && !$loop->last)
                    <a href="{{ $item['url'] }}"
                       class="font-medium text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors truncate max-w-[200px]">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="font-semibold text-gray-800 dark:text-gray-200 truncate max-w-[250px]"
                          aria-current="page">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
