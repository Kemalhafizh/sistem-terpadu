@extends('layouts.app')
@section('content')
@php
    $chips = ['scheduled' => 'bg-amber-100 text-amber-800', 'overdue' => 'bg-rose-100 text-rose-800', 'paid' => 'bg-emerald-100 text-emerald-800', 'cancelled' => 'bg-gray-100 text-gray-500 line-through'];
@endphp
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ school_name() }} - Kalender Pembayaran</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('treasury.vendor.calendar', ['month' => $start->copy()->subMonth()->format('Y-m')]) }}" class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-800 font-bold" aria-label="Bulan sebelumnya">&larr;</a>
            <span class="font-bold text-gray-900 dark:text-white min-w-[10rem] text-center">{{ $start->locale('id')->translatedFormat('F Y') }}</span>
            <a href="{{ route('treasury.vendor.calendar', ['month' => $start->copy()->addMonth()->format('Y-m')]) }}" class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-800 font-bold" aria-label="Bulan berikutnya">&rarr;</a>
        </div>
    </div>
    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
        {{ $totals['count'] }} pembayaran jatuh tempo bulan ini &middot;
        belum dibayar Rp {{ number_format($totals['due'], 0, ',', '.') }} &middot;
        sudah dibayar Rp {{ number_format($totals['paid'], 0, ',', '.') }}
    </p>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm overflow-x-auto">
        <table class="min-w-full table-fixed border-collapse text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                        <th class="px-2 py-2 text-xs font-bold text-gray-500 uppercase">{{ $dayName }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($weeks as $week)
                <tr>
                    @foreach($week as $day)
                    @php($inMonth = $day->isSameMonth($start))
                    <td class="align-top border border-gray-100 dark:border-gray-700 h-28 w-[14.28%] p-2 {{ $inMonth ? '' : 'bg-gray-50 dark:bg-gray-900 text-gray-400' }}">
                        <div class="text-xs font-bold {{ $day->isToday() ? 'text-indigo-600' : '' }}">{{ $day->day }}</div>
                        @if($inMonth)
                            @foreach($byDay[$day->toDateString()] ?? [] as $pay)
                                <a href="{{ route('treasury.vendor.show', $pay) }}" class="mt-1 block rounded px-1.5 py-1 text-xs {{ $chips[$pay->effectiveStatus()] ?? '' }}" title="{{ $pay->statusLabel() }}">
                                    {{ $pay->vendor_name }}<br>Rp {{ number_format($pay->amount, 0, ',', '.') }}
                                </a>
                            @endforeach
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
