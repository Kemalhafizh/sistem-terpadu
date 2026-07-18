<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CanteenMerchant;
use App\Models\CanteenTransaction;
use App\Models\Student;
use App\Models\User;
use App\Models\WadiahAccount;
use App\Services\WadiahService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class CanteenController extends Controller
{
    protected $wadiahService;

    public function __construct(WadiahService $wadiahService)
    {
        $this->wadiahService = $wadiahService;
    }

    /**
     * List all merchants and render registration page (Admin).
     */
    public function merchants()
    {
        $merchants = CanteenMerchant::with('owner')->get();
        // Users who can own merchants (we can use users with role 'merchant' or 'guru' / 'staf_tu' / 'admin' for demo)
        $potentialOwners = User::whereIn('role', ['staf_tu', 'guru', 'admin'])->get();

        return view('finance.canteen.merchants', compact('merchants', 'potentialOwners'));
    }

    /**
     * Store new canteen merchant.
     */
    public function storeMerchant(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'owner_user_id' => 'required|exists:users,id',
        ]);

        // Change target user role to 'merchant' for correct authorization if it is not already admin
        $owner = User::findOrFail($request->owner_user_id);
        if ($owner->role !== 'admin' && $owner->role !== 'superadmin') {
            $owner->update(['role' => 'merchant']);
        }

        CanteenMerchant::create([
            'name' => $request->name,
            'owner_user_id' => $request->owner_user_id,
            'is_active' => true,
        ]);

        return back()->with('success', 'Merchant kantin baru berhasil didaftarkan.');
    }

    /**
     * Toggle merchant active status.
     */
    public function toggleMerchant($id)
    {
        $merchant = CanteenMerchant::findOrFail($id);
        $merchant->update([
            'is_active' => ! $merchant->is_active,
        ]);

        return back()->with('success', 'Status merchant ' . $merchant->name . ' berhasil diperbarui.');
    }

    /**
     * Merchant Dashboard - sales summary and transaction log.
     */
    public function merchantDashboard()
    {
        $user = auth()->user();
        
        // Find merchant belonging to current user
        $merchant = CanteenMerchant::where('owner_user_id', $user->id)->first();
        
        if (! $merchant && ($user->role === 'admin' || $user->role === 'superadmin')) {
            // Admin can view the first merchant for demo purposes
            $merchant = CanteenMerchant::first();
        }

        if (! $merchant) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak terdaftar sebagai merchant kantin.');
        }

        $todaySales = CanteenTransaction::where('canteen_merchant_id', $merchant->id)
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');

        $totalSales = CanteenTransaction::where('canteen_merchant_id', $merchant->id)
            ->sum('total_amount');

        $recentTransactions = CanteenTransaction::with('wadiahTransaction.account.student.user')
            ->where('canteen_merchant_id', $merchant->id)
            ->latest()
            ->take(15)
            ->get();

        // 7-day sales chart
        $chartLabels = [];
        $salesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->translatedFormat('d M');
            $salesData[] = (float) CanteenTransaction::where('canteen_merchant_id', $merchant->id)
                ->whereDate('created_at', $date)
                ->sum('total_amount');
        }

        $chartData = [
            'labels' => $chartLabels,
            'sales' => $salesData,
        ];

        return view('finance.canteen.dashboard', compact('merchant', 'todaySales', 'totalSales', 'recentTransactions', 'chartData'));
    }

    /**
     * Show POS Terminal interface for cashier merchant.
     */
    public function posTerminal()
    {
        $user = auth()->user();
        $merchant = CanteenMerchant::where('owner_user_id', $user->id)->first();

        if (! $merchant && ($user->role === 'admin' || $user->role === 'superadmin')) {
            $merchant = CanteenMerchant::first();
        }

        if (! $merchant) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak terdaftar sebagai merchant kantin.');
        }

        // List of menu items for quick POS insertion
        $menuItems = [
            ['name' => 'Nasi Goreng Telur', 'price' => 12000, 'category' => 'Makanan'],
            ['name' => 'Mie Goreng Ayam', 'price' => 10000, 'category' => 'Makanan'],
            ['name' => 'Soto Ayam', 'price' => 12000, 'category' => 'Makanan'],
            ['name' => 'Bakso Kuah', 'price' => 12000, 'category' => 'Makanan'],
            ['name' => 'Gorengan Tempe/Tahu', 'price' => 1000, 'category' => 'Camilan'],
            ['name' => 'Roti Bakar', 'price' => 6000, 'category' => 'Camilan'],
            ['name' => 'Es Teh Manis', 'price' => 3000, 'category' => 'Minuman'],
            ['name' => 'Es Jeruk', 'price' => 4000, 'category' => 'Minuman'],
            ['name' => 'Air Mineral', 'price' => 3000, 'category' => 'Minuman'],
        ];

        return view('finance.canteen.pos', compact('merchant', 'menuItems'));
    }

    /**
     * Process cashless payment using Wadiah.
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'student_identifier' => 'required|string', // NIS or decrypted code
            'items' => 'required|array|min:1',
            'total_amount' => 'required|numeric|min:100',
        ]);

        $identifier = $request->student_identifier;
        
        // Find student by NIS or UUID
        $student = Student::where('nis', $identifier)
            ->orWhere('uuid', $identifier)
            ->first();

        if (! $student) {
            // Check if it's dynamic encrypted format: e.g. "SISTA-NIS-TIMESTAMP"
            if (str_starts_with($identifier, 'SISTA-')) {
                $parts = explode('-', $identifier);
                if (count($parts) >= 3) {
                    $nis = $parts[1];
                    $student = Student::where('nis', $nis)->first();
                }
            }
        }

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa dengan identitas tersebut tidak ditemukan.'
            ], 404);
        }

        $account = WadiahAccount::where('student_id', $student->id)->first();
        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tabungan wadiah siswa belum terdaftar.'
            ], 404);
        }

        try {
            $merchantUser = auth()->user();
            $merchant = CanteenMerchant::where('owner_user_id', $merchantUser->id)->first();
            
            if (! $merchant && ($merchantUser->role === 'admin' || $merchantUser->role === 'superadmin')) {
                $merchant = CanteenMerchant::first();
                if ($merchant) {
                    $merchantUser = $merchant->owner;
                }
            }

            if (! $merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses sebagai merchant kantin.'
                ], 403);
            }

            $tx = $this->wadiahService->purchase(
                $account,
                (float) $request->total_amount,
                $merchantUser->id,
                $request->items
            );

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses!',
                'receipt' => [
                    'ref_number' => $tx->reference_number,
                    'student_name' => $student->user->name,
                    'nis' => $student->nis,
                    'classroom' => $student->classroom->name ?? '-',
                    'total' => $tx->amount,
                    'balance_remaining' => $tx->current_balance,
                    'time' => $tx->created_at->format('d/m/Y H:i:s'),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Settlement report for merchant payout.
     */
    public function settlementReport()
    {
        $user = auth()->user();
        $merchant = CanteenMerchant::where('owner_user_id', $user->id)->first();

        if (! $merchant && ($user->role === 'admin' || $user->role === 'superadmin')) {
            $merchant = CanteenMerchant::first();
        }

        if (! $merchant) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak terdaftar sebagai merchant.');
        }

        $settlements = CanteenTransaction::with('wadiahTransaction')
            ->where('canteen_merchant_id', $merchant->id)
            ->latest()
            ->paginate(20);

        return view('finance.canteen.settlement', compact('merchant', 'settlements'));
    }
}
