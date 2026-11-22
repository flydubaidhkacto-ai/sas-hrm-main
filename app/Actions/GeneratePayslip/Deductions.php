<?php

namespace App\Actions\GeneratePayslip;

use App\Models\DeductionItemEntry;
use App\Models\EmpBill;
use App\Models\Fine;
use App\Models\FinePayment;
use App\Models\Loan;
use App\Models\LoanPayment;

trait Deductions
{
    protected array $loan_payments_data = [];
    protected array $fine_payments_data = [];
    function add_deductions()
    {
        // deduction
        foreach ($this->deduct_head_items as $i => $item) {
            $tmp = 0;
            if (
                $item['category'] === 'loan'
                || $item['category'] === 'fine'
            ) {
                if ($item['category'] === 'loan') {
                    if (!isset($this->deduct_head_items[$i]['total_amount']))
                        $this->deduct_head_items[$i]['total_amount'] = 0;

                    $loans = Loan::final_approved()
                        ->where('loans.user_id', $this->user->id)
                        ->whereDate('loans.issue_date', '<=', $this->end_date->format('Y-m-d'))
                        // ->where(function ($query) {

                        // })
                        ->selectRaw('loans.*, SUM(loan_payments.amount) AS total_paid')
                        ->leftJoin('loan_payments', 'loans.id', '=', 'loan_payments.loan_id')
                        ->groupBy('loans.id')
                        ->havingRaw('total_paid < loans.amount OR total_paid IS NULL')
                        ->get();
                    foreach ($loans as $loan) {
                        if ($loan->cut_off_as == 'once') {
                            $payAmount = round($loan->amount - ($loan->total_paid ?: 0), 2);
                            $this->loan_payments_data[] = [
                                'user_id' => $this->user->id,
                                'amount' => $payAmount,
                                'loan_id' => $loan->id,
                                'date' => $this->from_date->format('Y-m-d'),
                            ];
                            $this->deduct_head_items[$i]['total_amount'] += ($payAmount);
                            $this->earned_deductions += ($payAmount);
                            $this->earned_total_salary -= ($payAmount);
                            $this->system_notes[] = [
                                'type' => 'deduct',
                                'name' => 'Deduction',
                                'description' => 'Loan: ' . number_format($payAmount, 2, '.', '') . " Tk",
                                'value' => $payAmount,
                                'is_incremental' => false,
                                'balance' => $this->earned_total_salary,
                            ];
                        }
                        else
                        {
                            $duePayment = $loan->paymentSchedules()
                                ->whereDoesntHave('loanPayment')
                                ->first();
                            if ($duePayment) {
                                // $this->loan_payment_schedules_data = $duePayment;
                                $this->loan_payments_data[] = [
                                    'user_id' => $this->user->id,
                                    'amount' => $duePayment->amount,
                                    'schedule_data' => $duePayment,
                                    'loan_id' => $loan->id,
                                    'date' => $this->from_date->format('Y-m-d'),
                                ];
                                $this->deduct_head_items[$i]['total_amount'] += round($duePayment->amount, 2);
                                $this->earned_deductions += round($duePayment->amount, 2);
                                $this->earned_total_salary -= round($duePayment->amount, 2);
                                $this->system_notes[] = [
                                    'type' => 'deduct',
                                    'name' => 'Deduction',
                                    'description' => 'Loan: ' . number_format($duePayment->amount, 2, '.', '') . " Tk. Loan#" . $loan->id . ', Schedule#' . $duePayment->id,
                                    'value' => $duePayment->amount,
                                    'is_incremental' => false,
                                    'balance' => $this->earned_total_salary,
                                ];
                            }
                        }
                    }
                    // if($user->id == 1001) dd($loand_payments_data, $tmp, $loans);
                }
                if ($item['category'] === 'fine') {
                    $loans = Fine::final_approved()
                        ->where('fines.user_id', $this->user->id)
                        // ->whereDate('fines.created_at', '>=', $this->from_date->format('Y-m-d')) // get all past fines
                        ->whereDate('fines.created_at', '<=', $this->end_date->format('Y-m-d'))
                        ->selectRaw('fines.*, SUM(fine_payments.amount) AS total_paid')
                        ->leftJoin('fine_payments', 'fines.id', '=', 'fine_payments.fine_id')
                        ->groupBy('fines.id')
                        ->havingRaw('total_paid < fines.amount OR total_paid IS NULL')
                        ->get();
                    foreach ($loans as $loan) {
                        if ($loan->cut_off_as == 'once') {
                            $tmp = $loan->amount - ($loan->total_paid ?: 0);
                        } else {
                            $left_to_pay = $loan->amount - ($loan->total_paid ?: 0);
                            if ($left_to_pay >= $loan->cut_off_amount) {
                                $tmp = $loan->cut_off_amount;
                            } else {
                                $tmp = $left_to_pay;
                            }
                        }
                        $this->fine_payments_data[] = [
                            'user_id' => $this->user->id,
                            //   'payslip_id' => $check->id,
                            'amount' => $tmp,
                            'fine_id' => $loan->id,
                            'date' => $this->from_date->format('Y-m-d'),
                        ];
                        if (!isset($this->deduct_head_items[$i]['total_amount']))
                            $this->deduct_head_items[$i]['total_amount'] = 0;
                        $this->deduct_head_items[$i]['total_amount'] += round($tmp, 2);
                        $this->earned_deductions += round($tmp, 2);
                        $this->earned_total_salary -= round($tmp, 2);
                        $this->system_notes[] = [
                            'type' => 'deduct',
                            'name' => 'Deduction',
                            'description' => 'Fine : ' . number_format($tmp, 2, '.', '') . " Tk" . " (" . $loan->reason . ")",
                            'value' => $tmp,
                            'is_incremental' => false,
                            'balance' => $this->earned_total_salary,
                        ];
                    }
                }
            } else if ($item['category'] === 'absent') {
                // only for show. no real deduction
                $tmp = 0;
                foreach ($this->dates as $__date => $__data) {
                    if($__data['is_weekend']) {}
                    else if($__data['holiday'] || (
                        $__data['leave'] && $__data['leave']->leave_type?->is_paid
                    )) {}
                    else if($__data['attendance']) {}
                    else {
                        $tmp += $this->dynamic_daily_salary;
                    }
                }
                $this->deduct_head_items[$i]['total_amount'] = round($tmp, 2);
                // dd(2, $pay_data_per_dates, $total_duty_report, $tmp);
            } else if ($item['category'] === 'employee_bill') {
                // only for show. no real deduction
                $tmp = 0;
                $emp_bills = EmpBill::query()
                    ->where('user_id', $this->user->id)
                    ->whereDate('date', '>=', $this->from_date->format('Y-m-d'))
                    ->whereDate('date', '<=', $this->end_date->format('Y-m-d'))
                    ->get();
                $tmp = $emp_bills->sum('amount');
                $this->deduct_head_items[$i]['total_amount'] = round($tmp, 2);
                $this->earned_deductions += round($tmp, 2);
                $this->earned_total_salary -= round($tmp, 2);
                $this->system_notes[] = [
                    'type' => 'deduct',
                    'name' => 'Employee Bill',
                    'description' => 'Employee Bill: ' . number_format($tmp, 2, '.', '') . " Tk",
                    'value' => $tmp,
                    'is_incremental' => false,
                    'balance' => $this->earned_total_salary,
                ];
                // dd(2, $pay_data_per_dates, $total_duty_report, $tmp);
            } else if ($item['category'] === 'ration') {
                if(
                    $this->emplyee_worked_days >= 20
                )
                {
                    if ($item['amount_type'] === 'fixed') {
                        $tmp = $item['amount'];
                    } else if ($item['amount_type'] === 'percentage') {
                        // percentage
                        $tmp = $item['amount'] / 100 * $this->main_basic_salary;
                    } else {
                        $tmp = $item['amount'] * $this->main_basic_salary;
                    }

                    $this->deduct_head_items[$i]['total_amount'] = round($tmp, 2);
                    $this->earned_total_salary -= round($tmp, 2);
                    $this->earned_deductions += round($tmp, 2);
                    $this->system_notes[] = [
                        'type' => 'deduct',
                        'name' => 'Deduction',
                        'description' => $item['name'] . ': ' . number_format($tmp, 2, '.', '') . " Tk",
                        'value' => $tmp,
                        'is_incremental' => false,
                        'balance' => $this->earned_total_salary,
                    ];
                }
            } else {
                if ($item['amount_type'] === 'fixed') {
                    $tmp = $item['amount'];
                } else if ($item['amount_type'] === 'percentage') {
                    // percentage
                    $tmp = $item['amount'] / 100 * $this->main_basic_salary;
                } else {
                    $tmp = $item['amount'] * $this->main_basic_salary;
                }
                // create provident fund rcords
                if ($item['category'] === 'provident_fund') {
                    if(config('app.customer') == 'sas')
                    {
                        $this->provident_fund_amount = $tmp * 2;
                    }else
                    {
                        $this->provident_fund_amount = $tmp;
                    }
                }

                $this->deduct_head_items[$i]['total_amount'] = round($tmp, 2);
                $this->earned_total_salary -= round($tmp, 2);
                $this->earned_deductions += round($tmp, 2);
                $this->system_notes[] = [
                    'type' => 'deduct',
                    'name' => 'Deduction',
                    'description' => $item['name'] . ': ' . number_format($tmp, 2, '.', '') . " Tk",
                    'value' => $tmp,
                    'is_incremental' => false,
                    'balance' => $this->earned_total_salary,
                ];
            }

            // enter from bulk deduction
            $bulkitems = DeductionItemEntry::query()
                ->where('employee_id', $this->user->id)
                ->whereBetween('date', [$this->from_date, $this->end_date])
                ->where('name', $item['name'])
                ->get();
            $total = $bulkitems->sum('amount');
            if($total > 0)
            {
                $this->deduct_head_items[$i]['total_amount'] += $total;
                $this->earned_deductions += $total;
                $this->earned_total_salary -= $total;
                $this->system_notes[] = [
                    'type' => 'deduct',
                    'name' => 'Bulk Deduction',
                    'description' => 'Bulk Deduction: ' . $item['name'] . ': ' . number_format($total, 2, '.', '') . " Tk",
                    'value' => $total,
                    'is_incremental' => false,
                    'balance' => $this->earned_total_salary,
                ];
            }
        }


        // generate the loan payments
        foreach ($this->loan_payments_data as $item) {
            $schedule_data = $item['schedule_data'] ?? null;
            if($schedule_data) unset($item['schedule_data']);

            $payment = LoanPayment::query()->create(array_merge($item, [
                'payslip_id' => $this->payslip->id,
            ]));
            if ($schedule_data) {
                $schedule_data->update([
                    'loan_payment_id' => $payment->id,
                ]);
            }
        }

        // create the fine payments
        foreach ($this->fine_payments_data as $item) {
            FinePayment::query()
                ->create(array_merge($item, ['payslip_id' => $this->payslip->id]));
        }
    }
}
