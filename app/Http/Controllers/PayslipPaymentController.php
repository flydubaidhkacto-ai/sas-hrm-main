<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateEmployeePayslipBalance;
use App\Models\Payslip;
use App\Models\PayslipPayment;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class PayslipPaymentController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!role_can('payslip-payment') && !user()->admin_of_super_departments, 403);
        return view('payslip-payment.index', [
            'payslip_payments' => PayslipPayment::query()
                ->with(['user'])
                ->latest()
                ->limit(2000)
                ->when(!role_can('payslip-payment') && user()->admin_of_super_departments, function ($query) {
                    return $query->whereHas('user.department', function ($query) {
                        return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                    });
                })
                ->when($request->super_department, function ($query) use ($request) {
                    return $query->whereHas('user', function ($query) use ($request) {
                        return $query->where('super_department_id', $request->super_department);
                    });
                })
                ->when($request->department, function ($query) use ($request) {
                    return $query->whereHas('user', function ($query) use ($request) {
                        return $query->where('department_id', $request->department);
                    });
                })
                ->when($request->designation, function ($query) use ($request) {
                    return $query->whereHas('user', function ($query) use ($request) {
                        return $query->where('designation_id', $request->designation);
                    });
                })
                ->when($request->employee, function ($query) use ($request) {
                    return $query->whereHas('user', function ($query) use ($request) {
                        return $query->where('id', $request->employee);
                    });
                })
                ->when($request->month, function ($query) use ($request) {
                    $month = $request->month;
                    return $query->where('payslip_date', $month . '-01');
                })
                ->get(),
        ]);
    }

    public function pay(Request $request)
    {
        $payslip = Payslip::findOrFail($request->payslip_id);
        $user = $payslip->employee;
        abort_if(
            !role_can('payslip-payment')
            && !is_admin_of_super_department($user->department ?->super_department_id)
        , 403);
        $request->validate([
            'amount' => 'required|numeric',
            'payslip_id' => 'required|exists:payslips,id',
        ]);

        $payment = PayslipPayment::where('payslip_date', $payslip->month_date)
            ->where('user_id', $payslip->user_id)
            ->first();

        if ($payment) {
            $payment->delete();
        }
        $payment = PayslipPayment::create([
            'payslip_date' => $payslip->month_date,
            'user_id' => $user->id,
            'paid_amount' => $request->amount,
            'total_amount' => $payslip->total_salary,
            'previous_balance' => $user->payroll_balance,
            'due_amount' => $payslip->total_salary - $request->amount,
        ]);
        GenerateEmployeePayslipBalance::dispatch([$user->id]);
        return response()->json([
            'message' => 'Payslip payment created successfully',
            'data' => $payment,
            'id' => $payment->id,
        ]);
    }

    public function destroy(PayslipPayment $payslipPayment, Request $request)
    {
        $user = $payslipPayment->user_id;
        abort_if(
            !role_can('payslip-payment')
            && !is_admin_of_super_department(
                User::find($user) ?->department ?->super_department_id
            )
        , 403);
        $payslipPayment->delete();
        GenerateEmployeePayslipBalance::dispatch([$user]);
        Toastr::success("Record deleted!", 'Success');
        return back();
    }
}
