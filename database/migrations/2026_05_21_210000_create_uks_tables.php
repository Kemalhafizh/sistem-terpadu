<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Obat UKS
        Schema::create('uks_medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');                 // 'P3K', 'Vitamin', 'Obat Umum'
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['category', 'expiry_date']);
        });

        // 2. Tabel Kunjungan UKS
        Schema::create('uks_visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('handled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('visit_time');
            $table->dateTime('leave_time')->nullable();
            $table->string('complaint');
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->enum('severity', ['ringan', 'sedang', 'berat'])->default('ringan');
            $table->enum('disposition', ['kembali_kelas', 'istirahat_uks', 'pulang', 'rujuk_rs'])->default('istirahat_uks');
            $table->boolean('parent_notified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'visit_time']);
            $table->index(['severity', 'disposition']);
        });

        // 3. Tabel Pivot Kunjungan & Obat
        Schema::create('uks_visit_medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uks_visit_id')->constrained('uks_visits')->onDelete('cascade');
            $table->foreignId('uks_medicine_id')->constrained('uks_medicines')->onDelete('restrict');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        // 4. Tambahan kolom alergi dan gol darah ke tabel students (opsional & aman)
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'blood_type')) {
                $table->string('blood_type', 3)->nullable()->after('status');
            }
            if (! Schema::hasColumn('students', 'allergies')) {
                $table->text('allergies')->nullable()->after('blood_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'blood_type')) {
                $table->dropColumn('blood_type');
            }
            if (Schema::hasColumn('students', 'allergies')) {
                $table->dropColumn('allergies');
            }
        });

        Schema::dropIfExists('uks_visit_medicines');
        Schema::dropIfExists('uks_visits');
        Schema::dropIfExists('uks_medicines');
    }
};
