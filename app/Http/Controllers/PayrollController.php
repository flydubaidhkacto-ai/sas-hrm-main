<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\EarnHead;
use App\Models\Fine;
use App\Models\Grade;
use App\Models\PayrollCard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;

use function PHPUnit\Framework\returnSelf;

class PayrollController extends Controller
{
    function payroll_cards(Request $request)
    {
        $employees = User::active_employees();
        $type = $request->type ?? "All";
        if ($type === "Created") {
            $employees->whereHas('payroll_card');
        } else
        if ($type === "Not Created") {
            $employees->whereDoesntHave('payroll_card');
        }
        if ($request->department) {
            $employees->where('department_id', $request->department);
        } elseif ($request->super_department) {
            $employees->whereHas('department', function ($q) use ($request) {
                $q->where('super_department_id', $request->super_department);
            });
        }
        if ($request->designation) {
            $employees->where('designation_id', $request->designation);
        }
        if ($request->employee) {
            $employees->where(function ($q) use ($request) {
                $q->where('id', $request->employee);
            });
        }
        abort_if(
            !(
                role_can('payroll_card')
                || role_can('payroll_card.show')
                || role_can('payroll_card.self.show')
                || role_can('payroll_card.self.show.super_department')
                || user()->admin_of_super_departments
            ),
            403
        );

        if (
            !role_can('payroll_card')
            && user()->admin_of_super_departments
        ) {
            $employees->where(fn($q1) => $q1->whereHas('department', function ($q) {
                $q->whereIn('super_department_id', user()->admin_of_super_departments);
            })->orWhere('id', user()->id));
        }else
        if (
            !role_can('payroll_card')
            && !role_can('payroll_card.show')
            && role_can('payroll_card.self.show.super_department')
            && user()->department?->super_department_id
        ) {
            $employees->whereHas('department', function ($q) {
                $q->where('super_department_id', user()->department?->super_department_id);
            });
        }else
        if (
            !role_can('payroll_card')
            && !role_can('payroll_card.show')
            && !role_can('payroll_card.self.show.super_department')
            && role_can('payroll_card.self.show')
            && user()->id
        ) {
            $employees->where('id', user()->id);
        }

        return view('payroll_cards.index', [
            'employees' => $employees
                ->with(['payroll_card'])
                ->latest()
                ->paginate(1000),
            'type' => $type,
        ]);
    }
    function payroll_cards_create($user_id)
    {
        $em = User::query()->findOrFail($user_id);
        return view('payroll_cards.create', [
            'employee' => $em,
            'heads' => EarnHead::all(),
            'deductions' => Deduction::all(),
            'grades' => Grade::orderBy('name', 'asc')->get(),
            'employee_fines' => Fine::where('user_id', $user_id)->get(),
        ]);
    }
    function payroll_cards_show($id)
    {
        $card = PayrollCard::query()
            ->with(['employee', 'earn_heads', 'deductions'])
            ->findOrFail($id);
        abort_if(!(
            role_can('payroll_card')
            || role_can('payroll_card.show')
            || role_can('payroll_card.self.show')
            || role_can('payroll_card.self.show.super_department')
            || user()->admin_of_super_departments
            || user()->id == $card->user_id
        ), 403);

        return view('payroll_cards.show', [
            'card' => $card,
            'employee_fines' => Fine::where('user_id', $card->user_id)->get(),
        ]);
    }
    function payroll_cards_edit($id)
    {
        $card = PayrollCard::query()
            ->with(['employee', 'earn_heads', 'deductions', 'grade'])
            ->findOrFail($id);
        return view('payroll_cards.edit', [
            'card' => $card,
            'employee' => $card->employee,
            'heads' => EarnHead::all(),
            'deductions' => Deduction::all(),
            'grades' => Grade::orderBy('name', 'asc')->get(),
            'employee_fines' => Fine::where('user_id', $card->user_id)->get(),
        ]);
    }
    function payroll_cards_update(Request $request)
    {
        $employee = User::query()->findOrFail($request->user_id);
        abort_if(
            !(
                role_can('payroll_card')
                || role_can('payroll_card.create')
                || role_can('payroll_card.create.self.super_department')
                || is_admin_of_super_department($employee->department?->super_department_id)
            ),
            403
        );
        if (
            !role_can('payroll_card')
            && role_can('payroll_card.create.self.super_department')
            && !user()->admin_of_super_departments
            && user()->department?->super_department_id != $employee->department?->super_department_id
        ) {
            Toastr::error('Permission Denied for this super department!');
            return redirect()->back();
        }

        $data = $request->validate([
            'earn_heads' => 'nullable|array',
            'earn_heads.*.code' => "nullable",
            'earn_heads.*.name' => "nullable|max:50",
            'earn_heads.*.amount' => 'nullable|numeric',
            'earn_heads.*.amount_type' => 'nullable|string|in:percentage,fixed,other',
            'earn_heads.*.category' => 'nullable|string|in:' . join(',', array_keys(EarnHead::$categories)),

            'deductions.*.code' => "required",
            'deductions.*.name' => "required|max:50",
            'deductions.*.amount' => 'numeric',
            'deductions.*.amount_type' => 'required|string|in:percentage,fixed,other',
            'deductions.*.category' => 'required|string|in:' . join(',', array_keys(Deduction::$categories)),
            'deductions.*.reference_id' => 'nullable|integer',

            'basic_salary' => 'required|numeric|min:0',
            'gross_salary' => 'required|numeric|min:0',
            'net_salary' => 'required|numeric|min:0',
            'grade_id' => 'nullable|exists:grades,id',
        ]);

        $cardData = [
            'user_id' => $employee->id,
            'basic_salary' => $data['basic_salary'],
            'gross_salary' => $data['gross_salary'],
            'net_salary' => $data['net_salary'],
            'grade_id' => $data['grade_id'] ?? null,
        ];
        $card = $employee->payroll_card;
        if (!$card) {
            $card = $employee->payroll_card()->create($cardData);
        } else {
            $card->update($cardData);
        }

        // $grade = Grade::find($data['grade_id']);
        $card->earn_heads()->delete();
        foreach ($data['earn_heads'] ?? [] as $head) {
            $card->earn_heads()->create($head);
        }

        $card->deductions()->delete();
        foreach ($data['deductions'] ?? [] as $deduct) {
            $card->deductions()->create($deduct);
        }
        return response()->json([
            'success' => true,
            'message' => 'Payroll card updated successfully',
            'card' => $card,
        ]);
    }

    public function payroll_cards_earn_head_delete($id)
    {
        // dd($id);
    }
}
