import os

base_dir = r"c:\project\portofolio\project-super-web\sistem-terpadu"

files = {
    "database/migrations/2026_10_25_000000_create_microlearning_tables.php": """<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('micro_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->nullable()->constrained('syllabi')->nullOnDelete();
            $table->string('title');
            $table->text('ai_generated_summary')->nullable();
            $table->integer('estimated_minutes')->default(5);
            $table->boolean('is_published')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('micro_lesson_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('micro_lesson_id')->constrained('micro_lessons')->cascadeOnDelete();
            $table->integer('sequence_order');
            $table->enum('card_type', ['text', 'video', 'interactive', 'quiz']);
            $table->text('content');
            $table->json('ai_prompts_used')->nullable();
            $table->timestamps();
        });

        Schema::create('gamified_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('join_code')->unique();
            $table->enum('status', ['draft', 'waiting', 'live', 'finished'])->default('draft');
            $table->json('game_settings')->nullable();
            $table->timestamps();
        });

        Schema::create('quiz_leaderboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gamified_quiz_id')->constrained('gamified_quizzes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->integer('total_score')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('streak_count')->default(0);
            $table->timestamps();
        });

        Schema::create('memory_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->text('front_content');
            $table->text('back_content');
            $table->integer('difficulty_level')->default(1);
            $table->timestamps();
        });

        Schema::create('spaced_repetition_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('memory_card_id')->constrained('memory_cards')->cascadeOnDelete();
            $table->integer('repetition_count')->default(0);
            $table->decimal('easiness_factor', 5, 2)->default(2.50);
            $table->integer('interval_days')->default(1);
            $table->date('next_review_date');
            $table->timestamps();
        });

        Schema::create('digital_transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('transcript_hash')->unique();
            $table->json('competency_radar')->nullable();
            $table->text('verification_url')->nullable();
            $table->enum('status', ['draft', 'verified', 'revoked'])->default('draft');
            $table->timestamps();
        });

        Schema::create('micro_badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('image_url')->nullable();
            $table->json('awarding_criteria')->nullable();
            $table->timestamps();
        });

        Schema::create('student_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('micro_badge_id')->constrained('micro_badges')->cascadeOnDelete();
            $table->date('awarded_at');
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('evidence_url')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_badges');
        Schema::dropIfExists('micro_badges');
        Schema::dropIfExists('digital_transcripts');
        Schema::dropIfExists('spaced_repetition_logs');
        Schema::dropIfExists('memory_cards');
        Schema::dropIfExists('quiz_leaderboards');
        Schema::dropIfExists('gamified_quizzes');
        Schema::dropIfExists('micro_lesson_cards');
        Schema::dropIfExists('micro_lessons');
    }
};
""",
    "app/Models/MicroLesson.php": """<?php
namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

class MicroLesson extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['syllabus_id', 'title', 'ai_generated_summary', 'estimated_minutes', 'is_published', 'metadata'];
    protected $casts = [
        'is_published' => 'boolean',
        'metadata' => 'array',
    ];
    public function cards() { return $this->hasMany(MicroLessonCard::class); }
}
""",
    "app/Models/MicroLessonCard.php": """<?php
namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class MicroLessonCard extends Model
{
    use HasFactory;
    protected $fillable = ['micro_lesson_id', 'sequence_order', 'card_type', 'content', 'ai_prompts_used'];
    protected $casts = [
        'ai_prompts_used' => 'array',
    ];
    public function lesson() { return $this->belongsTo(MicroLesson::class, 'micro_lesson_id'); }
}
""",
    "app/Models/GamifiedQuiz.php": """<?php
namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class GamifiedQuiz extends Model
{
    use HasFactory;
    protected $fillable = ['teacher_id', 'title', 'join_code', 'status', 'game_settings'];
    protected $casts = [
        'game_settings' => 'array',
    ];
    public function leaderboards() { return $this->hasMany(QuizLeaderboard::class); }
}
""",
    "app/Models/QuizLeaderboard.php": """<?php
namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class QuizLeaderboard extends Model
{
    use HasFactory;
    protected $fillable = ['gamified_quiz_id', 'student_id', 'total_score', 'correct_answers', 'streak_count'];
    public function quiz() { return $this->belongsTo(GamifiedQuiz::class, 'gamified_quiz_id'); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}
""",
    "app/Models/MemoryCard.php": """<?php
namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class MemoryCard extends Model
{
    use HasFactory;
    protected $fillable = ['subject_id', 'front_content', 'back_content', 'difficulty_level'];
    public function logs() { return $this->hasMany(SpacedRepetitionLog::class); }
}
""",
    "app/Models/SpacedRepetitionLog.php": """<?php
namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class SpacedRepetitionLog extends Model
{
    use HasFactory;
    protected $fillable = ['student_id', 'memory_card_id', 'repetition_count', 'easiness_factor', 'interval_days', 'next_review_date'];
    protected $casts = [
        'next_review_date' => 'date',
        'easiness_factor' => 'decimal:2',
    ];
    public function card() { return $this->belongsTo(MemoryCard::class, 'memory_card_id'); }
}
""",
    "app/Models/DigitalTranscript.php": """<?php
namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class DigitalTranscript extends Model
{
    use HasFactory;
    protected $fillable = ['student_id', 'transcript_hash', 'competency_radar', 'verification_url', 'status'];
    protected $casts = [
        'competency_radar' => 'array',
    ];
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}
""",
    "app/Models/MicroBadge.php": """<?php
namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class MicroBadge extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'image_url', 'awarding_criteria'];
    protected $casts = [
        'awarding_criteria' => 'array',
    ];
    public function studentBadges() { return $this->hasMany(StudentBadge::class); }
}
""",
    "app/Models/StudentBadge.php": """<?php
namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class StudentBadge extends Model
{
    use HasFactory;
    protected $fillable = ['student_id', 'micro_badge_id', 'awarded_at', 'awarded_by', 'evidence_url'];
    protected $casts = [
        'awarded_at' => 'date',
    ];
    public function badge() { return $this->belongsTo(MicroBadge::class, 'micro_badge_id'); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}
""",
    "app/Services/Microlearning/MicroLessonService.php": """<?php
namespace App\\Services\\Microlearning;

use App\\Models\\MicroLesson;

class MicroLessonService
{
    public function generateFromSyllabus($syllabusId, $title) {
        return MicroLesson::create(['syllabus_id' => $syllabusId, 'title' => $title]);
    }
    public function publishMicroLesson(MicroLesson $lesson) {
        $lesson->update(['is_published' => true]);
        return $lesson;
    }
    public function getStudentProgress($studentId) {
        return [];
    }
}
""",
    "app/Services/Microlearning/GamifiedQuizService.php": """<?php
namespace App\\Services\\Microlearning;

use App\\Models\\GamifiedQuiz;
use Illuminate\\Support\\Str;

class GamifiedQuizService
{
    public function createLobby($teacherId, $title) {
        return GamifiedQuiz::create(['teacher_id' => $teacherId, 'title' => $title, 'join_code' => Str::random(6), 'status' => 'waiting']);
    }
    public function joinStudent($joinCode, $studentId) {}
    public function startQuiz(GamifiedQuiz $quiz) {
        $quiz->update(['status' => 'live']);
    }
    public function processAnswerScore() {}
    public function updateLeaderboard() {}
    public function endQuiz(GamifiedQuiz $quiz) {
        $quiz->update(['status' => 'finished']);
    }
}
""",
    "app/Services/Microlearning/SpacedRepetitionService.php": """<?php
namespace App\\Services\\Microlearning;

use App\\Models\\SpacedRepetitionLog;
use Carbon\\Carbon;

class SpacedRepetitionService
{
    public function calculateNextReview($quality, $easinessFactor, $repetitionCount, $intervalDays) {}
    public function logReviewAttempt($studentId, $memoryCardId, $quality) {}
    public function getDueCards($studentId) {
        return SpacedRepetitionLog::where('student_id', $studentId)
            ->where('next_review_date', '<=', Carbon::today())->get();
    }
    public function analyzeRetentionRate($studentId) { return []; }
}
""",
    "app/Services/Microlearning/DigitalTranscriptService.php": """<?php
namespace App\\Services\\Microlearning;

use App\\Models\\DigitalTranscript;

class DigitalTranscriptService
{
    public function generateTranscript($studentId) {}
    public function calculateCompetencyRadar($studentId) {}
    public function issueBadge($studentId, $badgeId, $awardedBy) {}
    public function verifyTranscriptHash($hash) {
        return DigitalTranscript::where('transcript_hash', $hash)->first();
    }
}
""",
    "app/Http/Controllers/Microlearning/MicroLessonController.php": """<?php
namespace App\\Http\\Controllers\\Microlearning;

use App\\Http\\Controllers\\Controller;
use App\\Models\\MicroLesson;
use Illuminate\\Http\\Request;

class MicroLessonController extends Controller
{
    public function index() { return view('micro-lessons.index'); }
    public function create() { return view('micro-lessons.create'); }
    public function store(Request $request) { return redirect()->route('micro-lessons.index'); }
    public function show($id) { return view('micro-lessons.show'); }
    public function edit($id) { return view('micro-lessons.create'); }
    public function update(Request $request, $id) { return redirect()->route('micro-lessons.index'); }
    public function destroy($id) { return redirect()->route('micro-lessons.index'); }
    public function publish($id) { return redirect()->route('micro-lessons.index'); }
    public function analytics() { return view('micro-lessons.analytics'); }
}
""",
    "app/Http/Controllers/Microlearning/GamifiedQuizController.php": """<?php
namespace App\\Http\\Controllers\\Microlearning;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;

class GamifiedQuizController extends Controller
{
    public function dashboard() { return view('gamified-quizzes.dashboard'); }
    public function create() { return view('gamified-quizzes.dashboard'); }
    public function store(Request $request) { return redirect()->route('gamified-quizzes.dashboard'); }
    public function host($id) { return view('gamified-quizzes.lobby'); }
    public function join() { return view('gamified-quizzes.dashboard'); }
    public function liveView($id) { return view('gamified-quizzes.live'); }
    public function results($id) { return view('gamified-quizzes.results'); }
}
""",
    "app/Http/Controllers/Microlearning/SpacedRepetitionController.php": """<?php
namespace App\\Http\\Controllers\\Microlearning;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;

class SpacedRepetitionController extends Controller
{
    public function deck() { return view('spaced-repetition.deck'); }
    public function review() { return view('spaced-repetition.review'); }
    public function submitReview(Request $request) { return redirect()->route('spaced-repetition.deck'); }
    public function stats() { return view('spaced-repetition.stats'); }
}
""",
    "app/Http/Controllers/Microlearning/DigitalTranscriptController.php": """<?php
namespace App\\Http\\Controllers\\Microlearning;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;

class DigitalTranscriptController extends Controller
{
    public function index() { return view('transcripts.student-view'); }
    public function show($id) { return view('transcripts.student-view'); }
    public function verify($hash) { return view('transcripts.public-verification'); }
    public function badgeGallery() { return view('transcripts.student-view'); }
    public function adminManager() { return view('transcripts.admin-manager'); }
}
""",
    "database/seeders/MicrolearningSeeder.php": """<?php
namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Str;
use App\\Models\\User;

class MicrolearningSeeder extends Seeder
{
    public function run()
    {
        $teacher = User::factory()->create(['name' => 'Guru SMA Islam Sultan Agung 1', 'role' => 'teacher']);
        $student = User::factory()->create(['name' => 'Siswa SMA Islam Sultan Agung 1', 'role' => 'student']);

        DB::table('micro_lessons')->insert([
            'title' => 'Dasar-Dasar Sejarah Kebudayaan Islam',
            'ai_generated_summary' => 'Ringkasan singkat SKI.',
            'estimated_minutes' => 10,
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('gamified_quizzes')->insert([
            'teacher_id' => $teacher->id,
            'title' => 'Kuis Tajwid Mingguan',
            'join_code' => Str::random(6),
            'status' => 'waiting',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('memory_cards')->insert([
            'front_content' => 'Apa hukum nun mati bertemu ba?',
            'back_content' => 'Iqlab',
            'difficulty_level' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
""",
    "routes/microlearning.php": """<?php

use Illuminate\\Support\\Facades\\Route;
use App\\Http\\Controllers\\Microlearning\\MicroLessonController;
use App\\Http\\Controllers\\Microlearning\\GamifiedQuizController;
use App\\Http\\Controllers\\Microlearning\\SpacedRepetitionController;
use App\\Http\\Controllers\\Microlearning\\DigitalTranscriptController;

Route::middleware(['auth'])->group(function () {
    Route::resource('micro-lessons', MicroLessonController::class);
    Route::post('micro-lessons/{id}/publish', [MicroLessonController::class, 'publish'])->name('micro-lessons.publish');
    Route::get('micro-lessons-analytics', [MicroLessonController::class, 'analytics'])->name('micro-lessons.analytics');

    Route::prefix('gamified-quizzes')->name('gamified-quizzes.')->group(function () {
        Route::get('/', [GamifiedQuizController::class, 'dashboard'])->name('dashboard');
        Route::get('host/{id}', [GamifiedQuizController::class, 'host'])->name('host');
        Route::get('live/{id}', [GamifiedQuizController::class, 'liveView'])->name('live');
        Route::get('results/{id}', [GamifiedQuizController::class, 'results'])->name('results');
    });

    Route::prefix('spaced-repetition')->name('spaced-repetition.')->group(function () {
        Route::get('deck', [SpacedRepetitionController::class, 'deck'])->name('deck');
        Route::get('review', [SpacedRepetitionController::class, 'review'])->name('review');
        Route::post('submit-review', [SpacedRepetitionController::class, 'submitReview'])->name('submitReview');
        Route::get('stats', [SpacedRepetitionController::class, 'stats'])->name('stats');
    });

    Route::prefix('transcripts')->name('transcripts.')->group(function () {
        Route::get('/', [DigitalTranscriptController::class, 'index'])->name('index');
        Route::get('admin', [DigitalTranscriptController::class, 'adminManager'])->name('admin');
    });
});

Route::get('/verify-transcript/{hash}', [DigitalTranscriptController::class, 'verify'])->name('transcripts.verify');
""",
    "tests/Feature/Microlearning/MicrolearningSuiteTest.php": """<?php
namespace Tests\\Feature\\Microlearning;

use Tests\\TestCase;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use App\\Models\\User;

class MicrolearningSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_micro_lessons_index()
    {
        $response = $this->actingAs($this->user)->get(route('micro-lessons.index'));
        $response->assertStatus(200);
    }

    public function test_gamified_quizzes_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('gamified-quizzes.dashboard'));
        $response->assertStatus(200);
    }

    public function test_spaced_repetition_deck()
    {
        $response = $this->actingAs($this->user)->get(route('spaced-repetition.deck'));
        $response->assertStatus(200);
    }

    public function test_transcripts_index()
    {
        $response = $this->actingAs($this->user)->get(route('transcripts.index'));
        $response->assertStatus(200);
    }
}
"""
}

