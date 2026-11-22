<?php

namespace App\Http\Controllers;

use App\Actions\GeneratePayrollCard;
use App\Actions\GeneratePayslip;
use App\Actions\GeneratePayslip\Generate;
use App\Actions\TaxCheck;
use App\Exports\Excel\Taxslip;
use App\Exports\PayslipSheet;
use App\Exports\TaxSlipSheet;
use App\Jobs\GenerateEmployeePayslipBalance;
use App\Models\EmpDailyExpense;
use App\Models\FinePayment;
use App\Models\LoanPayment;
use App\Models\Payslip;
use App\Models\ProvidentFundPayment;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendPayslipSmsJob;
use App\Models\EarnHead;
use App\Models\PayrollCardEarnHeadItem;

use App\Services\Approval\CommonService;

use App\Models\ApprovalProcessStep;
use App\Helpers\{PayrollHelper};

class PayslipController extends Controller
{
    protected int $count = 0;


    public function payslip(Request $request)
    {
        session(['payslip_return_url' => url()->full()]);

        $user = auth()->user();

        $query = Payslip::query()
            ->with([
                'employee:id,name,department_id,designation_id,is_active',
                'employee.department:id,name,super_department_id',
                'employee.department.super_department:id,name',
                'employee.designation:id,name',

                // 🔥 FIXED
                'approvalRecords:id,payslip_id,approval_step_id,status,note,reviewed_by,created_at',
                'approvalRecords.reviewer:id,name',
            ])
            ->latest()
            ->stageVisibleTo($user);

        // ✅ Active employee only
        $query->whereHas('employee', fn($q) => $q->where('is_active', true));

        // ================= FILTERS =================

        if ($request->employee) {
            $query->where('user_id', $request->employee);
        }

        if ($request->department) {
            $query->whereHas('employee', fn($q) =>
            $q->where('department_id', $request->department)
            );
        }

        if ($request->super_department) {
            $query->whereHas('employee.department', fn($q) =>
            $q->where('super_department_id', $request->super_department)
            );
        }

        if ($request->designation) {
            $query->whereHas('employee', fn($q) =>
            $q->where('designation_id', $request->designation)
            );
        }

        // ✅ Month filter
        $query->whereDate(
            'month_date',
            $request->month
                ? carbon('01-' . $request->month)
                : now()->startOfMonth()
        );

        // ================= EXPORT =================
        if ($request->export) {
            $data = $query->get();

            $month = carbon($request->month ? '01-' . $request->month : null)->format('d M Y');
            $name = "Salary sheet {$month}";

            return match ($request->export) {
            'xlsx' => PayslipSheet::download_excel($data, $name . '.xlsx', $month),
            'pdf'  => PayslipSheet::download_pdf($data, $name, $month),
            'print'=> PayslipSheet::print($data, $name, $month),
        };
    }

        // ================= GET DATA =================
        $payslips = $query->get();

        // ================= COMPUTED =================
        $payslips->transform(function ($p) use ($user) {

            $lastRecord = $p->approvalRecords
                ->sortByDesc('created_at')
                ->first();

            $p->computed = [
                'canApprove'      => $p->canApprove($user),
                'canFinalApprove' => $p->canFinalApprove($user),
                'canSendNext'     => $p->canSendToNext($user),
                'canView'         => $p->canViewByUser($user),

                'status' => \App\Helpers\PayrollHelper::statusBadge($p, $user),

                'nextStageLabel' => $p->nextStageLabel(),
                'prevStageLabel' => $p->previousStageLabel(),

                // 🔥 NEW: last action + note
                'last_note'   => $lastRecord?->note ?? '-',
                'last_action_by'     => $lastRecord?->reviewer?->name ?? '-',
                'last_stage'  => \App\Helpers\PayrollHelper::stageLabel($lastRecord?->action_stage ?? $p->payroll_stage),
            ];

            return $p;
        });

        // ================= 🔥 STATUS FILTER =================
        if ($request->filled('status')) {

            $status = $request->status;

            $payslips = $payslips->filter(function ($p) use ($status) {

                return match ($status) {
                'approved'       => $p->isApproved() && !$p->isReturned(),
                'pending'        => !$p->isApproved() && !$p->isReturned() && !$p->isRejected(),
                'returned'       => $p->isReturned(),
                'rejected'       => $p->isRejected(),
                'draft'          => $p->isDraft(),
                'regenerated'    => $p->isRegenerated(),
                'ready_to_send'  =>
                    !$p->isReturned()
                    && $p->isFinalStepApproved()
                    && $p->nextStage(),

                default => true,
            };
        });
        }

        // ================= PAGINATION =================
        $payslips = new \Illuminate\Pagination\LengthAwarePaginator(
            $payslips->forPage($request->page, 1000),
            $payslips->count(),
            1000,
            $request->page,
            ['path' => url()->current(), 'query' => request()->query()]
        );

        return view('payslip.draft.index', compact('payslips'));
    }

