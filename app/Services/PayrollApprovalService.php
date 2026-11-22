<?php

namespace App\Services;

use App\Models\Payslip;
use App\Models\ApprovalProcessStep;
use App\Models\ApprovalRecord;
use App\Reports\BankFileExcel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\PayslipPayment;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

use App\Actions\GeneratePayslip\Generate;
use App\Jobs\GenerateEmployeePayslipBalance;

class PayrollApprovalService
{

    // ================= APPROVE =================
    public function approve($request, Payslip $payslip, $user)
    {
        return DB::transaction(function () use ($request, $payslip, $user) {

            // ACCESS CHECK

            if (!$payslip->canApprove($user)) {
                return false;
            }

            // USER TYPE

            $isMd = $payslip->isMdUser($user);

            if ($isMd) {

                $actionStage = 'payslip_md_approve';
                $status = 'approved';
                $is_locked = 1;

                $step = ApprovalProcessStep::whereHas(
                    'approvalProcess',
                    fn($q) => $q->where('type', 'payslip_md_approve')
                )
                    ->orderByDesc('step_order')
                    ->first();

            } else {

                $step = $payslip->getUserStep($user);

                $actionStage = $payslip->payroll_stage;
                $status = 'pending';
                $is_locked = 0;
            }

            // SAFETY

            if (!$step) {
                return false;
            }

            // =====================================================
            // APPROVAL LOG
            // =====================================================

            ApprovalRecord::create([
                'payslip_id' => $payslip->id,
                'approval_step_id' => $step->id,
                'status' => 'approved',
                'note' => $request['note'] ?? null,
                'reviewed_by' => $user->id,

                // ✅ LOGIN USER STAGE
                'action_stage' => $actionStage,

                'to_stage' => null,
            ]);

            // =====================================================
            // UPDATE PAYSLIP
            // =====================================================

            $updateData = [

                'status' => $status,

                // ✅ MD/SUPER ADMIN FORCE MD STAGE
                'payroll_stage' => $actionStage,

                'current_approver_id' => $step->approver_id,
                'is_locked' => $is_locked,
            ];

            // =====================================================
            // PAYMENT METHOD
            // =====================================================

            if (
                !empty($request['payment_method'])
                && $payslip->canSetPaymentMethod($user)
            ) {
                $updateData['payment_method']
                    = $request['payment_method'];
            }

            $payslip->update($updateData);

            return true;
        });
    }

    public function sendToNextStage($request, Payslip $payslip, $user)
    {
        return DB::transaction(function () use ($request, $payslip, $user) {

            if (!$payslip->canSendToNext($user)) {
                return false;
            }

            $currentStep = $payslip->steps()->last();
            $nextStage = $payslip->nextStage();

            if (!$currentStep || !$nextStage) return false;

            // 🔥 BEFORE UPDATE
            $actionStage = $payslip->resolveActionStage($user);

            ApprovalRecord::create([
                'payslip_id' => $payslip->id,
                'approval_step_id' => $currentStep->id,
                'status' => 'sent',
                'note' => $request['note'] ?? null,
                'reviewed_by' => $user->id,

                'action_stage' => $actionStage,
                'to_stage' => $nextStage,
            ]);

            $nextStep = ApprovalProcessStep::whereHas('approvalProcess', function ($q) use ($nextStage) {
                $q->where('type', $nextStage);
            })
                ->orderBy('step_order')
                ->first();

            $payslip->update([
                'payroll_stage' => $nextStage,
                'status' => 'pending',
                'current_approver_id' => $nextStep ?->approver_id,
            ]);

        return true;
    });
    }

    public function backToStage($request, Payslip $payslip, $user)
    {
        return DB::transaction(function () use ($request, $payslip, $user) {

            $targetStage = $request['target_stage'];

            if (!$targetStage) return false;

            $targetStep = ApprovalProcessStep::whereHas('approvalProcess', function ($q) use ($targetStage) {
                $q->where('type', $targetStage);
            })
                ->orderBy('step_order')
                ->first();

            if (!$targetStep) return false;

            $currentStep = $payslip->steps()->last();

            if (!$currentStep) return false;

            // 🔥 FIXED: BEFORE CHANGE (IMPORTANT)
            $actionStage = $payslip->resolveActionStage($user);

            ApprovalRecord::create([
                'payslip_id' => $payslip->id,
                'approval_step_id' => $currentStep->id,
                'status' => 'returned',
                'note' => $request['note'] ?? null,
                'reviewed_by' => $user->id,

                // ✅ FROM WHERE ACTION HAPPENED
                'action_stage' => $actionStage,

                // ✅ WHERE IT GOES
                'to_stage' => $targetStage,
            ]);

            // update AFTER logging
            $payslip->update([
                'payroll_stage' => $targetStage,
                'status' => 'returned',
                'current_approver_id' => $targetStep->approver_id,
            ]);

            return true;
        });
    }

