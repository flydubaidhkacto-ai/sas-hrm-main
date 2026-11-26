<?php

namespace App\Models;

use App\Support\AppraisalAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appraisal extends Model
{
    public const FLOW = [
        'pending_supervisor', 'pending_employee', 'pending_hod',
        'pending_hr', 'pending_md', 'completed',
    ];

    public const STATUS_LABELS = [
        'pending_supervisor' => 'Waiting for Supervisor',
        'pending_employee'   => 'Waiting for Employee comment',
        'pending_hod'        => 'Waiting for Head of Department',
        'pending_hr'         => 'Waiting for HR',
        'pending_md'         => 'Waiting for Managing Director',
        'completed'          => 'Completed',
    ];

    protected $fillable = [
        'employee_id', 'supervisor_id', 'created_by', 'period', 'status',
        'total_percent', 'final_marks', 'employee_comment', 'employee_signed_at',
    ];

    protected $casts = ['employee_signed_at' => 'datetime'];

    public function employee(): BelongsTo { return $this->belongsTo(User::class, 'employee_id')->withTrashed(); }
    public function supervisor(): BelongsTo { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function ratings(): HasMany { return $this->hasMany(AppraisalRating::class); }
    public function jobItems(): HasMany { return $this->hasMany(AppraisalJobItem::class); }
    public function reviews(): HasMany { return $this->hasMany(AppraisalReview::class); }

    public function review(string $stage): ?AppraisalReview
    {
        return $this->reviews->firstWhere('stage', $stage);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /** Can this user act on the appraisal in its current status? */
    public function canBeActedBy(User $u): bool
    {
        if ($this->status === 'completed') {
            return false;
        }
        if (AppraisalAccess::isAdmin($u) && $this->status !== 'pending_employee') {
            return true;
        }

        return match ($this->status) {
            'pending_supervisor' => $u->id == $this->supervisor_id,
            'pending_employee'   => $u->id == $this->employee_id,
            'pending_hod'        => AppraisalAccess::is($u, 'hod') && $u->department_id == $this->employee->department_id,
            'pending_hr'         => AppraisalAccess::is($u, 'hr'),
            'pending_md'         => AppraisalAccess::is($u, 'md'),
            default              => false,
        };
    }

    public function visibleTo(User $u): bool
    {
        if (AppraisalAccess::seesAll($u)) {
            return true;
        }
        if ($u->id == $this->employee_id || $u->id == $this->supervisor_id) {
            return true;
        }
        return AppraisalAccess::is($u, 'hod') && $u->department_id == $this->employee->department_id;
    }

    public function advance(): void
    {
        $i = array_search($this->status, self::FLOW, true);
        $this->status = self::FLOW[min($i + 1, count(self::FLOW) - 1)];
        $this->save();
    }

    public function computeTotal(): float
    {
        $ratings = $this->ratings()->whereNotNull('marks')->get();
        if ($ratings->isEmpty()) {
            return 0;
        }
        return round($ratings->sum('marks') / ($ratings->count() * 10) * 100, 2);
    }
}
