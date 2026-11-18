<?php

namespace App\Models;

use App\Http\Middleware\RoleMiddleware;
use App\Support\Access\StudentDataAccess;
use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Student extends Model
{
    use HasFactory, HasUuid, LogsActivity, SoftDeletes;

    protected $fillable = ['user_id', 'parent_id', 'classroom_id', 'nis', 'nisn', 'gender', 'birth_place', 'birth_date', 'address', 'mother_name', 'status'];

    /** Roles that may see every student's records (school-wide duties). */
    public const SCHOOL_WIDE_ROLES = ['superadmin', 'admin', 'bk', 'staf_tu', ...RoleMiddleware::LEADERSHIP_ROLES];

    /**
     * The caller's own family: a student themself, a parent their children,
     * anyone else no one (staff included). For money that belongs to the
     * family, such as the wadiah savings, unlike scopeVisibleTo which lets
     * school staff see every student.
     */
    public function scopeOfFamily(Builder $query, User $user): Builder
    {
        if (in_array($user->role, StudentDataAccess::STUDENT_ROLES, true)) {
            return $query->where('user_id', $user->id);
        }
        if (in_array($user->role, StudentDataAccess::PARENT_ROLES, true)) {
            return $query->where('parent_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * The students [user] may see: school-wide roles
     * see everyone, a teacher the classes they teach (schedule) or lead
     * (homeroom) this academic year, a student themself, a parent their own
     * children, anyone else no one.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $effectiveRoles = $user->effectiveRoles();

        if (array_intersect($effectiveRoles, self::SCHOOL_WIDE_ROLES) !== []) {
            return $query;
        }
        if (array_intersect($effectiveRoles, StudentDataAccess::STUDENT_ROLES) !== []) {
            return $query->where('user_id', $user->id);
        }
        if (array_intersect($effectiveRoles, StudentDataAccess::PARENT_ROLES) !== []) {
            return $query->where('parent_id', $user->id);
        }
        if (array_intersect($effectiveRoles, StudentDataAccess::TEACHER_ROLES) !== []) {
            $year = AcademicYear::current();
            if (! $year) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where(fn (Builder $q) => $q
                ->whereIn('classroom_id', Schedule::where('teacher_id', $user->id)->where('academic_year_id', $year->id)->select('classroom_id'))
                ->orWhereIn('classroom_id', Classroom::where('teacher_id', $user->id)->where('academic_year', $year->year)->select('id')));
        }

        return $query->whereRaw('1 = 0');
    }

    public function isVisibleTo(User $user): bool
    {
        return static::query()->visibleTo($user)->whereKey($this->getKey())->exists();
    }

    /**
     * Graceful Encrypted Attribute for Student Address (handles legacy plain-text data safely).
     */
    protected function address(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (empty($value)) {
                    return $value;
                }
                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException $e) {
                    // Jika data lama di database masih berbentuk plain-text, kembalikan tanpa error
                    return $value;
                }
            },
            set: fn ($value) => ! empty($value) ? Crypt::encryptString($value) : $value
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /** @return BelongsTo<Classroom, $this> */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /** @return HasMany<ClassEnrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class);
    }

    /** @return HasMany<ElectiveChoice, $this> */
    public function electiveChoices(): HasMany
    {
        return $this->hasMany(ElectiveChoice::class);
    }

    /** @return HasMany<ClassPlacement, $this> */
    public function classPlacements(): HasMany
    {
        return $this->hasMany(ClassPlacement::class);
    }

    public function pointLogs()
    {
        return $this->hasMany(StudentPointLog::class);
    }

    public function tahfidzLogs()
    {
        return $this->hasMany(TahfidzLog::class);
    }

    public function tahfidzTargets()
    {
        return $this->hasMany(TahfidzTarget::class);
    }

    public function mutabaahLogs()
    {
        return $this->hasMany(MutabaahLog::class);
    }

    public function wadiahAccount()
    {
        return $this->hasOne(WadiahAccount::class);
    }

    public function mutations()
    {
        return $this->hasMany(StudentMutation::class);
    }

    public function alumniData()
    {
        return $this->hasOne(AlumniData::class);
    }

    public function getTotalPointsAttribute()
    {
        if ($this->relationLoaded('pointLogs')) {
            return $this->pointLogs->sum('points');
        }

        return (int) $this->pointLogs()->sum('points');
    }

    /** @return HasMany<Billing, $this> */
    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function healthRecord()
    {
        return $this->hasOne(StudentHealthRecord::class);
    }

    public function screeningResults()
    {
        return $this->hasMany(HealthScreeningResult::class);
    }

    /**
     * Get the student's name from the user relationship.
     */
    public function getNameAttribute()
    {
        $user = $this->relationLoaded('user') ? $this->user : $this->load('user')->user;

        return $user?->name;
    }

    /**
     * Get the student's email from the user relationship.
     */
    public function getEmailAttribute()
    {
        $user = $this->relationLoaded('user') ? $this->user : $this->load('user')->user;

        return $user?->email;
    }

    public function libraryLoans()
    {
        return $this->hasMany(LibraryLoan::class);
    }

    public function activeLoansCount(): int
    {
        return $this->libraryLoans()->active()->count();
    }
}
