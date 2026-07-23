<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Rekomendasi Buku Cerdas') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('library.index') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl font-bold text-xs hover:bg-gray-50 transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Katalog
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Welcome Recommendation Box (Glassmorphic) -->
            <div class="p-8 rounded-[2.5rem] bg-gradient-to-br from-emerald-600 via-teal-600 to-emerald-700 text-white relative overflow-hidden shadow-lg shadow-emerald-100 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="space-y-3 z-10">
                    <h3 class="text-xl font-black uppercase tracking-wider">Saran Buku Pintar khusus untukmu, {{ explode(' ', $student->user->name)[0] }}!</h3>
                    <p class="text-xs text-emerald-50 max-w-2xl font-bold leading-relaxed">
                        Sistem rekomendasi perpustakaan menganalisis kategori buku yang paling sering Anda pinjam sebelumnya, lalu menyajikan buku-buku relevan yang belum pernah Anda baca untuk memperluas cakrawala pengetahuan Anda.
                    </p>
                </div>
                <div class="z-10">
                    <span class="px-5 py-3 bg-white/20 backdrop-blur rounded-2xl font-black text-xs uppercase tracking-widest border border-white/10 select-none">
                        Smart Engine v1.0
                    </span>
                </div>
                <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
            </div>

            <!-- Recommendation Grid -->
            <div>
                <h3 class="text-base font-black text-gray-800 uppercase tracking-wider mb-6">Rekomendasi Buku Pilihan Hari Ini</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    @forelse($recommendedBooks as $book)
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
                                <h4 class="text-xs font-black text-gray-900 line-clamp-2 h-8 mb-1 uppercase">{{ $book?->title }}</h4>
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
                            <h3 class="text-lg font-black text-gray-800">Belum Ada Rekomendasi</h3>
                            <p class="text-sm text-gray-400">Silakan pinjam buku terlebih dahulu agar mesin kecerdasan kami mempelajari ketertarikan Anda.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
