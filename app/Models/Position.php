<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'description',
        'allowed_account_types',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'allowed_account_types' => 'array',
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<PositionPermission, $this> */
    public function permissions(): HasMany
    {
        return $this->hasMany(PositionPermission::class);
    }

    /** @return HasMany<PositionAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(PositionAssignment::class);
    }

    /** @return list<string> */
    public function permissionKeys(): array
    {
        return $this->permissions()->pluck('permission')->all();
    }

    public function allowsAccountType(string $accountType): bool
    {
        $allowed = $this->allowed_account_types ?? [];

        return $allowed === [] || in_array($accountType, $allowed, true);
    }
}
