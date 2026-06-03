<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-800 leading-tight">
            {{ __('Daftarkan Alumni Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <form action="{{ route('alumni.store') }}" method="POST" class="p-10 space-y-8">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Pilih Siswa Lulusan</label>
                            <select name="student_id" required class="w-full rounded-2xl border-gray-100 p-4 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                <option value="">Cari Nama Siswa...</option>
                                @foreach($activeStudents as $student)
                                    <option value="{{ $student?->id }}">{{ $student?->user?->name }} (Kelas: {{ $student?->classroom?->name ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-[10px] text-gray-400 italic">*Hanya siswa dengan status 'Aktif' yang muncul di sini.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Tahun Lulus</label>
                                <input type="number" name="graduation_year" value="{{ date('Y') }}" required class="w-full rounded-2xl border-gray-100 p-4 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Aktivitas Terkini</label>
                                <select name="current_activity" required class="w-full rounded-2xl border-gray-100 p-4 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                    <option value="study">Melanjutkan Kuliah</option>
                                    <option value="work">Bekerja</option>
                                    <option value="entrepreneur">Wirausaha</option>
                                    <option value="unemployed">Mencari Pekerjaan</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Nama Institusi / Perusahaan</label>
                            <input type="text" name="institution_name" class="w-full rounded-2xl border-gray-100 p-4 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="Contoh: Universitas Diponegoro / PT. Maju Jaya">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase mb-3 tracking-widest">Informasi Kontak (No HP / Email)</label>
                            <input type="text" name="contact_info" class="w-full rounded-2xl border-gray-100 p-4 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" placeholder="Untuk keperluan database tracer study">
                        </div>
                    </div>

                    <div class="pt-10 flex items-center justify-end space-x-4 border-t border-gray-50">
                        <a href="{{ route('alumni.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-all">Batal</a>
                        <button type="submit" class="px-10 py-4 bg-emerald-600 text-white rounded-[1.5rem] font-black text-sm shadow-xl shadow-emerald-100 hover:bg-emerald-700 hover:-translate-y-1 transition-all">KONFIRMASI KELULUSAN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
