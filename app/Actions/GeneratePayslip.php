<?php

namespace App\Actions;

use App\Http\Controllers\ReportController;
use App\Models\Attendance;
use App\Models\DeductionItemEntry;
use App\Models\DutyBonusRecord;
use App\Models\EmpBill;
use App\Models\EmpDailyExpense;
use App\Models\EmployeeReward;
use App\Models\Fine;
use App\Models\FinePayment;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanPaymentScheduleForInterest;
use App\Models\Payslip;
use App\Models\ProvidentFundPayment;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class GeneratePayslip
{
    static function execute(User $user, Carbon $date, bool $recreate = false)
    {
        $generate = new self(
            $user,
            $date,
            $recreate
        );
        return ($generate->handle());
    }

    /*
        [type: string, name: string, description: string, value: number, is_incremental: bool, balance: number]
    */
    protected $system_notes = [];
    protected $earn_head_items = [];
    protected $deduct_head_items = [];
    protected $base_salary = 0;
    protected $main_base_salary = 0;
    protected $total_salary = 0;
    protected $total_earn_heads = 0;
    protected $total_deductions = 0;
    protected $total_duty_report = null;
    protected $pay_data_per_dates = null;
    protected $working_days = 0;
    protected $total_worked_days = 0;
    protected $total_worked_seconds = 0;
    protected $total_overtimed_seconds = 0;
    protected $salary_per_day = 0;
    protected $shift = null;
    protected ?Carbon $from_date = null;
    protected ?Carbon $working_from_date = null;
    protected ?Carbon $end_date = null;
    protected array $loan_payments_data = [];
    protected array $fine_payments_data = [];
    protected ?float $provident_fund_amount = null;
    protected Collection $holidays;
    protected string $paytype;
    protected ?LoanPaymentScheduleForInterest $loan_payment_schedules_data = null;

    function __construct(
        protected User $user,
        protected Carbon $date,
        protected bool $recreate = false,
    ) {
        $this->paytype = $this->user->paytype;
        $this->from_date = $this->date->copy()->startOfMonth();
        $this->working_from_date = $this->date->copy()->startOfMonth();
        $this->end_date = $this->date->copy()->endOfMonth();

        // dd(config('services.have_broken_payslip_period'));
        if (config('services.have_broken_payslip_period')) {
            $this->end_date = $this->date->copy()->setDate(
                $this->date->year,
                $this->date->month,
                config('services.have_broken_payslip_period')
            );
            $this->from_date = $this->end_date->copy()->addDay()->subMonth();
            $this->working_from_date = $this->end_date->copy()->addDay()->subMonth();
        }
        $join_date = $this->user->join_date;

        if ($join_date && Carbon::parse($join_date)->greaterThan($this->from_date)) {
            // $this->from_date = Carbon::parse($join_date);
            $this->working_from_date = Carbon::parse($join_date);
            if($this->end_date->lessThan($this->working_from_date))
            {
                return null;
            }
        }

        $this->holidays = Holiday::forUser($this->user)
            ->whereDate('date', '>=', $this->working_from_date->format('Y-m-d'))
            ->whereDate('date', '<=', $this->end_date->format('Y-m-d'))
            ->get();
    }

    function handle()
    {
        $this->system_notes[] = [
            'type' => 'info',
            'name' => 'Start-End Date',
            'description' => 'Start Date: ' . $this->working_from_date->format('d M Y') . ' End Date: ' . $this->end_date->format('d M Y'),
        ];

        $previous_payslip = $this->user->payslips()
            ->whereDate('month_date', $this->date->format('Y-m-d'))
            ->first();
        if ($previous_payslip) {
            // payslip related data will be adjusted from payslip model deleting event
            $previous_payslip->delete();
        }

        if (!$this->step_1_init()) return 1;
        if (!$this->step_2_get_total_salary_from_attendance_working_time()) return 2;
        if (!$this->step_3_add_public_holiday_salary()) return 3; // (because they are paid)
        if (!$this->step_4_add_paid_leave_salary()) return 4;
        $this->earn_head_items[0]['total_amount'] = round($this->total_salary, 2);

        if (!$this->step_5_add_earn_heads()) return 5;
        if (!$this->step_6_add_deductions()) return 6;

        $data = [
            'month_date' => $this->date->format('Y-m-d'),
            'total_earn_heads' => number_format($this->total_earn_heads, 2, '.', ''),
            'total_deductions' => number_format($this->total_deductions, 2, '.', ''),
            'basic_salary' => number_format($this->base_salary, 2, '.', ''),
            'total_salary' => number_format($this->total_salary, 2, '.', ''),
            'working_days' => $this->working_days,
            'pay_type' => $this->paytype,
            'created_by' => user()->id,
            'earn_heads' => ($this->earn_head_items),
            'deductions' => ($this->deduct_head_items),
            'system_notes_json' => ($this->system_notes),
            'total_holidays' => $this->holidays->count(),
            'start_date' => $this->working_from_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'status' => 'draft',
            'payroll_stage' => 'payslip_prepare',
        ];

        // create a new payslip
        $payslip = $this->user->payslips()->create($data);

        // generate the loan payments
        foreach ($this->loan_payments_data as $item) {
            $schedule_data = $item['schedule_data'] ?? null;
            if($schedule_data) unset($item['schedule_data']);

            $payment = LoanPayment::query()->create(array_merge($item, [
                'payslip_id' => $payslip->id,
            ]));
            if ($schedule_data) {
                $schedule_data->update([
                    'loan_payment_id' => $payment->id,
                ]);
            }
        }

        // create the fine payments
        foreach ($this->fine_payments_data as $item) {
            FinePayment::query()->create(array_merge($item, ['payslip_id' => $payslip->id]));
        }

        // dd($this->user->id);
        // generate provident fund payment
        $this->generateProvidentFundPayment($payslip);

        return $payslip;
    }

    protected function step_1_init()
    {

        $attendances = Attendance::query()
            ->where('date', '>=', $this->working_from_date->format('Y-m-d'))
            ->where('date', '<=', $this->end_date->format('Y-m-d'))
            ->where('employee_id', $this->user->id)
            ->orderBy('date', 'asc')
            ->get();
        // dd(
        //     $attendances->toArray(), $this->working_from_date->format('Y-m-d'), $this->end_date->format('Y-m-d'),
        // );
        $this->earn_head_items = $this->user->payroll_card->grade
            ? $this->user->payroll_card->grade->items->toArray()
            : $this->user->payroll_card->earn_heads->toArray();
        $this->deduct_head_items = $this->user->payroll_card->deductions->toArray();
        $tmp_base_salary = $this->user->payroll_card->basic_salary;

        // get basic to use in percentage/multiply
        $basicIsUsingPercentage = $this->user->payroll_card->earn_heads()->where('category', 'basic')->first()?->amount_type !== 'fixed';
        $this->main_base_salary = ($basicIsUsingPercentage ? $this->user->payroll_card->gross_salary : $tmp_base_salary) + 0;

        $this->base_salary = $tmp_base_salary + 0;
        $this->total_salary = 0;
        $this->total_earn_heads = 0;
        $this->total_deductions = 0;
        $this->total_duty_report = (new ReportController)->generate_total_duty_of_employee($this->user, $this->from_date, $this->end_date);
        // dd($this->total_duty_report);
        if (!$tmp_base_salary || !$this->user->shift) {
            return null;
        }

        // get daily woring time from shift
        $this->shift = $this->user->shift;
        $this->pay_data_per_dates = GenerateDailyWorkingData::execute($attendances, $this->from_date, $this->end_date, $this->user);
        // dd($this->pay_data_per_dates);

        $this->working_days = $this->pay_data_per_dates['worked_days'];
        $this->total_worked_days = $this->pay_data_per_dates['max_working_days'];
        $this->total_worked_seconds = $this->pay_data_per_dates['worked_seconds'];
        $this->total_overtimed_seconds = $this->pay_data_per_dates['overtimed_seconds'];
        $this->salary_per_day = $this->pay_data_per_dates['salary_per_day'];

        $this->system_notes[] = [
            'type' => 'info',
            'name' => 'Generating on ' . $this->paytype . ' basis',
            'description' => 'Active Working days: ' . $this->working_days . ', Max working days: ' . $this->total_worked_days,
            'value' => 0,
            'is_incremental' => null,
            'balance' => $this->total_salary,
        ];
        return true;
    }
    protected function step_2_get_total_salary_from_attendance_working_time()
    {
        if ($this->paytype === 'monthly') {
            $this->total_salary = $this->base_salary + 0; //$salary_per_day * $total_worked_days;
            $this->system_notes[] = [
                'type' => 'earn',
                'name' => 'Basic Salary',
                'description' => 'Monthly Rate: ' . number_format($this->total_salary, 2, '.', '') . " Tk",
                'value' => $this->total_salary,
                'is_incremental' => true,
                'balance' => $this->total_salary,
            ];
        } elseif ($this->paytype === 'daily') {
            $amount = $this->salary_per_day * $this->working_days;
            $this->total_salary = $amount;
            $this->system_notes[] = [
                'type' => 'info',
                'name' => 'Daily Rate',
                'description' => 'Daily Working Rate: ' . number_format($this->salary_per_day, 2, '.', '') . " Tk",
                'value' => 0,
                'is_incremental' => null,
                'balance' => $this->total_salary,
            ];
            $this->system_notes[] = [
                'type' => 'earn',
                'name' => 'Basic Salary',
                'description' => 'Working day salary: ' . number_format($amount, 2, '.', '') . " Tk",
                'value' => $amount,
                'is_incremental' => true,
                'balance' => $this->total_salary,
            ];
        } elseif ($this->paytype === 'hourly') {
            // dd($this->pay_data_per_dates);
            foreach ($this->pay_data_per_dates['dates'] as $tmp_date => $_date) {
                if (
                    is_numeric($_date['salary_per_seconds'] ?? false)
                    && ($_date['is_working_day'] ?? false)
                ) {
                    $salary_per_seconds = $_date['salary_per_seconds'];
                    if ($_date['worked_time'] ?? false) {
                        $income = $salary_per_seconds * $_date['worked_time'];
                        $this->total_salary += $income;
                        $this->system_notes[] = [
                            'type' => 'info',
                            'name' => 'Working hour [' . $tmp_date . ']',
                            'description' => 'Hourly rate: ' . number_format($salary_per_seconds * 60 * 60, 2, '.', '') . " Tk, Working hour: " . seconds_to_hh_mm_ss($_date['worked_time']),
                            'value' => 0,
                            'is_incremental' => null,
                            'balance' => $this->total_salary,
                        ];
                        $this->system_notes[] = [
                            'type' => 'earn',
                            'name' => 'Earned [' . $tmp_date . ']',
                            'description' => 'Earned: ' . number_format($income, 2, '.', '') . " Tk",
                            'value' => $income,
                            'is_incremental' => true,
                            'balance' => $this->total_salary,
                        ];
                    }
                }
            }
        }

        // add grace days
        if(config('app.customer') == 'asp' && $this->pay_data_per_dates['grace_days'])
        {
            $income = $this->salary_per_day * $this->pay_data_per_dates['grace_days'];
            $this->total_salary += $income;
            $this->system_notes[] = [
                'type' => 'earn',
                'name' => 'Grace Days',
                'description' => 'Total grade days: ' . $this->pay_data_per_dates['grace_days'],
                'value' => $income,
                'is_incremental' => true,
                'balance' => $this->total_salary,
            ];
        }
        return true;
    }
    protected function step_3_add_public_holiday_salary()
    {
        // no paid holiday for monthly salary
        if ($this->paytype === 'monthly') return true;

        foreach ($this->holidays as $hol) {
            if ($this->pay_data_per_dates['dates'][$hol->date] ?? false) {
                $hol_salary = $this->pay_data_per_dates['dates'][$hol->date]['salary_per_seconds'] * $this->pay_data_per_dates['dates'][$hol->date]['max_working_seconds'];
                $this->total_salary += $hol_salary;
                $this->system_notes[] = [
                    'type' => 'earn',
                    'name' => 'Holiday Salary [' . $hol->date . ']',
                    'description' => 'Holiday Salary: ' . number_format($hol_salary, 2, '.', '') . " Tk",
                    'value' => $hol_salary,
                    'is_incremental' => true,
                    'balance' => $this->total_salary,
                ];
            }
        }
        return true;
    }
    protected function step_4_add_paid_leave_salary()
    {
        if ($this->paytype !== 'monthly') {
            $leaves = LeaveApplication::final_approved()
                ->whereHas('leave_type', function ($query) {
                    $query->where('is_paid', true);
                })
                ->where('employee_id', $this->user->id)
                ->get();
            foreach ($leaves as $leave) {
                $__dates = generateDateRange(Carbon::parse($leave->start_date), Carbon::parse($leave->end_date));
                foreach ($__dates as $tmp_date) {
                    if (
                        ($this->pay_data_per_dates['dates'][$tmp_date] ?? false)
                        && ($this->pay_data_per_dates['dates'][$tmp_date]['salary_per_seconds'] ?? false)
                    ) {
                        if ($leave->is_hourly)
                        {
                            $totalSeconds = Carbon::parse($leave->start_time)->diffInSeconds(Carbon::parse($leave->end_time));
                            $leave_salary = $this->pay_data_per_dates['dates'][$tmp_date]['salary_per_seconds'] * $totalSeconds;
                            $this->total_salary += $leave_salary;
                            $this->system_notes[] = [
                                'type' => 'earn',
                                'name' => 'Hourly Leave Salary [' . $tmp_date . ' ' . $leave->start_time . ' - ' . $leave->end_time . ']',
                                'description' => 'Hourly Leave Salary: ' . number_format($leave_salary, 2, '.', '') . " Tk",
                                'value' => $leave_salary,
                                'is_incremental' => true,
                                'balance' => $this->total_salary,
                            ];
                        }
                        else {
                            $leave_salary = $this->pay_data_per_dates['dates'][$tmp_date]['salary_per_seconds'] * $this->pay_data_per_dates['dates'][$tmp_date]['max_working_seconds'];
                            $this->total_salary += $leave_salary;
                            $this->system_notes[] = [
                                'type' => 'earn',
                                'name' => 'Leave Salary [' . $tmp_date . ']',
                                'description' => 'Leave Salary: ' . number_format($leave_salary, 2, '.', '') . " Tk",
                                'value' => $leave_salary,
                                'is_incremental' => true,
                                'balance' => $this->total_salary,
                            ];
                        }
                    }
                }
            }
        }
        return true;
    }
    protected function step_5_add_earn_heads()
    {

        foreach ($this->earn_head_items as $i => $item) {
            if ($item['category'] === 'overtime') {
                $total_overtimed_salary = 0;
                foreach ($this->pay_data_per_dates['dates'] as $tmp_date => $_date) {
                    if ($_date['salary_per_seconds'] ?? false) {
                        $salary_per_seconds = $_date['salary_per_seconds'];
                        $tmp_amount_per_seconds = 0;
                        if ($item['amount_type'] === 'fixed') {
                            $tmp_amount_per_seconds = $item['amount'] / 60 / 60;
                        } else if ($item['amount_type'] === 'percentage') {
                            // percentage
                            $tmp_amount_per_seconds = $item['amount'] / 100 * $salary_per_seconds;
                        } else {
                            $tmp_amount_per_seconds = $item['amount'] * $salary_per_seconds;
                        }
                        if ($_date['overtime_time'] ?? false) {
                            $overtimed_seconds = $_date['overtime_time'];
                            $total_overtimed_salary += $overtimed_seconds * $tmp_amount_per_seconds;
                            $this->system_notes[] = [
                                'type' => 'info',
                                'name' => 'Overtime hour: ' . $tmp_date,
                                'description' => 'Overtime hour: ' . seconds_to_hh_mm_ss($overtimed_seconds) . ' (Rate: ' . number_format($tmp_amount_per_seconds * 60 * 60, 2, '.', '') . " Tk)",
                                'value' => 0,
                                'is_incremental' => null,
                                'balance' => $this->total_salary,
                            ];
                            $this->system_notes[] = [
                                'type' => 'info',
                                'name' => 'Overtime Earned',
                                'description' => 'Overtime Earned: ' . number_format($total_overtimed_salary, 2, '.', '') . " Tk",
                                'value' => 0,
                                'is_incremental' => null,
                                'balance' => $this->total_salary,
                            ];
                        }
                    } else {
                    }
                }
                $this->total_salary += $total_overtimed_salary;
                $this->total_earn_heads += $total_overtimed_salary;
                $this->earn_head_items[$i]['total_amount'] = number_format($total_overtimed_salary, 2, '.', '');
                $this->system_notes[] = [
                    'type' => 'earn',
                    'name' => 'Overtime Earned',
                    'description' => 'Overtime Earned: ' . number_format($total_overtimed_salary, 2, '.', '') . " Tk",
                    'value' => $total_overtimed_salary,
                    'is_incremental' => true,
                    'balance' => $this->total_salary,
                ];
            }
            if ($item['category'] === 'emp_daily_expenses') {
                $expenses = EmpDailyExpense::final_approved()
                    ->where('employee_id', $this->user->id)
                    ->whereDate('date', '>=', $this->from_date->format('Y-m-d'))
                    ->whereDate('date', '<=', $this->end_date->format('Y-m-d'))
                    ->get();
                if ($expenses->count()) {
                    $expense_total = 0;
                    foreach ($expenses as $expense) {
                        $expense_total += $expense->amount;
                    }
                    $this->total_earn_heads += $expense_total;
                    $this->total_salary += $expense_total;
                    $this->earn_head_items[$i]['total_amount'] = number_format($expense_total, 2, '.', '');
                    $this->system_notes[] = [
                        'type' => 'earn',
                        'name' => 'Employee Daily Expenses',
                        'description' => 'Employee Daily Expenses: ' . number_format($expense_total, 2, '.', '') . " Tk",
                        'value' => $expense_total,
                        'is_incremental' => true,
                        'balance' => $this->total_salary,
                    ];
                }
            }
            if ($item['category'] === 'duty_bonuses') {
                if(config('app.customer') == 'asp')
                {
                    if(
                        is_numeric($this->pay_data_per_dates['worked_days'] ?? null) &&
                        is_numeric($this->pay_data_per_dates['max_working_days'] ?? null) &&
                        ($this->pay_data_per_dates['worked_days'] + $this->pay_data_per_dates['total_holidays'] + (
                            $this->pay_data_per_dates['grace_days'] ?? 0
                        ))
                            >= $this->pay_data_per_dates['max_working_days']
                    )
                    {
                        $duty = DutyBonusRecord::query()
                            ->where('user_id', $this->user->id)
                            ->whereDate('date', '=', $this->end_date->format('Y-m-d'))
                            ->first();
                        if($duty) $duty->delete();

                        $duty_bonus_total = round($this->salary_per_day + 1000, 2);
                        DutyBonusRecord::query()
                            ->create([
                                'user_id' => $this->user->id,
                                'name' => 'Auto bonus',
                                'date' => $this->end_date->format('Y-m-d'),
                                'amount' => $duty_bonus_total,
                            ]);
                        $this->total_earn_heads += $duty_bonus_total;
                        $this->total_salary += $duty_bonus_total;
                        $this->earn_head_items[$i]['total_amount'] = number_format($duty_bonus_total, 2, '.', '');
                        $this->system_notes[] = [
                            'type' => 'earn',
                            'name' => 'Duty Bonus',
                            'description' => 'Duty Bonus: ' . number_format($duty_bonus_total, 2, '.', '') . " Tk",
                            'value' => $duty_bonus_total,
                            'is_incremental' => true,
                            'balance' => $this->total_salary,
                        ];
                    }
                }

            }
            if ($item['category'] === 'allowance' || $item['category'] === 'other') {
                $tmp = 0;
                if ($item['amount_type'] === 'fixed') {
                    $tmp = $item['amount'];
                } else if ($item['amount_type'] === 'percentage') {
                    // percentage
                    $tmp = $item['amount'] / 100 * $this->main_base_salary;
                } else {
                    $tmp = $item['amount'] * $this->main_base_salary;
                }
                $this->total_salary += ($tmp);
                $this->total_earn_heads += ($tmp);
                $this->earn_head_items[$i]['total_amount'] = number_format($tmp, 2, '.', '');
                $this->system_notes[] = [
                    'type' => 'earn',
                    'name' => 'Earch Head',
                    'description' => 'Earch Head: ' . number_format($tmp, 2, '.', '') . " Tk",
                    'value' => $tmp,
                    'is_incremental' => true,
                    'balance' => $this->total_salary,
                ];
            }
            if ($item['category'] === 'rewards') {
                # Step 6: Add Rewards
                $rewards = EmployeeReward::query()
                    ->where('user_id', $this->user->id)
                    ->whereDate('date', '>=', $this->from_date->format('Y-m-d'))
                    ->whereDate('date', '<=', $this->end_date->format('Y-m-d'))
                    ->get();
                $rewards_amount = 0;
                $refs = [];
                foreach ($rewards as $reward) {
                    $refs[] = $reward->id;
                    $rewards_amount += $reward->amount;
                }
                if ($rewards_amount) {
                    $this->total_salary += $rewards_amount;
                    $this->total_earn_heads += $rewards_amount;
                    $this->earn_head_items[$i]['total_amount'] = number_format($rewards_amount, 2, '.', '');
                    $this->system_notes[] = [
                        'type' => 'earn',
                        'name' => 'Rewards',
                        'description' => 'Rewards: ' . number_format($rewards_amount, 2, '.', '') . " Tk (Ref: " . implode(", ", $refs) . ")",
                        'value' => $rewards_amount,
                        'is_incremental' => true,
                        'balance' => $this->total_salary,
                    ];
                }
            }
        }
        return true;
    }
    protected function step_6_add_deductions()
    {
        // deduction
        foreach ($this->deduct_head_items as $i => $item) {
            $tmp = 0;
            // dump($deduct_head_items);
            if (
                $item['category'] === 'loan'
                || $item['category'] === 'fine'
            ) {
                if ($item['category'] === 'loan') {
                    if (!isset($this->deduct_head_items[$i]['total_amount']))
                        $this->deduct_head_items[$i]['total_amount'] = 0;

                    $loans = Loan::final_approved()
                        ->where('loans.user_id', $this->user->id)
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
                            $this->total_deductions += ($payAmount);
                            $this->total_salary -= ($payAmount);
                            $this->system_notes[] = [
                                'type' => 'deduct',
                                'name' => 'Deduction',
                                'description' => 'Loan: ' . number_format($payAmount, 2, '.', '') . " Tk",
                                'value' => $payAmount,
                                'is_incremental' => false,
                                'balance' => $this->total_salary,
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
                                $this->total_deductions += round($duePayment->amount, 2);
                                $this->total_salary -= round($duePayment->amount, 2);
                                $this->system_notes[] = [
                                    'type' => 'deduct',
                                    'name' => 'Deduction',
                                    'description' => 'Loan: ' . number_format($duePayment->amount, 2, '.', '') . " Tk. Loan#" . $loan->id . ', Schedule#' . $duePayment->id,
                                    'value' => $duePayment->amount,
                                    'is_incremental' => false,
                                    'balance' => $this->total_salary,
                                ];
                            }
                        }
                    }
                    // if($user->id == 1001) dd($loand_payments_data, $tmp, $loans);
                }
                if ($item['category'] === 'fine') {
                    $loans = Fine::final_approved()
                        ->where('fines.user_id', $this->user->id)
                        // ->whereDate('fines.created_at', '>=', $this->from_date->format('Y-m-d'))
                        // ->whereDate('fines.created_at', '<=', $this->end_date->format('Y-m-d'))
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
                        $this->total_deductions += round($tmp, 2);
                        $this->total_salary -= round($tmp, 2);
                        $this->system_notes[] = [
                            'type' => 'deduct',
                            'name' => 'Deduction',
                            'description' => 'Fine : ' . number_format($tmp, 2, '.', '') . " Tk",
                            'value' => $tmp,
                            'is_incremental' => false,
                            'balance' => $this->total_salary,
                        ];
                    }
                }
            } else if ($item['category'] === 'absent') {
                // only for show. no real deduction
                $tmp = 0;
                foreach ($this->total_duty_report['data'] as $__tmp_date => $_tmp_item) {
                    if ($_tmp_item['absent'] ?? null) {
                        $tmp += $this->pay_data_per_dates['dates'][date('Y-m-d', $__tmp_date)]['salary_per_seconds'] * $this->pay_data_per_dates['dates'][date('Y-m-d', $__tmp_date)]['max_working_seconds'];
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
                $this->total_deductions += round($tmp, 2);
                $this->total_salary -= round($tmp, 2);
                $this->system_notes[] = [
                    'type' => 'deduct',
                    'name' => 'Employee Bill',
                    'description' => 'Employee Bill: ' . number_format($tmp, 2, '.', '') . " Tk",
                    'value' => $tmp,
                    'is_incremental' => false,
                    'balance' => $this->total_salary,
                ];
                // dd(2, $pay_data_per_dates, $total_duty_report, $tmp);
            } else if ($item['category'] === 'ration') {
                if(config('app.customer') == 'asp')
                {
                    if(
                        is_numeric($this->pay_data_per_dates['worked_days'] ?? null) &&
                        ($this->pay_data_per_dates['worked_days'] + $this->pay_data_per_dates['total_holidays'] + (
                            $this->pay_data_per_dates['grace_days'] ?? 0
                        ))
                            >= 20
                    )
                    {
                        if ($item['amount_type'] === 'fixed') {
                            $tmp = $item['amount'];
                        } else if ($item['amount_type'] === 'percentage') {
                            // percentage
                            $tmp = $item['amount'] / 100 * $this->main_base_salary;
                        } else {
                            $tmp = $item['amount'] * $this->main_base_salary;
                        }
                        $this->deduct_head_items[$i]['total_amount'] = round($tmp, 2);
                        $this->total_salary -= round($tmp, 2);
                        $this->total_deductions += round($tmp, 2);
                        $this->system_notes[] = [
                            'type' => 'deduct',
                            'name' => 'Deduction',
                            'description' => $item['name'] . ': ' . number_format($tmp, 2, '.', '') . " Tk",
                            'value' => $tmp,
                            'is_incremental' => false,
                            'balance' => $this->total_salary,
                        ];
                    }
                }
            } else {
                if ($item['amount_type'] === 'fixed') {
                    $tmp = $item['amount'];
                } else if ($item['amount_type'] === 'percentage') {
                    // percentage
                    $tmp = $item['amount'] / 100 * $this->main_base_salary;
                } else {
                    $tmp = $item['amount'] * $this->main_base_salary;
                }
                // create provident fund rcords
                if ($item['category'] === 'provident_fund') {
                    $this->provident_fund_amount = $tmp;
                }

                $this->deduct_head_items[$i]['total_amount'] = round($tmp, 2);
                $this->total_salary -= round($tmp, 2);
                $this->total_deductions += round($tmp, 2);
                $this->system_notes[] = [
                    'type' => 'deduct',
                    'name' => 'Deduction',
                    'description' => $item['name'] . ': ' . number_format($tmp, 2, '.', '') . " Tk",
                    'value' => $tmp,
                    'is_incremental' => false,
                    'balance' => $this->total_salary,
                ];
            }
            // if(!($item['name'] ?? null))
            // // dd($item);
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
                $this->total_deductions += $total;
                $this->total_salary -= $total;
                $this->system_notes[] = [
                    'type' => 'deduct',
                    'name' => 'Bulk Deduction',
                    'description' => 'Bulk Deduction: ' . $item['name'] . ': ' . number_format($total, 2, '.', '') . " Tk",
                    'value' => $total,
                    'is_incremental' => false,
                    'balance' => $this->total_salary,
                ];
            }
        }
        return true;
    }
    protected function generateProvidentFundPayment(Payslip $payslip)
    {
        // create provident fund records
        if ($this->provident_fund_amount !== null) {
            $lastFund = ProvidentFundPayment::query()
                ->where('user_id', $this->user->id)
                ->orderBy('month_date', 'desc')
                ->first();
            $sumOfPreviousFundInterests = ProvidentFundPayment::query()
                ->where('user_id', $this->user->id)
                ->sum('interest_amount');
            $initial_pf_balance = $this->user->initial_pf_balance;
            $lastFundBalance = $lastFund ? $lastFund->balance : $initial_pf_balance;
            $last_balance_with_interest = $lastFund ? $lastFund->balance_with_interest : (
                $initial_pf_balance
            );
            $newBalance = $this->provident_fund_amount + $lastFundBalance;

            if (Setting::provident_fund_have_interests()) {
                $rate = Setting::provident_fund_interest_rate();
                if ($rate == 0 || $rate < 0) $interest_amount = 0;
                else {
                    $closing_fund_balance = $last_balance_with_interest;
                    $interest_amount = ($closing_fund_balance * $rate / 12 / 100);
                }

                return ProvidentFundPayment::query()->create([
                    'user_id' => $this->user->id,
                    'payslip_id' => $payslip->id,
                    'basic_salary' => $this->base_salary,
                    'balance' => $newBalance,
                    'interest_amount' => $interest_amount,
                    'interest' => $rate,
                    'balance_with_interest' => round($interest_amount + $sumOfPreviousFundInterests + $newBalance, 2),
                    'month_date' => $payslip->month_date,
                ]);
            } else {
                return ProvidentFundPayment::query()->create([
                    'user_id' => $this->user->id,
                    'payslip_id' => $payslip->id,
                    'basic_salary' => $this->base_salary,
                    'balance' => $newBalance,
                    'interest_amount' => 0,
                    'interest' => 0,
                    'balance_with_interest' => round($newBalance, 2),
                    'month_date' => $payslip->month_date,
                ]);
            }
        }
    }
}
