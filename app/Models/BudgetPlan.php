<?php

namespace App\Models;

use App\Support\Access\Roles;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetPlan extends Model
{
    use HasFactory;

    /** Who drafts the RKAS and records its realisation. */
    public const PREPARER_ROLES = ['staf_tu', 'bendahara', 'admin'];

    /** Who approves it: the principal (RoleMiddleware lets admin through as well). */
    public const APPROVER_ROLES = Roles::PRINCIPAL;

    public const READER_ROLES = [...self::PREPARER_ROLES, ...self::APPROVER_ROLES];

    /** Line items can change only before the plan is submitted. */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'revised'], true);
    }

    /**
     * The dates the fiscal year covers: "2026/2027" is a school year
     * (1 July – 30 June), "2026" a calendar year.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}|null
     */
    public function period(): ?array
    {
        if (preg_match('/^(\d{4})\/(\d{4})$/', (string) $this->fiscal_year, $m)) {
            return [CarbonImmutable::create((int) $m[1], 7, 1), CarbonImmutable::create((int) $m[2], 6, 30)->endOfDay()];
        }
        if (preg_match('/^\d{4}$/', (string) $this->fiscal_year)) {
            return [CarbonImmutable::create((int) $this->fiscal_year, 1, 1), CarbonImmutable::create((int) $this->fiscal_year, 12, 31)->endOfDay()];
        }

        return null;
    }

    protected $fillable = [
        'fiscal_year',
        'title',
        'status',
        'total_revenue_planned',
        'total_expense_planned',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'total_revenue_planned' => 'decimal:2',
        'total_expense_planned' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /** @return HasMany<BudgetLineItem, $this> */
    public function lineItems(): HasMany
    {
        return $this->hasMany(BudgetLineItem::class);
    }

    /** @return HasMany<BudgetRevision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(BudgetRevision::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
