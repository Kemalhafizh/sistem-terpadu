<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 67.1: Visual Workflow Blueprints & Nodes (BPMN)
        if (! Schema::hasTable('workflow_blueprints')) {
            Schema::create('workflow_blueprints', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('process_type'); // rkas_approval, letter_signing, leave_request, procurement
                $table->json('bpmn_schema'); // nodes, edges, gateways, conditions
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('workflow_nodes')) {
            Schema::create('workflow_nodes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_blueprint_id')->constrained('workflow_blueprints')->cascadeOnDelete();
                $table->string('node_key');
                $table->string('node_name');
                $table->string('node_type'); // start, user_task, gateway, service, end
                $table->json('configuration')->nullable();
                $table->timestamps();
            });
        }

        // 67.2: Document Approval Chain Engine
        if (! Schema::hasTable('approval_chains')) {
            Schema::create('approval_chains', function (Blueprint $table) {
                $table->id();
                $table->nullableMorphs('approvable');
                $table->foreignId('workflow_blueprint_id')->constrained('workflow_blueprints')->cascadeOnDelete();
                $table->string('current_node_key')->default('start');
                $table->enum('status', ['in_progress', 'approved', 'rejected', 'escalated'])->default('in_progress');
                $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('approval_requests')) {
            Schema::create('approval_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('approval_chain_id')->constrained('approval_chains')->cascadeOnDelete();
                $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
                $table->string('node_key');
                $table->enum('decision', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('comments')->nullable();
                $table->timestamp('decided_at')->nullable();
                $table->timestamps();
            });
        }

        // 67.3: Process Mining & Bottleneck Logs
        if (! Schema::hasTable('process_mining_logs')) {
            Schema::create('process_mining_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('approval_chain_id')->constrained('approval_chains')->cascadeOnDelete();
                $table->string('node_key');
                $table->integer('duration_minutes')->default(0);
                $table->boolean('was_escalated')->default(false);
                $table->timestamp('recorded_at');
                $table->timestamps();
            });
        }

        // 67.4: SLA Definitions & Thresholds
        if (! Schema::hasTable('sla_definitions')) {
            Schema::create('sla_definitions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_blueprint_id')->constrained('workflow_blueprints')->cascadeOnDelete();
                $table->string('node_key');
                $table->integer('max_duration_hours')->default(24);
                $table->string('escalation_action')->default('notify_headmaster'); // notify_headmaster, auto_approve, reassign
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_definitions');
        Schema::dropIfExists('process_mining_logs');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_chains');
        Schema::dropIfExists('workflow_nodes');
        Schema::dropIfExists('workflow_blueprints');
    }
};
