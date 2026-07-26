@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        @if(isset($program))
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-1">Pendaftaran Beasiswa: {{ $program->name }}</h1>
        <p class="text-xs text-gray-500 mb-6">Besaran Bantuan: <strong class="text-emerald-600">Rp {{ number_format($program->amount_per_student, 0, ',', '.') }}</strong></p>

        <form action="{{ route('financial.scholarships.submit_application', $program->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Siswa</label>
                <select name="student_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                    @foreach($students as $st)
                    <option value="{{ $st->id }}">{{ $st->name }} (NISN: {{ $st->nisn ?? '12345' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nilai Rata-rata IPK / Rapor</label>
                    <input type="number" step="0.01" name="gpa_score" value="3.75" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Penghasilan Orang Tua (Rp/Bulan)</label>
                    <input type="number" name="family_income" value="2500000" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('financial.scholarships.programs') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">Kirim Permohonan Beasiswa</button>
            </div>
        </form>
        @else
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-2">Buat Program Beasiswa Baru</h1>
        <p class="text-xs text-gray-500 mb-6">Formulir pembukaan kuota beasiswa pemerintah, yayasan, atau corporate</p>

        <form action="{{ route('financial.scholarships.store_program') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Program Beasiswa</label>
                <input type="text" name="name" placeholder="Beasiswa Prestasi Tahfidz YBWSA 2026" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe Beasiswa</label>
                    <select name="type" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                        <option value="internal_merit">Internal Merit (Prestasi)</option>
                        <option value="internal_need">Internal Need-Based (Keringanan SPP)</option>
                        <option value="pip">PIP (Program Indonesia Pintar)</option>
                        <option value="bos">BOS (Bantuan Operasional Sekolah)</option>
                        <option value="external">Eksternal Corporate / Donor</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sumber Dana</label>
                    <select name="source" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                        <option value="yayasan">Yayasan YBWSA</option>
                        <option value="government">Pemerintah (Kemenag/Kemdikbud)</option>
                        <option value="corporate">Mitra Perusahaan / CSR</option>
                        <option value="individual">Alumni / Perorangan</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tahun Ajaran</label>
                    <input type="text" name="academic_year" value="2026/2027" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kuota Siswa</label>
                    <input type="number" name="total_quota" value="25" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nominal / Siswa (Rp)</label>
                    <input type="number" name="amount_per_student" value="3600000" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Mulai Pendaftaran</label>
                    <input type="date" name="application_start_date" value="{{ date('Y-m-d') }}" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tutup Pendaftaran</label>
                    <input type="date" name="application_end_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('financial.scholarships.programs') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">Simpan Program Beasiswa</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
