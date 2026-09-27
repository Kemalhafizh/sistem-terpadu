<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Manajemen E-Library') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('library.circulation.index') }}" class="px-4 py-2 bg-blue-50 text-blue-600 rounded-xl font-bold text-xs hover:bg-blue-100 transition-all flex items-center gap-1">Sirkulasi</a>
                <a href="{{ route('library.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs hover:bg-gray-200 transition-all">Lihat Library</a>
                <a href="{{ route('library.create') }}" class="px-6 py-2 bg-primary-600 text-white rounded-xl font-black text-xs shadow-lg shadow-primary-200 hover:bg-primary-700 transition-all">+ Upload Materi Baru</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Judul & Penulis</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Kategori & Mapel</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tipe</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Unduhan</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($books as $book)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="font-bold text-gray-900 line-clamp-1">{{ $book?->title }}</div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">{{ $book?->author ?? '-' }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-xs font-bold text-gray-700">{{ $book?->category?->name }}</div>
                                    <div class="text-[10px] text-primary-600 font-bold uppercase tracking-widest">{{ $book?->subject?->name ?? 'Umum' }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    @if($book?->type == 'digital')
                                        <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase">Digital</span>
                                    @else
                                        <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase">Fisik</span>
                                    @endif
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="text-xs font-black text-gray-900">{{ number_format($book?->download_count ?? 0) }}</span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex justify-end items-center gap-1.5">
                                        @if($book->type == 'physical')
                                            <button onclick="showBookBarcode('{{ $book->uuid }}', '{{ addslashes($book->title) }}', '{{ addslashes($book->author ?? 'Sekolah') }}')" class="p-2 bg-gray-50 text-emerald-500 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-all" title="Cetak QR Label Buku">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 16h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </button>
                                        @endif
                                        <a href="{{ route('library.edit', $book->uuid) }}" class="p-2 bg-gray-50 text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 transition-all" title="Edit Buku">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('library.destroy', $book->uuid) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 bg-gray-50 text-red-400 rounded-lg hover:bg-red-50 hover:text-red-600 transition-all" title="Hapus Buku">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($books->hasPages())
                    <div class="p-6 border-t border-gray-50">
                        {{ $books->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- QRCode.js & SweetAlert2 -->
    <x-vendor.script lib="qrcodejs" />
    <x-vendor.script lib="sweetalert2" />
    <script>
        function showBookBarcode(uuid, title, author) {
            Swal.fire({
                title: 'Label QR Code Buku',
                html: `
                    <div class="flex flex-col items-center justify-center p-4 bg-white select-none">
                        <div id="swal-qrcode" class="p-2.5 border border-emerald-100 rounded-2xl shadow-sm bg-emerald-50/10"></div>
                        <div class="mt-4 text-xs font-black text-gray-900 leading-tight text-center max-w-[250px] uppercase">${title}</div>
                        <div class="text-[9px] text-gray-400 font-bold uppercase mt-1 tracking-wider">${author}</div>
                        <div class="text-[9px] text-emerald-600 font-black tracking-widest mt-2.5 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100">${uuid.substring(0, 8)}...</div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Cetak Label',
                cancelButtonText: 'Tutup'
            }).then((result) => {
                if (result.isConfirmed) {
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(`
                        <html>
                        <head>
                            <title>Cetak Label Buku - ${title}</title>
                            <style>
                                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; padding: 30px; background: white; color: black; }
                                .label-card { border: 2px dashed #10b981; padding: 25px; display: inline-block; border-radius: 20px; }
                                .title { font-weight: 900; margin-top: 15px; font-size: 13px; max-width: 220px; text-transform: uppercase; line-height: 1.3; }
                                .author { font-size: 9px; color: gray; text-transform: uppercase; margin-top: 5px; font-weight: bold; letter-spacing: 1px; }
                                .uuid { font-size: 8px; color: #10b981; font-weight: 900; letter-spacing: 2px; margin-top: 12px; font-family: monospace; }
                                #print-qrcode { display: flex; justify-content: center; }
                            </style>
                            <script src="{{ \App\Support\VendorAsset::url('qrcodejs') }}"><\/script>
                        </head>
                        <body>
                            <div class="label-card">
                                <div id="print-qrcode"></div>
                                <div class="title">${title}</div>
                                <div class="author">${author}</div>
                                <div class="uuid">${uuid}</div>
                            </div>
                            <script>
                                new QRCode(document.getElementById("print-qrcode"), {
                                    text: "${uuid}",
                                    width: 110,
                                    height: 110,
                                    colorDark: "#064e3b"
                                });
                                setTimeout(() => { window.print(); window.close(); }, 500);
                            <\/script>
                        </body>
                        </html>
                    `);
                }
            });

            // Berikan waktu render modal
            setTimeout(() => {
                new QRCode(document.getElementById("swal-qrcode"), {
                    text: uuid,
                    width: 110,
                    height: 110,
                    colorDark: "#064e3b",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            }, 100);
        }
    </script>
</x-app-layout>
