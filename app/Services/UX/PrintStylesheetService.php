<?php

namespace App\Services\UX;

class PrintStylesheetService
{
    public function generatePrintLayout(string $title, string $paperSize = 'A4', string $orientation = 'portrait'): array
    {
        return [
            'title' => $title,
            'paper_size' => $paperSize,
            'orientation' => $orientation,
            'watermark' => school_name(),
            'show_header_footer' => true,
            'margin' => '15mm',
        ];
    }
}
