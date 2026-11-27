<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('positions')) {
            return;
        }

        DB::table('positions')
            ->where('code', 'koordinator_p5')
            ->update([
                'is_system' => false,
                'updated_at' => now(),
            ]);

        if (! Schema::hasTable('position_permissions')) {
            return;
        }

        $targetPositionIds = DB::table('positions')
            ->whereIn('code', ['koordinator_p5', 'waka_kurikulum', 'kepala_sekolah'])
            ->pluck('id')
            ->all();

        foreach ($targetPositionIds as $positionId) {
            DB::table('position_permissions')->insertOrIgnore([
                'position_id' => $positionId,
                'permission' => 'kurikulum.kelola_p5',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('positions') || ! Schema::hasTable('position_permissions')) {
            return;
        }

        $leadershipIds = DB::table('positions')
            ->whereIn('code', ['waka_kurikulum', 'kepala_sekolah'])
            ->pluck('id')
            ->all();

        if ($leadershipIds !== []) {
            DB::table('position_permissions')
                ->whereIn('position_id', $leadershipIds)
                ->where('permission', 'kurikulum.kelola_p5')
                ->delete();
        }
    }
};
