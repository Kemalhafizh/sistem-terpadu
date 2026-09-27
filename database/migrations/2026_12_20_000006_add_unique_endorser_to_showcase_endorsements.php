<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * One endorsement per person per showcase entry. ShowcaseService checks this
 * too, but only the database can stop two simultaneous requests from both
 * getting through. Any existing duplicates are collapsed to the earliest one
 * first so the index can be created.
 */
return new class extends Migration
{
    public function up(): void
    {
        $keep = DB::table('showcase_endorsements')
            ->selectRaw('MIN(id) as id')
            ->groupBy('showcase_id', 'endorser_id')
            ->pluck('id');
        DB::table('showcase_endorsements')->whereNotIn('id', $keep)->delete();

        Schema::table('showcase_endorsements', function (Blueprint $table) {
            $table->unique(['showcase_id', 'endorser_id'], 'showcase_endorsements_unique_endorser');
        });
    }

    public function down(): void
    {
        Schema::table('showcase_endorsements', function (Blueprint $table) {
            $table->dropUnique('showcase_endorsements_unique_endorser');
        });
    }
};
