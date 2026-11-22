<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'departments' => 'array',
        'branches' => 'array',
    ];

    /**
     * Scope a query to only include holidays that apply to the given user.
     *
     * A holiday applies to a user when:
     *  - it has no branch restriction (organization-wide), OR
     *  - the user's super department is in the holiday's branch list.
     *
     * This keeps backward compatibility: existing holidays with a NULL
     * `branches` column continue to apply to everyone.
     */
    public function scopeForUser(Builder $query, ?User $user)
    {
        $branchId = $user?->super_department_id;

        return $query->where(function (Builder $q) use ($branchId) {
            $q->whereNull('branches')
                ->orWhereJsonLength('branches', 0)
                ->orWhereJsonContains('branches', $branchId);
        });
    }

    /**
     * Whether this holiday applies organization-wide (no branch restriction).
     */
    public function isOrganizationWide(): bool
    {
        return empty($this->branches);
    }
}
