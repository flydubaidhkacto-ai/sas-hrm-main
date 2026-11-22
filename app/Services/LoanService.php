<?php

namespace App\Services;

use App\Models\Loan;
use Illuminate\Http\Request;

class LoanService
{
    /**
     * Calculate EMI based on loan type
     */
    static function calculateEMI($principal, $annualRate, $tenure, $loanType)
    {
        if ($loanType === 'reducing_balance') {
            return self::calculateReducingBalanceEMI($principal, $annualRate, $tenure);
        } else {
            return self::calculateFixedRateEMI($principal, $annualRate, $tenure);
        }
    }

    /**
     * Generate EMI schedule based on loan type
     */
    static function generateEMISchedule($principal, $annualRate, $emi, $tenure, $loanType)
    {
        if ($loanType === 'reducing_balance') {
            return self::generateReducingBalanceSchedule($principal, $annualRate, $emi, $tenure);
        } else {
            return self::generateFixedRateSchedule($principal, $annualRate, $emi, $tenure);
        }
    }

    /**
     * Calculate EMI for Reducing Balance Loan
     */
    static function calculateReducingBalanceEMI($principal, $annualRate, $tenure)
    {
        if ($tenure <= 0) {
            return 0; // No tenure means no EMI
        }

        $monthlyRate = $annualRate / 12 / 100;

        if ($monthlyRate == 0) {
            // If no interest rate, simple division of principal over tenure
            return round($principal / $tenure, 2);
        }

        $denominator = pow(1 + $monthlyRate, $tenure) - 1;

        if ($denominator == 0) {
            return 0; // This avoids division by zero
        }

        $emi = $principal * $monthlyRate * pow(1 + $monthlyRate, $tenure) / $denominator;

        return round($emi, 2);
    }

    /**
     * Calculate EMI for Fixed-Rate Loan
     */
    static function calculateFixedRateEMI($principal, $annualRate, $tenure)
    {
        $totalInterest = ($principal * $annualRate * $tenure) / 12 / 100;
        $totalPayment = $principal + $totalInterest;
        return round($totalPayment / $tenure, 2);
    }

    /**
     * Generate EMI Schedule for Reducing Balance Loan
     */
    static function generateReducingBalanceSchedule($principal, $annualRate, $emi, $tenure)
    {
        $monthlyRate = $annualRate / 12 / 100;
        $schedule = [];

        for ($i = 1; $i <= $tenure; $i++) {
            $interest = round($principal * $monthlyRate, 2);
            $principalPaid = round($emi - $interest, 2);
            $principal = round($principal - $principalPaid, 2);

            $schedule[] = [
                'month' => $i,
                'emi' => $emi,
                'principal_paid' => $principalPaid,
                'interest_paid' => $interest,
                'remaining_balance' => max($principal, 0),
            ];
        }

        return $schedule;
    }

    /**
     * Generate EMI Schedule for Fixed-Rate Loan
     */
    static function generateFixedRateSchedule($principal, $annualRate, $emi, $tenure)
    {
        $monthlyInterest = round(($principal * $annualRate / 12 / 100), 2);
        $principalPaid = round($emi - $monthlyInterest, 2);

        $schedule = [];
        for ($i = 1; $i <= $tenure; $i++) {
            $schedule[] = [
                'month' => $i,
                'emi' => round($emi, 2),
                'principal_paid' => round($principalPaid, 2),
                'interest_paid' => round($monthlyInterest, 2),
                'remaining_balance' => round(max($principal - ($i * $principalPaid), 0), 2),
            ];
        }

        return $schedule;
    }

    /**
     * Static method to preview payment schedule
     */
    static function previewPaymentSchedule(
        $totalAmount, $tenure, $loanType, $annualRate
    )
    {
        $emi = self::calculateEMI($totalAmount, $annualRate, $tenure, $loanType);
        $schedule = self::generateEMISchedule($totalAmount, $annualRate, $emi, $tenure, $loanType);

        return [
            'emi' => $emi,
            'emi_schedule' => $schedule,
        ];
    }
}
