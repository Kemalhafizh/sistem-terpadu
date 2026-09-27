<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DapodikExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    protected $data;

    protected $headings;

    protected $title;

    public function __construct(array $data, array $headings, string $title)
    {
        $this->data = $data;
        $this->headings = $headings;
        $this->title = $title;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }
}
