<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('crisis_incidents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->enum('type', ['gempa_bumi', 'banjir', 'kebakaran', 'tsunami', 'pandemi', 'ancaman_keamanan', 'kecelakaan', 'keracunan', 'demo_rusuh', 'bencana_teknologi', 'lainnya']);
            $table->enum('severity', ['level_1_minor', 'level_2_moderate', 'level_3_major', 'level_4_critical']);
            $table->enum('status', ['detected', 'responding', 'contained', 'recovering', 'resolved', 'post_mortem']);
            $table->text('description');
            $table->text('location_detail')->nullable();
            $table->timestamp('detected_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('contained_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('reported_by')->constrained('users');
            $table->foreignId('incident_commander_id')->nullable()->constrained('users');
            $table->integer('affected_students_count')->default(0);
            $table->integer('affected_staff_count')->default(0);
            $table->integer('injured_count')->default(0);
            $table->json('evacuation_zones')->nullable();
            $table->text('post_mortem_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('crisis_response_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crisis_incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('responder_id')->constrained('users');
            $table->enum('action_type', ['evakuasi_dimulai', 'evakuasi_selesai', 'p3k_diberikan', 'kontak_darurat_dihubungi', 'pihak_berwajib_dihubungi', 'siswa_dijemput_ortu', 'pemulihan_dimulai', 'update_situasi', 'media_statement', 'lainnya']);
            $table->text('description');
            $table->string('evidence_file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('crisis_protocols', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('crisis_type', ['gempa_bumi', 'banjir', 'kebakaran', 'tsunami', 'pandemi', 'ancaman_keamanan', 'kecelakaan', 'keracunan', 'lainnya']);
            $table->json('steps');
            $table->json('emergency_contacts');
            $table->json('assembly_points');
            $table->text('special_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('emergency_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('crisis_incident_id')->nullable()->constrained();
            $table->string('title');
            $table->text('message');
            $table->enum('priority', ['informational', 'warning', 'critical', 'life_threatening']);
            $table->json('target_groups');
            $table->json('channels');
            $table->integer('total_recipients')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('read_count')->default(0);
            $table->foreignId('sent_by')->constrained('users');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('emergency_broadcast_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained('emergency_broadcasts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ['queued', 'delivered', 'read', 'acknowledged', 'failed']);
            $table->string('channel');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });

        Schema::create('safety_headcounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crisis_incident_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('classroom_id')->nullable();
            $table->string('assembly_point');
            $table->integer('expected_count');
            $table->integer('accounted_count');
            $table->integer('missing_count');
            $table->json('missing_persons')->nullable();
            $table->enum('status', ['counting', 'all_accounted', 'missing_persons']);
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bullying_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('ticket_number')->unique();
            $table->text('description');
            $table->enum('type', ['verbal', 'fisik', 'cyber', 'sosial_eksklusi', 'pemerasan', 'pelecehan', 'intimidasi', 'lainnya']);
            $table->enum('severity', ['ringan', 'sedang', 'berat', 'sangat_berat']);
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
        Schema::dropIfExists('safety_headcounts');
        Schema::dropIfExists('emergency_broadcast_receipts');
        Schema::dropIfExists('emergency_broadcasts');
        Schema::dropIfExists('crisis_protocols');
        Schema::dropIfExists('crisis_response_logs');
        Schema::dropIfExists('crisis_incidents');
    }
};