@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')

@section('icon')
    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
@endsection

@section('code', '404')
@section('message', 'Halaman Menghilang!')

@section('description', 'Waduh! Sepertinya halaman yang Anda cari pindah ke sekolah lain atau sudah dihapus dari arsip digital kita. Jangan khawatir, yuk kita balik ke dashboard.')
