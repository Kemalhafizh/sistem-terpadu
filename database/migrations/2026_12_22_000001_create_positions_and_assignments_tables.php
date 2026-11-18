<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'account_type')) {
                $table->string('account_type', 32)->nullable()->after('role')->index();
            }
            if (! Schema::hasColumn('users', 'account_status')) {
                $table->string('account_status', 24)->default('aktif')->after('account_type')->index();
            }
            if (! Schema::hasColumn('users', 'deactivated_reason')) {
                $table->string('deactivated_reason', 64)->nullable()->after('account_status');
            }
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('allowed_account_types')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('position_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->string('permission', 80);
            $table->timestamps();

            $table->unique(['position_id', 'permission']);
        });

        Schema::create('position_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('decree_number', 120)->nullable();
            $table->boolean('is_acting')->default(false);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'starts_on', 'ends_on']);
            $table->index(['position_id', 'starts_on', 'ends_on']);
        });

        // Backfill account_type on existing users
        $roleMap = config('permissions.role_to_account_type', []);
        foreach ($roleMap as $role => $accountType) {
            DB::table('users')
                ->where('role', $role)
                ->whereNull('account_type')
                ->update(['account_type' => $accountType]);
        }

        // Seed the 7 default school positions
        $now = now();
        foreach (config('permissions.default_positions', []) as $pos) {
            $positionId = DB::table('positions')->insertGetId([
                'code' => $pos['code'],
                'name' => $pos['name'],
                'description' => $pos['description'] ?? null,
                'allowed_account_types' => json_encode($pos['allowed_account_types'] ?? ['guru']),
                'is_system' => (bool) ($pos['is_system'] ?? false),
                'is_active' => (bool) ($pos['is_active'] ?? true),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($pos['permissions'] ?? [] as $perm) {
                DB::table('position_permissions')->insert([
                    'position_id' => $positionId,
                    'permission' => $perm,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('position_assignments');
        Schema::dropIfExists('position_permissions');
        Schema::dropIfExists('positions');

        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(
                ['account_type', 'account_status', 'deactivated_reason'],
                fn (string $col) => Schema::hasColumn('users', $col)
            );
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
