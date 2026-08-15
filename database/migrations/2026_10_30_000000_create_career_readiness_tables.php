<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('industry_sector');
            $table->text('address');
            $table->string('pic_name');
            $table->string('pic_contact');
            $table->boolean('is_verified_partner')->default(true);
            $table->timestamps();
        });

        Schema::create('internship_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('internship_companies')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['pending', 'approved', 'rejected', 'active', 'completed'])->default('pending');
            $table->decimal('final_score', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('internship_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('internship_applications')->cascadeOnDelete();
            $table->date('journal_date');
            $table->text('activities');
            $table->text('reflection')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('university_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('university_name');
            $table->string('major');
            $table->decimal('passing_grade_index', 5, 2)->default(80.00);
            $table->json('alumni_acceptance_history')->nullable();
            $table->string('city')->nullable();
            $table->enum('accreditation', ['A', 'B', 'C', 'Unggul', 'Baik_Sekali', 'Baik'])->default('Unggul');
            $table->timestamps();
        });

        Schema::create('college_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('university_catalog_id')->constrained('university_catalogs')->cascadeOnDelete();
            $table->enum('admission_type', ['snbp', 'snbt', 'mandiri', 'international']);
            $table->enum('status', ['preparing', 'submitted', 'accepted', 'rejected', 'waitlisted'])->default('preparing');
            $table->foreignId('counselor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('student_showcases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('project_title');
            $table->text('description');
            $table->string('cover_image_url')->nullable();
            $table->json('media_urls')->nullable();
            $table->boolean('is_public')->default(true);
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });

        Schema::create('showcase_endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showcase_id')->constrained('student_showcases')->cascadeOnDelete();
            $table->foreignId('endorser_id')->constrained('users')->cascadeOnDelete();
            $table->text('endorsement_text');
            $table->timestamps();
        });

        Schema::create('student_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('savings_target', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mini_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('student_wallets')->cascadeOnDelete();
            $table->enum('transaction_type', ['deposit', 'withdrawal', 'payment', 'transfer']);
            $table->decimal('amount', 15, 2);
            $table->string('reference_code')->unique();
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_bank_transactions');
        Schema::dropIfExists('student_wallets');
        Schema::dropIfExists('showcase_endorsements');
        Schema::dropIfExists('student_showcases');
        Schema::dropIfExists('college_applications');
        Schema::dropIfExists('university_catalogs');
        Schema::dropIfExists('internship_journals');
        Schema::dropIfExists('internship_applications');
        Schema::dropIfExists('internship_companies');
    }
};
