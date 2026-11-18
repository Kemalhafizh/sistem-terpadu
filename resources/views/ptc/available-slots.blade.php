@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Booking Slot Konsultasi Orang Tua: {{ $session->title }}</h1>
            <p class="text-xs text-gray-500 mt-1">Tanggal: {{ $session->date->format('d M Y') }} ({{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB)</p>
        </div>
        <a href="{{ route('growth.ptc.sessions') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    @foreach(['success' => 'bg-emerald-50 text-emerald-800 border-emerald-200', 'error' => 'bg-rose-50 text-rose-800 border-rose-200'] as $flash => $classes)
        @if(session($flash))
        <div class="p-4 border rounded-2xl text-sm font-semibold {{ $classes }}">{{ session($flash) }}</div>
        @endif
    @endforeach

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Daftar Slot Waktu Konsultasi Guru</h3>

        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Guru / Konselor</th>
                    <th class="py-3 px-4">Jam Waktu Slot</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-right">Aksi Booking Orang Tua</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($slots as $sl)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $sl->teacher->name }}</td>
                    <td class="py-3 px-4 font-mono text-xs font-bold text-indigo-600">{{ substr($sl->start_time, 0, 5) }} - {{ substr($sl->end_time, 0, 5) }} WIB</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase
                            {{ $sl->status === 'available' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ __('ptc.status.'.$sl->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        @if($sl->status === 'available' && $children->isNotEmpty())
                        <form action="{{ route('growth.ptc.book_slot', $sl->id) }}" method="POST" class="flex gap-2 justify-end items-center">
                            @csrf
                            <select name="student_id" required class="bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-lg px-2 py-1 text-xs font-semibold">
                                @foreach($children as $child)
                                <option value="{{ $child->user_id }}">{{ $child->user?->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold">{{ __('Pesan') }} &rarr;</button>
                        </form>
                        @elseif($sl->status === 'booked' && (int) $sl->parent_id === auth()->id())
                        <form action="{{ route('growth.ptc.cancel_slot', $sl->id) }}" method="POST" class="inline">
                            @csrf
                            <span class="text-xs font-bold text-indigo-600 mr-2">{{ __('Pesanan Anda: :name', ['name' => $sl->student?->name]) }}</span>
                            <button type="submit" class="text-xs font-bold text-rose-600 hover:underline">{{ __('Batalkan') }}</button>
                        </form>
                        @elseif($sl->status === 'booked' && $children->isEmpty())
                        {{-- Staff see who booked; other parents don't. --}}
                        <span class="text-xs font-bold text-gray-500">{{ $sl->parent?->name }} — {{ $sl->student?->name }}</span>
                        @elseif($sl->status === 'booked')
                        <span class="text-xs font-bold text-gray-400">{{ __('Terisi') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-400">Belum ada slot waktu konsultasi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
