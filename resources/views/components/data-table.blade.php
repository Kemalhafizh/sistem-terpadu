{{-- Premium DataTable Wrapper Component --}}
@props([
    'headers' => [],
    'items' => null, // Paginated items collection for pagination links and empty state checks
    'emptyIcon' => 'box',
    'emptyMessage' => 'Belum ada data yang tersedia.',
    'emptySubMessage' => 'Coba sesuaikan filter Anda atau tambahkan item baru.',
    'emptyActionUrl' => null,
    'emptyActionText' => null,
])

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xs border border-gray-200 dark:border-slate-700/80 overflow-hidden">
    {{-- Header Actions Slot (e.g. Filters, Search) --}}
    @if(isset($search) || isset($actions))
        <div class="p-5 border-b border-gray-100 dark:border-slate-700 flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gray-50/20 dark:bg-slate-900/10">
            @if(isset($search))
                <div class="w-full md:w-auto">
                    {{ $search }}
                </div>
            @endif
            @if(isset($actions))
                <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto md:justify-end">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    {{-- Responsive Table --}}
    <div class="overflow-x-auto">
        @if(($items && $items->isNotEmpty()) || (!$items && strlen(trim($slot)) > 0))
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/75 dark:bg-slate-900/50 border-b border-gray-200 dark:border-slate-700">
                        @foreach($headers as $header)
                            @php
                                $alignClass = isset($header['align']) && $header['align'] === 'right' ? 'text-right' : (isset($header['align']) && $header['align'] === 'center' ? 'text-center' : 'text-left');
                                $widthClass = isset($header['width']) ? $header['width'] : '';
                            @endphp
                            <th scope="col" class="py-4 px-6 font-semibold uppercase text-[11px] text-gray-500 dark:text-gray-400 tracking-wider {{ $alignClass }} {{ $widthClass }}">
                                {{ $header['label'] ?? $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 dark:divide-slate-700 bg-white dark:bg-slate-800">
                    {{ $slot }}
                </tbody>
            </table>
        @else
            {{-- Empty State integrated! --}}
            <x-empty-state 
                :icon="$emptyIcon"
                :message="$emptyMessage"
                :submessage="$emptySubMessage"
                :actionUrl="$emptyActionUrl"
                :actionText="$emptyActionText"
            />
        @endif
    </div>

    {{-- Pagination Footer --}}
    @if($items && method_exists($items, 'links') && $items->hasPages())
        <div class="p-5 border-t border-gray-150 dark:border-slate-700 bg-gray-50/10 dark:bg-slate-900/5">
            {{ $items->links() }}
        </div>
    @endif
</div>
