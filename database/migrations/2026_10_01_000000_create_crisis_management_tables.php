<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bullying_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('ticket_number')->unique();
            $table->text('description');
            // A reporter may not know the type and does not judge severity; counselling fills them in.
            $table->enum('type', ['verbal', 'fisik', 'cyber', 'sosial_eksklusi', 'pemerasan', 'pelecehan', 'intimidasi', 'lainnya'])->nullable();
            $table->enum('severity', ['ringan', 'sedang', 'berat', 'sangat_berat'])->nullable();
            $table->string('location')->nullable();
            $table->date('incident_date')->nullable();
            $table->boolean('is_anonymous')->default(true);
            $table->foreignId('reporter_id')->nullable()->constrained('users');
            $table->string('reporter_alias')->nullable();
            $table->enum('status', ['reported', 'under_review', 'investigating', 'action_taken', 'closed', 'dismissed']);
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->text('investigation_notes')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('follow_up_plan')->nullable();
            $table->boolean('is_escalated')->default(false);
            $table->timestamps();
        });

        Schema::create('bullying_investigation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bullying_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('investigator_id')->constrained('users');
            $table->enum('action', ['wawancara_saksi', 'wawancara_pelaku', 'wawancara_korban', 'pengumpulan_bukti', 'mediasi', 'sanksi_dijatuhkan', 'konseling_korban', 'konseling_pelaku', 'pemanggilan_ortu', 'eskalasi', 'update_status', 'lainnya']);
            $table->text('notes');
            $table->boolean('is_confidential')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bullying_investigation_logs');
        Schema::dropIfExists('bullying_reports');
    }
};
