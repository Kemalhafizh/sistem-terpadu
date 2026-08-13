<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{BankAccount, BankStatement, BankTransaction, PettyCashFund, PettyCashTransaction, VendorPayment, User};

class TreasurySeeder extends Seeder {
    public function run() {
        $user = User::first() ?? User::factory()->create();

        $bsi = BankAccount::create(['bank_name' => 'BSI', 'account_number' => '12345', 'account_name' => 'Sekolah', 'account_type' => 'giro', 'current_balance' => 50000000, 'is_primary' => true]);
        BankAccount::create(['bank_name' => 'BCA', 'account_number' => '54321', 'account_name' => 'Sekolah', 'account_type' => 'tabungan', 'current_balance' => 20000000]);
        BankAccount::create(['bank_name' => 'Mandiri', 'account_number' => '98765', 'account_name' => 'Sekolah', 'account_type' => 'giro', 'current_balance' => 30000000]);

        $statement = BankStatement::create(['bank_account_id' => $bsi->id, 'statement_date' => now(), 'period_start' => now()->startOfMonth(), 'period_end' => now()->endOfMonth(), 'file_path' => 'dummy.csv', 'total_transactions' => 20, 'matched_count' => 10, 'unmatched_count' => 10, 'status' => 'has_discrepancies', 'imported_by' => $user->id]);

        for($i=1; $i<=10; $i++) BankTransaction::create(['bank_statement_id' => $statement->id, 'transaction_date' => now()->subDays($i), 'description' => "Match Tx $i", 'debit' => 1000, 'match_status' => 'auto_matched']);
        for($i=1; $i<=10; $i++) BankTransaction::create(['bank_statement_id' => $statement->id, 'transaction_date' => now()->subDays($i), 'description' => "Unmatch Tx $i", 'debit' => 2000, 'match_status' => 'unmatched']);

        $tu = PettyCashFund::create(['fund_name' => 'Kas Kecil TU', 'custodian_id' => $user->id, 'imprest_amount' => 5000000, 'current_balance' => 3000000]);
        $lab = PettyCashFund::create(['fund_name' => 'Kas Kecil Lab', 'custodian_id' => $user->id, 'imprest_amount' => 2000000, 'current_balance' => 1500000]);

        for($i=1; $i<=10; $i++) PettyCashTransaction::create(['fund_id' => $tu->id, 'type' => 'pengeluaran', 'amount' => 100000, 'description' => "Beli ATK $i", 'transaction_date' => now(), 'category' => 'atk', 'approval_status' => 'approved', 'requested_by' => $user->id]);

        $vendors = ['PLN', 'PDAM', 'Telkom', 'Catering', 'Cleaning Service'];
        foreach($vendors as $v) VendorPayment::create(['vendor_name' => $v, 'description' => "Bayar $v", 'amount' => rand(100, 500) * 1000, 'due_date' => now()->addDays(rand(1,10)), 'payment_method' => 'transfer', 'status' => 'scheduled', 'created_by' => $user->id]);
    }
}