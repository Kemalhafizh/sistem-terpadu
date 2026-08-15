<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('omnichannel_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_code')->unique();
            $table->string('subject')->nullable();
            $table->text('body_content');
            $table->json('allowed_channels');
            $table->json('variables')->nullable();
            $table->timestamps();
        });

        Schema::create('omnichannel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('channel', ['email', 'sms', 'whatsapp', 'push']);
            $table->string('recipient_address');
            $table->foreignId('template_id')->nullable()->constrained('omnichannel_templates')->nullOnDelete();
            $table->enum('delivery_status', ['queued', 'sent', 'delivered', 'failed', 'read'])->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('drip_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_name');
            $table->enum('trigger_event', ['onboarding', 'graduation', 'inactive', 'custom']);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('campaign_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drip_campaign_id')->constrained('drip_campaigns')->cascadeOnDelete();
            $table->integer('step_order');
            $table->integer('delay_minutes')->default(0);
            $table->foreignId('omnichannel_template_id')->constrained('omnichannel_templates')->onDelete('cascade');
            $table->json('conditions')->nullable();
            $table->timestamps();
        });

        Schema::create('nps_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_title');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('target_audience', ['all_parents', 'new_parents', 'graduating_parents']);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('nps_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nps_campaign_id')->constrained('nps_campaigns')->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('users')->onDelete('cascade');
            $table->integer('score');
            $table->text('verbatim_feedback')->nullable();
            $table->enum('category', ['promoter', 'passive', 'detractor']);
            $table->timestamps();
        });

        Schema::create('media_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('journalist_name');
            $table->string('media_outlet');
            $table->string('email');
            $table->string('phone_number')->nullable();
            $table->enum('relationship_status', ['warm', 'cold', 'blacklisted'])->default('warm');
            $table->timestamps();
        });

        Schema::create('press_releases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->date('release_date');
            $table->json('distributed_to')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'published'])->default('draft');
            $table->integer('media_pickup_count')->default(0);
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('press_releases');
        Schema::dropIfExists('media_contacts');
        Schema::dropIfExists('nps_responses');
        Schema::dropIfExists('nps_campaigns');
        Schema::dropIfExists('campaign_steps');
        Schema::dropIfExists('drip_campaigns');
        Schema::dropIfExists('omnichannel_logs');
        Schema::dropIfExists('omnichannel_templates');
    }
};
