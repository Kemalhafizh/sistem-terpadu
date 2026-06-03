<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Pengumuman (Mading Digital)') }}
            </h2>
            <a href="{{ route('announcements.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 shadow-sm transition-all">
                + Buat Pengumuman Baru
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-0">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Poster/Foto</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Judul Pengumuman</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Target Audience</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Status / Expired</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($announcements ?? [] as $announcement)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($announcement->image_path)
                                            <img src="{{ Storage::url($announcement->image_path) }}" alt="Poster" class="h-12 w-12 object-cover rounded-md border border-gray-200">
                                        @else
                                            <div class="h-12 w-12 rounded-md bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            @if($announcement->priority == 'urgent')
                                                <span class="flex w-3 h-3 bg-red-500 rounded-full mr-2 animate-pulse"></span>
                                            @endif
                                            <div>
                                                <div class="text-sm font-bold text-gray-900">{{ $announcement->title }}</div>
                                                <div class="text-xs text-gray-500 truncate w-48">{{ Str::limit($announcement->content, 50) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $announcement->target_role ? ucfirst($announcement->target_role) : 'Semua Orang' }}
                                        </span>
                                        @if($announcement->targetClassroom)
                                            <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                {{ $announcement->targetClassroom->name }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($announcement->expires_at && $announcement->expires_at->isPast())
                                            <span class="text-xs font-bold text-red-500">Expired</span>
                                        @else
                                            <span class="text-xs font-bold text-emerald-500">Tayang</span>
                                            @if($announcement->expires_at)
                                                <div class="text-[10px] text-gray-400">s.d {{ $announcement->expires_at->format('d M Y') }}</div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengumuman ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">
                                        Belum ada pengumuman yang disebarkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($announcements->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $announcements->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
