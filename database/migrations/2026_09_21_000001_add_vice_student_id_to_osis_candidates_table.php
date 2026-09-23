<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('osis_candidates', function (Blueprint $table) {
            $table->foreignId('vice_student_id')->nullable()->after('student_id')
                ->constrained('students')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('osis_candidates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vice_student_id');
        });
    }
};
