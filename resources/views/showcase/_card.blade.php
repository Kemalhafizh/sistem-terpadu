{{-- One showcase entry. Expects $showcase with endorsements_count (and student.student.classroom unless $hideAuthor). --}}
@php($hideAuthor = $hideAuthor ?? false)
<a href="{{ route('showcase.show', $showcase) }}" class="group block bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm overflow-hidden hover:shadow-md transition">
    @if($showcase->cover_url)
        <img src="{{ $showcase->cover_url }}" alt="Sampul {{ $showcase->project_title }}" class="w-full h-40 object-cover" loading="lazy">
    @else
        <div class="w-full h-40 bg-gradient-to-br from-amber-100 to-emerald-100 dark:from-amber-900/30 dark:to-emerald-900/30 flex items-center justify-center text-amber-700 dark:text-amber-300 text-xs font-bold">Tanpa gambar sampul</div>
    @endif
    <div class="p-5 space-y-2">
        <div class="flex items-start justify-between gap-2">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 group-hover:text-amber-700 dark:group-hover:text-amber-300">{{ $showcase->project_title }}</h2>
            @unless($showcase->is_public)
                <span class="shrink-0 text-[10px] font-bold uppercase bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded">Privat</span>
            @endunless
        </div>
        @unless($hideAuthor)
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Oleh: {{ $showcase->student?->name ?? 'Siswa' }}@if($showcase->student?->student?->classroom) &bull; {{ $showcase->student->student->classroom->name }}@endif
            </p>
        @endunless
        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $showcase->description }}</p>
        <p class="text-xs text-gray-400">
            {{ $showcase->endorsements_count }} dukungan &bull; {{ $showcase->view_count }} kali dilihat &bull; {{ $showcase->created_at->translatedFormat('d M Y') }}
        </p>
    </div>
</a>
