<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Book Reader | {{ $libraryBook->title }}</title>
    <!-- Tailwind CSS (via CDN karena ini standalone page) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Mencegah seleksi teks global */
        body {
            -webkit-user-select: none; /* Safari */
            -ms-user-select: none; /* IE 10 and Bill Gates */
            user-select: none; /* Standard syntax */
        }
        /* Mencegah interaksi langsung dengan Iframe menggunakan invisible overlay */
        .iframe-shield {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
            z-index: 10;
        }
    </style>
</head>
<body class="h-full flex flex-col overflow-hidden text-gray-100 select-none">

    <!-- Header Panel E-Book -->
    <header class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between z-20 shadow-md">
        <div class="flex items-center gap-4">
            <a href="{{ route('library.index') }}" class="p-2 bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="font-black text-sm text-white leading-tight truncate max-w-[250px] sm:max-w-md">{{ $libraryBook->title }}</h1>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">{{ $libraryBook->author ?? 'Sekolah' }} &bull; E-Book Terproteksi</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 bg-gray-800 border border-gray-700 rounded-xl text-[10px] font-black text-emerald-500 uppercase tracking-widest">ONLINE READER ONLY</span>
        </div>
    </header>

    <!-- Main Container (PDF Viewer dengan Lapisan Keamanan) -->
    <main class="flex-1 relative bg-gray-950 flex items-center justify-center">
        <!-- Lapisan Shield Pelindung (Mencegah Klik Kanan/Drag di Iframe) -->
        <div class="iframe-shield" id="shield"></div>

        <!-- Iframe Rendering PDF dengan header embed khusus -->
        <iframe 
            src="{{ Storage::url($libraryBook->file_path) }}#toolbar=0&navpanes=0&scrollbar=1" 
            class="w-full h-full border-none z-0" 
            id="pdf-frame"
            title="{{ $libraryBook->title }}">
        </iframe>
    </main>

    <!-- Footer Copyright Status -->
    <footer class="bg-gray-900 border-t border-gray-800 px-6 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider z-20">
        &copy; {{ date('Y') }} SMA Islam Sultan Agung 1. Hak Cipta Dilindungi. Dilarang menggandakan atau menyebarluaskan dokumen ini.
    </footer>

    <!-- JavaScript Proteksi Keamanan Premium -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Mencegah Klik Kanan di seluruh halaman
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            triggerSecurityAlert('Aksi Klik Kanan diblokir demi keamanan hak cipta materi sekolah.');
        });

        // Mencegah Hotkeys Pintasan Keyboard (Ctrl+P, Ctrl+S, F12, dll.)
        document.addEventListener('keydown', function(e) {
            // Mencegah cetak (Ctrl+P / Cmd+P)
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                triggerSecurityAlert('Pencetakan dokumen dilarang demi keamanan hak cipta.');
            }
            // Mencegah simpan (Ctrl+S / Cmd+S)
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                triggerSecurityAlert('Pengunduhan dokumen dilarang demi keamanan hak cipta.');
            }
            // Mencegah salin (Ctrl+C / Cmd+C)
            if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
                e.preventDefault();
                triggerSecurityAlert('Penyalinan teks dilarang.');
            }
            // Mencegah Developer Tools (F12)
            if (e.key === 'F12') {
                e.preventDefault();
                triggerSecurityAlert('Akses Inspeksi Elemen diblokir.');
            }
            // Mencegah Inspeksi Elemen (Ctrl+Shift+I / Cmd+Opt+I)
            if ((e.ctrlKey && e.shiftKey && e.key === 'I') || (e.metaKey && e.altKey && e.key === 'i')) {
                e.preventDefault();
                triggerSecurityAlert('Akses Inspeksi Elemen diblokir.');
            }
        });

        function triggerSecurityAlert(text) {
            // Mainkan audio alarm pendek
            try {
                let context = new AudioContext();
                let osc = context.createOscillator();
                osc.type = "sawtooth";
                osc.frequency.value = 440;
                osc.connect(context.destination);
                osc.start();
                osc.stop(context.currentTime + 0.15);
            } catch(e) {}

            Swal.fire({
                title: 'Proteksi Keamanan',
                text: text,
                icon: 'warning',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#111827',
                color: '#fff',
                iconColor: '#f59e0b'
            });
        }
    </script>
</body>
</html>
