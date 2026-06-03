<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-800 leading-tight">
            {{ __('Catat Mutasi Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <form action="{{ route('mutations.store') }}" method="POST" enctype="multipart/form-data" class="p-10 space-y-8">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Pilih Siswa</label>
                            <select name="student_id" required class="w-full rounded-2xl border-gray-100 p-4 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                                <option value="">Cari Nama Siswa...</option>
                                @foreach($students as $student)
                                    <option value="{{ $student?->id }}">{{ $student?->user?->name }} (NIS: {{ $student?->nis }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Tipe Mutasi</label>
                                <select name="type" required class="w-full rounded-2xl border-gray-100 p-4 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                                    <option value="out">Mutasi Keluar (Pindah Sekolah/DO)</option>
                                    <option value="in">Mutasi Masuk (Pindahan Baru)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Tanggal Mutasi</label>
                                <input type="date" name="mutation_date" required class="w-full rounded-2xl border-gray-100 p-4 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Alasan Mutasi</label>
                            <input type="text" name="reason" required class="w-full rounded-2xl border-gray-100 p-4 focus:border-primary-500 focus:ring-primary-500 shadow-sm" placeholder="Contoh: Ikut Orang Tua, Pindah Domisili">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Sekolah Tujuan / Asal</label>
                            <input type="text" name="target_school" class="w-full rounded-2xl border-gray-100 p-4 focus:border-primary-500 focus:ring-primary-500 shadow-sm" placeholder="Nama Sekolah Terkait">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Unggah Berkas / Surat (PDF/JPG)</label>
                            <input type="file" name="document" class="w-full text-sm text-gray-400 file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                        </div>
                    </div>

                    <div class="pt-10 flex items-center justify-end space-x-4 border-t border-gray-50">
                        <a href="{{ route('mutations.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-all">Batal</a>
                        <button type="submit" class="px-10 py-4 bg-gray-900 text-white rounded-[1.5rem] font-black text-sm shadow-xl hover:bg-black hover:-translate-y-1 transition-all">SIMPAN DATA MUTASI</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
