<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category->name ?? 'N/A',
            'month' => $this->month,
            'amount' => (float) $this->amount,
            'amount_formatted' => 'Rp '.number_format($this->amount, 0, ',', '.'),
            'due_date' => $this->due_date,
            'status' => $this->status,
            'remaining_balance' => (float) $this->remaining_balance,
            'payment_url' => $this->payment_url,
        ];
    }
}
