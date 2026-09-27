<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected Collection $data,
        protected array $headings,
        protected array $columns
    ) {}

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        $mapped = [];
        foreach ($this->columns as $col) {
            $mapped[] = $row->{$col} ?? '-';
        }

        return $mapped;
    }
}
