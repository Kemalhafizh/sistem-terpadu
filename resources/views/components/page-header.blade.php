{{-- Premium Page Header Component --}}
@props([
    'title' => null,
    'subtitle' => null,
    'breadcrumbs' => null,
])

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-5">
    <div>
        {{-- Breadcrumbs --}}
        @if($breadcrumbs)
            <div class="mb-2">
                <x-breadcrumb :items="$breadcrumbs" />
            </div>
        @endif

        {{-- Title & Subtitle --}}
        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
            @if($title)
                {{ $title }}
            @else
                {{ $slot }}
            @endif
        </h1>
        
        @if($subtitle)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    {{-- Actions Slot --}}
    @if(isset($actions))
        <div class="flex items-center gap-3 shrink-0 self-start md:self-center">
            {{ $actions }}
        </div>
    @endif
</div>
