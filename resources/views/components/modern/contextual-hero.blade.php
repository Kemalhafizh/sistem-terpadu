@props([
    'title' => 'Ahlan wa Sahlan',
    'subtitle' => 'Selamat datang di Ekosistem Terpadu ' . school_short_name(),
    'gradientFrom' => '#0D5C3A',
    'gradientTo' => '#063823',
    'icon' => null,
])

<div class="relative overflow-hidden rounded-3xl p-6 sm:p-8 text-white shadow-xl"
     style="background: linear-gradient(135deg, {{ $gradientFrom }}, {{ $gradientTo }});">
    <div class="relative z-10 max-w-2xl">
        <div class="flex items-center space-x-2 text-2xl sm:text-3xl font-extrabold tracking-tight">
            <h2>{{ $title }}</h2>
        </div>
        <p class="mt-2 text-sm sm:text-base text-emerald-100/90 leading-relaxed">
            {{ $subtitle }}
        </p>
        <div class="mt-4 flex flex-wrap gap-3">
            {{ $slot }}
        </div>
    </div>
    <!-- Decorative Islamic Geometric Glow -->
    <div class="absolute -right-10 -bottom-10 h-64 w-64 rounded-full bg-emerald-400/20 blur-3xl pointer-events-none"></div>
</div>
