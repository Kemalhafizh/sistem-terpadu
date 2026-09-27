{{-- Sub navigation shared by the strategic planning pages. --}}
@php($links = [
    'strategic.dashboard' => 'Dashboard',
    'strategic.plan.index' => 'RENSTRA',
    'strategic.workplan.index' => 'RKS',
    'strategic.survey.index' => 'Survei Kepuasan',
    'strategic.swot.index' => 'Analisis SWOT',
])
<nav class="flex flex-wrap gap-2">
    @foreach ($links as $route => $label)
        @php($active = request()->routeIs(str_replace('.index', '.*', $route)))
        <a href="{{ route($route) }}" @class([
            'px-4 py-2 rounded-xl text-xs font-bold transition-colors',
            'bg-indigo-600 text-white' => $active,
            'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' => ! $active,
        ])>{{ $label }}</a>
    @endforeach
</nav>
