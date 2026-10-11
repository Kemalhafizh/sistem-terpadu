<?php

namespace App\Models;

use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation360Cycle extends Model
{
    use HasFactory;

    /** Who runs the cycles and sees every teacher's results and the ranking. */
    public const MANAGER_ROLES = [...Roles::PRINCIPAL, 'waka_kurikulum', 'admin'];

    /** Who fills the web form: teachers (peer or self) and leadership (supervisor). Students rate in the app. */
    public const EVALUATOR_ROLES = [...StudentDataAccess::TEACHER_ROLES, 'bk', 'wali_kelas', ...Roles::LEADERSHIP, 'admin'];

    /** Teachers who can be evaluated. */
    public const EVALUATEE_ROLES = [...StudentDataAccess::TEACHER_ROLES, 'bk', 'wali_kelas'];

    protected $table = 'evaluation_360_cycles';

    protected $fillable = [
        'title',
        'academic_year',
        'semester',
        'status',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function instruments(): HasMany
    {
        return $this->hasMany(Evaluation360Instrument::class, 'cycle_id');
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(Evaluation360Summary::class, 'cycle_id');
    }
}
