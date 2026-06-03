<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class GenericExport implements FromCollection, WithHeadings, WithMapping
{
    protected $collection;
    protected $headings;
    protected $mapCallback;

    public function __construct(Collection $collection, array $headings, callable $mapCallback)
    {
        $this->collection = $collection;
        $this->headings = $headings;
        $this->mapCallback = $mapCallback;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return ($this->mapCallback)($row);
    }
}
