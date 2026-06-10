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
        Schema::table('announcements', function (Blueprint $table) {
            $table->enum('category', ['akademik', 'keuangan', 'kegiatan', 'keislaman', 'umum'])->default('umum')->after('content');
            $table->string('attachment_path')->nullable()->after('image_path');
            $table->boolean('is_pinned')->default(false)->after('priority');
            $table->boolean('require_acknowledgement')->default(false)->after('is_pinned');
            $table->integer('read_count')->default(0)->after('require_acknowledgement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['category', 'attachment_path', 'is_pinned', 'require_acknowledgement', 'read_count']);
        });
    }
};