# Add views
views = [
    'micro-lessons/index', 'micro-lessons/create', 'micro-lessons/show', 'micro-lessons/analytics',
    'gamified-quizzes/dashboard', 'gamified-quizzes/lobby', 'gamified-quizzes/live', 'gamified-quizzes/results',
    'spaced-repetition/deck', 'spaced-repetition/review', 'spaced-repetition/stats',
    'transcripts/student-view', 'transcripts/admin-manager', 'transcripts/public-verification'
]
for v in views:
    name = v.split('/')[-1].replace('-', ' ').title()
    files[f"resources/views/{v}.blade.php"] = f"""@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 p-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            {name} - SMA Islam Sultan Agung 1 Semarang
        </h1>
    </div>
</div>
@endsection
"""

for path, content in files.items():
    full_path = os.path.join(base_dir, path)
    os.makedirs(os.path.dirname(full_path), exist_ok=True)
    with open(full_path, "w", encoding="utf-8") as f:
        f.write(content)

# Update web.php
web_php_path = os.path.join(base_dir, "routes/web.php")
if os.path.exists(web_php_path):
    with open(web_php_path, "r", encoding="utf-8") as f:
        content = f.read()
    if "routes/microlearning.php" not in content:
        content = content.replace("require __DIR__.'/auth.php';", "require __DIR__.'/microlearning.php';\\nrequire __DIR__.'/auth.php';")
        with open(web_php_path, "w", encoding="utf-8") as f:
            f.write(content)

print("Done")
