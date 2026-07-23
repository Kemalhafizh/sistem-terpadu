<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Biodata Siswa') }}
            </h2>
            <div class="flex space-x-2">
                <button onclick="document.getElementById('modal-import').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-bold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import Masal
                </button>
                <a href="{{ route('students.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-primary-700 shadow-sm transition ease-in-out duration-150">
                    + Tambah Biodata
                </a>
            </div>
        </div>

        <!-- Modal Import -->
        <div id="modal-import" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 max-w-md w-full shadow-2xl relative">
                <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 mb-4 uppercase tracking-widest">Import Data Siswa</h3>
                <p class="text-xs text-gray-500 mb-6 italic">* Format Excel: Kolom [nama, email, nis, gender, classroom_id]. Password otomatis diset sesuai NIS.</p>
                <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-xs font-black text-gray-400 uppercase mb-2">Pilih File Excel (.xlsx)</label>
                        <input type="file" name="file" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" required>
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" onclick="document.getElementById('modal-import').classList.add('hidden')" class="flex-1 py-3 bg-gray-100 text-gray-500 font-bold rounded-xl text-xs uppercase">Batal</button>
                        <button type="submit" class="flex-1 py-3 bg-primary-600 text-white font-black rounded-xl text-xs uppercase shadow-lg shadow-primary-900/20">Eksekusi Import</button>
                    </div>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            <!-- Filter & Search Bar -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <form action="{{ route('students.index') }}" method="GET" class="flex flex-col md:flex-row items-center gap-3 w-full">
                    <div class="relative w-full md:w-96">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="search" id="global-search" value="{{ request('search') }}" 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition duration-150 ease-in-out" 
                            placeholder="Cari Nama, NIS, atau NISN (Ctrl+K)">
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <select name="classroom_id" class="block w-full md:w-48 py-2 px-3 border border-gray-300 bg-gray-50 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <option value="">Semua Kelas</option>
                            @foreach($classrooms as $class)
                                <option value="{{ $class->id }}" {{ request('classroom_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>

                        <select name="gender" class="block w-full md:w-32 py-2 px-3 border border-gray-300 bg-gray-50 rounded-lg focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <option value="">Semua JK</option>
                            <option value="L" {{ request('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ request('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>

                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Filter
                        </button>
                        
                        @if(request()->hasAny(['search', 'classroom_id', 'gender']))
                            <a href="{{ route('students.index') }}" class="text-xs text-red-600 hover:text-red-800 font-bold uppercase underline">Reset</a>
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
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider">NIS / NISN</th>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider">Kelas</th>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider">JK</th>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($students as $student)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="text-sm font-bold text-gray-900">{{ $student?->user?->name ?? 'Tanpa Nama' }}</div>
                                    <div class="text-xs text-gray-500">{{ $student?->user?->email ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-sm text-gray-700">{{ $student->nis }}</div>
                                    <div class="text-xs text-primary-600 font-medium">{{ $student->nisn }}</div>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-600">
                                    @if($student->classroom)
                                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-green-100 text-green-800">{{ $student?->classroom?->name }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Belum Masuk Kelas</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-500">
                                    {{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </td>
                                <td class="py-4 px-6 text-sm font-medium text-right flex items-center justify-end space-x-2">
                                    <a href="{{ route('students.pdf', $student->id) }}" target="_blank" class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 p-1.5 rounded-lg transition-all" title="Cetak Biodata PDF">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 012-2H5a2 2 0 012 2v4a2 2 0 002 2m8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </a>
                                    <a href="{{ route('students.edit', $student->id) }}" class="text-primary-600 hover:text-primary-900 bg-primary-50 p-1.5 rounded-lg transition-all" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus biodata siswa ini? Akun loginnya tidak akan ikut terhapus, hanya biodatanya saja.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 p-1.5 rounded-lg transition-all" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            
                            @if($students->isEmpty())
                            <tr>
                                <td colspan="5" class="py-8 px-6 text-center text-gray-400">
                                    Belum ada pendataan siswa. Silakan tambahkan biodata untuk akun siswa kelas yang bersangkutan.
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $students->links() }}
                </div>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
