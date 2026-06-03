<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Kelas') }}
            </h2>
            <a href="{{ route('classrooms.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">
                + Tambah Kelas Baru
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
            <div class="p-6 bg-white border-b border-gray-200">
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider">Nama Kelas</th>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider">Tingkat</th>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider">Jurusan</th>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider">Wali Kelas</th>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider">Tahun Ajaran</th>
                                <th class="py-4 px-6 bg-gray-50 font-semibold uppercase text-xs text-gray-500 border-b border-gray-200 tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($classrooms as $classroom)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="text-sm font-bold text-gray-900">{{ $classroom->name }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-primary-100 text-primary-800">Kelas {{ $classroom->grade_level }}</span>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-600">
                                    {{ $classroom->major }}
                                </td>
                                <td class="py-4 px-6 text-sm font-medium text-gray-700">
                                    {{ $classroom->teacher ? $classroom->teacher->name : 'Belum Atur Wali Kelas' }}
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-500">
                                    {{ $classroom->academic_year }}
                                </td>
                                <td class="py-4 px-6 text-sm font-medium text-right">
                                    <a href="{{ route('classrooms.edit', $classroom->id) }}" class="text-primary-600 hover:text-primary-900 mr-3">Edit</a>
                                    <form action="{{ route('classrooms.destroy', $classroom->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus kelas ini? Data siswa terkait mungkin bermasalah jika kelas dihapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            
                            @if($classrooms->isEmpty())
                            <tr>
                                <td colspan="6" class="py-8 px-6 text-center text-gray-400">
                                    Belum ada data kelas yang terdaftar.
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $classrooms->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
