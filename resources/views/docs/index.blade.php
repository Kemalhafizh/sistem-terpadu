<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — SISTA Developer Portal</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <x-vendor.script lib="tailwind-browser" />
    <style type="text/tailwindcss">
        /* Designed on Tailwind v3: keep its default border color. */
        @layer base {
            *, ::after, ::before, ::backdrop, ::file-selector-button { border-color: var(--color-gray-200, currentColor); }
        }
        @custom-variant dark (&:where(.dark, .dark *));
        @theme {
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            --font-outfit: 'Outfit', sans-serif;
        }
    </style>
    <style>
        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .sidebar-glass {
            background: rgba(9, 15, 30, 0.8);
            border-right: 1px solid rgba(255, 255, 255, 0.04);
        }
        .glow-button {
            position: relative;
            transition: all 0.3s ease;
        }
        .glow-button::after {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
            border-radius: inherit;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
            filter: blur(6px);
        }
        .glow-button:hover::after {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-[#070b13] text-gray-200 font-sans min-h-screen flex flex-col selection:bg-purple-600 selection:text-white">

    <!-- Decorative Gradients -->
    <div class="fixed top-0 left-0 w-[500px] h-[500px] rounded-full bg-blue-600/5 blur-[120px] pointer-events-none z-0"></div>
    <div class="fixed bottom-0 right-0 w-[500px] h-[500px] rounded-full bg-purple-600/5 blur-[120px] pointer-events-none z-0"></div>

    <!-- Header -->
    <header class="w-full h-20 border-b border-white/5 flex items-center justify-between px-8 sticky top-0 z-50 bg-[#070b13]/85 backdrop-blur-md">
        <a href="/" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-purple-600 flex items-center justify-center font-outfit font-black text-lg text-white shadow-lg shadow-blue-500/20">S</div>
            <span class="font-outfit font-bold text-xl tracking-wide bg-gradient-to-r from-white via-gray-200 to-gray-400 bg-clip-text text-transparent">SISTA <span class="text-blue-500 font-medium text-xs tracking-widest align-super">DEV</span></span>
        </a>

        <div class="flex items-center gap-4">
            <a href="/docs" target="_blank" class="glow-button px-5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 text-sm font-bold text-white shadow-md shadow-blue-600/10 hover:shadow-blue-600/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                API reference & try it
            </a>
            <a href="/" class="px-4 py-2 rounded-xl border border-white/10 hover:bg-white/5 text-sm font-semibold text-gray-300 hover:text-white transition">
                Kembali ke Utama
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <div class="grow flex w-full relative z-10">
        
        <!-- Sidebar Navigation -->
        <aside class="w-80 sidebar-glass flex flex-col p-6 overflow-y-auto max-h-[calc(100vh-5rem)] sticky top-20 select-none">
            <div class="mb-6 px-3 py-2.5 rounded-xl bg-white/5 border border-white/5">
                <p class="text-xs text-gray-500 font-mono uppercase tracking-wider">File Aktif</p>
                <p class="text-sm font-semibold text-blue-400 font-mono truncate" title="{{ $selectedFile }}">
                     docs/{{ $selectedFile }}
                </p>
            </div>

            <nav class="space-y-8 grow">
                @foreach($navigation as $groupName => $links)
                    <div>
                        <h4 class="text-xs font-bold font-outfit uppercase tracking-widest text-gray-500 mb-3 px-3">
                            {{ $groupName }}
                        </h4>
                        <ul class="space-y-1">
                            @foreach($links as $link)
                                @php
                                    $isActive = url()->current() === $link['route'] || 
                                                ($link['label'] === 'Overview & Portal Hub' && $selectedFile === 'README.md' && url()->current() === route('developer.portal'));
                                @endphp
                                <li>
                                    <a href="{{ $link['route'] }}" 
                                       class="block px-3 py-2 rounded-xl text-sm transition-all duration-200 {{ $isActive ? 'bg-gradient-to-r from-blue-600/20 to-purple-600/10 text-white border-l-2 border-blue-500 font-semibold font-outfit' : 'text-gray-400 hover:text-gray-100 hover:bg-white/5' }}">
                                        {{ $link['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="grow p-10 max-w-5xl mx-auto overflow-x-hidden">
            <div class="glass p-12 rounded-3xl relative overflow-hidden shadow-2xl">
                <!-- Watermark Background -->
                <div class="absolute top-0 right-0 translate-x-12 -translate-y-12 w-96 h-96 rounded-full bg-blue-600/5 blur-[100px] pointer-events-none"></div>

                <!-- Breadcrumbs -->
                <nav class="flex items-center gap-2 text-xs text-gray-500 font-mono mb-8 border-b border-white/5 pb-4">
                    <span>SISTA</span>
                    <span>/</span>
                    <span>DOCS</span>
                    <span>/</span>
                    <span class="text-blue-400 uppercase">{{ str_replace('.md', '', basename($selectedFile)) }}</span>
                </nav>

                <!-- Document Body -->
                <article class="prose prose-invert max-w-none text-gray-300">
                    {!! $htmlContent !!}
                </article>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="w-full border-t border-white/5 py-6 bg-[#060a12]/80 backdrop-blur z-20">
        <div class="w-full px-8 flex flex-col md:flex-row items-center justify-between text-xs text-gray-500">
            <p>&copy; 2026 SISTA Smart School Platform. All Rights Reserved.</p>
            <p class="font-mono">Developer Portal v2.0.0</p>
        </div>
    </footer>

</body>
</html>
