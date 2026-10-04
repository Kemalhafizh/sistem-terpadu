@extends('errors.layout')

@section('title', 'Sesi Berakhir')

@section('icon')
    <svg class="w-12 h-12" width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
@endsection

@section('code', '419')
@section('message', 'Sesi Kadaluarsa')

@section('description', 'Keamanan sistem mendeteksi Anda terlalu lama meninggalkan halaman ini. Demi keamanan data '.school_short_name().', silakan muat ulang halaman atau login kembali.')

@section('buttons')
<div class="space-y-4 sm:space-y-0 sm:space-x-4 flex flex-col sm:flex-row justify-center" style="display: flex; gap: 1rem; justify-content: center; align-items: center;">
    <button onclick="window.location.reload();" class="px-8 py-4 bg-white border border-gray-100 rounded-2xl font-black text-sm text-gray-600 shadow-sm hover:bg-gray-50 transition-all btn-click" style="padding: 1rem 2rem; background-color: #ffffff; border: 1px solid #f3f4f6; border-radius: 1rem; color: #4b5563; cursor: pointer; font-weight: 800; font-size: 0.875rem; border-style: solid;">
        MUAT ULANG HALAMAN
    </button>
    <a href="{{ route('dashboard') }}" class="px-8 py-4 bg-primary-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-primary-900/20 hover:scale-[1.05] active:scale-95 transition-all btn-click" style="padding: 1rem 2rem; background-color: #059669; color: #ffffff; border-radius: 1rem; text-decoration: none; font-weight: 800; font-size: 0.875rem;">
        MENU UTAMA DASHBOARD
    </a>
</div>
@endsection