    // ================= REGENERATE =================
    public function reGenerate_bk($request, Payslip $payslip, $user)
    {
        return DB::transaction(function () use ($request, $payslip, $user) {

            // ACCESS CHECK

            if (!$payslip->canReGenerate($user)) {
                return false;
            }

            $isMd = $payslip->isMdUser($user);

            // ACTION STAGE

            $actionStage = ($isMd)
                ? 'payslip_md_approve'
                : $payslip->payroll_stage;

            // STEP

            $step = ApprovalProcessStep::whereHas(
                'approvalProcess',
                fn($q) => $q->where('type', $actionStage)
            )
                ->orderByDesc('step_order')
                ->first();

            if (!$step) {
                return false;
            }

            // LOG

            ApprovalRecord::create([
                'payslip_id' => $payslip->id,
                'approval_step_id' => $step->id,
                'status' => 'regenerated',
                'note' => $request['note'] ?? null,
                'reviewed_by' => $user->id,

                // ✅ LOGIN USER STAGE
                'action_stage' => $actionStage,

                'to_stage' => $actionStage,
            ]);

            // UPDATE

            $updateData = [

                'status' => 'regenerated',

                // ✅ FORCE CURRENT LOGIN STAGE
                'payroll_stage' => $actionStage,

                'current_approver_id' => $step->approver_id,
            ];

            // PAYMENT METHOD

            if (
                !empty($request['payment_method'])
                && $payslip->canSetPaymentMethod($user)
            ) {
                $updateData['payment_method']
                    = $request['payment_method'];
            }

            $payslip->update($updateData);

            return true;
        });
    }


    public function reGenerate($request, Payslip $payslip, $user)
    {
        return DB::transaction(function () use ($request, $payslip, $user) {

            if (!$payslip->canReGenerate($user)) {
                return false;
            }

            $employee = $payslip->employee;

            if (!$employee) {
                return false;
            }

            $monthDate = carbon($payslip->month_date);

            $isMd = $payslip->isMdUser($user);

            $actionStage = ($isMd)
                ? 'payslip_md_approve'
                : $payslip->payroll_stage;

            $step = ApprovalProcessStep::whereHas(
                'approvalProcess',
                fn($q) => $q->where('type', $actionStage)
            )
                ->orderByDesc('step_order')
                ->first();

            if (!$step) {
                return false;
            }

            /*
            |--------------------------------------------------
            | LOG ONLY (NO PAYSLIP DELETE)
            |--------------------------------------------------
            */

            ApprovalRecord::create([
                'payslip_id'       => $payslip->id,
                'approval_step_id' => $step->id,
                'status'           => 'regenerated',
                'note'             => $request['note'] ?? null,
                'reviewed_by'      => $user->id,
                'action_stage'     => $actionStage,
                'to_stage'         => $actionStage,
            ]);

            /*
            |--------------------------------------------------
            | 🔥 IMPORTANT: RECALCULATE USING SAME ENGINE
            |--------------------------------------------------
            */

            $dutyData = \App\Actions\GenerateTotalDutyOfEmployee::execute(
                $employee,
                carbon($payslip->start_date),
                carbon($payslip->end_date)
            );

            /*
            |--------------------------------------------------
            | UPDATE SAME PAYSLIP (NO DELETE)
            |--------------------------------------------------
            */

            $updateData = [

                'status' => 'regenerated',

                'payroll_stage' => $actionStage,

                'current_approver_id' => $step->approver_id,

            ];

            /*
            | payment method
            */

            if (
                !empty($request['payment_method'])
                && $payslip->canSetPaymentMethod($user)
            ) {
                $updateData['payment_method'] = $request['payment_method'];
            }

            /*
            |--------------------------------------------------
            | 🔥 CRITICAL: DO NOT TOUCH payslip ID
            |--------------------------------------------------
            */

            $payslip->update($updateData);

            /*
            |--------------------------------------------------
            | BALANCE UPDATE
            |--------------------------------------------------
            */

            GenerateEmployeePayslipBalance::dispatch([$employee->id]);

            return true;
        });
    }

