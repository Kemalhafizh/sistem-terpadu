<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->string('graduation_year');
            $table->string('current_company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('industry')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->boolean('is_willing_to_mentor')->default(false);
            $table->json('contact_preferences')->nullable();
            $table->timestamps();
        });

        Schema::create('crowdfund_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'active', 'successful', 'expired'])->default('draft');
            $table->string('cover_image_url')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('alumni_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_profile_id')->constrained('alumni_profiles')->cascadeOnDelete();
            $table->string('achievement_title');
            $table->enum('category', ['academic', 'professional', 'public_service', 'entrepreneurship']);
            $table->date('date_achieved');
            $table->text('description');
            $table->string('media_link')->nullable();
            $table->boolean('is_featured_in_hall_of_fame')->default(false);
            $table->timestamps();
        });

        Schema::create('legacy_pledges', function (Blueprint $table) {
            $table->id();
            $table->string('donor_name');
            $table->string('donor_type');
            $table->decimal('pledged_amount', 15, 2);
            $table->string('fund_designation');
            $table->date('expected_fulfillment_date');
            $table->enum('status', ['pledged', 'partially_fulfilled', 'fulfilled', 'cancelled'])->default('pledged');
            $table->text('naming_recognition_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_pledges');
        Schema::dropIfExists('alumni_achievements');
        Schema::dropIfExists('crowdfund_campaigns');
        Schema::dropIfExists('alumni_profiles');
    }
};
