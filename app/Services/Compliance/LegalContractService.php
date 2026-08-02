<?php

namespace App\Services\Compliance;

use App\Models\LegalContract;

class LegalContractService
{
    public function registerContract(string $num, string $title, string $vendor, float $val, string $start, string $end): LegalContract
    {
        return LegalContract::create([
            'contract_number' => $num,
            'title' => $title,
            'vendor_name' => $vendor,
            'contract_value' => $val,
            'start_date' => $start,
            'end_date' => $end,
            'status' => 'active',
        ]);
    }
}
