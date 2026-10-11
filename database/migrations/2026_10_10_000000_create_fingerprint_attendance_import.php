<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * FASE 119.6.1: staff attendance comes from the fingerprint machine. Each
 * employee carries the ID the machine knows them by; each upload is recorded
 * with what it did, including the machine IDs nobody is mapped to.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('fingerprint_id', 32)->nullable()->unique()->after('employee_number');
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            // 'fingerprint' for rows built from a machine log; null for ones entered by hand.
            $table->string('source', 20)->nullable()->after('status');
        });

        Schema::create('fingerprint_imports', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('rows_read')->default(0);
            $table->unsignedInteger('scans_matched')->default(0);
            $table->unsignedInteger('attendances_created')->default(0);
            $table->unsignedInteger('attendances_updated')->default(0);
            $table->unsignedInteger('attendances_unchanged')->default(0);
            $table->unsignedInteger('leave_days_kept')->default(0);
            // {"machine id": number of scans} for IDs no employee is mapped to.
            $table->json('unknown_ids')->nullable();
            // [{"line": 12, "reason": "..."}] for rows that could not be read.
            $table->json('invalid_rows')->nullable();
            $table->date('first_date')->nullable();
            $table->date('last_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerprint_imports');
        Schema::table('employee_attendances', fn (Blueprint $table) => $table->dropColumn('source'));
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['fingerprint_id']);
            $table->dropColumn('fingerprint_id');
        });
    }
};
