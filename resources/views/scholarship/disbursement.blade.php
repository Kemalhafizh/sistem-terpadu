@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-2">Pencairan Dana Beasiswa</h1>
        <p class="text-xs text-gray-500 mb-4">Penerima: <strong class="text-indigo-600">{{ $application->student->name }}</strong> | Program: {{ $application->program->name }}</p>

        <div class="bg-emerald-50 dark:bg-emerald-950/30 p-4 rounded-2xl border border-emerald-200 mb-6 flex justify-between items-center">
            <span class="text-xs font-bold text-gray-600 uppercase">Nominal Pencairan:</span>
            <span class="text-xl font-black text-emerald-600">Rp {{ number_format($application->program->amount_per_student, 0, ',', '.') }}</span>
        </div>

        <form action="{{ route('financial.scholarships.disburse_action', $application->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Metode Penyaluran Dana</label>
                <select name="disbursement_method" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                    <option value="bank_transfer">Transfer Bank (Rekening Siswa/Orang Tua)</option>
                    <option value="cash">Pencairan Tunai Langsung (Kas Sekolah)</option>
                    <option value="wadiah_credit">Kredit Otomatis Tabungan Wadiah / Potong Tagihan SPP</option>
                </select>
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('financial.scholarships.review', $application->scholarship_program_id) }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md">Proses Pencairan Sekarang</button>
            </div>
        </form>
    </div>
</div>
@endsection
