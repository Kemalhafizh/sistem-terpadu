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
        Schema::table('alumni_data', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('phone_number')->nullable()->after('contact_info');
            $table->string('email')->nullable()->after('phone_number');
            $table->string('city')->nullable()->after('email');
            $table->string('province')->nullable()->after('city');
            $table->string('country')->default('Indonesia')->after('province');
            $table->string('linkedin_url')->nullable()->after('country');
            $table->string('instagram_url')->nullable()->after('linkedin_url');
            $table->string('current_position')->nullable()->after('instagram_url');
        });

        Schema::table('tracer_responses', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('employer_name')->nullable()->after('job_title');
            $table->year('entry_year')->nullable()->after('employer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni_data', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'phone_number', 'email', 'city', 'province', 'country', 'linkedin_url', 'instagram_url', 'current_position']);
        });

        Schema::table('tracer_responses', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'employer_name', 'entry_year']);
        });
    }
};
