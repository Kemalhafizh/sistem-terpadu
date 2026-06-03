<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight tracking-tight">
                {{ __('Workspace Humas: Manajemen Portal Karir') }}
            </h2>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <a href="{{ route('career-portal.index') }}" class="px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl text-xs font-bold transition-all flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Lihat Portal Publik</span>
                </a>
                <a href="{{ route('career-portal.create') }}" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black shadow-lg shadow-primary-900/10 transition-all flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Lowongan / Beasiswa</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="py-8 max-w-7xl mx-auto space-y-8">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center space-x-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Management Table Card -->
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 text-xs font-black uppercase tracking-widest text-gray-400">
                            <th class="pb-3 w-1/3">Informasi Kesempatan</th>
                            <th class="pb-3 w-1/6">Penyelenggara / Kontak</th>
                            <th class="pb-3 text-center w-1/12">Status</th>
                            <th class="pb-3 text-center w-1/8">Batas Akhir</th>
                            <th class="pb-3 text-center w-1/12">Brosur PDF</th>
                            <th class="pb-3 text-center w-1/6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50 text-sm">
                        @forelse($opportunities as $opt)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-all">
                                <!-- Title -->
                                <td class="py-4">
                                    <div class="font-bold text-gray-900 dark:text-gray-100 truncate max-w-sm" title="{{ $opt->title }}">{{ $opt->title }}</div>
                                    <div class="flex items-center space-x-2 mt-1">
                                        @if($opt->type === 'job')
                                            <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400 rounded text-[9px] font-black uppercase tracking-wider">Job</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 rounded text-[9px] font-black uppercase tracking-wider">Scholarship</span>
                                        @endif
                                        <span class="text-[10px] text-gray-400 font-semibold">Oleh: {{ $opt->creator?->name }}</span>
                                    </div>
                                </td>
                                <!-- Instansi / Perusahaan -->
                                <td class="py-4 font-semibold">
                                    <div class="text-gray-800 dark:text-gray-200">{{ $opt->company_name }}</div>
                                    <div class="text-xs text-gray-400 font-bold mt-1">{{ $opt->contact_email }}</div>
                                </td>
                                <!-- Status -->
                                <td class="py-4 text-center">
                                    @if($opt->closing_date->isPast())
                                        <span class="px-2 py-1 bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 rounded text-[10px] font-black uppercase tracking-widest">Selesai</span>
                                    @else
                                        <span class="px-2 py-1 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 rounded text-[10px] font-black uppercase tracking-widest animate-pulse">Aktif</span>
                                    @endif
                                </td>
                                <!-- Batas Akhir -->
                                <td class="py-4 text-center font-bold text-gray-600 dark:text-gray-400">
                                    {{ $opt->closing_date->format('d M Y') }}
                                </td>
                                <!-- Brosur -->
                                <td class="py-4 text-center">
                                    @if($opt->file_path)
                                        <a href="{{ route('career-portal.download', $opt) }}" class="inline-flex items-center justify-center w-8 h-8 bg-primary-50 dark:bg-primary-950/20 text-primary-600 dark:text-primary-400 hover:bg-primary-100 rounded-xl transition-all" title="Unduh Brosur">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-300 dark:text-gray-600 italic">Tidak ada</span>
                                    @endif
                                </td>
                                <!-- Aksi CRUD -->
                                <td class="py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Edit -->
                                        <a href="{{ route('career-portal.edit', $opt) }}" class="p-2 bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-xl transition-all flex items-center justify-center" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 00-2 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <!-- Delete -->
                                        <form action="{{ route('career-portal.destroy', $opt) }}" method="POST" id="delete-form-{{ $opt->uuid }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('{{ $opt->uuid }}')" class="p-2 bg-red-50 dark:bg-red-950/20 hover:bg-red-100 text-red-600 rounded-xl transition-all flex items-center justify-center" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400 dark:text-gray-500 italic">Belum ada kesempatan karir atau beasiswa yang terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $opportunities->links() }}
            </div>
        </div>
    </div>

    <!-- Confirm delete SweetAlert2 script -->
    <script>
        function confirmDelete(uuid) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data lowongan / beasiswa ini akan dihapus secara permanen dari portal!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus Sekarang!',
                cancelButtonText: 'Batal',
                background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#000000'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + uuid).submit();
                }
            })
        }
    </script>
</x-app-layout>
