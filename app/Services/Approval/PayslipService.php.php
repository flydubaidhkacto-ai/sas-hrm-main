<?php
namespace App\Services\Approval;

class PayslipService
{
    public function onApprove($payslip, $user, $data)
    {
        if ($data['is_final']) {

            $payslip->update([
                'status' => 'approved'
            ]);

            // 🔥 Lock salary
            // 🔥 Generate payment
        }
    }

    public function onReject($payslip, $user)
    {
        $payslip->update([
            'status' => 'rejected'
        ]);
    }
}