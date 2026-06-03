<?php

namespace App\Exports;

use App\Models\Billing;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BillingsExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @return Collection
     */
    public function query()
    {
        return Billing::query()->with(['student.user', 'category', 'academicYear']);
    }

    public function headings(): array
    {
        return [
            'Nomor Tagihan',
            'Siswa',
            'Kategori',
            'Tahun Ajaran',
            'Jumlah',
            'Sisa Bayar',
            'Jatuh Tempo',
            'Status',
        ];
    }

    public function map($billing): array
    {
        return [
            '#'.$billing->id,
            $billing->student->user->name,
            $billing->category->name,
            $billing->academicYear->year.' - '.$billing->academicYear->semester,
            $billing->amount,
            $billing->remaining_balance,
            $billing->due_date,
            strtoupper($billing->status),
        ];
    }
}
