<?php

namespace App\Http\Controllers\MobileApi;

use App\Http\Controllers\Controller;
use App\Models\PayrollCard;
use App\Models\Payslip;
use App\Models\ProvidentFundPayment;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    function cards(Request $request) {
        return PayrollCard::query()
            ->when($request->employee, function ($query) use ($request) {
                return $query->where('user_id', $request->employee);
            })
            ->with(['employee.designation', 'employee.department.super_department', 'grade', 'deductions', 'earn_heads'])
            ->get();
    }
    function provident_funds(Request $request) {
        $users = User::active_employees()
            ->when($request->employee, function ($query) use ($request) {
                return $query->where('id', $request->employee);
            })
            ->with([])
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
        return $data;
    }
    function payslips(Request $request) {
        return Payslip::query()
            ->when($request->employee, function ($query) use ($request) {
                return $query->where('user_id', $request->employee);
            })
            ->where('month_date', $request->month . '-01')
            ->with(['employee.designation', 'employee.department.super_department'])
            ->get();
    }
}
