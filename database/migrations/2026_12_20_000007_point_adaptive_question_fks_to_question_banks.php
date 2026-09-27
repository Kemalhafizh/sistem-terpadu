<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * irt_item_parameters.question_id and cat_item_responses.question_id were
 * created with a bare constrained(), which points at a `questions` table that
 * has never existed — every insert failed with "no such table: questions".
 * The CBT question model is QuestionBank (question_banks), same as
 * ItemAnalysisService and CbtEssaySubmission use, so point the keys there.
 * Skipped when the key is already correct (fresh installs).
 */
return new class extends Migration
{
    /** @var array<string, bool> table => cascade on delete */
    private array $tables = [
        'irt_item_parameters' => true,
        'cat_item_responses' => false,
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName => $cascade) {
            if (! Schema::hasTable($tableName) || ! $this->pointsAtMissingQuestionsTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['question_id']);
            });

            Schema::table($tableName, function (Blueprint $table) use ($cascade) {
                $foreign = $table->foreign('question_id')->references('id')->on('question_banks');
                if ($cascade) {
                    $foreign->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        // Intentionally empty: restoring a key to a non-existent table would
        // only make both tables unwritable again.
    }

    private function pointsAtMissingQuestionsTable(string $tableName): bool
    {
        return collect(Schema::getForeignKeys($tableName))->contains(
            fn (array $fk) => $fk['columns'] === ['question_id'] && $fk['foreign_table'] === 'questions'
        );
    }
};
