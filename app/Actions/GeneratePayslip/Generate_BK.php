<?php

namespace App\Actions\GeneratePayslip;

use App\Actions\GenerateTotalDutyOfEmployee;
use App\Models\Fine;
use App\Models\Holiday;
use App\Models\PayrollCard;
use App\Models\Payslip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class Generate
{
    use BasicByDynamicDaySalary,
        BasicBy30DaySalary,
        EarnHeads,
        Deductions,
        GenerateDates;

    public static function execute(User $user, Carbon $date, $throw_exception = true)
    {
        if (!$user->is_active) {
            return null;
        }

        DB::beginTransaction();

        try {

            $result = (new self($user, $date))->handle();

            DB::commit();

            return $result;

        } catch (\Throwable $e) {

            DB::rollBack();

            if ($throw_exception) {
                throw $e;
            }

            return null;
        }
    }

    protected string $paytype;
    protected PayrollCard $payroll_card;
    protected bool $follow_30_day_rule;

    protected Carbon $from_date;
    protected Carbon $working_from_date;
    protected Carbon $end_date;

    protected Collection $holidays;

    protected array $holiday_dates = [];

    protected array $system_notes = [];

    protected ?Payslip $payslip = null;

    public array $dates = [];

    protected float $main_basic_salary = 0;
    protected float $main_gross_salary = 0;
    protected float $main_net_salary = 0;

    public float $dynamic_daily_salary = 0;

    protected float $basic_salary_for_percentage = 0;

    protected float $earned_basic_salary = 0;
    protected float $earned_earn_heads = 0;
    protected float $earned_deductions = 0;
    protected float $earned_total_salary = 0;

    protected array $earn_head_items = [];
    protected array $deduct_head_items = [];

    public function __construct(
        protected User $user,
        protected Carbon $date
    ) {

        $this->paytype = $this->user->paytype;

        $this->follow_30_day_rule = config('services.follow_30_day_rule');

        $this->from_date = $this->date->copy()->startOfMonth();

        $this->working_from_date = $this->date->copy()->startOfMonth();

        $this->end_date = $this->date->copy()->endOfMonth();

        $this->payroll_card = $this->user->payroll_card;
    }

public function handle()
{
    $joinDate = $this->user->join_date;

    if (
        $joinDate &&
        Carbon::parse($joinDate)->greaterThan($this->from_date)
    ) {

        $this->working_from_date = Carbon::parse($joinDate);

        if ($this->end_date->lessThan($this->working_from_date)) {
            return null;
        }
    }

    /**
     * META
     */
    $this->get_meta_data();

    $this->payslip = $this->get_meta_data_payslip();

    /**
     * DATES
     */
    $this->generateDates();

    /**
     * DYNAMIC DAILY SALARY
     * MONTH DAYS BASED
     */
    $daysInMonth = $this->working_from_date->daysInMonth;

    $this->dynamic_daily_salary = (
    $daysInMonth > 0
        ? ($this->main_gross_salary / $daysInMonth)
        : 0
    );

    /**
     * BASIC
     */
    $this->earn_basic_salary();

    $this->earned_total_salary = $this->earned_basic_salary;

    $this->earned_earn_heads = $this->earned_basic_salary;

    $this->basic_salary_for_percentage = $this->main_basic_salary;

    /**
     * EARN HEADS
     */
    $this->add_earn_heads();

    /**
     * DEDUCTIONS
     */
    $this->add_deductions();

    /**
     * DUTY
     */
    $duty = GenerateTotalDutyOfEmployee::execute(
        $this->user,
        $this->working_from_date,
        $this->end_date,
        $this->dynamic_daily_salary
    );

    /**
     * ===========================
     * SYSTEM GENERATED FINES
     * ===========================
     */

    /**
     * LATE FINE DEDUCTION
     */
    if (($duty['total_late_fine'] ?? 0) > 0) {

        $lateFine = round($duty['total_late_fine'], 2);

        $this->earned_deductions += $lateFine;

        $this->earned_total_salary -= $lateFine;

        $this->deduct_head_items[] = [
            'title' => 'Late Fine',
            'amount' => $lateFine,
            'type' => 'late_fine',
            'system_generated' => true,
        ];
    }

    /**
     * ABSENT FINE DEDUCTION
     */
    if (($duty['total_absent_fine'] ?? 0) > 0) {

        $absentFine = round($duty['total_absent_fine'], 2);

        $this->earned_deductions += $absentFine;

        $this->earned_total_salary -= $absentFine;

        $this->deduct_head_items[] = [
            'title' => 'Absent Fine',
            'amount' => $absentFine,
            'type' => 'absent_fine',
            'system_generated' => true,
        ];
    }

    /**
     * LWP FINE
     */
    if (($duty['total_lwp_fine'] ?? 0) > 0) {

        $lwpFine = round($duty['total_lwp_fine'], 2);

        $this->earned_deductions += $lwpFine;

        $this->earned_total_salary -= $lwpFine;

        $this->deduct_head_items[] = [
            'title' => 'Leave Without Pay',
            'amount' => $lwpFine,
            'type' => 'lwp_fine',
            'system_generated' => true,
        ];
    }

    /**
     * OTHER MANUAL FINES
     * (NOT AUTO LATE / ABSENT)
     */
    $manualFineAmount = Fine::query()
        ->where('user_id', $this->user->id)
        ->whereBetween('created_at', [
            $this->working_from_date,
            $this->end_date
        ])
        ->sum('amount');

    if ($manualFineAmount > 0) {

        $this->earned_deductions += $manualFineAmount;

        $this->earned_total_salary -= $manualFineAmount;

        $this->deduct_head_items[] = [
            'title' => 'Other Fine',
            'amount' => round($manualFineAmount, 2),
            'type' => 'manual_fine',
            'system_generated' => true,
        ];
    }

    /**
     * NEVER NEGATIVE
     */
    if ($this->earned_total_salary < 0) {
        $this->earned_total_salary = 0;
    }

    /**
     * UPDATE PAYSLIP
     */
    $this->payslip->update([

        'total_earn_heads' => round($this->earned_earn_heads, 2),

        'total_deductions' => round($this->earned_deductions, 2),

        'basic_salary' => round($this->earned_basic_salary, 2),

        'total_salary' => round($this->earned_total_salary, 2),

        'working_days' => $duty['total_worked_days'] ?? 0,

        'earn_heads' => $this->earn_head_items,

        'deductions' => $this->deduct_head_items,

        'system_notes_json' => $this->system_notes,

        'total_holidays' => $duty['total_holidays'] ?? 0,

        /**
         * NEW COLUMNS
         */
        'total_working_days' => $duty['total_working_days'] ?? 0,

        'present_days' => $duty['total_worked_days'] ?? 0,

        'total_late_days' => $duty['total_late_days'] ?? 0,

        'approved_late_days' => $duty['approved_late_days'] ?? 0,

        'total_late_fine' => round(
            $duty['total_late_fine'] ?? 0,
            2
        ),

        'total_absent_days' => $duty['total_absent_days'] ?? 0,

        'total_absent_fine' => round(
            $duty['total_absent_fine'] ?? 0,
            2
        ),

        'total_lwp_days' => $duty['total_lwp_days'] ?? 0,

        'total_lwp_fine' => round(
            $duty['total_lwp_fine'] ?? 0,
            2
        ),
    ]);

    return $this->payslip;
}

protected function get_meta_data()
{
    $this->earn_head_items = $this->payroll_card->grade
        ? $this->payroll_card->grade->items->toArray()
        : $this->payroll_card->earn_heads->toArray();

    $this->deduct_head_items = $this->payroll_card->deductions->toArray();

    $this->holidays = Holiday::query()
        ->whereDate(
            'date',
            '>=',
            $this->working_from_date->format('Y-m-d')
        )
        ->whereDate(
            'date',
            '<=',
            $this->end_date->format('Y-m-d')
        )
        ->get();

    foreach ($this->holidays as $holiday) {
        $this->holiday_dates[$holiday->date] = $holiday;
    }

    /**
     * FIXED
     */
    $this->main_basic_salary = (float) ($this->payroll_card->basic_salary ?? 0);

    $this->main_gross_salary = (float) ($this->payroll_card->gross_salary ?? 0);

    $this->main_net_salary = (float) ($this->payroll_card->net_salary ?? 0);

    $this->system_notes[] = [
        'type' => 'info',
        'name' => 'Payroll Period',
        'description' =>
            $this->working_from_date->format('d M Y')
            . ' - ' .
            $this->end_date->format('d M Y'),
    ];
}

protected function get_meta_data_payslip(): Payslip
{
    $previousPayslip = $this->user->payslips()
        ->whereDate(
            'month_date',
            $this->date->format('Y-m') . '-01'
        )
        ->first();

    if ($previousPayslip) {
        $previousPayslip->delete();
    }

    return $this->user->payslips()->create([
        'month_date' => $this->date->format('Y-m') . '-01',

        'total_earn_heads' => 0,
        'total_deductions' => 0,
        'basic_salary' => 0,
        'total_salary' => 0,

        'working_days' => 0,

        'pay_type' => $this->paytype,

        'created_by' => user()
            ? user()->id
            : (
                request()->auth_user->id ?? 1
            ),

        'earn_heads' => [],
        'deductions' => [],
        'system_notes_json' => [],

        'total_holidays' => 0,

        'start_date' => $this->working_from_date->format('Y-m-d'),

        'end_date' => $this->end_date->format('Y-m-d'),
    ]);
}

protected function earn_basic_salary()
{
    if ($this->paytype === 'monthly') {

        $this->earned_basic_salary = $this->main_basic_salary;

    } else {

        if ($this->follow_30_day_rule) {

            $this->basicBy30DaySalary();

        } else {

            $this->basicByDynamicDaySalary();
        }
    }

    if ($this->earned_basic_salary < 0) {
        $this->earned_basic_salary = 0;
    }
}
}