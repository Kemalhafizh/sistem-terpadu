<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('department', ['academic', 'administrative', 'facilities', 'it', 'islamic']);
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->enum('urgency', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->decimal('estimated_budget', 15, 2);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'ordered', 'received', 'completed'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('approval_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')->constrained('procurement_requests')->cascadeOnDelete();
            $table->string('po_number')->unique();
            $table->string('vendor_name');
            $table->string('vendor_contact')->nullable();
            $table->json('items');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->date('delivery_date');
            $table->enum('status', ['issued', 'partial_delivery', 'delivered', 'completed', 'cancelled'])->default('issued');
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->string('grn_number')->unique();
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->date('received_date');
            $table->json('items_received');
            $table->text('condition_notes')->nullable();
            $table->boolean('is_complete')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_received_notes');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('procurement_requests');
    }
};
