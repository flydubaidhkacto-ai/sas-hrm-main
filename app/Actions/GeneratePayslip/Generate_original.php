<?php

namespace App\Actions\GeneratePayslip;

use App\Actions\GenerateTotalDutyOfEmployee;
use App\Jobs\InitAutoAbsentFineEmployees;
use App\Models\AutoAbsentFine;
use App\Models\Fine;
use App\Models\Holiday;
use App\Models\PayrollCard;
use App\Models\Payslip;
use App\Models\ProvidentFundPayment;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Generate
{
    use BasicByDynamicDaySalary, BasicBy30DaySalary, EarnHeads, Deductions, GenerateDates;

    static function execute(User $user, Carbon $date, $throw_exception = true)
    {
        if(!$user->is_active) return null;
        DB::beginTransaction();
        try {
            $result = (new self($user, $date))->handle();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
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
    protected array $holiday_dates;
    protected array $system_notes;
    protected ?Payslip $payslip = null;
    public array $dates = [];

    // fixed
    protected float $main_basic_salary = 0;
    protected float $main_gross_salary = 0;
    protected float $main_net_salary = 0;

    public float $dynamic_daily_salary = 0;
    protected float $basic_salary_for_percentage = 0;
    // protected int $working_days = 0;

    // earned
    protected float $earned_basic_salary = 0;
    protected float $provident_fund_amount = 0;
    protected float $earned_earn_heads = 0;
    protected float $earned_deductions = 0;
    protected float $earned_total_salary = 0;
    protected array $earn_head_items = [];
    protected array $deduct_head_items = [];

    function __construct(
        protected User $user,
        protected Carbon $date
    ) {
        $this->paytype = $this->user->paytype;
        $this->follow_30_day_rule = config('services.follow_30_day_rule');
        $this->from_date = $this->date->copy()->startOfMonth();
        $this->working_from_date = $this->date->copy()->startOfMonth();
        $this->end_date = $this->date->copy()->endOfMonth();
        $this->payroll_card = $this->user->payroll_card;

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
    }
    function handle()
    {
        $join_date = $this->user->join_date;

        if ($join_date && Carbon::parse($join_date)->greaterThan($this->from_date)) {
            // $this->from_date = Carbon::parse($join_date);
            $this->working_from_date = Carbon::parse($join_date);
            if($this->end_date->lessThan($this->working_from_date))
            {
                return null;
            }
        }

        // Step 1
        $this->get_meta_data();
        $this->payslip = $this->get_meta_data_payslip();
        // Step 2
        $this->generateDates();
        // step 3
        $this->earn_basic_salary();
        $this->earned_total_salary = $this->earned_basic_salary;
        $this->earned_earn_heads = $this->earned_basic_salary;
        // dd($this->grouped_paid_weekends_holidays, $this->dates, $this->emplyee_worked_days, $this->emplyee_paid_leave_days, $this->available_working_days);

        /**
         * Uncomment which one you want to use for basic calculation for other earn heads/deductions
         */
        $this->basic_salary_for_percentage = $this->main_basic_salary; // for taking main basic`
        // $this->basic_salary_for_percentage = $this->earned_basic_salary; // for taking earned basic

        $this->add_earn_heads();

        // run the auto fine
        $this->auto_fine();

        $this->add_deductions();
        $data = [
            'total_earn_heads' => round($this->earned_earn_heads, 2),
            'total_deductions' => round($this->earned_deductions, 2),
            'basic_salary' => round($this->earned_basic_salary, 2),
            'total_salary' => round($this->earned_total_salary, 2),
            'working_days' => $this->emplyee_worked_days,
            'earn_heads' => ($this->earn_head_items),
            'deductions' => ($this->deduct_head_items),
            'system_notes_json' => ($this->system_notes),
            'total_holidays' => $this->holidays->count(),
        ];
        // create a new payslip
        $this->payslip->update($data);
        // generate provident fund payment
        $this->generateProvidentFundPayment($this->payslip);
        return $this->payslip;
    }
    function get_meta_data()
    {
        $this->earn_head_items = $this->payroll_card->grade
            ? $this->payroll_card->grade->items->toArray()
            : $this->payroll_card->earn_heads->toArray();
        $this->deduct_head_items = $this->payroll_card->deductions->toArray();

        $this->holidays = Holiday::query()
            ->whereDate('date', '>=', $this->working_from_date->format('Y-m-d'))
            ->whereDate('date', '<=', $this->end_date->format('Y-m-d'))
            ->get();
        foreach($this->holidays as $holiday) {
            $this->holiday_dates[$holiday->date] = $holiday;
        }

        $this->system_notes[] = [
            'type' => 'info',
            'name' => 'Start-End Date',
            'description' => 'Start Date: ' . $this->working_from_date->format('d M Y') . ' End Date: ' . $this->end_date->format('d M Y'),
        ];

        // get main basic salary
        $this->main_basic_salary = $this->payroll_card->basic_salary;
        $this->main_gross_salary = $this->payroll_card->gross_salary;
        $this->main_net_salary = $this->payroll_card->net_salary;
    }
    function get_meta_data_payslip(): Payslip
    {
        $previous_payslip = $this->user->payslips()
            ->whereDate('month_date', $this->date->format('Y-m') . '-01')
            ->first();
        if ($previous_payslip) {
            // payslip related data will be adjusted from payslip model deleting event
            $previous_payslip->delete();
        }
        return $this->user->payslips()->create([
            'month_date' => $this->date->format('Y-m') . '-01',
            'total_earn_heads' => 0,
            'total_deductions' => 0,
            'basic_salary' => 0,
            'total_salary' => 0,
            'working_days' => 0,
            'pay_type' => $this->paytype,
            'created_by' => user() ? user()->id : (
                request()->auth_user ? request()->auth_user->id : 1
            ),
            'earn_heads' => [],
            'deductions' => [],
            'system_notes_json' => [],
            'total_holidays' => 0,
            'start_date' => $this->working_from_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
        ]);
    }
    function earn_basic_salary()
    {
        if($this->paytype === 'monthly')
        {
            $this->earned_basic_salary = $this->main_basic_salary;
            $this->earned_total_salary = $this->earned_basic_salary;
            $this->system_notes[] = [
                'type' => 'earn',
                'name' => 'Basic Salary',
                'description' => 'Monthly Rate: ' . number_format($this->earned_basic_salary, 2, '.', '') . " Tk",
                'value' => $this->earned_basic_salary,
                'is_incremental' => true,
                'balance' => $this->earned_total_salary,
            ];
        }else {
            if($this->follow_30_day_rule)
            {
                $this->basicBy30DaySalary();
            }
            else
            {
                $this->basicByDynamicDaySalary();
            }
        }
        if(
            $this->earned_basic_salary > $this->main_basic_salary
            && config('services.dynamic_daily_salary_by_days') === false
        )
        {
            $this->earned_basic_salary = $this->main_basic_salary;
            $this->system_notes[] = [
                'type' => 'earn',
                'name' => 'Basic Salary',
                'description' => "Adjusted to max",
                'value' => $this->earned_basic_salary,
                'is_incremental' => true,
                'balance' => $this->earned_total_salary,
            ];
        }
        if($this->earned_basic_salary < 0) $this->earned_basic_salary = 0;
    }
    function auto_fine()
    {
        if(config('app.customer') !== 'asp') return;

        foreach(generateDateRange($this->from_date, $this->end_date) as $date) {
            Fine::asp_delete_auto_absent_fines_if_present_later(
                [$date],
                $this->user->id
            );
            $dataOfDuty = GenerateTotalDutyOfEmployee::execute(
                $this->user, carbon($date), carbon($date)
            );
            $rules = AutoAbsentFine::query()->get();

            if ($rules->count() > 0) {
                if(
                    $dataOfDuty['total_absent_days'] &&
                    $dataOfDuty['total_leave_days'] == 0 &&
                    $dataOfDuty['total_holidays'] == 0 &&
                    $dataOfDuty['total_weekends'] == 0
                ) {

                    foreach ($rules as $rule) {
                        $amount = $rule->fine_amount ?: 0;
                        if ($rule->fine_amount_type === 'days') {
                            $amount = round((
                                $this->dynamic_daily_salary * $rule->fine_amount
                            ), 2);
                        }
                        $reason = 'Auto Absent Fine for ' . carbon($date)->format('d F, Y');
                        $findPrev = Fine::where('user_id', $this->user->id)
                            ->where('reason', $reason)
                            ->exists();
                        if($findPrev) continue;
                        try{
                            $newFine = Fine::create([
                                'user_id' => $this->user->id,
                                'added_by_user_id' => 1,
                                'amount' => $amount,
                                'reason' => $reason,
                                'cut_off_as' => 'once',
                                'approved_at' => strtolower($rule->fine_status) === 'approved' ? now() : null,
                                'status' => strtolower($rule->fine_status) == 'pending' ? 'pending' : 'approved',
                                'created_at' => $date,
                                'updated_at' => $date,
                            ]);
                        }catch(\Throwable $e) {}
                    }
                }
            }
        }
    }
}
