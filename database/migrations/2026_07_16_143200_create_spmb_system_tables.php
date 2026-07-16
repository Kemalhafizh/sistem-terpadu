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
        Schema::create('spmb_periods', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('name'); // e.g. "SPMB 2026/2027"
            $table->integer('total_quota')->default(100);
            $table->enum('status', ['draft', 'open', 'closed', 'announced'])->default('draft');
            $table->timestamps();
        });

        Schema::create('spmb_waves', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('spmb_period_id')->constrained('spmb_periods')->cascadeOnDelete();
            $table->string('name'); // e.g. "Gelombang 1"
            $table->date('registration_start');
            $table->date('registration_end');
            $table->date('announcement_date')->nullable();
            $table->integer('quota')->default(50);
            $table->decimal('registration_fee', 12, 2)->default(0);
            $table->enum('status', ['upcoming', 'open', 'closed', 'announced'])->default('upcoming');
            $table->timestamps();
        });

        Schema::create('spmb_tracks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('spmb_period_id')->constrained('spmb_periods')->cascadeOnDelete();
            $table->string('name'); // e.g. "Reguler", "Prestasi", "Tahfidz"
            $table->integer('quota')->default(30);
            $table->json('scoring_weights')->nullable(); // {"rapor": 40, "tes": 30, ...}
            $table->text('requirements')->nullable();
            $table->timestamps();
        });

        // Modifikasi registrations
        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('spmb_wave_id')->nullable()->constrained('spmb_waves')->nullOnDelete();
            $table->foreignId('spmb_track_id')->nullable()->constrained('spmb_tracks')->nullOnDelete();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->text('address')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('document_skhun_path')->nullable();
            $table->string('document_akta_path')->nullable();
            $table->json('rapor_scores')->nullable(); // {"ipa": 85, "ips": 80, ...}
            $table->json('achievement_data')->nullable(); // [{"title": "MTQ", ...}]
            $table->json('tahfidz_data')->nullable(); // {"juz_count": 5}
            $table->decimal('entrance_test_score', 5, 2)->nullable();
            $table->decimal('total_score', 5, 2)->nullable();
            $table->integer('rank')->nullable();
            $table->enum('document_status', ['incomplete', 'submitted', 'verified', 'rejected'])->default('submitted');
            $table->text('admin_notes')->nullable();
            $table->boolean('has_paid_registration')->default(false);
            $table->boolean('has_reregistered')->default(false);
        });

        Schema::create('spmb_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->string('document_type'); // 'kk', 'certificate', 'skhun', 'akta', 'photo', 'achievement'
            $table->string('file_path');
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_documents');

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['spmb_wave_id']);
            $table->dropForeign(['spmb_track_id']);
            $table->dropColumn([
                'spmb_wave_id',
                'spmb_track_id',
                'gender',
                'birth_date',
                'birth_place',
                'address',
                'parent_name',
                'photo_path',
                'document_skhun_path',
                'document_akta_path',
                'rapor_scores',
                'achievement_data',
                'tahfidz_data',
                'entrance_test_score',
                'total_score',
                'rank',
                'document_status',
                'admin_notes',
                'has_paid_registration',
                'has_reregistered',
            ]);
        });

        Schema::dropIfExists('spmb_tracks');
        Schema::dropIfExists('spmb_waves');
        Schema::dropIfExists('spmb_periods');
    }
};
