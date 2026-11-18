@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">{{ $plan->title }} ({{ $plan->fiscal_year }})</h1>
            <p class="text-xs text-gray-500 mt-1">{{ __('Status') }}: <span class="uppercase font-bold text-emerald-600">{{ __('budget.status.'.$plan->status) }}</span> | {{ __('Disusun oleh') }}: {{ $plan->creator->name }}@if($plan->approver) | {{ __('Disetujui oleh') }}: {{ $plan->approver->name }}@endif</p>
        </div>
        <div class="flex gap-2">
            @php($canPrepare = \App\Support\Access\RouteAccess::allowsRoute(auth()->user(), 'financial.budget.submit'))
            @if($plan->isEditable() && $canPrepare)
            <form action="{{ route('financial.budget.submit', $plan->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs shadow-md">{{ __('Ajukan ke Kepala Sekolah') }}</button>
            </form>
            @elseif($plan->status === 'submitted' && (int) $plan->created_by !== auth()->id() && \App\Support\Access\RouteAccess::allowsRoute(auth()->user(), 'financial.budget.approve'))
            <form action="{{ route('financial.budget.approve', $plan->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md">{{ __('Setujui RKAS') }}</button>
            </form>
            @endif
            <a href="{{ route('financial.budget.dashboard') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
        </div>
    </div>

    @foreach(['success' => 'bg-emerald-50 text-emerald-800 border-emerald-200', 'error' => 'bg-rose-50 text-rose-800 border-rose-200'] as $flash => $classes)
        @if(session($flash))
        <div class="p-4 border rounded-2xl text-sm font-semibold {{ $classes }}">{{ session($flash) }}</div>
        @endif
    @endforeach

    <!-- Form Add Item -->
    @if($plan->isEditable() && $canPrepare)
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Tambah Item Pos Anggaran</h3>
        <form action="{{ route('financial.budget.store_line_item', $plan->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kategori</label>
                <select name="category" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-sm font-semibold">
                    <option value="revenue">Pendapatan</option>
                    <option value="expense">Belanja / Pengeluaran</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sub Kategori</label>
                <input type="text" name="sub_category" placeholder="misal: SPP Siswa, Gaji Guru" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kode Akun (COA)</label>
                <input type="text" name="account_code" placeholder="4-1000" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kuartal / Periode</label>
                <select name="quarter" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-sm font-semibold">
                    <option value="annual">Tahunan (Annual)</option>
                    <option value="Q1">Q1 (Triwulan I)</option>
                    <option value="Q2">Q2 (Triwulan II)</option>
                    <option value="Q3">Q3 (Triwulan III)</option>
                    <option value="Q4">Q4 (Triwulan IV)</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi Pos Anggaran</label>
                <input type="text" name="description" placeholder="Penerimaan SPP siswa kelas X, XI, XII" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Anggaran Rencana (Rp)</label>
                <input type="number" name="planned_amount" placeholder="500000000" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-4 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md">+ {{ __('Tambah Pos') }}</button>
            </div>
        </form>
        <p class="text-xs text-gray-500">{{ __('Bila kode akun ada di bagan akun, realisasinya dihitung otomatis dari buku besar. Selain itu, realisasi dicatat manual di tabel di bawah.') }}</p>
    </div>
    @endif

    <!-- Line Items Table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Rincian Pos Anggaran RAPBS</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Tipe</th>
                    <th class="py-3 px-4">Kode COA</th>
                    <th class="py-3 px-4">Sub Kategori</th>
                    <th class="py-3 px-4">Deskripsi</th>
                    <th class="py-3 px-4">Periode</th>
                    <th class="py-3 px-4">Rencana</th>
                    <th class="py-3 px-4">Aktual</th>
                    <th class="py-3 px-4">Variansi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($report['items'] as $line)
                @php($item = $line['item'])
                <tr>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 text-xs font-bold rounded {{ $item->category === 'revenue' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $item->category === 'revenue' ? __('Pendapatan') : __('Belanja') }}
                        </span>
                    </td>
                    <td class="py-3 px-4 font-mono font-bold">{{ $item->account_code }}</td>
                    <td class="py-3 px-4 font-semibold">{{ $item->sub_category }}</td>
                    <td class="py-3 px-4">{{ $item->description }}</td>
                    <td class="py-3 px-4 text-xs font-bold">{{ $item->quarter }}</td>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">Rp {{ number_format($item->planned_amount, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 font-semibold text-emerald-600">
                        @if($line['source'] === 'ledger')
                            Rp {{ number_format($line['actual'], 0, ',', '.') }}
                            <span class="block text-[10px] text-gray-400 font-bold">{{ __('dari buku besar') }}</span>
                        @elseif($canPrepare)
                            <form action="{{ route('financial.budget.realization', [$plan->id, $item->id]) }}" method="POST" class="flex items-center gap-1">
                                @csrf
                                @method('PUT')
                                <input type="number" name="actual_amount" min="0" value="{{ $line['actual'] === null ? '' : (int) $line['actual'] }}" placeholder="–" class="w-32 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-2 py-1 text-xs font-mono">
                                <button type="submit" class="text-[10px] font-bold text-emerald-700 hover:underline">{{ __('Simpan') }}</button>
                            </form>
                        @else
                            {{ $line['actual'] === null ? '–' : 'Rp '.number_format($line['actual'], 0, ',', '.') }}
                        @endif
                    </td>
                    <td class="py-3 px-4 font-bold {{ ($line['variance'] ?? 0) >= 0 ? 'text-teal-600' : 'text-rose-600' }}">
                        {{ $line['variance'] === null ? '–' : 'Rp '.number_format($line['variance'], 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-6 text-center text-gray-400">{{ __('Belum ada pos anggaran.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
