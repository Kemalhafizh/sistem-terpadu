<?php
namespace App\Http\Controllers\Treasury;
use App\Http\Controllers\Controller;
use App\Models\VendorPayment;
use Illuminate\Http\Request;

class VendorPaymentController extends Controller {
    public function index() {
        $payments = VendorPayment::all();
        return view('treasury.vendor.index', compact('payments'));
    }
    public function create() {
        return view('treasury.vendor.create');
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'vendor_name' => 'required',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'description' => 'required',
            'payment_method' => 'required'
        ]);
        $validated['created_by'] = auth()->id() ?? 1;
        VendorPayment::create($validated);
        return redirect()->route('treasury.vendor.index');
    }
    public function show($id) {}
    public function calendar() {
        $payments = VendorPayment::all();
        return view('treasury.vendor.calendar', compact('payments'));
    }
}