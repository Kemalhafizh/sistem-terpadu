<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Repair for databases that already ran 2026_10_17_000000_create_adaptive_learning_tables
 * before it was fixed: irt_item_parameters.question_id and
 * cat_item_responses.question_id referenced a "questions" table that has never
 * existed, so every insert failed ("no such table: main.questions"). The items
 * are CBT bank rows, so the keys now point at question_banks.
 *
 * On MySQL the original migration could not have succeeded (a foreign key to a
 * missing table is rejected), and fresh installs now create the right key, so
 * this is a no-op there.
 */
return new class extends Migration
{
    /** table => cascade on delete? (mirrors the original definitions) */
    private const TABLES = [
        'irt_item_parameters' => true,
        'cat_item_responses' => false,
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $cascade) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $current = collect(Schema::getForeignKeys($table))
                ->first(fn (array $fk) => $fk['columns'] === ['question_id']);

            if ($current && $current['foreign_table'] === 'question_banks') {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($current, $cascade) {
                if ($current) {
                    $blueprint->dropForeign(['question_id']);
                }
                $foreign = $blueprint->foreign('question_id')->references('id')->on('question_banks');
                if ($cascade) {
                    $foreign->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        // Intentionally empty: the previous key pointed at a table that doesn't exist.
    }
};
