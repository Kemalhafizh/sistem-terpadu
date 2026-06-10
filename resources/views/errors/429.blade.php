@extends('errors.layout')

@section('title', 'Terlalu Banyak Permintaan')

@section('icon')
    <svg class="w-12 h-12" width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
@endsection

@section('code', '429')
@section('message', 'Sabar Sebentar ya!')

@section('description')
    Sistem mendeteksi aktivitas yang terlalu cepat dari perangkat Anda. Untuk menjaga keamanan server sekolah, akses dibatasi sementara.
    <br><br>
    <div x-data="{ seconds: 60 }" x-init="setInterval(() => { if (seconds > 0) seconds--; }, 1000)" style="margin-bottom: 1rem; display: flex; justify-content: center;">
        <span style="display: inline-block; padding: 0.5rem 1rem; border: 1px solid #fde68a; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 800; background-color: #fef3c7; color: #92400e;">
            <span x-show="seconds > 0">Coba lagi dalam <span x-text="seconds" style="font-weight: 900;">60</span> detik</span>
            <span x-show="seconds === 0">Batas waktu habis. Silakan muat ulang halaman.</span>
        </span>
    </div>
@endsection

@section('buttons')
<div x-data="{ seconds: 60 }" x-init="setInterval(() => { if (seconds > 0) seconds--; }, 1000)" class="space-y-4 sm:space-y-0 sm:space-x-4 flex flex-col sm:flex-row justify-center" style="display: flex; gap: 1rem; justify-content: center; align-items: center;">
    <button :disabled="seconds > 0" onclick="window.location.reload();" :class="seconds > 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50 cursor-pointer'" class="px-8 py-4 bg-white border border-gray-100 rounded-2xl font-black text-sm text-gray-600 shadow-sm transition-all btn-click" style="padding: 1rem 2rem; background-color: #ffffff; border: 1px solid #f3f4f6; border-radius: 1rem; color: #4b5563; font-weight: 800; font-size: 0.875rem; border-style: solid;">
        COBA LAGI
    </button>
    <a href="{{ route('dashboard') }}" class="px-8 py-4 bg-primary-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-primary-900/20 hover:scale-[1.05] active:scale-95 transition-all btn-click" style="padding: 1rem 2rem; background-color: #059669; color: #ffffff; border-radius: 1rem; text-decoration: none; font-weight: 800; font-size: 0.875rem;">
        MENU UTAMA DASHBOARD
    </a>
</div>
@endsection