    function create()
    {
        abort_if(!(
            role_can('payslip.generate')
            || role_can('payslip.generate.self.super_department')
            || user()->admin_of_super_departments
        ), 403);

        return view('payslip.create');
    }

    function generate(Request $request)
    {
        abort_if(!(
            role_can('payslip.generate')
            || role_can('payslip.generate.self.super_department')
            || user()->admin_of_super_departments
        ), 403);

        $request->validate([
            'month' => 'required',
        ]);
        $users = User::active_employees()
            ->whereHas('payroll_card');

        if (
            !role_can('payslip.generate')
            && user()->admin_of_super_departments
        ) {
            $users->whereHas('department', function ($query) {
                return $query->whereIn('super_department_id', user()->admin_of_super_departments);
            });
        } else
            if (
            !role_can('payslip.generate')
            && role_can('payslip.generate.self.super_department')
            && user()->department ?->super_department_id
        ) {
        $users->whereHas('department', function ($query) {
            return $query->where('super_department_id', user()->department ?->super_department_id);
            })
            ->orWhereIn('id', user()->managed_employees->pluck('id'));
    }
        if ($request->super_department) {
            $users->whereHas('department', function ($query) use ($request) {
                return $query->where('super_department_id', $request->super_department);
            })
                ->orWhereIn('id', user()->managed_employees->pluck('id'));
        }
        if ($request->department) {
            $users->where('department_id', $request->department);
        }
        if ($request->designation) {
            $users->where('designation_id', $request->designation);
        }
        if ($request->employee) {
            $users->where('id', $request->employee);
        }
        if ($request->reset) {
            $users = $users->get(['id'])->pluck('id');
            Payslip::query()
                ->whereIn('user_id', $users)
                ->where('month_date', carbon('01-' . $request->month))
                ->chunk(100, function ($payslips) {
                    foreach ($payslips as $payslip) {
                        $payslip->delete();
                        $this->count++;
                    }
                });
            Toastr::success('Reset ' . $this->count . ' payslips successfully :)', 'Success');
            GenerateEmployeePayslipBalance::dispatch($users);
            return redirect()->back();
        }
        $limit = 1;
        if ($request->step_2) {
            $users = $users->paginate($limit);
            $done = [];
            foreach ($users as $user) {
                if ($user->is_active) {
                    Generate::execute(
                        $user,
                        carbon('01-' . $request->month),
                        config('app.env') == 'local'
                    );
                } else {
                    Payslip::query()
                        ->where('user_id', $user->id)
                        ->where('month_date', carbon('01-' . $request->month))
                        ->delete();
                }
                $done[] = $user->id;
            }
            GenerateEmployeePayslipBalance::dispatch($done);
            return $done;
        }

        return [
            'total_user_pages' => ceil($users->count() / $limit),
        ];
    }

