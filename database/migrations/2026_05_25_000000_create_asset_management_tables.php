<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Alter tabel assets (tambahkan kolom-kolom baru)
        Schema::table('assets', function (Blueprint $table) {
            $table->string('qr_code')->nullable()->unique()->after('condition');
            $table->integer('useful_life_years')->nullable()->after('purchase_price');
            $table->boolean('is_borrowable')->default(false)->after('useful_life_years');
        });

        // 2. Tabel Peminjaman Aset (Asset Loans)
        Schema::create('asset_loans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('borrower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('borrowed_at')->nullable();
            $table->dateTime('expected_return_at');
            $table->dateTime('returned_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'borrowed', 'returned', 'overdue', 'lost', 'rejected'])->default('pending');
            $table->text('purpose')->nullable();
            $table->text('return_notes')->nullable();
            $table->enum('return_condition', ['good', 'damaged', 'broken'])->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'status']);
            $table->index(['borrower_id', 'status']);
        });

        // 3. Tabel Perawatan Aset (Asset Maintenances)
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->date('maintenance_date');
            $table->date('next_maintenance_date')->nullable();
            $table->enum('type', ['preventif', 'korektif', 'upgrade'])->default('korektif');
            $table->text('description');
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('vendor')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed'])->default('completed');
            $table->foreignId('handled_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['asset_id', 'maintenance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('asset_loans');
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['qr_code', 'useful_life_years', 'is_borrowable']);
        });
    }
};
