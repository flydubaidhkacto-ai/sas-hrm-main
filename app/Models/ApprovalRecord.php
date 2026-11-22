<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRecord extends Model
{
    protected $guarded = [];

    public function leave()
    {
        return $this->belongsTo(LeaveApplication::class, 'leave_id');
    }
    public function fine()
    {
        return $this->belongsTo(Fine::class, 'fine_id');
    }
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public function step()
    {
        return $this->belongsTo(ApprovalProcessStep::class, 'approval_step_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

}
