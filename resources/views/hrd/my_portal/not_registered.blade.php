<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-900 tracking-tight">Portal HRD Anda</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-12 text-center">
                
                <div class="w-24 h-24 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>

                <h3 class="text-2xl font-black text-gray-900 mb-2">Akun Belum Terdaftar</h3>
                <p class="text-gray-500 max-w-lg mx-auto mb-8">
                    Halo, <strong>{{ auth()->user()->name }}</strong>. Akun Anda saat ini belum terdaftar dalam sistem informasi kepegawaian (HRD) Yayasan. Anda tidak dapat menggunakan fitur presensi mandiri dan pengajuan cuti.
                </p>

                <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl inline-block text-left mb-6">
                    <p class="text-sm text-blue-800 font-bold mb-1">Apa yang harus saya lakukan?</p>
                    @if(auth()->user()->isAdmin())
                        <p class="text-xs text-blue-600 mb-3">Karena Anda adalah Administrator/Superadmin, Anda memiliki wewenang untuk menambahkan profil kepegawaian Anda sendiri ke dalam sistem.</p>
                        <a href="{{ route('hrd.employees.create') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg font-bold text-xs shadow-sm hover:bg-blue-700 transition-colors">
                            Buat Data Pegawai Saya
                        </a>
                    @else
                        <p class="text-xs text-blue-600">Silakan hubungi Administrator atau Bagian HRD Yayasan untuk memploting akun Anda ke dalam Data Pegawai Utama.</p>
                    @endif
                </div>

                <div>
                    <a href="{{ route('dashboard') }}" class="inline-block px-6 py-3 bg-gray-900 text-white rounded-xl font-bold text-sm shadow-md hover:bg-black transition-colors">
                        Kembali ke Dashboard Utama
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
