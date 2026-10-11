<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-3xl text-gray-900 tracking-tight">Kalkulasi Payroll Baru</h2>
        <p class="text-sm text-gray-500 font-medium mt-1">Generate lembar penggajian bulanan untuk seluruh pegawai secara otomatis.</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-black text-lg text-gray-900">Form Kalkulasi Payroll Bulanan</h3>
                    <p class="text-xs text-gray-500 mt-1">Harap pilih periode bulan dan tahun penagihan payroll yang ingin diproses.</p>
                </div>
                
                <div class="p-8 space-y-8">
                    <!-- Aturan perhitungan: nilai sebenarnya dari pengaturan, bukan teks tetap -->
                    <div class="bg-primary-50 border border-primary-100 rounded-2xl p-5 space-y-3">
                        <div class="flex items-center space-x-2 text-primary-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-xs font-black uppercase tracking-wider">{{ __('Cara Slip Gaji Dihitung') }}</span>
                        </div>
                        <ul class="text-xs text-primary-700 space-y-2 list-disc pl-5 font-semibold">
                            <li><strong>{{ __('Hari kerja') }}:</strong> {{ __('hari :days yang tercakup data presensi (ada pegawai yang scan atau tercatat cuti). Hari libur, saat tidak ada yang scan, tidak dihitung.', ['days' => $workDays]) }}</li>
                            <li><strong>{{ __('Terlambat') }}:</strong> {{ __('scan pertama setelah pukul :time.', ['time' => $lateAfter]) }}
                                {{ $rates['late'] !== null ? __('Potongan :amount per hari.', ['amount' => 'Rp '.number_format($rates['late'], 0, ',', '.')]) : __('Belum ada potongan (tarif belum diatur).') }}</li>
                            <li><strong>{{ __('Tidak hadir') }}:</strong> {{ __('hari kerja tanpa scan dan tanpa pengajuan izin/cuti yang disetujui.') }}
                                {{ $rates['absent'] !== null ? __('Potongan :amount per hari.', ['amount' => 'Rp '.number_format($rates['absent'], 0, ',', '.')]) : __('Belum ada potongan (tarif belum diatur).') }}</li>
                            <li><strong>{{ __('Izin, sakit, dan cuti yang disetujui') }}:</strong> {{ __('tidak dipotong. Hari sebelum tanggal mulai kerja pegawai juga tidak dihitung.') }}</li>
                            <li><strong>{{ __('Hitung ulang') }}:</strong> {{ __('slip draf dihitung ulang dengan tunjangan dan potongan lain yang sudah diisi. Slip yang sudah diterbitkan atau dibayar tidak diubah.') }}</li>
                        </ul>
                    </div>

                    <!-- Tarif potongan, diatur sekolah -->
                    <form action="{{ route('hrd.payrolls.rules') }}" method="POST" class="space-y-4 border border-gray-100 rounded-2xl p-5">
                        @csrf
                        <div>
                            <h4 class="text-sm font-black text-gray-900">{{ __('Tarif Potongan Kehadiran') }}</h4>
                            <p class="text-xs text-gray-500 mt-1">{{ __('Kosongkan bila sekolah tidak memotong gaji untuk hal tersebut.') }}</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="block">
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">{{ __('Per hari terlambat (Rp)') }}</span>
                                <input type="number" name="late_deduction" min="0" step="1000" value="{{ old('late_deduction', $rates['late'] !== null ? (int) $rates['late'] : '') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 font-mono text-sm">
                            </label>
                            <label class="block">
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">{{ __('Per hari tidak hadir (Rp)') }}</span>
                                <input type="number" name="absent_deduction" min="0" step="1000" value="{{ old('absent_deduction', $rates['absent'] !== null ? (int) $rates['absent'] : '') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 font-mono text-sm">
                            </label>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black tracking-wider">{{ __('Simpan Tarif') }}</button>
                        </div>
                    </form>

                    <!-- Form Generate -->
                    <form action="{{ route('hrd.payrolls.generate') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Pilihan Bulan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Pilih Bulan Periode</label>
                                <select name="month" required class="w-full rounded-xl border-gray-200 bg-gray-50 p-4 focus:ring-primary-500 font-bold text-sm text-gray-700">
                                    @foreach(['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $key => $name)
                                        <option value="{{ $key }}" {{ date('m') == $key ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Pilihan Tahun -->
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Pilih Tahun Periode</label>
                                <select name="year" required class="w-full rounded-xl border-gray-200 bg-gray-50 p-4 focus:ring-primary-500 font-bold text-sm text-gray-700">
                                    @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                            <a href="{{ route('hrd.payrolls.index') }}" class="px-5 py-3 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-xl text-xs font-black tracking-wider transition-colors">
                                KEMBALI
                            </a>
                            <button type="submit" class="px-6 py-3.5 bg-gray-900 hover:bg-black text-white font-black text-xs rounded-xl tracking-wider shadow-md transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.29"></path></svg>
                                PROSES GENERATE MASSAL
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
