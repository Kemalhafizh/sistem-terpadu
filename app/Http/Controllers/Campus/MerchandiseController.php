<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\MerchandiseItem;
use App\Models\MerchandiseOrder;
use App\Services\Campus\MerchandiseService;
use Illuminate\Http\Request;

class MerchandiseController extends Controller
{
    protected $merchandiseService;

    public function __construct(MerchandiseService $merchandiseService)
    {
        $this->merchandiseService = $merchandiseService;
    }

    public function catalog()
    {
        $items = MerchandiseItem::where('is_active', true)->get();
        return view('store.catalog', compact('items'));
    }

    public function adminInventory()
    {
        $items = MerchandiseItem::all();
        return view('store.inventory', compact('items'));
    }

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'item_name' => 'required|string',
            'sku' => 'required|string|unique:merchandise_items',
            'category' => 'required|in:uniform,stationery,merchandise',
            'price' => 'required|numeric',
            'current_stock' => 'required|integer',
        ]);
        
        MerchandiseItem::create($data);
        return back()->with('success', 'Item created successfully');
    }

    public function cart()
    {
        // Simple cart view representation
        return view('store.catalog'); // redirecting to catalog for simple flow
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'total_amount' => 'required|numeric',
            'order_items' => 'required|array',
        ]);
        
        $data['student_id'] = auth()->id() ?? 1; // Fallback for testing
        
        $this->merchandiseService->createOrder($data);
        return back()->with('success', 'Order placed successfully');
    }

    public function fulfillment()
    {
        $orders = MerchandiseOrder::where('fulfillment_status', '!=', 'completed')->get();
        return view('store.fulfillment', compact('orders'));
    }
}
