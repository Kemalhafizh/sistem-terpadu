<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-200 leading-tight">
                {{ __('System Queue Monitor') }}
            </h2>
            <div class="flex space-x-2">
                <form action="{{ route('admin.queue.retry-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-lg shadow-emerald-500/20">
                        <i class="fa-solid fa-rotate-right mr-1"></i> Retry All Failed
                    </button>
                </form>
                <form action="{{ route('admin.queue.flush') }}" method="POST" onsubmit="return confirm('Are you sure you want to flush all failed jobs? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors shadow-lg shadow-rose-500/20">
                        <i class="fa-solid fa-trash-can mr-1"></i> Flush Failed
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            <!-- Metrics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pending Jobs -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 overflow-hidden rounded-2xl shadow-xl transition-all duration-300 hover:border-slate-600/50">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500/10 p-4 rounded-xl">
                                <i class="fa-solid fa-spinner fa-spin text-blue-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Pending Jobs</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-3xl font-semibold text-white">{{ $metrics['pending_count'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Failed Jobs -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 overflow-hidden rounded-2xl shadow-xl transition-all duration-300 hover:border-slate-600/50">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-rose-500/10 p-4 rounded-xl">
                                <i class="fa-solid fa-triangle-exclamation text-rose-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Failed Jobs</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-3xl font-semibold text-rose-400">{{ $metrics['failed_count'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Failed Jobs Table -->
            <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl overflow-hidden mt-8">
                <div class="px-6 py-5 border-b border-slate-700/50 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-slate-200">Failed Jobs Queue</h3>
                </div>
                
                @if($failedJobs->isEmpty())
                    <div class="p-8 text-center text-slate-400">
                        <i class="fa-solid fa-check-circle text-4xl text-emerald-400/50 mb-3 block"></i>
                        <p>No failed jobs found. Everything is running smoothly!</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-700/50">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">ID / Connection</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Queue / Payload</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Failed At</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50 bg-slate-800/30">
                                @foreach($failedJobs as $job)
                                    <tr class="hover:bg-slate-700/30 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white">#{{ $job->id }}</div>
                                            <div class="text-xs text-slate-400">{{ $job->connection }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-slate-300 font-mono bg-slate-900/50 p-1 rounded inline-block mb-1">{{ $job->queue }}</div>
                                            <div class="text-xs text-slate-500 truncate max-w-xs" title="{{ $job->payload }}">
                                                @php
                                                    $payload = json_decode($job->payload);
                                                    echo $payload ? $payload->displayName : 'Unknown Job';
                                                @endphp
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                            {{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form action="{{ route('admin.queue.retry', $job->uuid) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-emerald-400 hover:text-emerald-300 bg-emerald-400/10 px-3 py-1 rounded-md transition-colors" title="Retry Job">
                                                    <i class="fa-solid fa-rotate-right"></i> Retry
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <tr class="bg-rose-900/10">
                                        <td colspan="4" class="px-6 py-3">
                                            <div class="text-xs font-mono text-rose-300/80 whitespace-pre-wrap max-h-32 overflow-y-auto custom-scrollbar p-2 bg-slate-900/50 rounded border border-rose-900/30">{{ $job->exception }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Pending Jobs List -->
            @if($pendingJobs->isNotEmpty())
            <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl overflow-hidden mt-8">
                <div class="px-6 py-5 border-b border-slate-700/50 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-slate-200">Pending Jobs in Queue</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700/50">
                        <thead class="bg-slate-900/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Queue</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Attempts</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Created At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50 bg-slate-800/30">
                            @foreach($pendingJobs as $job)
                                <tr class="hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">#{{ $job->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300 font-mono">{{ $job->queue }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">{{ $job->attempts }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                        {{ \Carbon\Carbon::createFromTimestamp($job->created_at)->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
