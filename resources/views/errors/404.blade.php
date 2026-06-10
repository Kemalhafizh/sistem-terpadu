@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')

@section('icon')
    <svg class="w-12 h-12" width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
@endsection

@section('code', '404')
@section('message', 'Halaman Menghilang!')

@section('description')
    Waduh! Sepertinya halaman yang Anda cari pindah ke sekolah lain atau sudah dihapus dari arsip digital kita. Jangan khawatir, yuk kita balik ke dashboard.
    <div style="margin-top: 1.5rem; text-align: left; background-color: #f9fafb; padding: 1.25rem 1.5rem; border-radius: 1rem; border: 1px solid #f3f4f6;">
        <p style="font-weight: 800; font-size: 0.85rem; color: #374151; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Halaman Rekomendasi:</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; font-size: 0.875rem;">
            <a href="{{ route('dashboard') }}" style="color: #059669; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard Utama
            </a>
            <a href="{{ route('calendar.index') }}" style="color: #059669; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Kalender Akademik
            </a>
            <a href="{{ route('helpdesk.tickets.create') }}" style="color: #059669; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                IT Helpdesk Bantuan
            </a>
        </div>
    </div>
@endsection
