<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The mobile app's visit-recording form (UksVisitScreen) captures body
     * temperature and blood pressure, but uks_visits never had columns for
     * either — recordUksVisit() was a stub that discarded the whole request
     * and returned a fabricated response instead of persisting anything.
     */
    public function up(): void
    {
        Schema::table('uks_visits', function (Blueprint $table) {
            if (!Schema::hasColumn('uks_visits', 'temperature')) {
                $table->decimal('temperature', 4, 1)->nullable()->after('treatment');
            }
            if (!Schema::hasColumn('uks_visits', 'blood_pressure')) {
                $table->string('blood_pressure', 20)->nullable()->after('temperature');
            }
        });
    }

    public function down(): void
    {
        Schema::table('uks_visits', function (Blueprint $table) {
            $cols = array_filter(['temperature', 'blood_pressure'], fn ($c) => Schema::hasColumn('uks_visits', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
