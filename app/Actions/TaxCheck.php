<?php

namespace App\Actions;

use App\Models\Payslip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TaxCheck {
    public static function caluclate_tax($deductions)
    {
        foreach ($deductions as $key => $value) {
            if($value["category"] == 'tax')
            {
                return $value['amount'];
            }
        }
    }

    public static function caluclate_total_tax($payslips)
    {
       $total = 0;
        foreach ($payslips as $key => $item) {
            $deductions = $item['deductions'];
            foreach ($deductions as $key => $value) {
                if($value['category'] == 'tax') {
                    $total = $total + $value['amount'];
                }
            }
        }
        return $total;
    }

    static function handle($payslips)
    {
        $employeeDeductions = [];
        $total = 0;
        foreach ($payslips as $payslip)
        {
            $employeeId = $payslip->user_id;
            $tax = self::caluclate_tax($payslip->deductions);
            $total += $tax;
            if (isset($employeeDeductions[$employeeId]['tax'])) {
                $employeeDeductions[$employeeId]['tax'] += $tax;
            } else {
                $employeeDeductions[$employeeId]['tax'] = $tax;
            }
            if(!isset($employeeDeductions[$employeeId]['employee']))
                $employeeDeductions[$employeeId]['employee'] = $payslip->employee;
            if(!isset($employeeDeductions[$employeeId]['months']))
                $employeeDeductions[$employeeId]['months'] = [];
            $employeeDeductions[$employeeId]['months'][] = [
                'month' => $payslip->month_date,
                'tax' => $tax
            ];
        }
        return ['total' => $total, 'employees' => $employeeDeductions];
    }
}
