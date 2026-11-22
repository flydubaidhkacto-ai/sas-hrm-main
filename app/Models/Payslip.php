<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ApprovalProcessModal;
use App\Models\ApprovalProcessStep;
use App\Models\ApprovalRecord;
use App\Helpers\PayrollHelper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use App\Models\Grade;

class Payslip extends Model
{
    use HasFactory, ApprovalProcessModal;

    protected $approval_process_type = 'payslip';

    protected $guarded = [];

    protected $casts = [
        'earn_heads' => 'array',
        'deductions' => 'array',
        'system_notes_json' => 'array',
    ];

    /**
     * Prevent computed metadata from being persisted.
     */
    public function getDirty(array $options = [])
    {
        $dirty = parent::getDirty($options);
        unset($dirty['computed']);
        return $dirty;
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT (SAFE DELETE CASCADE)
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($payslip) {
            $payslip->loanPayments()->delete();
            $payslip->finePayments()->delete();
            $payslip->dailyExpenses()->delete();
            $payslip->providentFundPayments()->delete();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dailyExpenses()
    {
        return $this->hasMany(EmpDailyExpense::class, 'paid_payslip_id');
    }

    public function finePayments()
    {
        return $this->hasMany(FinePayment::class, 'payslip_id');
    }

    public function loanPayments()
    {
        return $this->hasMany(LoanPayment::class, 'payslip_id');
    }

    public function providentFundPayments()
    {
        return $this->hasMany(ProvidentFundPayment::class, 'payslip_id');
    }

    public function approvalRecords()
    {
        return $this->hasMany(ApprovalRecord::class, 'payslip_id');
    }

    public function payment()
    {
        return $this->hasOne(PayslipPayment::class, 'payslip_date', 'month_date');
    }

    public function payrollCard()
    {
        return $this->hasOne(PayrollCard::class, 'user_id', 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS HELPERS
    |--------------------------------------------------------------------------
    */

    public function getLegacyStatus(): string
    {
        if (Schema::hasColumn($this->getTable(), 'status')) {
            return (string) ($this->attributes['status'] ?? $this->status ?? '');
        }

        if ($this->is_locked == 1 && $this->payroll_stage === 'payslip_md_approve') {
            return 'approved';
        }

        if ($this->payroll_stage === 'payslip_prepare') {
            return 'draft';
        }

        if ($this->payroll_stage === 'payslip_accounts_check' || $this->payroll_stage === 'payslip_finance_verify') {
            return 'pending';
        }

        return 'draft';
    }

    public function isDraft()
    {
        return $this->getLegacyStatus() === 'draft';
    }

    public function isPending()
    {
        return $this->getLegacyStatus() === 'pending';
    }

    public function isApproved()
    {
        return $this->getLegacyStatus() === 'approved';
    }

    public function isRejected()
    {
        return $this->getLegacyStatus() === 'rejected';
    }

    public function isReturned()
    {
        return $this->getLegacyStatus() === 'returned';
    }

    public function isRegenerated()
    {
        return $this->getLegacyStatus() === 'regenerated';
    }

    public function isDone()
    {
        return $this->getLegacyStatus() === 'done';
    }

    public function isUpdate()
    {
        return $this->getLegacyStatus() === 'updated';
    }

    /*
    |--------------------------------------------------------------------------
    | STAGE SYSTEM (STATIC CONFIG)
    |--------------------------------------------------------------------------
    */

    public static function stages()
    {
        return [
            'payslip_prepare' => ['next' => 'payslip_accounts_check', 'prev' => null, 'label' => 'HR'],
            'payslip_accounts_check' => ['next' => 'payslip_finance_verify', 'prev' => 'payslip_prepare', 'label' => 'Accounts'],
            'payslip_finance_verify' => ['next' => 'payslip_md_approve', 'prev' => 'payslip_accounts_check', 'label' => 'Finance'],
            'payslip_md_approve' => ['next' => null, 'prev' => 'payslip_finance_verify', 'label' => 'MD'],
        ];
    }

    public function stage()
    {
        return self::stages()[$this->payroll_stage] ?? null;
    }

    public function nextStage()
    {
        return $this->stage()['next'] ?? null;
    }

    public function previousStage()
    {
        return $this->stage()['prev'] ?? null;
    }

    public function nextStageLabel()
    {
        return $this->nextStage()
            ? self::stages()[$this->nextStage()]['label']
            : null;
    }

    public function previousStageLabel()
    {
        return $this->previousStage()
            ? self::stages()[$this->previousStage()]['label']
            : null;
    }

    public function stageNiceName()
    {
        return PayrollHelper::stageLabel($this->payroll_stage);
    }

    /*
    |--------------------------------------------------------------------------
    | OPTIMIZED STEP CACHE (IMPORTANT PERFORMANCE FIX)
    |--------------------------------------------------------------------------
    */

    public function steps()
    {
        return cache()->remember(
            "payslip_steps_{$this->payroll_stage}",
            600,
            fn() => ApprovalProcessStep::whereHas('approvalProcess', function ($q) {
                $q->where('type', $this->payroll_stage);
            })->orderBy('step_order')->get()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVAL CORE (OPTIMIZED)
    |--------------------------------------------------------------------------
    */

    public function getUserStep($user)
    {

        return $this->steps()
            ->firstWhere('approver_id', $user->id);
    }

    public function canApprove($user)
    {
        if (!$user) return false;

        // ✅ SUPER ADMIN BYPASS
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // ✅ MD CAN APPROVE ANY STAGE
        if ($this->isMdUser($user)) {
            return true;
        }

        $step = $this->getUserStep($user);
        if (!$step) return false;

        if ($this->isDraft()) return true;
        if ($this->isRegenerated()) return true;
        if ($this->isReturned()) return true;
        if ($this->isUpdate()) return true;
        if (!$this->isPending()) return false;

        $approved = $this->approvalRecords
            ->where('approval_step_id', $step->id)
            ->where('status', 'approved')
            ->isNotEmpty();

        if ($approved) return false;

        $higherApproved = $this->approvalRecords->where('status', 'approved')
            ->filter(fn($r) => optional($r->step)->step_order > $step->step_order
            )->isNotEmpty();

        return !$higherApproved;
    }

    public function isFinalApprover($user)
    {
        return optional($this->steps()->last())->approver_id == $user->id;
    }

    public function canFinalApprove($user)
    {
        // ✅ SUPER ADMIN BYPASS
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->isMdUser($user)) {
            return true;
        }

        return $this->isFinalApprover($user);
    }

    public function canSendToNext($user)
    {
        // ✅ SUPER ADMIN BYPASS
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->isMdUser($user)) {
            return true;
        }

        return $this->isFinalApprover($user) && $this->isFinalStepApproved();
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVAL STATE CHECK (OPTIMIZED)
    |--------------------------------------------------------------------------
    */

    public function getNextPendingStep()
    {
        return $this->steps()->first(function ($step) {
            return !$this->approvalRecords
                ->where('approval_step_id', $step->id)
                ->where('status', 'approved')
                ->count();
        });
    }

    public function isFinalStepApproved()
    {
        $final = $this->steps()->last();

        if (!$final) return false;

        return $this->approvalRecords
            ->where('approval_step_id', $final->id)
            ->where('status', 'approved')
            ->isNotEmpty();
    }

    public function isApprovedByPreviousOfFinal()
    {
        $steps = $this->steps();

        if ($steps->count() < 2) return false;

        $prev = $steps[$steps->count() - 2] ?? null;

        return $this->approvalRecords
            ->where('approval_step_id', optional($prev)->id)
            ->where('status', 'approved')
            ->isNotEmpty();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESS CONTROL (UNCHANGED LOGIC)
    |--------------------------------------------------------------------------
    */

    public function canViewByUser($user)
    {
        if (!$user) return false;

        // MD SEE ALL
        if ($this->isMdUser($user)) {
            return true;
        }

        if (role_can('payslip.show')) {
            return true;
        }

        if ($this->user_id == $user->id) {
            return true;
        }

        return $this->steps()
            ->contains('approver_id', $user->id);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES (OPTIMIZED)
    |--------------------------------------------------------------------------
    */

    public function scopeStageVisibleTo($query, $user)
    {
        // ✅ SUPER ADMIN BYPASS
        if ($user && $user->is_super_admin()) {
            return $query;
        }


        // MD SEE ALL
        if ($this->isMdUser($user)) {
            return $query;
        }

        $allowed = ApprovalProcessStep::where('approver_id', $user->id)
            ->join('approval_processes', 'approval_process_steps.approval_process_id', '=', 'approval_processes.id')
            ->pluck('approval_processes.type')
            ->unique();

        return $query->whereIn('payroll_stage', $allowed);
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS BADGE (ALL LOGIC MOVED TO HELPER)
    |--------------------------------------------------------------------------
    */

    public function approvalStatusLabel($user)
    {
        return PayrollHelper::statusBadge($this, $user);
    }

    public function getRowColor($status)
    {
        return match($status){
        'approved' => 'table-success',
        'rejected' => 'table-danger',
        'returned' => 'table-warning',
        'sent' => 'table-info',
        'updated'    => 'table-primary',
        default => '',
        };
    }

    public function allPreviousStages()
    {
        $stages = self::stages();

        $current = $this->payroll_stage;

        $previousStages = [];

        while ($stages[$current]['prev']) {
            $prev = $stages[$current]['prev'];
            $previousStages[] = $prev;
            $current = $prev;
        }

        return $previousStages; // ['finance', 'accounts', 'hr']
    }

    public function canSetPaymentMethod($user)
    {
        // ✅ SUPER ADMIN BYPASS
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->isMdUser($user)) {
            return true;
        }

        return in_array($this->payroll_stage, [
            'payslip_prepare',
            'payslip_md_approve'
        ]);
    }

    public function getComputedStatus($user)
    {
        return \App\Helpers\PayrollHelper::statusBadge($this, $user)['key'] ?? null;
    }


    // ===============================================
    // PAYSLIP MODEL

    public function isSuperAdmin($user = null)
    {
        $user = $user ?: auth()->user();

        return $user && $user->is_super_admin();
    }

    public function canReGenerate($user)
    {
        if (!$user) {
            return false;
        }

        // ✅ SUPER ADMIN
//        if ($this->isSuperAdmin($user)) {
//            return true;
//        }

        // ✅ MD
        if ($this->isMdUser($user)) {
            return true;
        }

        // ✅ HR FINAL APPROVER ONLY
        return
            $this->payroll_stage === 'payslip_prepare';
    }

    public function getCurrentStageFinalStep()
    {
        return $this->steps()->last();
    }

    public static function isMdUser($user)
    {
        if (!$user) {
            return false;
        }

        return ApprovalProcessStep::where('approver_id', $user->id)
            ->whereHas('approvalProcess', function ($q) {
                $q->where('type', 'payslip_md_approve');
            })
            ->exists();
    }

    public static function isHrUser($user)
    {
        if (!$user) {
            return false;
        }

        return ApprovalProcessStep::where('approver_id', $user->id)
            ->whereHas('approvalProcess', function ($q) {
                $q->where('type', 'payslip_prepare');
            })
            ->exists();
    }

    public function isLocked()
    {
        return $this->is_locked === 1;
    }

    public function resolveActionStage($user): string
    {
        if ($this->isMdUser($user)) {
            return 'payslip_md_approve';
        }

        $step = $this->getUserStep($user);

        if ($step ?->approvalProcess ?->type) {
        return $step->approvalProcess->type;
    }

      return $this->payroll_stage;
    }

    //--------------------------------------New Code--------------------------------------

    private static function baseListQuery(array $filters)
    {
        $query = self::query()
            ->with([

                // ================= EMPLOYEE =================
                'employee:id,name,department_id,designation_id,is_active',

                'employee.department:id,name,super_department_id',

                'employee.department.super_department:id,name',

                'employee.designation:id,name',

                // ================= APPROVAL =================
                'approvalRecords:id,payslip_id,approval_step_id,status,note,reviewed_by,action_stage,to_stage,created_at',

                'approvalRecords.step:id,approval_process_id,approver_id',

                'approvalRecords.reviewer:id,name',
            ])
            ->latest();

        /*
        | ACTIVE EMPLOYEE ONLY
        */

        $query->whereHas('employee', function ($q) {

            $q->where('is_active', true);
        });

        /*
        | EMPLOYEE FILTER
        */

        if (!empty($filters['employee'])) {

            $query->where(
                'user_id',
                $filters['employee']
            );
        }

        /*
        | DEPARTMENT FILTER
        */

        if (!empty($filters['department'])) {

            $query->whereHas('employee', function ($q) use ($filters) {

                $q->where(
                    'department_id',
                    $filters['department']
                );
            });
        }

        /*
        | SUPER DEPARTMENT FILTER
        */

        if (!empty($filters['super_department'])) {

            $query->whereHas(
                'employee.department',
                function ($q) use ($filters) {

                    $q->where(
                        'super_department_id',
                        $filters['super_department']
                    );
                }
            );
        }

        /*
        | DESIGNATION FILTER
        */

        if (!empty($filters['designation'])) {

            $query->whereHas('employee', function ($q) use ($filters) {

                $q->where(
                    'designation_id',
                    $filters['designation']
                );
            });
        }

        /*
        | PAYMENT METHOD FILTER
        */

        $paymentMethods = array_filter(
            (array)($filters['payment_method'] ?? [])
        );

        if (!empty($paymentMethods)) {

            $query->whereIn(
                'payment_method',
                $paymentMethods
            );
        }

        /*
        | MONTH FILTER
        */

        if (!empty($filters['month'])) {
            $query->whereDate(
                'month_date',
                carbon('01-' . $filters['month'])
            );
        }

        return $query;
    }

    private static function applyCurrentStageVisibility(
        $query,
        $user,
        $filters = []
    )
    {
        if (!empty($filters['current_stage'])) {

            $query->where(
                'payroll_stage',
                $filters['current_stage']
            );
        }

        if (!$user || $user->is_super_admin() || self::isMdUser($user)) {
            return $query;
        }

        /*
        | USER ALLOWED STAGES:
        | If you are assigned to an approval process, you will be able to view the data for that stage.

        | HR | Accounts | Finance | MD | Future Roles
        */

        $allowedStages = ApprovalProcessStep::query()
            ->where(
                'approver_id',
                $user->id
            )
            ->join(
                'approval_processes',
                'approval_process_steps.approval_process_id',
                '=',
                'approval_processes.id'
            )
            ->pluck('approval_processes.type')
            ->unique()
            ->values()
            ->toArray();

        /*
        | NO STAGE ACCESS
        */

        if (empty($allowedStages)) {

            $query->whereRaw('1 = 0');

            return $query;
        }

        /*
        | STAGE ACCESS
        */

        $query->whereIn(
            'payroll_stage',
            $allowedStages
        );

        /*
        | OPTIONAL CURRENT STAGE FILTER
        */

        return $query;
    }

    private static function applyApprovedVisibility(
        $query,
        $user
    )
    {

        if (!$user || $user->is_super_admin()) {
            return $query;
        }

        if (self::isMdUser($user)) {
            return $query;
        }

        /*
        | USER APPROVAL STAGES

        | HR, | Accounts | Finance | MD | Future Roles

        | You can only view the approved list if you are assigned to the approval process.
        |
        */

        $allowedStages = self::userApprovalStages($user);

        /*
        | NO ACCESS
        */

        if (empty($allowedStages)) {

            $query->whereRaw('1 = 0');

            return $query;
        }

        /*
        | APPROVED VISIBILITY

        | Since the approved payslip is at the final stage, | only members of the approval process | can see all approved ones.

        */

        return $query;
    }

    /*
   | READY TO SEND CHECK
   |--------------------------------------------------------------------------
    This is not DB status. This is custom computed status.

    - status must be pending
    - final step must be approved
    - next stage must be in
    - approved/rejected/returned/regenerated/draft will not be
    */

    public function isReadyToSend()
    {
        return

            // only pending
            $this->getLegacyStatus() === 'pending'

            // Not returned
            && !$this->isReturned()

            // Not approved
            && !$this->isApproved()

            // Not rejected
            && !$this->isRejected()

            // Not regenerated
            && !$this->isRegenerated()

            // Not draft
            && !$this->isDraft()

            && !$this->isUpdate()

            // final step approved
            && $this->isFinalStepApproved()

            // There is a next stage.
            && $this->nextStage();
    }

    /*
   |--------------------------------------------------------------------------
   | FINAL APPROVED CHECK
   |--------------------------------------------------------------------------
   */

    public function isApprovedLocked()
    {
        return
            $this->getLegacyStatus() === 'approved'
            && $this->is_locked == 1;
    }

    /*
   | COMMON TRANSFORM
   | All computed fields will be created here.
   */

    private static function transformList($payslips, $user)
    {
        return $payslips->transform(function ($p) use ($user) {

            $lastRecord = $p->approvalRecords
                ->sortByDesc('created_at')
                ->first();

            $lastActionStage =
                $lastRecord ?->action_stage
                ?: $p->payroll_stage;

            $isSuperAdmin =
                $p->isSuperAdmin($user);

            $p->computed = [

                /*
                | PERMISSION
                */

                'canApprove' =>
                    $p->canApprove($user),

                'canFinalApprove' =>
                    $p->canFinalApprove($user),

                'canSendNext' =>
                    $p->canSendToNext($user),

                'canView' =>
                    $p->canViewByUser($user),

                /*
                | STATUS
                */

                'status' =>

                    \App\Helpers\PayrollHelper::statusBadge(
                        $p,
                        $user
                    ),

                /*
                | STAGE
                */

                'nextStageLabel' =>
                    $p->nextStageLabel(),

                'prevStageLabel' =>
                    $p->previousStageLabel(),

                /*
                | LAST ACTION
                */

                'last_note' =>
                    $lastRecord ?->note ?? '-',

                'last_action_by' =>
                    $lastRecord ?->reviewer ?->name ?? '-',

                'last_stage' =>
                    \App\Helpers\PayrollHelper::stageLabel(
                        $lastActionStage
                    ),
            ];

            return $p;
        });
    }

    /*
   |--------------------------------------------------------------------------
   | COLLECTION PAGINATION
   |--------------------------------------------------------------------------
   */

    private static function paginateCollection(
        $items,
        $page = 1,
        $perPage = 1000
    )
    {
        return new LengthAwarePaginator(

            $items->forPage($page, $perPage),

            $items->count(),

            $perPage,

            $page,

            [
                'path' => url()->current(),

                'query' => request()->query(),
            ]
        );
    }

    /*
   | DRAFT LIST

   | Here only: pending | returned | draft | regenerated
   | There will be no ready_to_send.

*/

    public static function draftList(
        array $filters,
        $user
    )
    {

        $query = self::baseListQuery($filters);

        /*
        | CURRENT STAGE ACCESS
        */

        self::applyCurrentStageVisibility(
            $query,
            $user,
            $filters
        );

        /*
        | ALLOWED DB STATUS
        */

        $allowedStatuses = [

            'pending',

            'returned',

            'draft',

            'regenerated',

            'updated',
        ];

        /*
        |--------------------------------------------------------------------------
        | FRONTEND STATUS FILTER
        |--------------------------------------------------------------------------
        */

        $status = $filters['status'] ?? null;

        $payslips = $query->get();

        if (
            !empty($status)
            && $status !== 'all'
        ) {
            $payslips = $payslips
                ->filter(function ($p) use ($status) {
                    return $p->getLegacyStatus() === $status;
                })
                ->values();
        } else {
            $payslips = $payslips
                ->filter(function ($p) use ($allowedStatuses) {
                    return in_array($p->getLegacyStatus(), $allowedStatuses, true);
                })
                ->values();
        }

        /*
        | READY TO SEND REMOVE
        | Because it won't go on the draft list.
        */

        $payslips = $payslips
            ->filter(function ($p) {

                return !$p->isReadyToSend();
            })
            ->values();

        /*
        | COMPUTED
        */

        $payslips = self::transformList(
            $payslips,
            $user
        );

        return self::paginateCollection(

            $payslips,

            $filters['page'] ?? 1
        );
    }

    /*

    | READY TO SEND LIST
    | This is not DB status.
    | - The status must be pending.
    | - final approval complete
    | - There is a next stage.
    |
    */

    public static function readyToSendList(
        array $filters,
        $user
    )
    {
        $query = self::baseListQuery($filters);

        /*
        | CURRENT STAGE ACCESS
        */

        self::applyCurrentStageVisibility(
            $query,
            $user,
            $filters
        );

        $payslips = $query->get();

        /*
        | Computed Ready to Send Filter
        */

        $payslips = $payslips
            ->filter(function ($p) {

                return $p->isReadyToSend();
            })
            ->values();

        /*
        | COMPUTED
        */

        $payslips = self::transformList(
            $payslips,
            $user
        );

        return self::paginateCollection(

            $payslips,

            $filters['page'] ?? 1
        );
    }

    /*
   |--------------------------------------------------------------------------
   | APPROVED LIST
   | Only: status = approved and is_locked = 1
   |
   */

    public static function approvedList(
        array $filters,
        $user
    )
    {
        $query = self::baseListQuery($filters);

        /*
        | APPROVED + LOCKED
        */

        $query->where('is_locked', 1);

        /*
        | VISIBILITY
        */

        self::applyApprovedVisibility(
            $query,
            $user
        );

        $payslips = $query->get();

        /*
        | TRANSFORM
        */

        $payslips = self::transformList(
            $payslips,
            $user
        );

        return self::paginateCollection(

            $payslips,

            $filters['page'] ?? 1
        );
    }

    public static function doneList(
        array $filters,
        $user
    )
    {
        $query = self::baseListQuery($filters);

        /*
        | APPROVED + LOCKED
        */

        $query->where('is_locked', 1);

        /*
        | VISIBILITY
        */

        self::applyApprovedVisibility(
            $query,
            $user
        );

        $payslips = $query->get();

        /*
        | TRANSFORM
        */

        $payslips = self::transformList(
            $payslips,
            $user
        );

        return self::paginateCollection(

            $payslips,

            $filters['page'] ?? 1
        );
    }

    public static function userApprovalStages($user)
    {
        return ApprovalProcessStep::query()
            ->where('approver_id', $user->id)
            ->join(
                'approval_processes',
                'approval_process_steps.approval_process_id',
                '=',
                'approval_processes.id'
            )
            ->pluck('approval_processes.type')
            ->unique()
            ->values()
            ->toArray();
    }

    public function canHistoryView($user)
    {
        if (!$user) {
            return false;
        }

        // Super admin can inspect
        if ($user->is_super_admin()) {
            return true;
        }

        // MD can inspect
        if (self::isMdUser($user)) {
            return true;
        }

        return $this->canViewByUser($user);
    }


    public static function listQuery(
        array $filters,
        $user,
        string $type = 'draft'
    )
    {
        $query = self::baseListQuery($filters);

        /*
        |--------------------------------------------------------------------------
        | TYPE: APPROVED
        |--------------------------------------------------------------------------
        */

        if ($type === 'approved') {

            $query->where('is_locked', 1);

            self::applyApprovedVisibility(
                $query,
                $user
            );

            return $query;
        }

        if ($type === 'history') {

            self::applyHistoryVisibility(
                $query,
                $user
            );

            $status = $filters['status'] ?? null;

            if (
                !empty($status)
                && $status !== 'all'
                && $status !== 'ready_to_send'
            ) {

                $query->where(
                    'status',
                    $status
                );
            }

            return $query;
        }

        /*
        |--------------------------------------------------------------------------
        | STAGE VISIBILITY
        |--------------------------------------------------------------------------
        */

        self::applyCurrentStageVisibility(
            $query,
            $user,
            $filters
        );

        /*
        |--------------------------------------------------------------------------
        | TYPE: READY TO SEND
        |--------------------------------------------------------------------------
        */

        if ($type === 'ready_to_send') {

            return $query;
        }

        /*
        |--------------------------------------------------------------------------
        | TYPE: DRAFT
        |--------------------------------------------------------------------------
        */

        $allowedStatuses = [

            'pending',

            'returned',

            'draft',

            'regenerated',

            'updated',
        ];

        $status = $filters['status'] ?? null;

        return $query;
    }

    public static function historyList(
        array $filters,
        $user
    )
    {
        $query = self::baseListQuery($filters);

        /*
        |--------------------------------------------------------------------------
        | VISIBILITY
        |--------------------------------------------------------------------------
        */

        self::applyHistoryVisibility(
            $query,
            $user
        );

        /*
        |--------------------------------------------------------------------------
        | CURRENT STAGE FILTER
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['current_stage'])) {

            $query->where(
                'payroll_stage',
                $filters['current_stage']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS FILTER
        |--------------------------------------------------------------------------
        */

        $status = $filters['status'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | APPROVED
        |--------------------------------------------------------------------------
        | approved + locked only
        */

        if ($status === 'approved') {

            $query->where('is_locked', 1);
        } /*
        |--------------------------------------------------------------------------
        | DONE
        |--------------------------------------------------------------------------
        | done + locked only
        */

        elseif ($status === 'done') {

            $query->where('is_locked', 1);
        } /*
        |--------------------------------------------------------------------------
        | NORMAL STATUS
        |--------------------------------------------------------------------------
        */

        elseif (
            !empty($status)
            && $status !== 'all'
            && $status !== 'ready_to_send'
        ) {
            // legacy status filter is applied in PHP after fetching data since
            // the current schema no longer exposes the old status column.
        }

        /*
        |--------------------------------------------------------------------------
        | GET DATA
        |--------------------------------------------------------------------------
        */

        $payslips = $query->get();

        /*
        |--------------------------------------------------------------------------
        | READY TO SEND
        |--------------------------------------------------------------------------
        | computed status
        */

        if ($status === 'ready_to_send') {

            $payslips = $payslips
                ->filter(function ($p) {

                    return $p->isReadyToSend();
                })
                ->values();
        } /*
        |--------------------------------------------------------------------------
        | REMOVE READY TO SEND
        |--------------------------------------------------------------------------
        | from all other statuses
        */

        elseif (
            $status !== 'done'
        ) {

            $payslips = $payslips
                ->filter(function ($p) {

                    return !$p->isReadyToSend();
                })
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | TRANSFORM
        |--------------------------------------------------------------------------
        */

        $payslips = self::transformList(
            $payslips,
            $user
        );

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        return self::paginateCollection(

            $payslips,

            $filters['page'] ?? 1
        );
    }

    private static function applyHistoryVisibility($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // ✅ FULL ACCESS ROLES
        if ($user->is_super_admin() || self::isMdUser($user)) {
            return $query;
        }

        // 🔥 check if user is in ANY approval process
        $hasAnyAccess = ApprovalProcessStep::where('approver_id', $user->id)->exists();

        if (!$hasAnyAccess) {
            return $query->whereRaw('1 = 0');
        }

        // ✅ IMPORTANT FIX:
        // assigned user → can see ALL payslips (not stage restricted)
        return $query;
    }

    public static function canPaymentConfirm($user): bool
    {
        if (!$user) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | MD USER
        |--------------------------------------------------------------------------
        */

        if (self::isMdUser($user)) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | FINANCE FINAL APPROVER
        |--------------------------------------------------------------------------
        */

        $financeFinalStep = ApprovalProcessStep::query()
            ->whereHas('approvalProcess', function ($q) {

                $q->where('type', 'payslip_finance_verify');
            })
            ->orderByDesc('step_order')
            ->first();

        if (!$financeFinalStep) {
            return false;
        }

        return $financeFinalStep->approver_id == $user->id;
    }

    public static function bankFileData(array $filters = [])
    {
        $query = self::query()
            /*
            |--------------------------------------------------------------------------
            | SELECT
            |--------------------------------------------------------------------------
            */

            ->select([

                'payslips.total_salary',

                'users.account_number',

                'users.account_holder_name',
            ])
            /*
            |--------------------------------------------------------------------------
            | JOINS
            |--------------------------------------------------------------------------
            */

            ->join(
                'users',
                'payslips.user_id',
                '=',
                'users.id'
            )
            ->leftJoin(
                'departments',
                'users.department_id',
                '=',
                'departments.id'
            )
            /*
            |--------------------------------------------------------------------------
            | BASE FILTER
            |--------------------------------------------------------------------------
            */

            ->whereIn(
                'payslips.status',
                ['approved', 'done']
            )
            ->where(
                'payslips.payment_method',
                'bank'
            )
            ->where(
                'payslips.is_locked',
                1
            )
            ->where(
                'users.is_active',
                1
            );

        /*
        |--------------------------------------------------------------------------
        | EMPLOYEE FILTER
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['employee'])) {

            $query->where(
                'payslips.user_id',
                $filters['employee']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DEPARTMENT FILTER
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['department'])) {

            $query->where(
                'users.department_id',
                $filters['department']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SUPER DEPARTMENT FILTER
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['super_department'])) {

            $query->where(
                'departments.super_department_id',
                $filters['super_department']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MONTH FILTER
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['month'])) {

            $monthDate = carbon(
                '01-' . $filters['month']
            );

            $query->whereDate(
                'payslips.month_date',
                $monthDate
            );

        } else {

            $query->whereDate(
                'payslips.month_date',
                now()->startOfMonth()
            );
        }

        return $query->get();
    }

    public static function bankSalaryReport(array $filters = [])
    {
        $query = self::query()
            ->with([
                'employee:id,name,employee_id,join_date,department_id,designation_id,account_number,account_holder_name',

                'employee.department:id,name,super_department_id',
                'employee.department.super_department:id,name',
                'employee.designation:id,name',
                'employee.grade:id,name',
            ]);

        $status = $filters['status'] ?? null;

        $query->when($status && $status !== 'all', function ($q) use ($status) {

            if ($status === 'approved') {

                $q->where('status', 'approved');

            } elseif ($status === 'done') {

                $q->where('status', 'done');

            } elseif ($status === 'pending') {

                $q->whereNotIn('status', ['approved', 'done']);
            }
        });

        // ================= FILTERS =================

        if (!empty($filters['payment_method'])) {
            $query->whereIn('payment_method', (array)$filters['payment_method']);
        }

        if (!empty($filters['employee'])) {
            $query->where('user_id', $filters['employee']);
        }

        if (!empty($filters['department'])) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $filters['department'])
            );
        }

        if (!empty($filters['super_department'])) {
            $query->whereHas('employee.department', fn($q) => $q->where('super_department_id', $filters['super_department'])
            );
        }

        if (!empty($filters['designation'])) {
            $query->whereHas('employee', fn($q) => $q->where('designation_id', $filters['designation'])
            );
        }

        $query->whereDate(
            'month_date',
            !empty($filters['month'])
                ? carbon('01-' . $filters['month'])
                : now()->startOfMonth()
        );

        $payslips = $query->orderBy('user_id')->get();

        // ================= GROUPING (SAFE VERSION) =================

        return $payslips
            ->groupBy(function ($p) {
                return $p->employee ?->department ?->super_department ?->id ?? 0;
        })
            ->map(function ($stationGroup) {

                $first = $stationGroup->first();

                return [
                    'station' => $first ?->employee ?->department ?->super_department,

                'departments' => $stationGroup
                    ->groupBy(function ($p) {
                        return $p->employee ?->department ?->id ?? 0;
                    })
                    ->map(function ($deptGroup) {

                        $first = $deptGroup->first();

                        return [
                            'department' => $first ?->employee ?->department,
                            'employees' => $deptGroup,
                        ];
                    }),
            ];
        });
    }

    public function getGradeNameAttribute()
    {
        $gradeId = collect($this->earn_heads)->first()['grade_id'] ?? null;

        return Grade::find($gradeId) ?->name;
    }

    public function getStageApproval($stage, $status)
    {

        $query=$this->approvalRecords()
            ->where('action_stage', $stage);

        if (!empty($status) && $status !== 'all') {

            if ($status === 'approved') {
                $query->whereIn('status', ['approved', 'sent']);
            }

            elseif ($status === 'done') {
                $query->whereIn('status', ['done']);
            }

            elseif ($status === 'pending') {
                $query->whereNotIn('status', ['approved', 'sent', 'done']);
            }
        }

        return $query->latest('created_at')
            ->first();
    }

    public function getStageUser($stage, $status)
    {
        return $this->getStageApproval($stage, $status) ?->reviewer;
    }

    /*
|--------------------------------------------------------------------------
| SUMMARY REPORT DATA
|--------------------------------------------------------------------------
*/

    public static function summaryReport(array $filters = [])
    {
        $query = self::query()
            ->with([
                'employee.department.super_department:id,name'
            ]);

        /*
        |--------------------------------------------------------------------------
        | STATUS FILTER
        |--------------------------------------------------------------------------
        */

        $status = $filters['status'] ?? null;

        $query->when($status && $status !== 'all', function ($q) use ($status) {

            if ($status === 'approved') {

                $q->where('status', 'approved');

            } elseif ($status === 'done') {

                $q->where('status', 'done');

            } elseif ($status === 'pending') {

                $q->whereNotIn('status', ['approved', 'done']);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['payment_method'])) {

            $query->whereIn(
                'payment_method',
                (array)$filters['payment_method']
            );
        }

        if (!empty($filters['employee'])) {

            $query->where('user_id', $filters['employee']);
        }

        if (!empty($filters['department'])) {

            $query->whereHas('employee', function ($q) use ($filters) {

                $q->where('department_id', $filters['department']);
            });
        }

        if (!empty($filters['super_department'])) {

            $query->whereHas('employee.department', function ($q) use ($filters) {

                $q->where(
                    'super_department_id',
                    $filters['super_department']
                );
            });
        }

        if (!empty($filters['designation'])) {

            $query->whereHas('employee', function ($q) use ($filters) {

                $q->where(
                    'designation_id',
                    $filters['designation']
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | MONTH
        |--------------------------------------------------------------------------
        */

        $query->whereDate(
            'month_date',
            !empty($filters['month'])
                ? carbon('01-' . $filters['month'])
                : now()->startOfMonth()
        );

        $payslips = $query->get();

        /*
        |--------------------------------------------------------------------------
        | STATION GROUPING
        |--------------------------------------------------------------------------
        */

        $stations = $payslips
            ->groupBy(function ($p) {

                return $p->employee
                    ?->department
                    ?->super_department
                    ?->name
                ?? 'N/A';
        })
            ->map(function ($items, $stationName) {

                $pf = 0;
                $tax = 0;
                $bankPay = 0;
                $cashPay = 0;
                $salary = 0;

                foreach ($items as $payslip) {

                    $salary += $payslip->total_salary;

                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT METHOD
                    |--------------------------------------------------------------------------
                    */

                    if ($payslip->payment_method === 'bank') {

                        $bankPay += $payslip->total_salary;
                    }

                    if ($payslip->payment_method === 'cash') {

                        $cashPay += $payslip->total_salary;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | DEDUCTIONS
                    |--------------------------------------------------------------------------
                    */

                    foreach (($payslip->deductions ?? []) as $deduction) {

                        $category = $deduction['category'] ?? null;

                        $amount = (float)($deduction['total_amount'] ?? 0);

                        if ($category === 'provident_fund') {

                            $pf += $amount;
                        }

                        if ($category === 'tax') {

                            $tax += $amount;
                        }
                    }
                }

                return [

                    'station' => $stationName,

                    /*
                    |--------------------------------------------------------------------------
                    | SECTION 1
                    |--------------------------------------------------------------------------
                    */

                    'employee_pf' => $pf,

                    'sas_pf' => $pf,

                    'pf_total' => $pf + $pf,

                    /*
                    |--------------------------------------------------------------------------
                    | SECTION 2
                    |--------------------------------------------------------------------------
                    */

                    'tax' => $tax,

                    /*
                    |--------------------------------------------------------------------------
                    | SECTION 3
                    |--------------------------------------------------------------------------
                    */

                    'bank_pay' => $bankPay,

                    'cash_pay' => $cashPay,

                    'salary_total' => $bankPay + $cashPay,

                    /*
                    |--------------------------------------------------------------------------
                    | SECTION 4
                    |--------------------------------------------------------------------------
                    */

                    'sas_salary' => $salary,

                    'sas_pf_contribution' => $pf,

                    'employee_tax' => $tax,

                    'employee_pf' => $pf,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL
        |--------------------------------------------------------------------------
        */

        $totals = [

            'employee_pf' => $stations->sum('employee_pf'),

            'sas_pf' => $stations->sum('sas_pf'),

            'pf_total' => $stations->sum('pf_total'),

            'tax' => $stations->sum('tax'),

            'bank_pay' => $stations->sum('bank_pay'),

            'cash_pay' => $stations->sum('cash_pay'),

            'salary_total' => $stations->sum('salary_total'),

            'sas_salary' => $stations->sum('sas_salary'),

            'sas_pf_contribution' => $stations->sum('sas_pf_contribution'),

            'employee_tax' => $stations->sum('employee_tax'),

            'employee_pf_total' => $stations->sum('employee_pf'),
        ];

        return [

            'stations' => $stations,

            'totals' => $totals,

            'lastPayslip' => $payslips->last(),
        ];
    }

    public function getSalarySummary()
    {
        return [

            // attendance
            'present_days'        => $this->working_days ?? 0,
            'total_working_days'  => $this->total_working_days ?? 0,
            'total_absent_days'   => $this->total_absent_days ?? 0,
            'total_late_days'     => $this->total_late_days ?? 0,
            'approved_late_days'  => $this->approved_late_days ?? 0,

            // time
            'worked_hours' => seconds_to_hh_mm($this->total_worked_seconds ?? 0),
            'late_hours'   => seconds_to_hh_mm($this->total_late_seconds ?? 0),

            // salary
            'basic_salary'      => $this->basic_salary ?? 0,
            'gross_salary'      => $this->total_earn_heads ?? 0,
            'total_deductions'  => $this->total_deductions ?? 0,
            'net_salary'        => $this->total_salary ?? 0,

            // payment
            'payment_method' => ucfirst($this->payment_method ?? 'bank'),

            // remarks
            'remarks' => $this->remarks,
        ];
    }

    public function isViewMdAndHR($user): bool
    {
        if (!$user) {
            return false;
        }

        // MD OR HR allowed
        if (self::isMdUser($user) || $this->isHrUser($user)) {
            return true;
        }

        return false;

    }


}