{{-- Approve / reject controls for one pending petty cash transaction ($tx). --}}
@if((int) $tx->requested_by === (int) auth()->id())
    <span class="text-xs text-gray-400">Menunggu persetujuan pejabat lain</span>
@else
    <div class="flex flex-col gap-2">
        <form method="POST" action="{{ route('treasury.petty-cash.approve', $tx->id) }}">
            @csrf
            <button type="submit" class="text-emerald-700 font-bold text-xs hover:text-emerald-900">Setujui</button>
        </form>
        <form method="POST" action="{{ route('treasury.petty-cash.reject', $tx->id) }}" class="flex items-center gap-2">
            @csrf
            <input type="text" name="reason" required placeholder="Alasan penolakan" class="w-40 rounded-md border-gray-300 shadow-sm text-xs">
            <button type="submit" class="text-rose-700 font-bold text-xs hover:text-rose-900">Tolak</button>
        </form>
    </div>
@endif
