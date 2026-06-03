<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Monitoring Jejak Digital (Audit Logs)') }}
        </h2>
    </x-slot>

    <div class="py-6 space-y-6">
        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <form action="{{ route('admin.logs') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Pengguna</label>
                        <select name="user_id" class="w-full rounded-xl border-gray-100 bg-gray-50 text-xs focus:ring-primary-500">
                            <option value="">-- Semua --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Aktivitas</label>
                        <select name="event" class="w-full rounded-xl border-gray-100 bg-gray-50 text-xs focus:ring-primary-500">
                            <option value="">-- Semua --</option>
                            @foreach($events as $e)
                                <option value="{{ $e }}" {{ request('event') == $e ? 'selected' : '' }}>{{ strtoupper($e) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Objek</label>
                        <select name="type" class="w-full rounded-xl border-gray-100 bg-gray-50 text-xs focus:ring-primary-500">
                            <option value="">-- Semua --</option>
                            @foreach($types as $t)
                                <option value="{{ $t['full'] }}" {{ request('type') == $t['full'] ? 'selected' : '' }}>{{ $t['short'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-center space-x-2">
                        <div class="flex-1">
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-xl border-gray-100 bg-gray-50 text-xs focus:ring-primary-500">
                        </div>
                        <div class="flex-1">
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-xl border-gray-100 bg-gray-50 text-xs focus:ring-primary-500">
                        </div>
                        <div class="flex space-x-1 pl-2">
                            <button type="submit" class="p-2.5 bg-gray-800 text-white rounded-xl hover:bg-gray-700 transition-all shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </button>
                            @if(request()->anyFilled(['user_id', 'event', 'type', 'date_from', 'date_to']))
                                <a href="{{ route('admin.logs') }}" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-all border border-red-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-6 bg-gray-50 border-b border-gray-100">
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Log Aktivitas Sistem</h3>
                    <p class="text-xs text-gray-500 mt-1">Memantau setiap perubahan data untuk transparansi & akuntabilitas.</p>
                </div>

                <div class="overflow-x-auto animate-fade-in">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-wider">Pengguna</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-wider">Aktivitas</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-wider">Objek</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-wider">Detail Perubahan</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-500 uppercase tracking-wider">IP & Browser</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($logs ?? [] as $log)
                                @php /** @var \App\Models\ActivityLog $log */ @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-[11px] text-gray-400 font-bold">
                                        {{ $log->created_at?->format('d/m/Y H:i:s') }}
                                        <span class="block text-[9px] font-medium">{{ $log->created_at?->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-7 h-7 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-[10px] font-black mr-3">
                                                {{ $log->user ? substr($log?->user?->name, 0, 1) : 'G' }}
                                            </div>
                                            <div class="text-xs font-bold text-gray-800">{{ $log?->user?->name ?? 'Guest/System' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $eventColors = [
                                                'created' => 'bg-emerald-100 text-emerald-700',
                                                'updated' => 'bg-blue-100 text-blue-700',
                                                'deleted' => 'bg-rose-100 text-rose-700',
                                                'login'   => 'bg-purple-100 text-purple-700'
                                            ];
                                            $color = $eventColors[$log->event] ?? 'bg-gray-100 text-gray-700';
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-widest {{ $color }}">
                                            {{ $log->event }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs font-bold text-gray-700">{{ class_basename($log->auditable_type) }}</div>
                                        <div class="text-[9px] text-gray-400 font-medium tracking-tighter">ID: {{ $log->auditable_id }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-xs">
                                            @if($log->event === 'updated')
                                                <div class="space-y-1">
                                                    @php $count = 0; @endphp
                                                    @foreach($log->new_values ?? [] as $key => $value)
                                                        @if($count < 3)
                                                            <div class="text-[10px] flex items-center space-x-1 truncate">
                                                                <span class="font-black text-gray-400">{{ $key }}:</span>
                                                                <span class="text-rose-500 line-through">{{ is_array($log->old_values[$key] ?? '') ? '...' : Str::limit($log->old_values[$key] ?? '-', 10) }}</span>
                                                                <svg class="w-2 h-2 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                                <span class="text-emerald-600 font-bold">{{ is_array($value) ? '...' : Str::limit($value, 10) }}</span>
                                                            </div>
                                                        @endif
                                                        @php $count++; @endphp
                                                    @endforeach
                                                    @if(count($log->new_values ?? []) > 3)
                                                        <button onclick="showLogDetail({{ json_encode($log) }})" class="text-[9px] font-black text-primary-600 hover:underline uppercase mt-1">
                                                            + {{ count($log->new_values) - 3 }} field lainnya
                                                        </button>
                                                    @endif
                                                </div>
                                            @elseif($log->event === 'created')
                                                <button onclick="showLogDetail({{ json_encode($log) }})" class="text-[10px] text-emerald-600 font-bold hover:underline italic flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Detail data baru
                                                </button>
                                            @elseif($log->event === 'deleted')
                                                <button onclick="showLogDetail({{ json_encode($log) }})" class="text-[10px] text-rose-500 font-bold hover:underline italic flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Lihat data yang dihapus
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-[10px] text-gray-500">
                                        <div class="flex flex-col">
                                            <span class="font-bold">{{ $log->ip_address }}</span>
                                            <span class="truncate w-32 opacity-50" title="{{ $log->user_agent }}">{{ $log->user_agent }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400 italic">
                                        Belum ada rekaman aktivitas digital.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="logModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" onclick="closeLogModal()" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full animate-fade-in">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-black text-gray-800 uppercase tracking-tight" id="modal-title">Detail Aktivitas</h3>
                        <button onclick="closeLogModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div id="modalContent" class="space-y-4">
                        <!-- Content injected by JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showLogDetail(log) {
            const modal = document.getElementById('logModal');
            const content = document.getElementById('modalContent');
            
            let html = `
                <div class="grid grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase">Waktu</p>
                        <p class="text-xs font-bold text-gray-700">${log.created_at}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase">Pengguna</p>
                        <p class="text-xs font-bold text-gray-700">${log.user ? log.user.name : 'System'}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase">Aktivitas</p>
                        <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase bg-primary-100 text-primary-700">${log.event}</span>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase">Objek</p>
                        <p class="text-xs font-bold text-gray-700">${log.auditable_type.split('\\').pop()} (ID: ${log.auditable_id})</p>
                    </div>
                </div>
                
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Perubahan Data</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="py-2 font-black text-gray-400">Field</th>
                                <th class="py-2 font-black text-gray-400">Nilai Lama</th>
                                <th class="py-2 font-black text-gray-400">Nilai Baru</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
            `;
            
            const allKeys = new Set([...Object.keys(log.old_values || {}), ...Object.keys(log.new_values || {})]);
            
            allKeys.forEach(key => {
                const oldVal = log.old_values ? log.old_values[key] : '-';
                const newVal = log.new_values ? log.new_values[key] : '-';
                
                html += `
                    <tr>
                        <td class="py-3 font-bold text-gray-800">${key}</td>
                        <td class="py-3 text-rose-500 line-through">${typeof oldVal === 'object' ? JSON.stringify(oldVal) : oldVal}</td>
                        <td class="py-3 text-emerald-600 font-bold">${typeof newVal === 'object' ? JSON.stringify(newVal) : newVal}</td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            content.innerHTML = html;
            modal.classList.remove('hidden');
        }
        
        function closeLogModal() {
            document.getElementById('logModal').classList.add('hidden');
        }
    </script>

    <style>
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</x-app-layout>
