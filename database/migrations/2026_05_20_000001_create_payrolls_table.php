<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->char('month', 2); // '01' - '12'
            $table->char('year', 4);  // '2026'
            $table->decimal('base_salary', 12, 2);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2);

            // Summary kehadiran bulanan
            $table->integer('working_days')->default(0);
            $table->integer('present_days')->default(0);
            $table->integer('late_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->integer('leave_days')->default(0);

            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
        });

        // Insert new settings for GPS Geofencing and Payroll Engine
        $settings = [
            [
                'key' => 'school_latitude',
                'value' => '-6.9535',
                'group' => 'system',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_longitude',
                'value' => '110.4355',
                'group' => 'system',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'school_attendance_radius',
                'value' => '100',
                'group' => 'system',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'payroll_late_deduction',
                'value' => '25000',
                'group' => 'system',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'payroll_absent_deduction',
                'value' => '100000',
                'group' => 'system',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            // Check if key already exists to prevent duplicate key errors
            $exists = DB::table('settings')->where('key', $setting['key'])->exists();
            if (! $exists) {
                DB::table('settings')->insert($setting);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');

        DB::table('settings')->whereIn('key', [
            'school_latitude',
            'school_longitude',
            'school_attendance_radius',
            'payroll_late_deduction',
            'payroll_absent_deduction',
        ])->delete();
    }
};
