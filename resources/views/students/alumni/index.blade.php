<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Database Alumni & Tracer Study') }}
            </h2>
            <a href="{{ route('alumni.create') }}" class="px-6 py-2 bg-emerald-600 text-white rounded-xl font-black text-xs shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all">+ Daftarkan Alumni Baru</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Alumni</p>
                    <h3 class="text-2xl font-black text-gray-900">{{ number_format($alumni->total()) }}</h3>
                </div>
                <div class="bg-blue-600 p-6 rounded-[2rem] shadow-lg text-white">
                    <p class="text-[10px] font-black text-blue-100 uppercase tracking-widest mb-1">Lanjut Kuliah</p>
                    <h3 class="text-2xl font-black">{{ $alumni->where('current_activity', 'study')->count() }}</h3>
                </div>
                <div class="bg-amber-500 p-6 rounded-[2rem] shadow-lg text-white">
                    <p class="text-[10px] font-black text-amber-100 uppercase tracking-widest mb-1">Bekerja/Wirausaha</p>
                    <h3 class="text-2xl font-black">{{ $alumni->whereIn('current_activity', ['work', 'entrepreneur'])->count() }}</h3>
                </div>
                <div class="bg-gray-900 p-6 rounded-[2rem] shadow-sm text-white">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Update Terkini</p>
                    <h3 class="text-2xl font-black text-primary-400">{{ date('Y') }}</h3>
                </div>
            </div>

            <!-- Alumni Table -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center">
                    <h4 class="text-lg font-black text-gray-800">Daftar Lulusan</h4>
                    <form action="{{ route('alumni.index') }}" method="GET">
                        <select name="year" onchange="this.form.submit()" class="rounded-xl border-gray-100 text-xs font-bold focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Semua Tahun Lulus</option>
                            @for($y = date('Y'); $y >= 2010; $y--)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                            @endfor
                        </select>
                    </form>
                </div>
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Alumni</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tahun Lulus</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Aktivitas Terkini</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Institusi / Kontak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($alumni as $alm)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="font-bold text-gray-900">{{ $alm?->student?->user?->name }}</div>
                                    <div class="text-[10px] font-bold text-gray-400">NISN: {{ $alm?->student?->nisn }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-black">{{ $alm?->graduation_year }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    @php
                                        $badges = [
                                            'study' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'label' => 'Kuliah'],
                                            'work' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'label' => 'Bekerja'],
                                            'entrepreneur' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'label' => 'Wirausaha'],
                                            'unemployed' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'label' => 'Mencari Kerja'],
                                            'other' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'label' => 'Lainnya'],
                                        ];
                                        $badge = $badges[$alm?->current_activity] ?? $badges['other'];
                                    @endphp
                                    <span class="px-3 py-1 {{ $badge['bg'] }} {{ $badge['text'] }} rounded-full text-[10px] font-black uppercase tracking-tighter">{{ $badge['label'] }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-xs font-bold text-gray-800">{{ $alm?->institution_name ?? '-' }}</div>
                                    <div class="text-[10px] text-gray-400 font-medium">{{ $alm?->contact_info ?? 'No Contact' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center text-gray-400 italic text-sm">Belum ada data alumni yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($alumni->hasPages())
                    <div class="p-6 border-t border-gray-50">
                        {{ $alumni->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
