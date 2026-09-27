<?php

namespace App\Services\Campus;

use App\Models\MerchandiseItem;
use App\Models\MerchandiseOrder;
use Illuminate\Support\Str;

class MerchandiseService
{
    public function createOrder(array $data)
    {
        $data['order_number'] = 'ORD-'.strtoupper(Str::random(6));
        $data['payment_status'] = 'unpaid';
        $data['fulfillment_status'] = 'processing';

        $order = MerchandiseOrder::create($data);

        // Update stock based on order items
        foreach ($data['order_items'] as $item) {
            $this->updateStock($item['id'], -1 * $item['quantity']);
        }

        return $order;
    }

    public function updateStock($itemId, $quantityChange)
    {
        $item = MerchandiseItem::findOrFail($itemId);
        $item->increment('current_stock', $quantityChange);

        return $item;
    }

    public function completeFulfillment(MerchandiseOrder $order)
    {
        $order->update([
            'fulfillment_status' => 'completed',
        ]);

        return $order;
    }
}
