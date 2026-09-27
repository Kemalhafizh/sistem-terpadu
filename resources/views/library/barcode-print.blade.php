<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Barcode Buku Perpustakaan - SISTA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&family=Libre+Barcode+128&display=swap" rel="stylesheet">
    <x-vendor.script lib="tailwind-browser" />
    <style type="text/tailwindcss">
        /* Designed on Tailwind v3: keep its default border color. */
        @layer base {
            *, ::after, ::before, ::backdrop, ::file-selector-button { border-color: var(--color-gray-200, currentColor); }
        }
    </style>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .barcode-font {
            font-family: 'Libre Barcode 128', cursive, monospace;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
            }
            .page-break {
                page-break-after: always;
            }
            .sticker-grid {
                gap: 8mm !important;
            }
        }
    </style>
</head>
<body class="p-6 sm:p-10">

    <!-- Top Action Bar (Hidden on print) -->
    <div class="no-print max-w-6xl mx-auto mb-8 bg-white p-5 rounded-3xl shadow-lg border border-gray-200 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-gray-900 tracking-tight">Lembar Cetak Stiker Barcode Buku Perpustakaan</h1>
            <p class="text-xs text-gray-500 mt-1">Format grid 3 kolom A4 siap cetak untuk pelabelan buku fisik perpustakaan.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-xs shadow-md shadow-teal-500/20 flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Stiker (Print A4)
            </button>
            <a href="{{ route('library.circulation.quick-terminal') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                Kembali ke Terminal
            </a>
        </div>
    </div>

    <!-- Printable Grid Container -->
    <div class="max-w-6xl mx-auto">
        <div class="sticker-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @forelse($books as $book)
                <div class="bg-white p-4 rounded-2xl border-2 border-gray-300 shadow-xs flex flex-col justify-between space-y-3 relative overflow-hidden">
                    <!-- Header -->
                    <div class="border-b border-gray-200 pb-2 flex items-center justify-between">
                        <div>
                            <span class="text-[8px] font-black uppercase tracking-widest text-teal-700 block">{{ mb_strtoupper(school_short_name()) }}</span>
                            <span class="text-[9px] font-bold text-gray-500 uppercase">Perpustakaan Digital SISTA</span>
                        </div>
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-[9px] font-mono font-bold">
                            {{ $book->category->name ?? 'BUKU' }}
                        </span>
                    </div>

                    <!-- Book Info -->
                    <div>
                        <h3 class="font-black text-xs text-gray-900 line-clamp-2 leading-snug">{{ $book->title }}</h3>
                        <p class="text-[10px] text-gray-500 font-medium mt-0.5 truncate">Penulis: {{ $book->author ?? 'Sekolah' }}</p>
                    </div>

                    <!-- Visual Barcode Display -->
                    <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-200 text-center space-y-1">
                        <!-- Simulated Barcode Lines -->
                        <div class="h-10 w-full flex items-center justify-center space-x-[2px] px-2 overflow-hidden">
                            @php
                                $codeStr = $book->barcode ?? $book->uuid ?? ('BK-' . str_pad($book->id, 6, '0', STR_PAD_LEFT));
                                $codeLength = strlen($codeStr);
                            @endphp
                            @for($i = 0; $i < 38; $i++)
                                @php
                                    $w = ($i % 3 == 0) ? 'w-1.5' : (($i % 2 == 0) ? 'w-1' : 'w-0.5');
                                    $h = ($i % 5 == 0) ? 'h-full bg-gray-900' : 'h-[85%] bg-gray-800';
                                @endphp
                                <div class="{{ $w }} {{ $h }} rounded-xs"></div>
                            @endfor
                        </div>
                        <span class="block font-mono font-bold text-xs tracking-widest text-gray-900">
                            {{ $book->barcode ?? $book->uuid ?? ('BK-' . str_pad($book->id, 6, '0', STR_PAD_LEFT)) }}
                        </span>
                    </div>

                    <!-- Footer Info -->
                    <div class="flex items-center justify-between text-[8px] text-gray-400 font-semibold pt-1 border-t border-gray-100">
                        <span>Hak Cipta Perpustakaan</span>
                        <span class="font-mono">ID: {{ $book->id }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-12 text-center text-gray-500 font-medium italic">
                    Belum ada data buku fisik yang memiliki barcode.
                </div>
            @endforelse
        </div>
    </div>

</body>
</html>
