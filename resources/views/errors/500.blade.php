@extends('errors.layout')

@section('title', 'Kesalahan Server')

@section('icon')
    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
@endsection

@section('code', '500')
@section('message', 'Aplikasi Sedang Lelah')

@section('description', 'Ada gangguan teknis yang tidak terduga di dalam server. Tim IT Sultan Agung 1 sedang berusaha memperbaikinya. Silakan coba muat ulang halaman beberapa saat lagi.')
