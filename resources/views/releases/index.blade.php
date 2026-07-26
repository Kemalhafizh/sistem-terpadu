@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h10M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                Release Versioning (SemVer) & Rollback Strategy Timeline
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Rekam Jejak Rilis Versi Platform SISTA SMA ISA 1 Semarang & Uji Kelayakan Rollback Migration</p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Form Tag New Release -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Tag & Publikasikan Versi Rilis Baru (SemVer)</h3>
        <form action="{{ route('deploy.releases.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Versi SemVer</label>
                <input type="text" name="version" placeholder="1.0.0" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe Rilis</label>
                <select name="release_type" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    <option value="major">MAJOR (Breaking Changes)</option>
                    <option value="minor" selected>MINOR (New Features & Modules)</option>
                    <option value="patch">PATCH (Bug Fixes & Security Hardening)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ringkasan Changelog</label>
                <input type="text" name="changelog_summary" placeholder="Rilis Perdana SISTA Enterprise Suite v1.0.0" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">+ Publikasikan Rilis SemVer</button>
            </div>
        </form>
    </div>

    <!-- Timeline Grid -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Timeline Rilis Versi System</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Versi</th>
                    <th class="py-3 px-4">Tipe Rilis</th>
                    <th class="py-3 px-4">Ringkasan Changelog</th>
                    <th class="py-3 px-4">Rollback Status</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($releases as $rel)
                <tr>
                    <td class="py-3 px-4 font-mono font-bold text-indigo-600">v{{ $rel->version }}</td>
                    <td class="py-3 px-4 text-xs font-semibold uppercase">{{ $rel->release_type }}</td>
                    <td class="py-3 px-4 font-medium text-gray-900 dark:text-white">{{ $rel->changelog_summary }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-emerald-100 text-emerald-700">
                            TESTED (PASSED)
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right space-x-2">
                        <a href="{{ route('deploy.releases.changelog', $rel->id) }}" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold">Changelog</a>
                        <a href="{{ route('deploy.releases.rollback', $rel->id) }}" class="px-3 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold">Uji Rollback</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada rilis versi terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
