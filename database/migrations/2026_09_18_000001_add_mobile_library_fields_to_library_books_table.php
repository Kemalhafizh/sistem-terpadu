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
        Schema::table('library_books', function (Blueprint $table) {
            $table->string('isbn')->nullable()->index()->after('author');
            $table->string('shelf_location')->nullable()->after('cover_path');
            $table->integer('total_copies')->default(1)->after('is_physical_available');
            $table->integer('available_copies')->default(1)->after('total_copies');
            $table->decimal('rating', 3, 2)->default(4.80)->after('available_copies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('library_books', function (Blueprint $table) {
            $table->dropColumn([
                'isbn',
                'shelf_location',
                'total_copies',
                'available_copies',
                'rating',
            ]);
        });
    }
};
