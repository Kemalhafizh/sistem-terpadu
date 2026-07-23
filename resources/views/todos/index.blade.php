<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            title="Agenda & Todo List" 
            subtitle="Personal Productivity — {{ auth()->user()->name }}"
        >
            <x-slot name="actions">
                <button onclick="document.getElementById('addTodoModal').classList.remove('hidden')" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Todo Baru
                </button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            {{-- ── Statistics Cards ─────────────────────────────────────── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                <x-stat-card title="Total" :value="$stats['total']" color="secondary">
                    <x-slot name="icon"></x-slot>
                </x-stat-card>
                <x-stat-card title="Pending" :value="$stats['pending']" color="info">
                    <x-slot name="icon">⏳</x-slot>
                </x-stat-card>
                <x-stat-card title="Selesai" :value="$stats['completed']" color="success">
                    <x-slot name="icon"></x-slot>
                </x-stat-card>
                <x-stat-card title="Terlambat" :value="$stats['overdue']" color="danger" class="{{ $stats['overdue'] > 0 ? 'animate-pulse' : '' }}">
                    <x-slot name="icon"></x-slot>
                </x-stat-card>
                <x-stat-card title="Hari Ini" :value="$stats['due_today']" color="warning">
                    <x-slot name="icon"></x-slot>
                </x-stat-card>
                <x-stat-card title="Prioritas" :value="$stats['high_priority']" color="danger">
                    <x-slot name="icon"></x-slot>
                </x-stat-card>
                <div class="bg-gradient-to-br from-primary-600 to-indigo-700 dark:from-primary-750 dark:to-indigo-850 rounded-2xl p-4 shadow-xs relative overflow-hidden group hover:shadow-md transition-all transform hover:-translate-y-0.5 border border-transparent">
                    <div class="absolute top-0 right-0 w-12 h-12 bg-white/10 rounded-bl-xl flex items-center justify-center">
                        <span class="text-md"></span>
                    </div>
                    <p class="text-[10px] font-black text-white/70 uppercase tracking-widest">Produktivitas</p>
                    <p class="text-2xl font-black text-white mt-1">{{ $stats['completion_rate'] }}%</p>
                </div>
            </div>

            {{-- ── Filter & Sort Panel ──────────────────────────────────── --}}
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                <form action="{{ route('todos.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Pencarian</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari todo..." class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Status</label>
                        <select name="status" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                            <option value="all" {{ request('status', 'all') == 'all' ? 'selected' : '' }}>Semua</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Terlambat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Kategori</label>
                        <select name="category" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Semua Kategori</option>
                            <option value="mengajar" {{ request('category') == 'mengajar' ? 'selected' : '' }}> Mengajar</option>
                            <option value="administrasi" {{ request('category') == 'administrasi' ? 'selected' : '' }}> Administrasi</option>
                            <option value="rapat" {{ request('category') == 'rapat' ? 'selected' : '' }}> Rapat</option>
                            <option value="penilaian" {{ request('category') == 'penilaian' ? 'selected' : '' }}> Penilaian</option>
                            <option value="lainnya" {{ request('category') == 'lainnya' ? 'selected' : '' }}> Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Urutan</label>
                        <select name="sort" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                            <option value="due_date" {{ request('sort', 'due_date') == 'due_date' ? 'selected' : '' }}>Deadline Terdekat</option>
                            <option value="priority" {{ request('sort') == 'priority' ? 'selected' : '' }}>Prioritas Tertinggi</option>
                            <option value="created" {{ request('sort') == 'created' ? 'selected' : '' }}>Terbaru Dibuat</option>
                            <option value="category" {{ request('sort') == 'category' ? 'selected' : '' }}>Kategori</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 py-2.5 bg-gray-950 text-white rounded-xl font-bold text-xs hover:bg-gray-800 transition-all">
                            Filter
                        </button>
                        @if(request()->anyFilled(['search', 'category', 'priority', 'sort']) || request('status', 'all') !== 'all')
                            <a href="{{ route('todos.index') }}" class="py-2.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold text-xs transition-all flex items-center justify-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- ── Todo List ────────────────────────────────────────────── --}}
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-black text-xs text-gray-400 uppercase tracking-widest">
                        Daftar Todo ({{ $todos->total() }} item)
                    </h3>
                    <div class="flex items-center gap-2 text-[10px] font-bold text-gray-400">
                        <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-400"></span> High</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Medium</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Low</span>
                    </div>
                </div>

                <div class="space-y-3" id="todoList">
                    @forelse($todos as $todo)
                        @php
                            $priorityColors = [
                                'high' => 'border-l-red-400 bg-red-50/30',
                                'medium' => 'border-l-amber-400 bg-amber-50/20',
                                'low' => 'border-l-emerald-400 bg-emerald-50/20',
                            ];
                            $borderClass = $priorityColors[$todo->priority] ?? 'border-l-gray-300';
                            $isOverdue = $todo->isOverdue();
                            $isDueToday = $todo->isDueToday();
                            $isDueSoon = $todo->isDueSoon();
                        @endphp

                        <div class="group relative border-l-4 {{ $borderClass }} rounded-2xl p-5 hover:shadow-md transition-all duration-300 {{ $todo->is_completed ? 'opacity-60' : '' }}" id="todo-{{ $todo->id }}">
                            <div class="flex items-start gap-4">
                                {{-- Toggle Checkbox --}}
                                <form method="POST" action="{{ route('todos.toggle', $todo) }}" class="flex-shrink-0 mt-0.5">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all duration-200 {{ $todo->is_completed ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-gray-300 hover:border-primary-400 hover:bg-primary-50' }}">
                                        @if($todo->is_completed)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        @endif
                                    </button>
                                </form>

                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1">
                                            <h4 class="font-black text-sm text-gray-900 {{ $todo->is_completed ? 'line-through text-gray-400' : '' }}">
                                                {{ $todo->title }}
                                            </h4>
                                            @if($todo->description)
                                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $todo->description }}</p>
                                            @endif
                                        </div>

                                        {{-- Actions --}}
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                                            <button onclick="openEditModal({{ $todo->id }}, '{{ addslashes($todo->title) }}', '{{ addslashes($todo->description ?? '') }}', '{{ $todo->due_date ? $todo->due_date->format('Y-m-d') : '' }}', '{{ $todo->priority }}', '{{ $todo->category }}')" class="p-1.5 rounded-lg text-gray-400 hover:bg-yellow-50 hover:text-yellow-600 transition-all" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                            <form method="POST" action="{{ route('todos.destroy', $todo) }}" onsubmit="return confirm('Hapus todo ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition-all" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- Meta badges --}}
                                    <div class="flex flex-wrap items-center gap-2 mt-3">
                                        {{-- Category Badge --}}
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider bg-gray-50 text-gray-600 border border-gray-100">
                                            {{ $todo->category_icon }} {{ $todo->category_label }}
                                        </span>

                                        {{-- Priority Badge --}}
                                        @php
                                            $prioStyles = [
                                                'high' => 'bg-red-50 text-red-600 border-red-100',
                                                'medium' => 'bg-amber-50 text-amber-600 border-amber-100',
                                                'low' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider border {{ $prioStyles[$todo->priority] ?? 'bg-gray-50 text-gray-600' }}">
                                            {{ $todo->priority === 'high' ? '' : ($todo->priority === 'medium' ? '' : '') }}
                                            {{ ucfirst($todo->priority) }}
                                        </span>

                                        {{-- Due Date Badge --}}
                                        @if($todo->due_date)
                                            @php
                                                $dueBadge = 'bg-gray-50 text-gray-500 border-gray-100';
                                                $dueLabel = $todo->due_date->translatedFormat('d M Y');
                                                if ($isOverdue) {
                                                    $dueBadge = 'bg-red-100 text-red-700 border-red-200 animate-pulse';
                                                    $dueLabel = 'TERLAMBAT — ' . $todo->due_date->translatedFormat('d M Y');
                                                } elseif ($isDueToday) {
                                                    $dueBadge = 'bg-amber-100 text-amber-700 border-amber-200';
                                                    $dueLabel = 'HARI INI';
                                                } elseif ($isDueSoon) {
                                                    $dueBadge = 'bg-orange-50 text-orange-600 border-orange-100';
                                                    $dueLabel = $todo->due_date->diffForHumans() . ' — ' . $todo->due_date->translatedFormat('d M');
                                                }
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider border {{ $dueBadge }}">
                                                 {{ $dueLabel }}
                                            </span>
                                        @endif

                                        {{-- Completed At --}}
                                        @if($todo->is_completed && $todo->completed_at)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-600 border border-emerald-100">
                                                 Selesai {{ $todo->completed_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16">
                            <div class="text-5xl mb-4"></div>
                            <h3 class="font-black text-gray-400 text-sm uppercase tracking-wider">Belum Ada Todo</h3>
                            <p class="text-xs text-gray-400 mt-2">
                                @if($status === 'completed')
                                    Belum ada todo yang selesai.
                                @elseif($status === 'overdue')
                                    Tidak ada todo yang terlambat — kerja bagus!
                                @else
                                    Klik tombol "Tambah Todo Baru" untuk memulai.
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $todos->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── ADD TODO MODAL ─────────────────────────────────────────── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="addTodoModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden" onclick="event.stopPropagation()">
            <div class="bg-gradient-to-r from-primary-600 to-indigo-600 p-6">
                <h3 class="font-black text-white text-lg">Tambah Todo Baru</h3>
                <p class="text-white/70 text-xs font-bold mt-1">Atur tugas baru Anda dengan prioritas dan deadline</p>
            </div>
            <form action="{{ route('todos.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Judul Todo *</label>
                    <input type="text" name="title" required placeholder="Contoh: Siapkan RPP Matematika Bab 5" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500" autofocus>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="3" placeholder="Detail tambahan (opsional)..." class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500"></textarea>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Deadline</label>
                        <input type="date" name="due_date" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Prioritas *</label>
                        <select name="priority" required class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                            <option value="low"> Low</option>
                            <option value="medium" selected> Medium</option>
                            <option value="high"> High</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Kategori *</label>
                        <select name="category" required class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                            <option value="mengajar"> Mengajar</option>
                            <option value="administrasi"> Administrasi</option>
                            <option value="rapat"> Rapat</option>
                            <option value="penilaian"> Penilaian</option>
                            <option value="lainnya"> Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('addTodoModal').classList.add('hidden')" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-bold text-xs hover:bg-gray-200 transition-all">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-bold text-xs hover:bg-primary-700 transition-all shadow-lg shadow-primary-200">
                        Simpan Todo
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- ── EDIT TODO MODAL ────────────────────────────────────────── --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="editTodoModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden" onclick="event.stopPropagation()">
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 p-6">
                <h3 class="font-black text-white text-lg">Edit Todo</h3>
                <p class="text-white/70 text-xs font-bold mt-1">Perbarui detail tugas Anda</p>
            </div>
            <form id="editTodoForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Judul Todo *</label>
                    <input type="text" name="title" id="editTitle" required class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Deskripsi</label>
                    <textarea name="description" id="editDescription" rows="3" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500"></textarea>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Deadline</label>
                        <input type="date" name="due_date" id="editDueDate" class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Prioritas *</label>
                        <select name="priority" id="editPriority" required class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                            <option value="low"> Low</option>
                            <option value="medium"> Medium</option>
                            <option value="high"> High</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Kategori *</label>
                        <select name="category" id="editCategory" required class="w-full text-xs font-bold rounded-xl border-gray-100 focus:border-primary-500 focus:ring-primary-500">
                            <option value="mengajar"> Mengajar</option>
                            <option value="administrasi"> Administrasi</option>
                            <option value="rapat"> Rapat</option>
                            <option value="penilaian"> Penilaian</option>
                            <option value="lainnya"> Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('editTodoModal').classList.add('hidden')" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-bold text-xs hover:bg-gray-200 transition-all">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-500 text-white rounded-xl font-bold text-xs hover:bg-amber-600 transition-all shadow-lg shadow-amber-200">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openEditModal(id, title, description, dueDate, priority, category) {
            const form = document.getElementById('editTodoForm');
            form.action = '{{ url("todos") }}/' + id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editDescription').value = description;
            document.getElementById('editDueDate').value = dueDate;
            document.getElementById('editPriority').value = priority;
            document.getElementById('editCategory').value = category;
            document.getElementById('editTodoModal').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