    public function show($id, Request $request)
    {
        $payslip = Payslip::with([
            'approvalRecords.step.approvalProcess.steps',
            'approvalRecords.reviewer',
        ])->findOrFail($id);
        
        $returnUrl = session('payslip_return_url', route('payslip.draft'));

        $status = \App\Helpers\PayrollHelper::statusBadge($payslip, auth()->user());

        /*
        |------------------------------------------
        | 1. HISTORY (ASC)
        |------------------------------------------
        */
        $records = $payslip->approvalRecords
            ->sortBy('created_at')
            ->values();

        /*
        |------------------------------------------
        | 2. ALL STEPS
        |------------------------------------------
        */
        $stages = array_keys(Payslip::stages());

        $allSteps = collect();

        foreach ($stages as $stageKey) {

            $steps = \App\Models\ApprovalProcessStep::whereHas('approvalProcess', function ($q) use ($stageKey) {
                $q->where('type', $stageKey);
            })->orderBy('step_order')->get();

            foreach ($steps as $step) {
                $allSteps->push($step);
            }
        }

        $timeline = [];

        /*
        |------------------------------------------
        | 3. NO HISTORY → ALL PENDING
        |------------------------------------------
        */
        if ($records->isEmpty()) {

            foreach ($allSteps as $step) {

                $timeline[] = [
                    'stage' => $step->approvalProcess->type,
                    'role' => \App\Helpers\PayrollHelper::stageLabel($step->approvalProcess->type),
                    'to_stage' => \App\Helpers\PayrollHelper::stageLabel($step->approvalProcess->type),
                    'approver' => $step->approver->name ?? '-',

                    'label' => $step->step_order == $step->approvalProcess->steps->max('step_order')
                        ? 'Final Approver'
                        : 'Approver '.$step->step_order,

                    'status' => 'pending',
                    'is_ready_to_send' => false,

                    'time' => '-',
                    'note' => '-',
                    'row_color' => '',
                ];
            }

            return view('payslip.show', [
                'payslip' => $payslip,
                'approvalTimeline' => $timeline,
                'status' => $status,
                'duty_data' => [],
            ]);
        }

        /*
        |------------------------------------------
        | 4. FIRST STEP PENDING (IF NO ACTION)
        |------------------------------------------
        */
        $firstStep = $allSteps->first();

        if ($firstStep) {

            $hasFirstHistory = $records->contains('approval_step_id', $firstStep->id);

            if (!$hasFirstHistory) {

                $timeline[] = [
                    'stage' => $firstStep->approvalProcess->type,
                    'role' => \App\Helpers\PayrollHelper::stageLabel($firstStep->approvalProcess->type),
                    'to_stage' => \App\Helpers\PayrollHelper::stageLabel($step->approvalProcess->type),
                    'approver' => $firstStep->approver->name ?? '-',

                    'label' => 'Approver '.$firstStep->step_order,

                    'status' => 'pending',
                    'is_ready_to_send' => false,

                    'time' => '-',
                    'note' => '-',
                    'row_color' => '',
                ];
            }
        }

        /*
        |------------------------------------------
        | 5. LAST ACTION PER STEP
        |------------------------------------------
        */
        $lastActionPerStep = [];

        foreach ($records as $record) {
            $lastActionPerStep[$record->approval_step_id] = $record;
        }

        /*
        |------------------------------------------
        | 6. CURRENT STAGE DETECT 🔥
        |------------------------------------------
        */
        $currentStage = optional($records->last())->action_stage;

        /*
        |------------------------------------------
        | 7. ADD FULL HISTORY
        |------------------------------------------
        */
        foreach ($records as $record) {

            $step = $record->step;
            $maxStep = $step->approvalProcess->steps->max('step_order');

            $isLast = isset($lastActionPerStep[$record->approval_step_id]) &&
                $lastActionPerStep[$record->approval_step_id]->id === $record->id;

            /*
            |------------------------------------------
            | 🔥 Pending Send (FINAL FIX)
            |------------------------------------------
            */
            $isReadyToSend = false;

            if (
                $isLast &&
                $record->status === 'approved' &&
                $step->step_order == $maxStep &&          // final approver
                $record->action_stage == $currentStage    // current stage only
            ) {
                $isReadyToSend = true;
            }

            $timeline[] = [
                'stage' => $record->action_stage,
                'role' => \App\Helpers\PayrollHelper::stageLabel($record->action_stage),
                'to_stage' => \App\Helpers\PayrollHelper::stageLabel($record->to_stage),
                'approver' => $record->reviewer->name ?? '-',

                'label' => $step->step_order == $maxStep
                    ? 'Final Approver'
                    : 'Approver '.$step->step_order,

                'status' => $record->status,
                'is_ready_to_send' => $isReadyToSend,

                'time' => optional($record->created_at)->format('d M Y h:i A'),
                'note' => $record->note ?? '-',

                'row_color' => match ($record->status) {
                'approved' => 'table-success',
                'returned' => 'table-warning',
                'rejected' => 'table-danger',
                'sent' => 'table-info',
                'regenerated' => 'table-info',
                default => '',
            },
        ];
    }

        /*
        |------------------------------------------
        | 8. REMAINING PENDING (END)
        |------------------------------------------
        */
        foreach ($allSteps as $step) {

            $hasHistory = $records->contains('approval_step_id', $step->id);

            if (!$hasHistory && $step->id !== optional($firstStep)->id) {

                $timeline[] = [
                    'stage' => $step->approvalProcess->type,
                    'role' => \App\Helpers\PayrollHelper::stageLabel($step->approvalProcess->type),
                    'to_stage' => \App\Helpers\PayrollHelper::stageLabel($step->approvalProcess->type),
                    'approver' => $step->approver->name ?? '-',

                    'label' => $step->step_order == $step->approvalProcess->steps->max('step_order')
                        ? 'Final Approver'
                        : 'Approver '.$step->step_order,

                    'status' => 'pending',
                    'is_ready_to_send' => false,

                    'time' => '-',
                    'note' => '-',
                    'row_color' => '',
                ];
            }
        }

        return view('payslip.show', [
            'payslip' => $payslip,
            'approvalTimeline' => array_values($timeline),
            'status' => $status,
            'duty_data' => [],
            'returnUrl' => $returnUrl,
        ]);
    }