    public function paymentConfirm_bk($request, $user)
    {
        if (!Payslip::canPaymentConfirm($user)) {
            return false;
        }

        return DB::transaction(function () use ($request, $user) {

            $monthDate = !empty($request['month'])
                ? carbon('01-' . $request['month'])
                : now()->startOfMonth();

            $query = Payslip::query()
                ->whereDate('month_date', $monthDate)
                ->where('status', 'approved')
                ->where('is_locked', 1);

            /**
             * =========================
             * FILTERS ADD HERE
             * =========================
             */

            // Employee
            if (!empty($request['employee'])) {
                $query->where('payslips.user_id', $request['employee']);
            }

            // Department
            if (!empty($request['department'])) {
                $query->where('users.department_id', $request['department']);
            }

            // Super Department
            if (!empty($request['super_department'])) {
                $query->whereHas('employee.department', function ($q) use ($request) {
                    $q->where('super_department_id', $request['super_department']);
                });
            }


            // Payment Method
            if (!empty($request['payment_method'])) {
                $query->where('payment_method', $request['payment_method']);
            }


            $payslips = $query->get();

            if ($payslips->isEmpty()) {
                return false;
            }

            foreach ($payslips as $payslip) {

                $status = 'done';
                $actionStage = $payslip->payroll_stage;
                $is_locked = 1;

                /**
                 * =========================
                 * STEP DETECTION LOGIC
                 * =========================
                 */


                $isMd = $payslip->isMdUser($user);

                if ($isMd) {

                    $actionStage = 'payslip_md_approve';

                    $step = ApprovalProcessStep::whereHas(
                        'approvalProcess',
                        fn($q) => $q->where('type', 'payslip_md_approve')
                    )
                        ->orderByDesc('step_order')
                        ->first();

                } else {

                    $step = $payslip->getUserStep($user);

                    $actionStage = $payslip->payroll_stage;
                }

                /**
                 * =========================
                 * UPDATE PAYSLIP
                 * =========================
                 */
                $payslip->update([
                    'status'    => 'done'
                ]);

                /**
                 * =========================
                 * APPROVAL RECORD
                 * =========================
                 */
                ApprovalRecord::create([
                    'payslip_id'        => $payslip->id,
                    'approval_step_id'  => $step?->id,
                    'reviewed_by'       => $user->id,
                    'status'            => $status,
                    'action_stage'      => $actionStage,
                    'to_stage'          => null,
                    'note'              => $request['remarks'] ?? null,
                ]);

                /**
                 * =========================
                 * PAYMENT ENTRY
                 * =========================
                 */
                PayslipPayment::create([
                    'user_id'           => $payslip->user_id,
                    'total_amount'      => $payslip->total_salary,
                    'paid_amount'       => $payslip->total_salary,
                    'due_amount'        => 0,
                    'previous_balance'  => 0,
                    'new_balance'       => $payslip->total_salary,
                    'payslip_date'      => $payslip->month_date,
                ]);
            }

            return true;
        });
    }

