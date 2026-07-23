<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between py-2">
            <div>
                <h2 class="font-extrabold text-3xl text-slate-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Riwayat Persetujuan') }}
                </h2>
                <p class="text-xs font-bold text-teal-600 dark:text-teal-400 tracking-widest uppercase mt-1">
                    SISTA • LOG AUDIT ALUR KERJA SELESAI
                </p>
            </div>
        </div>
    </x-slot>

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

    <div class="py-8 bg-slate-50 dark:bg-slate-900 min-h-screen text-slate-900 dark:text-slate-100">
        <div class="w-full sm:px-6 lg:px-8 space-y-8 animate-fade-in-up">
            
            <!-- Navigation Bar -->
            <nav class="flex flex-wrap gap-2 p-1.5 bg-white dark:bg-slate-800 rounded-[1.5rem] border border-slate-200/50 dark:border-slate-700/50 shadow-sm">
                <a href="{{ route('workflows.dashboard') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    Dashboard
                </a>
                <a href="{{ route('workflows.pending') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    Pending Approvals
                </a>
                <a href="{{ route('workflows.my-requests') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    My Requests
                </a>
                <a href="{{ route('workflows.definitions') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    Alur Dinamis
                </a>
                <a href="{{ route('workflows.history') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 bg-teal-500 text-white shadow-lg shadow-teal-500/25">
                    Riwayat
                </a>
                <a href="{{ route('workflows.analytics') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    Analitik
                </a>
            </nav>

            <!-- Filter Panel -->
            <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 border border-slate-100 dark:border-slate-700/50 shadow-md">
                <form action="{{ route('workflows.history') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <div>
                        <label class="block text-[0.65rem] font-extrabold tracking-widest text-slate-400 uppercase mb-2">Tipe Alur Kerja</label>
                        <select name="definition_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-teal-500 text-xs font-semibold text-slate-800 dark:text-slate-200">
                            <option value="">-- Semua Alur Kerja --</option>
                            @foreach($definitions as $definition)
                                <option value="{{ $definition->id }}" {{ request('definition_id') == $definition->id ? 'selected' : '' }}>
                                    {{ $definition->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[0.65rem] font-extrabold tracking-widest text-slate-400 uppercase mb-2">Status Akhir</label>
                        <select name="status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl focus:outline-none focus:ring-2 focus:ring-teal-500 text-xs font-semibold text-slate-800 dark:text-slate-200">
                            <option value="">-- Semua Status --</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved (Disetujui)</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled (Dibatalkan)</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 inline-flex justify-center items-center px-5 py-3.5 bg-teal-500 hover:bg-teal-600 text-white text-xs font-bold uppercase tracking-widest rounded-2xl transition-all duration-300 hover:scale-105 shadow-md shadow-teal-500/10">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filter Log
                        </button>
                        @if(request()->has('definition_id') || request()->has('status'))
                            <a href="{{ route('workflows.history') }}" class="px-5 py-3.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold uppercase tracking-widest rounded-2xl transition-all duration-300 flex items-center justify-center">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Table of Logs -->
            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border border-slate-100 dark:border-slate-700/50 shadow-xl overflow-hidden">
                <div class="p-8 border-b border-slate-100 dark:border-slate-700/50">
                    <h3 class="text-xl font-extrabold text-slate-900 dark:text-white">Log Audit Penyelesaian</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">Seluruh berkas pengajuan yang telah mencapai keputusan akhir atau dibatalkan oleh pemohon.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/50">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Pengaju</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Alur Kerja</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Model Induk (Entitas)</th>
                                <th class="px-6 py-4 text-center text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Status Keputusan</th>
                                <th class="px-6 py-4 text-center text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Tanggal Selesai</th>
                                <th class="px-6 py-4 text-right text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            @forelse($requests as $req)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors duration-200">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-sm">
                                                {{ strtoupper(substr($req->requester?->name ?? 'A', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $req->requester?->name ?? 'Anonim' }}</div>
                                                <div class="text-xs text-slate-400">{{ $req->requester?->email ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                                            {{ $req->definition?->name ?? 'Tanpa Nama Alur' }}
                                        </div>
                                        <div class="text-[0.65rem] font-mono text-slate-400 uppercase tracking-wider mt-0.5">
                                            KODE: {{ $req->definition?->code ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-sm">
                                        <span class="font-mono text-xs bg-slate-100 dark:bg-slate-900 px-2.5 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 border border-slate-200/40 dark:border-slate-800/80 inline-block">
                                            {{ class_basename($req->requestable_type) }} #{{ $req->requestable_id }}
                                        </span>
                                        @if($req->requestable && isset($req->requestable->title))
                                            <span class="block text-xs text-slate-500 font-semibold mt-1 max-w-[200px] truncate" title="{{ $req->requestable->title }}">
                                                {{ $req->requestable->title }}
                                            </span>
                                        @elseif($req->requestable && isset($req->requestable->name))
                                            <span class="block text-xs text-slate-500 font-semibold mt-1 max-w-[200px] truncate" title="{{ $req->requestable->name }}">
                                                {{ $req->requestable->name }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        @if($req->status === 'approved')
                                            <span class="inline-flex items-center px-3.5 py-1 text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-full border border-emerald-500/20">
                                                Approved
                                            </span>
                                        @elseif($req->status === 'rejected')
                                            <span class="inline-flex items-center px-3.5 py-1 text-xs font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-full border border-rose-500/20">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3.5 py-1 text-xs font-medium bg-slate-100 dark:bg-slate-900 text-slate-500 dark:text-slate-400 rounded-full border border-slate-200/50">
                                                Cancelled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-center whitespace-nowrap text-xs text-slate-500 dark:text-slate-400 font-semibold">
                                        {{ $req->updated_at ? $req->updated_at->translatedFormat('d M Y, H:i') : '-' }}
                                        <div class="text-[0.65rem] text-slate-400 font-medium mt-0.5">
                                            {{ $req->updated_at ? $req->updated_at->diffForHumans() : '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-right whitespace-nowrap">
                                        <a href="{{ route('workflows.show', $req->id) }}" class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-xs font-bold rounded-xl transition-all duration-200 hover:scale-105 shadow-md shadow-teal-500/10">
                                            Buka Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <p class="font-bold">Belum ada riwayat alur persetujuan selesai.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginated List Links -->
                @if($requests->hasPages())
                    <div class="px-8 py-6 border-t border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/10">
                        {{ $requests->links() }}
                    </div>
                @endif
            </div>
            
        </div>
    </div>
</x-app-layout>
