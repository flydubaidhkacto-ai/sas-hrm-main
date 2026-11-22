<?php

namespace App\Helpers;

class PayrollHelper
{
    public static function ordinal($number)
    {
        $ends = ['th','st','nd','rd','th','th','th','th','th','th'];

        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }

        return $number . $ends[$number % 10];
    }

    public static function stageLabel($stage)
    {
        return [
                'payslip_prepare' => 'HR',
                'payslip_accounts_check' => 'Accounts',
                'payslip_finance_verify' => 'Finance',
                'payslip_md_approve' => 'MD'
            ][$stage] ?? $stage;
    }

    public static function statusBadge($payslip, $user = null)
    {
        if ($payslip->isDraft()) {
            return ['label' => 'Draft', 'class' => 'secondary'];
        }

        if ($payslip->isDone()) {
            return [
                'label' => 'Done',
                'class' => 'success'
            ];
        }

        if ($payslip->isReturned()) {
            return [
                'label' => 'Returned',
                'class' => 'warning'
            ];
        }

        if ($payslip->isRegenerated()) {
            return [
                'label' => 'Regenerated',
                'class' => 'info'
            ];
        }

        if ($payslip->isUpdate()) {
            return [
                'label' => 'Updated',
                'class' => 'primary'
            ];
        }

        if ($payslip->isRejected()) {
            return ['label' => 'Rejected', 'class' => 'danger'];
        }

        // 🔥 NEW: Final step done but not sent yet
        if (
            !$payslip->isReturned()
            && $payslip->isFinalStepApproved()
            && $payslip->nextStage() // next stage আছে
        ) {
            return ['label' => 'Ready to Send', 'class' => 'warning'];
        }

        if (!$payslip->isReturned() && $payslip->isApproved()) {
            return ['label' => 'Approved', 'class' => 'success'];
        }

        if (!$payslip->isReturned() && $payslip->isFinalStepApproved()) {
            return ['label' => 'Final Approved', 'class' => 'success'];
        }

        if ($payslip->isApprovedByPreviousOfFinal()) {
            return ['label' => 'Pending Final Approver', 'class' => 'warning'];
        }

        if ($user && $payslip->canApprove($user)) {
            return ['label' => 'Pending My Action', 'class' => 'primary'];
        }

        $next = $payslip->getNextPendingStep();

        if ($next) {
            return [
                'label' => 'Pending ' . self::ordinal($next->step_order) . ' Approver',
                'class' => 'warning'
            ];
        }

        return ['label' => 'Pending', 'class' => 'warning'];
    }
}