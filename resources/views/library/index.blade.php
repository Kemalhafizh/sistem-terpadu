<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Smart Library & Resources') }}
            </h2>
            <div class="flex items-center gap-2">
                @if(auth()->user()->hasRole('student') && auth()->user()->student)
                    <a href="{{ route('library.student-card', auth()->user()->student->id) }}" target="_blank" class="px-4 py-2 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-xl font-bold text-xs hover:bg-emerald-600 hover:text-white transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.333 0 4 1 4 2v1H8v-1c0-1-2.667-2-4-2z"></path></svg>
                        Kartu Anggota
                    </a>
                @endif
                @if(auth()->user()->isAdmin() || auth()->user()->hasRole('kepala_sekolah') || auth()->user()->hasRole('staf_tu') || auth()->user()->hasRole('guru'))
                    <a href="{{ route('library.circulation.index') }}" class="px-4 py-2 bg-blue-50 text-blue-600 border border-blue-100 rounded-xl font-bold text-xs hover:bg-blue-600 hover:text-white transition-all mr-1">Sirkulasi</a>
                    <a href="{{ route('library.manage') }}" class="px-4 py-2 bg-gray-900 text-white rounded-xl font-bold text-xs hover:bg-black transition-all">Manajemen Perpus</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Search & Filter Bar -->
            <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100">
                <form action="{{ route('library.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[200px] relative">
                        <input type="text" name="search" value="{{ request('search') }}" class="w-full pl-12 pr-4 py-3 rounded-2xl border-gray-50 bg-gray-50 text-sm focus:bg-white transition-all" placeholder="Cari judul buku atau materi...">
                        <svg class="w-5 h-5 absolute left-4 top-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <select name="category" class="rounded-2xl border-gray-50 bg-gray-50 text-sm py-3 px-6 focus:bg-white">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat?->id }}" {{ request('category') == $cat?->id ? 'selected' : '' }}>{{ $cat?->name }}</option>
                        @endforeach
                    </select>
                    <select name="subject" class="rounded-2xl border-gray-50 bg-gray-50 text-sm py-3 px-6 focus:bg-white">
                        <option value="">Semua Mapel</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub?->id }}" {{ request('subject') == $sub?->id ? 'selected' : '' }}>{{ $sub?->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-8 py-3 bg-primary-600 text-white rounded-2xl font-black text-sm hover:bg-primary-700 shadow-lg shadow-primary-200 transition-all">TEMUKAN</button>
                </form>
            </div>

            <!-- Library Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @forelse($books as $book)
                    <div class="group relative bg-white rounded-[2rem] overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                        <!-- Book Cover -->
                        <div class="aspect-[3/4] relative overflow-hidden bg-gray-100">
                            @if($book?->cover_path)
                                <img src="{{ Storage::url($book?->cover_path) }}" alt="{{ $book?->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center p-4 text-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">{{ $book?->title }}</span>
                                </div>
                            @endif
                            
                            <!-- Badges -->
                            <div class="absolute top-3 left-3 flex flex-col gap-1">
                                <span class="px-2 py-1 bg-white/90 backdrop-blur text-[8px] font-black uppercase rounded-lg shadow-sm">{{ $book?->category?->name }}</span>
                                @if($book?->type == 'digital')
                                    <span class="px-2 py-1 bg-emerald-500/90 backdrop-blur text-white text-[8px] font-black uppercase rounded-lg shadow-sm italic">E-Book</span>
                                @else
                                    <span class="px-2 py-1 bg-blue-500/90 backdrop-blur text-white text-[8px] font-black uppercase rounded-lg shadow-sm italic">Fisik</span>
                                @endif
                            </div>
                        </div>

                        <!-- Info Content -->
                        <div class="p-4">
                            <h4 class="text-xs font-black text-gray-900 line-clamp-2 h-8 mb-1">{{ $book?->title }}</h4>
                            <p class="text-[9px] font-bold text-gray-400 uppercase mb-3 truncate">{{ $book?->author ?? 'Sekolah' }}</p>
                            
                            @if($book?->type == 'digital' && $book?->file_path)
                                <a href="{{ route('library.read', $book?->uuid) }}" class="block w-full py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black text-center hover:bg-emerald-600 hover:text-white transition-all">BACA ONLINE</a>
                            @elseif($book?->video_url)
                                <a href="{{ $book?->video_url }}" target="_blank" class="block w-full py-2 bg-red-50 text-red-600 rounded-xl text-[10px] font-black text-center hover:bg-red-600 hover:text-white transition-all">TONTON VIDEO</a>
                            @else
                                <div class="w-full py-2 bg-gray-50 text-gray-400 rounded-xl text-[10px] font-black text-center border border-gray-100">
                                    {{ $book?->is_physical_available ? 'TERSEDIA' : 'DIPINJAM' }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h3 class="text-lg font-black text-gray-800">Belum Ada Materi</h3>
                        <p class="text-sm text-gray-400">Silakan cek kembali nanti atau gunakan filter lain.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($books->hasPages())
                <div class="pt-6">
                    {{ $books->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