    public function tax(Request $request)
    {
        abort_if(!(
            role_can('payslip.tax')
            || user()->admin_of_super_departments
        ), 403);
        $for_one = $request->employee_id;
        if ($for_one) $for_one = User::findOrFail($for_one);

        $p = Payslip::latest()
            ->when(!role_can('payslip.tax') && user()->admin_of_super_departments, function ($query) {
                return $query->whereHas('employee.department', function ($query) {
                    return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            });
        if ($for_one) {
            $p->whereHas('employee', function ($query) use ($for_one) {
                return $query->where('id', $for_one->id);
            });
        } else {
            if ($request->employee) {
                $p->whereHas('employee', function ($query) use ($request) {
                    return $query->where('id', $request->employee);
                });
            }
            if ($request->department) {
                $p->whereHas('employee', function ($query) use ($request) {
                    return $query->where('department_id', $request->department);
                });
            } elseif ($request->super_department) {
                $p->whereHas('employee', function ($query) use ($request) {
                    return $query->whereHas('department', function ($query) use ($request) {
                        return $query->where('super_department_id', $request->super_department);
                    })
                        ->orWhereIn('id', user()->managed_employees->pluck('id'));
                });
            }
            if ($request->designation) {
                $p->whereHas('employee', function ($query) use ($request) {
                    return $query->where('designation_id', $request->designation);
                });
            }

            if ($request->start && $request->end) {
                $startMonth = Carbon::createFromFormat('m-Y', $request->start)->startOfMonth();
                $endMonth = Carbon::createFromFormat('m-Y', $request->end)->startOfMonth();
                $p->where('month_date', '>=', $startMonth)
                    ->where('month_date', '<=', $endMonth);
            }
        }

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return TaxSlipSheet::download_excel($p->get(), $for_one);
            }
            if ($as == 'pdf') {
                return TaxSlipSheet::download_pdf($p->get(), $for_one);
            }
            if ($as == 'print') {
                return TaxSlipSheet::print($p->get(), $for_one);
            }
        }

        $emp = TaxCheck::handle($p->get());
        return view('payslip.index_tax', [
            'data' => $emp,
            'for_one' => $for_one,
        ]);
    }

