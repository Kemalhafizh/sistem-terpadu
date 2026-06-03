<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kelola Pengguna') }}
            </h2>
            <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">
                + Tambah Pengguna
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Filter & Search Bar -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <form action="{{ route('users.index') }}" method="GET" class="flex flex-col md:flex-row items-center gap-3 w-full">
                    <div class="relative w-full md:w-96">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="search" id="global-search" value="{{ request('search') }}" 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition duration-150 ease-in-out" 
                            placeholder="Cari Nama atau Email (Ctrl+K)">
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <select name="role" class="block w-full md:w-48 py-2 px-3 border border-gray-300 bg-gray-50 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <option value="">Semua Peran</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Siswa</option>
                            <option value="parent" {{ request('role') == 'parent' ? 'selected' : '' }}>Wali Murid</option>
                            <option value="staf_tu" {{ request('role') == 'staf_tu' ? 'selected' : '' }}>Staf TU</option>
                        </select>

                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Filter
                        </button>
                        
                        @if(request()->hasAny(['search', 'role']))
                            <a href="{{ route('users.index') }}" class="text-xs text-red-600 hover:text-red-800 font-bold uppercase underline">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 bg-white border-b border-gray-200">
                
                <div class="overflow-x-auto animate-fade-in">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider">Nama Lengkap</th>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider">Email</th>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider">Peran (Role)</th>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 bg-primary-100 rounded-full flex items-center justify-center">
                                            <span class="text-primary-700 font-bold text-sm">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-500">
                                    {{ $user->email }}
                                </td>
                                <td class="py-4 px-6 text-sm">
                                    @if($user->role == 'admin')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 uppercase tracking-wider">Admin</span>
                                    @elseif($user->role == 'guru')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 uppercase tracking-wider">Guru</span>
                                    @elseif($user->role == 'student')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 uppercase tracking-wider">Siswa</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 uppercase tracking-wider">{{ $user->role }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-sm font-medium text-right flex items-center justify-end space-x-3">
                                    {{-- Masquerade Feature --}}
                                    @if(auth()->user()->isAdmin() && $user->id !== auth()->id() && !$user->isAdmin())
                                        <a href="{{ route('admin.impersonate.take', $user->id) }}" class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 px-3 py-1 rounded-lg transition-all flex items-center" title="Login Sebagai User Ini">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                            In 
                                        </a>
                                    @endif

                                    <a href="{{ route('users.edit', $user->id) }}" class="text-primary-600 hover:text-primary-900">Edit</a>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            
                            @if($users->isEmpty())
                            <tr>
                                <td colspan="4" class="py-8 px-6 text-center text-gray-400">
                                    Belum ada data pengguna.
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
