@extends('errors.layout')

@section('title', 'Kesalahan Server')

@section('icon')
    <svg class="w-12 h-12" width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
@endsection

@section('code', '500')
@section('message', 'Aplikasi Sedang Lelah')

@section('description', 'Ada gangguan teknis yang tidak terduga di dalam server. Tim IT '.school_short_name().' sedang berusaha memperbaikinya. Silakan coba muat ulang halaman beberapa saat lagi.')

@section('buttons')
<div class="space-y-4 sm:space-y-0 sm:space-x-4 flex flex-col sm:flex-row justify-center" style="display: flex; gap: 1rem; justify-content: center; align-items: center;">
    <a href="{{ route('helpdesk.tickets.create') }}" class="px-8 py-4 bg-white border border-gray-100 rounded-2xl font-black text-sm text-gray-600 shadow-sm hover:bg-gray-50 transition-all btn-click" style="padding: 1rem 2rem; background-color: #ffffff; border: 1px solid #f3f4f6; border-radius: 1rem; color: #4b5563; text-decoration: none; font-weight: 800; font-size: 0.875rem;">
        LAPOR KE HELPDESK
    </a>
    <a href="{{ route('dashboard') }}" class="px-8 py-4 bg-primary-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-primary-900/20 hover:scale-[1.05] active:scale-95 transition-all btn-click" style="padding: 1rem 2rem; background-color: #059669; color: #ffffff; border-radius: 1rem; text-decoration: none; font-weight: 800; font-size: 0.875rem;">
        MENU UTAMA DASHBOARD
    </a>
</div>
@endsection