    public function emp_provident_fund(Request $request)
    {
        abort_if(!(
            role_can('provident_fund')
            || (is_admin_of_super_department(
                User::find($request->employee) ?->department ?->super_department_id
        ))
        ), 403);
        $data = [];
        if ($request->employee) {
            $data['data'] = ProvidentFundPayment::query()
                ->where('user_id', $request->employee)
                ->orderBy('month_date', 'desc')
                ->get();
        }
        return view('payslip.emp_provident_fund', $data);
    }

    public function provident_fund(Request $request)
    {
        abort_if(!(
            role_can('payslip.pf')
            || user()->admin_of_super_departments
        ), 403);

        $users = User::active_employees()
            ->when(!role_can('payslip.pf') && user()->admin_of_super_departments, function ($query) {
                return $query->whereHas('department', function ($query) {
                    return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            })
            ->get();
        $data = [];
        foreach ($users as $user) {
            $data[$user->id] = [
                'employee' => $user,
                'pf' => ProvidentFundPayment::query()
                    ->where('user_id', $user->id)
                    ->latest()
                    ->first(),
            ];
        }
        // dd($data);
        return view('payslip.provident_fund', ['provident_funds' => $data]);
    }

    public function sendPayslipSms(Request $request, $month)
    {
        abort_if(!(
            role_can('payslip')
            || user()->admin_of_super_departments
        ), 403);
        SendPayslipSmsJob::dispatch('01-' . $month, $request->emps);
        Toastr::success('Payslip SMS successfully sent for employees.', 'Success');
        return redirect()->back();
    }

    function update_ssbd_field(Request $request)
    {
        $request->validate([
            'field' => 'required',
            'value' => 'required',
            'payslip_id' => 'required',
        ]);
        $payslip = Payslip::findOrFail($request->payslip_id);
        if (in_array($request->field, ['basic', 'overtime', 'duty_bonuses'])) {
            $earnHeads = $payslip->earn_heads;
            $totalEarn = 0;
            $found = false;

            foreach ($earnHeads as $i => $earnHead) {
                if ($earnHead['category'] == $request->field) {
                    $earnHeads[$i]['total_amount'] = $request->value;
                    $earnHeads[$i]['amount'] = $request->value;
                    $found = true;
                }
                $totalEarn += $earnHeads[$i]['total_amount'] ?? 0;
            }

            $mainEarnHead = EarnHead::where('category', $request->field)->first();
            if (!$found && $mainEarnHead) {
                $data = $mainEarnHead->toArray();
                unset($data['id']);
                $data['total_amount'] = $request->value;
                $data['amount'] = $request->value;
                $totalEarn += $request->value;
                $data['payslip_id'] = $payslip->id;
                $earnHeads[] = ($data);
            }
            $payslip->update([
                'total_earn_heads' => $totalEarn,
                'total_salary' => $totalEarn - $payslip->total_deductions,
                'earn_heads' => $earnHeads,
            ]);
        } else {
            $payslip->update([
                $request->field => $request->value,
            ]);
        }
        return $payslip;
    }

    private function labelName($steps, $index)
    {
        return ($index == $steps->count() - 1)
            ? 'Final Approver'
            : 'Approver ' . ($index + 1);
    }

    private function getRowColor($status)
    {
        return match ($status) {
        'returned' => 'table-warning',
        'rejected' => 'table-danger',
        'approved' => 'table-success',
        'sent' => 'table-info',
        default => '',
    };
}

}
