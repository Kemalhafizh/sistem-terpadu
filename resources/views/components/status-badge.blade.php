{{-- Premium Status Badge Component --}}
@props([
    'status' => 'neutral', // default status category, or auto-detected label
    'showDot' => false
])

@php
    $statusString = strtolower(trim($status));
    
    // Map of commonly used status labels to color categories
    $type = 'neutral';
    if (in_array($statusString, ['aktif', 'active', 'lunas', 'hadir', 'diterima', 'success', 'selesai', 'approved', 'published', 'sudah', 'terbuka'])) {
        $type = 'success';
    } elseif (in_array($statusString, ['nonaktif', 'non-aktif', 'inactive', 'alpa', 'ditolak', 'danger', 'gagal', 'overdue', 'expired', 'belum', 'ditutup', 'banned'])) {
        $type = 'danger';
    } elseif (in_array($statusString, ['pending', 'revisi', 'warning', 'izin', 'sakit', 'proses', 'draft', 'revisi-diperlukan'])) {
        $type = 'warning';
    } elseif (in_array($statusString, ['info', 'detail', 'kontrak', 'dibaca'])) {
        $type = 'info';
    } elseif (in_array($statusString, ['primary', 'biru', 'admin', 'guru', 'staff'])) {
        $type = 'primary';
    } else {
        // Fallback or explicit override
        $type = in_array($statusString, ['success', 'danger', 'warning', 'info', 'primary', 'neutral']) ? $statusString : 'neutral';
    }

    $colorMap = [
        'success' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-250 dark:border-emerald-900/40',
        'danger' => 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 border-red-250 dark:border-red-900/40',
        'warning' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-250 dark:border-amber-900/40',
        'info' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border-blue-250 dark:border-blue-900/40',
        'primary' => 'bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300 border-primary-250 dark:border-primary-900/40',
        'neutral' => 'bg-slate-50 text-slate-750 dark:bg-slate-800/80 dark:text-slate-300 border-slate-200 dark:border-slate-700/60'
    ];

    $style = $colorMap[$type] ?? $colorMap['neutral'];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $style }}">
    @if($showDot)
        <span class="w-1.5 h-1.5 rounded-full mr-1.5 shrink-0
            {{ $type === 'success' ? 'bg-emerald-500' : '' }}
            {{ $type === 'danger' ? 'bg-red-500' : '' }}
            {{ $type === 'warning' ? 'bg-amber-500' : '' }}
            {{ $type === 'info' ? 'bg-blue-500' : '' }}
            {{ $type === 'primary' ? 'bg-primary-500' : '' }}
            {{ $type === 'neutral' ? 'bg-slate-400' : '' }}">
        </span>
    @endif
    {{ $slot->isEmpty() ? $status : $slot }}
</span>
