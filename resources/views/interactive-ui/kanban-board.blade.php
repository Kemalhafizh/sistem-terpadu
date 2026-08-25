<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Papan Kanban Tugas & Proyek') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola alur kerja guru, kepanitiaan sekolah, dan tugas administratif secara visual.
                </p>
            </div>
            <button onclick="document.getElementById('newTaskModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-semibold shadow-sm transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Tugas</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Board Columns Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @php
                $columns = [
                    ['id' => 'todo', 'title' => 'Rencana / To Do', 'border' => 'border-slate-300 dark:border-slate-700', 'badge' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'],
                    ['id' => 'in_progress', 'title' => 'Sedang Dikerjakan', 'border' => 'border-blue-300 dark:border-blue-800', 'badge' => 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300'],
                    ['id' => 'review', 'title' => 'Review / Evaluasi', 'border' => 'border-amber-300 dark:border-amber-800', 'badge' => 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300'],
                    ['id' => 'done', 'title' => 'Selesai', 'border' => 'border-emerald-300 dark:border-emerald-800', 'badge' => 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300'],
                ];
            @endphp

            @foreach($columns as $col)
                <div class="bg-slate-50 dark:bg-slate-900/60 rounded-3xl p-4 border {{ $col['border'] }} flex flex-col space-y-3 min-h-[500px]">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ $col['title'] }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $col['badge'] }}">
                            {{ $activeBoard->tasks->where('column_id', $col['id'])->count() }}
                        </span>
                    </div>

                    <!-- Cards in Column -->
                    <div class="space-y-3 flex-1">
                        @forelse($activeBoard->tasks->where('column_id', $col['id']) as $task)
                            <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-all cursor-move space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $task->priority === 'urgent' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $task->priority }}
                                    </span>
                                    @if($task->due_date)
                                        <span class="text-[10px] text-slate-400">{{ $task->due_date->format('d M') }}</span>
                                    @endif
                                </div>
                                <h4 class="font-bold text-slate-800 dark:text-white text-sm leading-snug">{{ $task->title }}</h4>
                                @if($task->description)
                                    <p class="text-xs text-slate-500 line-clamp-2">{{ $task->description }}</p>
                                @endif
                                @if($task->assignee)
                                    <div class="pt-1 flex items-center space-x-1.5">
                                        <div class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">
                                            {{ substr($task->assignee->name, 0, 1) }}
                                        </div>
                                        <span class="text-xs text-slate-600 dark:text-slate-400 truncate">{{ $task->assignee->name }}</span>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="h-24 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-400 text-xs">
                                Kolom Kosong
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
