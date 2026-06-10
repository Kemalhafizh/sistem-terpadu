@extends('errors.layout')

@section('title', 'Pembayaran Diperlukan')

@section('icon')
    <svg class="w-12 h-12" width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
@endsection

@section('code', '402')
@section('message', 'Pembayaran Diperlukan')
@section('description', 'Akses ke halaman ini memerlukan pembayaran atau penyelesaian transaksi yang sah terlebih dahulu.')
