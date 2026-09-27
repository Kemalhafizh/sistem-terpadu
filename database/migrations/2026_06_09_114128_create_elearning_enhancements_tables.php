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
        Schema::table('elearning_classes', function (Blueprint $table) {
            $table->boolean('is_archived')->default(false)->after('description');
        });

        Schema::create('elearning_announcements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('elearning_class_id')->constrained('elearning_classes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Creator (teacher)
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('elearning_material_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('elearning_material_id')->constrained('elearning_materials')->cascadeOnDelete();
            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->unique(['student_id', 'elearning_material_id']);
        });

        Schema::create('elearning_material_comments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('elearning_material_id')->constrained('elearning_materials')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Commenter (student/teacher)
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elearning_material_comments');
        Schema::dropIfExists('elearning_material_views');
        Schema::dropIfExists('elearning_announcements');

        Schema::table('elearning_classes', function (Blueprint $table) {
            $table->dropColumn('is_archived');
        });
    }
};
