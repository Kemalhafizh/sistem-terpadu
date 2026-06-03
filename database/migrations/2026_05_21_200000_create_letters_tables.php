<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // "Surat Keterangan Aktif Siswa"
            $table->string('code')->unique();               // "SKA", "STG", "SDM"
            $table->text('body_template');                   // Template HTML/Text dengan placeholders {{nama_siswa}}, {{purpose}}, dll.
            $table->text('header_html')->nullable();         // Kop surat HTML standar
            $table->text('footer_html')->nullable();
            $table->json('required_fields');                 // ["student_id", "purpose", "start_date"]
            $table->string('signature_path')->nullable();    // Berkas tanda tangan digital
            $table->string('signer_name')->nullable();       // "Drs. H. Soetarto, M.Pd."
            $table->string('signer_title')->nullable();      // "Kepala Sekolah"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('template_id')->constrained('letter_templates')->onDelete('restrict');
            $table->string('letter_number')->nullable();     // Null saat draf, otomatis terisi saat 'issued'
            $table->json('field_values');                    // ["purpose" => "Lomba Debat", "start_date" => "2026-06-01"]
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->date('issue_date');
            $table->enum('status', ['draft', 'issued', 'revoked'])->default('draft');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('letter_number');
            $table->index(['template_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letters');
        Schema::dropIfExists('letter_templates');
    }
};
