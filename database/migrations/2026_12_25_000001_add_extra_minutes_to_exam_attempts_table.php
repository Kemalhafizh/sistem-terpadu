<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('exam_attempts', 'extra_minutes')) {
                $table->integer('extra_minutes')->default(0)->after('is_auto_submitted');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('exam_attempts', 'extra_minutes')) {
                $table->dropColumn('extra_minutes');
            }
        });
    }
};
