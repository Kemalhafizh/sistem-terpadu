<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Student extends Model
{
    use HasFactory, HasUuid, LogsActivity, SoftDeletes;

    protected $fillable = ['user_id', 'parent_id', 'classroom_id', 'nis', 'nisn', 'gender', 'birth_date', 'address', 'status', 'total_xp', 'current_streak', 'longest_streak'];

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
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

    public function billings()
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

    /**
     * Get portfolio items belonging to the student.
     */
    public function portfolioItems()
    {
        return $this->hasMany(PortfolioItem::class)->orderBy('date', 'desc');
    }

    /**
     * Get award badges earned by the student.
     */
    public function badges()
    {
        return $this->belongsToMany(PortfolioBadge::class, 'student_badges', 'student_id', 'badge_id')
            ->withPivot('awarded_by', 'earned_at', 'notes')
            ->withTimestamps()
            ->orderBy('student_badges.earned_at', 'desc');
    }

    /**
     * Relationship to StudentAchievements.
     */
    public function achievements()
    {
        return $this->hasMany(StudentAchievement::class);
    }

    /**
     * Relationship to StudentXpLogs.
     */
    public function xpLogs()
    {
        return $this->hasMany(StudentXpLog::class);
    }

    /**
     * Accessor to calculate Student Level based on total XP.
     */
    public function getLevelAttribute(): int
    {
        return 1 + (int) floor($this->total_xp / 100);
    }

    /**
     * Accessor to calculate XP progress percentage towards next level.
     */
    public function getXpProgressPercentAttribute(): float
    {
        $currentLevelXp = (int) floor($this->total_xp / 100) * 100;
        $nextLevelXp = $currentLevelXp + 100;
        $earnedXpInLevel = $this->total_xp - $currentLevelXp;

        return round(($earnedXpInLevel / 100) * 100, 1);
    }
}
