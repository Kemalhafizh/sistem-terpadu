<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Mutaba\'ah Yaumiyah') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Jurnal Ibadah Harian: {{ $student?->user?->name }}</p>
            </div>
            <div class="flex items-center space-x-3 bg-white p-2 rounded-2xl border border-gray-100 shadow-sm">
                <form action="{{ route('mutabaah.index') }}" method="GET" class="flex items-center">
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="border-none focus:ring-0 text-sm font-black text-primary-600 bg-transparent rounded-lg">
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto space-y-6">
            
            {{-- Status Banner --}}
            @php
                $verifiedLog = $logs->where('verified_by', '!=', null)->first();
            @endphp

            @if($verifiedLog)
                <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-[1.5rem] flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="bg-emerald-500 text-white p-2 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-black text-emerald-900 tracking-tight">AMALAN TERVERIFIKASI</p>
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Diverifikasi oleh Orang Tua/Guru</p>
                        </div>
                    </div>
                </div>
            @elseif(auth()->user()->role === 'parent' || auth()->user()->role === 'admin')
                <div class="bg-primary-50 border border-primary-100 p-4 rounded-[1.5rem] flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="bg-primary-600 text-white p-2 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-black text-primary-900 tracking-tight">KONFIRMASI AMALAN</p>
                            <p class="text-[10px] font-bold text-primary-600 uppercase tracking-widest">Sudah mengecek amalan anak hari ini?</p>
                        </div>
                    </div>
                    <form action="{{ route('mutabaah.verify', $student->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-primary-900/20 hover:scale-[1.05] transition-all">VERIFIKASI SEKARANG</button>
                    </form>
                </div>
            @endif

            {{-- Categories --}}
            @foreach($items->groupBy('category') as $category => $categoryItems)
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-8 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-xs font-black text-gray-500 uppercase tracking-[0.3em]">{{ $category }}</h3>
                        <span class="text-[10px] font-bold text-gray-400">{{ $categoryItems->count() }} Amalan</span>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach($categoryItems ?? [] as $item)
                            @php
                                $log = $logs->get($item->id);
                                $isCompleted = $log ? $log->is_completed : false;
                            @endphp
                            <div class="flex items-center justify-between px-8 py-5 hover:bg-gray-50/50 transition-all group">
                                <span class="text-sm font-bold text-gray-700 group-hover:text-primary-900 transition-colors">{{ $item->name }}</span>
                                
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           class="sr-only peer" 
                                           {{ $isCompleted ? 'checked' : '' }}
                                           onchange="toggleMutabaah({{ $student->id }}, {{ $item->id }}, '{{ $date }}', this.checked)"
                                           {{ auth()->user()->role !== 'student' ? 'disabled' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>
    </div>

    @push('scripts')
    <script>
        function toggleMutabaah(studentId, itemId, date, status) {
            fetch('{{ route("mutabaah.toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    student_id: studentId,
                    item_id: itemId,
                    date: date,
                    status: status ? 1 : 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Progress saved');
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