    public function paymentConfirm($request, $user)
    {
        /*
        |--------------------------------------------------------------------------
        | AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        if (!Payslip::canPaymentConfirm($user)) {
            return false;
        }

        return DB::transaction(function () use ($request, $user) {

            /*
            |--------------------------------------------------------------------------
            | MONTH FILTER
            |--------------------------------------------------------------------------
            */

            $monthDate = !empty($request['month'])
                ? carbon('01-' . $request['month'])
                : now()->startOfMonth();

            /*
            |--------------------------------------------------------------------------
            | BASE QUERY
            |--------------------------------------------------------------------------
            */

            $query = Payslip::query()

                ->select('payslips.*')

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

                ->whereDate(
                    'payslips.month_date',
                    $monthDate
                )

                ->where(
                    'payslips.status',
                    'approved'
                )

                ->where(
                    'payslips.is_locked',
                    1
                );

            /*
            |--------------------------------------------------------------------------
            | FILTER : EMPLOYEE
            |--------------------------------------------------------------------------
            */

            if (!empty($request['employee'])) {

                $query->where(
                    'payslips.user_id',
                    $request['employee']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | FILTER : DEPARTMENT
            |--------------------------------------------------------------------------
            */

            if (!empty($request['department'])) {

                $query->where(
                    'users.department_id',
                    $request['department']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | FILTER : SUPER DEPARTMENT
            |--------------------------------------------------------------------------
            */

            if (!empty($request['super_department'])) {

                $query->where(
                    'departments.super_department_id',
                    $request['super_department']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | FILTER : PAYMENT METHOD
            |--------------------------------------------------------------------------
            */

            if (!empty($request['payment_method'])) {

                $query->where(
                    'payslips.payment_method',
                    $request['payment_method']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | GET PAYSLIPS
            |--------------------------------------------------------------------------
            */

            $payslips = $query->get();

            /*
            |--------------------------------------------------------------------------
            | NO DATA
            |--------------------------------------------------------------------------
            */

            if ($payslips->isEmpty()) {
                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | PROCESS PAYMENT CONFIRMATION
            |--------------------------------------------------------------------------
            */

            foreach ($payslips as $payslip) {

                /*
                |--------------------------------------------------------------------------
                | DEFAULT VALUES
                |--------------------------------------------------------------------------
                */

                $status = 'done';

                $actionStage = $payslip->payroll_stage;

                $step = null;

                /*
                |--------------------------------------------------------------------------
                | MD USER
                |--------------------------------------------------------------------------
                */

                $isMd = $payslip->isMdUser($user);

                if ($isMd) {

                    $actionStage = 'payslip_md_approve';

                    $step = ApprovalProcessStep::query()

                        ->whereHas('approvalProcess', function ($q) {

                            $q->where(
                                'type',
                                'payslip_md_approve'
                            );
                        })

                        ->orderByDesc('step_order')

                        ->first();

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | FINANCE FINAL APPROVER
                    |--------------------------------------------------------------------------
                    */

                    $actionStage = 'payslip_finance_verify';

                    $step = ApprovalProcessStep::query()

                        ->whereHas('approvalProcess', function ($q) {

                            $q->where(
                                'type',
                                'payslip_finance_verify'
                            );
                        })

                        ->orderByDesc('step_order')

                        ->first();
                }

                /*
                |--------------------------------------------------------------------------
                | SAFETY CHECK
                |--------------------------------------------------------------------------
                */

                if (!$step) {

                    \Log::warning(
                        'Payment confirmation skipped. Step not found.',
                        [
                            'payslip_id' => $payslip->id,
                            'user_id' => $user->id,
                        ]
                    );

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | UPDATE PAYSLIP
                |--------------------------------------------------------------------------
                */

                $payslip->update([

                    'status' => 'done',
                ]);

                /*
                |--------------------------------------------------------------------------
                | APPROVAL RECORD
                |--------------------------------------------------------------------------
                */

                ApprovalRecord::create([

                    'payslip_id'       => $payslip->id,

                    'approval_step_id' => $step->id,

                    'reviewed_by'      => $user->id,

                    'status'           => $status,

                    'action_stage'     => $actionStage,

                    'to_stage'         => null,

                    'note'             => $request['remarks'] ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | PAYMENT ENTRY
                |--------------------------------------------------------------------------
                */

                PayslipPayment::create([

                    'user_id'          => $payslip->user_id,

                    'total_amount'     => $payslip->total_salary,

                    'paid_amount'      => $payslip->total_salary,

                    'due_amount'       => 0,

                    'previous_balance' => 0,

                    'new_balance'      => $payslip->total_salary,

                    'payslip_date'     => $payslip->month_date,
                ]);
            }

            return true;
        });
    }

    public function updateHeads($request, Payslip $payslip, $user)
    {
        return DB::transaction(function () use ($request, $payslip, $user) {

            // ================= ACCESS CONTROL =================
            $isMd = $payslip->isMdUser($user);
            $isHrStage = $payslip->payroll_stage === 'payslip_prepare';

            if (!($isMd || $isHrStage) || $payslip->isLocked()) {
                return false;
            }

            // ================= STEP FIND =================
            $actionStage = ($isMd)
                ? 'payslip_md_approve'
                : $payslip->payroll_stage;

            $step = ApprovalProcessStep::whereHas(
                'approvalProcess',
                fn($q) => $q->where('type', $actionStage)
            )
                ->orderByDesc('step_order')
                ->first();

            if (!$step) {
                return false;
            }

            /*
            |--------------------------------------------------
            | 🔥 UPDATE EARNING HEADS (DIRECT VALUE ONLY)
            |--------------------------------------------------
            */

            $earnHeads = collect($request['earn_heads'])->map(function ($item) {
                return [
                    'id'           => $item['id'],
                    'code'         => $item['code'],
                    'name'         => $item['name'],
                    'category'     => $item['category'],
                    'amount'       => $item['amount'],
                    'amount_type'  => $item['amount_type'],
                    'total_amount' => (float) $item['total_amount'],
                ];
            })->values()->toArray();

            /*
            |--------------------------------------------------
            | 🔥 UPDATE DEDUCTIONS (DIRECT VALUE ONLY)
            |--------------------------------------------------
            */

            $deductions = collect($request['deductions'])->map(function ($item) {
                return [
                    'id'           => $item['id'],
                    'code'         => $item['code'],
                    'name'         => $item['name'],
                    'category'     => $item['category'],
                    'amount'       => $item['amount'],
                    'amount_type'  => $item['amount_type'],
                    'total_amount' => (float) $item['total_amount'],
                ];
            })->values()->toArray();

            /*
            |--------------------------------------------------
            | SUM (NO CALCULATION LOGIC CHANGE)
            |--------------------------------------------------
            */

            $totalEarn = collect($earnHeads)->sum('total_amount');
            $totalDeduct = collect($deductions)->sum('total_amount');
            $netSalary = $totalEarn - $totalDeduct;

            /*
            |--------------------------------------------------
            | APPROVAL LOG (LIKE REGENERATE)
            |--------------------------------------------------
            */

            ApprovalRecord::create([
                'payslip_id'       => $payslip->id,
                'approval_step_id' => $step->id,
                'status'           => 'updated',   // 🔥 IMPORTANT
                'note'             => $request['note'] ?? null,
                'reviewed_by'      => $user->id,
                'action_stage'     => $actionStage,
                'to_stage'         => $actionStage,
            ]);

            /*
            |--------------------------------------------------
            | UPDATE PAYSLIP STATE
            |--------------------------------------------------
            */

            $updateData = [
                'status'              => 'updated',   // 🔥 IMPORTANT
                'payroll_stage'      => $actionStage,
                'current_approver_id' => $step->approver_id,

                // heads update
                'earn_heads'         => $earnHeads,
                'deductions'         => $deductions,

                // totals (still stored, no recalculation logic change)
                'total_earn_heads'   => $totalEarn,
                'total_deductions'   => $totalDeduct,
                'total_salary'       => $netSalary,
                'remarks'             => $request['note'] ?? null,
                'updated_at'         => now(),
            ];

            /*
            | PAYMENT METHOD
            */

            if (
                !empty($request['payment_method'])
                && $payslip->canSetPaymentMethod($user)
            ) {
                $updateData['payment_method'] = $request['payment_method'];
            }

            /*
            |--------------------------------------------------
            | APPLY UPDATE
            |--------------------------------------------------
            */

            $payslip->update($updateData);

            return true;
        });
    }

    private function getFirstStep($stage)
    {
        return ApprovalProcessStep::whereHas('approvalProcess', function ($q) use ($stage) {
            $q->where('type', $stage);
        })->orderBy('step_order')->first();
    }

    private function getNextStep($step)
    {
        return ApprovalProcessStep::where('approval_process_id', $step->approval_process_id)
            ->where('step_order', '>', $step->step_order)
            ->orderBy('step_order')
            ->first();
    }

    public function bankFile(array $payload)
    {
        $data = $payload['data'];

        $reportName = $payload['reportName'];

        $fileName = $this->makeFileName(
            $reportName,
            'xlsx'
        );

        return Excel::download(
            new BankFileExcel($data),
            $fileName
        );
    }

    protected function makeFileName(
        string $name,
        string $extension = 'xlsx'
    ): string {

        $name = str_replace(' ', '_', strtolower($name));

        return $name . '_' . now()->format('Y_m_d_H_i_s') . '.' . $extension;
    }
}