<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // "Bolos", "Terlambat", "Juara Lomba"
            $table->enum('type', ['penalty', 'reward']);
            $table->integer('default_points'); // Default poin (10, 5, -20)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add category reference to student_point_logs
        Schema::table('student_point_logs', function (Blueprint $table) {
            $table->foreignId('point_category_id')->nullable()->after('type')
                ->constrained('point_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_point_logs', function (Blueprint $table) {
            $table->dropForeign(['point_category_id']);
            $table->dropColumn('point_category_id');
        });
        Schema::dropIfExists('point_categories');
    }
};
