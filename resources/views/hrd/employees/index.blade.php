<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-black text-2xl text-gray-900 tracking-tight">Manajemen Pegawai & Guru</h2>
                <p class="text-sm text-gray-500 font-medium">Direktori seluruh SDM Yayasan Sultan Agung 1.</p>
            </div>
            <a href="{{ route('hrd.employees.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-800 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-lg shadow-primary-500/30">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Pegawai Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Filter Section -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 mb-8 flex flex-col md:flex-row gap-4 justify-between items-center">
                <form method="GET" action="{{ route('hrd.employees.index') }}" class="flex w-full md:w-auto gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama atau NIY..." class="rounded-xl border-gray-200 bg-gray-50 focus:ring-primary-500 focus:border-primary-500 text-sm w-full md:w-64">
                    
                    <select name="status" class="rounded-xl border-gray-200 bg-gray-50 focus:ring-primary-500 focus:border-primary-500 text-sm">
                        <option value="">Semua Status</option>
                        <option value="gty" {{ request('status') == 'gty' ? 'selected' : '' }}>GTY</option>
                        <option value="gtt" {{ request('status') == 'gtt' ? 'selected' : '' }}>GTT</option>
                        <option value="staf_tetap" {{ request('status') == 'staf_tetap' ? 'selected' : '' }}>Staf Tetap</option>
                        <option value="honorer" {{ request('status') == 'honorer' ? 'selected' : '' }}>Honorer</option>
                    </select>

                    <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-xl font-bold text-sm hover:bg-gray-800 transition-colors">
                        Filter
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('hrd.employees.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-colors">Reset</a>
                    @endif
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-400 uppercase tracking-widest bg-gray-50/50 border-b border-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-black">NIY</th>
                                <th scope="col" class="px-6 py-4 font-black">Nama Pegawai / Email</th>
                                <th scope="col" class="px-6 py-4 font-black">Jabatan</th>
                                <th scope="col" class="px-6 py-4 font-black">Status</th>
                                <th scope="col" class="px-6 py-4 font-black text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($employees as $employee)
                                <tr class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-gray-900">{{ $employee->employee_number }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-black text-gray-900">{{ $employee?->user?->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $employee?->user?->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-gray-700 font-medium">{{ $employee->position }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($employee->employment_status == 'gty')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 uppercase">GTY</span>
                                        @elseif($employee->employment_status == 'staf_tetap')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-100 text-blue-800 uppercase">Staf Tetap</span>
                                        @elseif($employee->employment_status == 'gtt')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 uppercase">GTT</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-800 uppercase">Honorer</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('hrd.employees.show', $employee) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors" title="Lihat Profil">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                            <a href="{{ route('hrd.employees.edit', $employee) }}" class="p-2 text-amber-500 hover:bg-amber-50 rounded-xl transition-colors" title="Edit Data">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            <p class="text-base font-medium">Belum ada data pegawai.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($employees->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
