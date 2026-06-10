@extends('errors.layout')

@section('title', 'Akses Tidak Sah')

@section('icon')
    <svg class="w-12 h-12" width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
@endsection

@section('code', '401')
@section('message', 'Akses Tidak Sah')
@section('description', 'Maaf, identitas Anda tidak dapat diverifikasi oleh sistem. Silakan login atau login kembali menggunakan akun yang valid.')
