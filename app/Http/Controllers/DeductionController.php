<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\DeductionItemEntry;
use App\Models\PayrollCardDeductionItem;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    function index()
    {
        return view('deductions.index', [
            'data' => Deduction::latest()->get()
        ]);
    }
    function create()
    {
        return view('deductions.create', [
            'categories' => Deduction::$categories,
        ]);
    }
    function store(Request $request)
    {
        abort_if(!role_can('deduction'), 403);
        $data = $request->validate([
            'code' => "required|max:50|unique:deductions,code",
            'name' => "required|max:50|unique:deductions,name",
            'amount' => 'numeric',
            'amount_type' => 'required|string|in:percentage,fixed,other',
            'category' => 'required|string|in:' . join(',', array_keys(Deduction::$categories)),
        ]);

        $head = Deduction::create($data);
        Toastr::success('Deduction item added');
        return redirect()->route('deductions.index');
    }
    function edit($id)
    {
        abort_if(!role_can('deduction'), 403);
        return view('deductions.edit', [
            'head' => Deduction::findOrFail($id)
        ]);
    }
    function update($id, Request $request)
    {
        abort_if(!role_can('deduction'), 403);
        $head = Deduction::findOrFail($id);

        $data = $request->validate([
            'code' => "required|max:50|unique:deductions,code," . $id,
            'name' => "required|max:50|unique:earn_heads,name," . $id,
            'amount' => 'numeric',
            'amount_type' => 'required|string|in:percentage,fixed,other',
            'category' => 'required|string|in:' . join(',', array_keys(Deduction::$categories)),
        ]);

        $head->update($data);
        if($request->update_all == 'on')
        {
            PayrollCardDeductionItem::query()
                ->where('code', $head->code)
                ->update([
                    'name' => $head->name,
                    'category' => $head->category,
                    'amount' => $request->amount,
                    'amount_type' => $request->amount_type,
                ]);
            Toastr::success('Deduction items updated in all salary cards!');
        }
        Toastr::success('Deduction item edited');
        return redirect()->route('deductions.index');
    }
    function delete($id)
    {
        abort_if(!role_can('deduction'), 403);
        $head = Deduction::findOrFail($id);
        $head->delete();
        Toastr::success('Deduction item deleted');
        return redirect()->route('deductions.index');
    }
    function bulk($type)
    {
        return view('deductions.bulk', [
            'type' => $type,
            'employees' => User::active_employees()->whereHas('payroll_card')->get(),
        ]);
    }
    function bulkStore(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string',
            'date' => 'required|date',
            'amount' => 'required|array',
        ]);
        foreach ($data['amount'] as $employeeId => $amount) {
            if($amount <= 0) continue;
            $deditem = PayrollCardDeductionItem::where('name', $data['type'])
                ->whereHas('payrollCard', function ($query) use ($employeeId) {
                    $query->where('user_id', $employeeId);
                })
                ->first();
            if ($deditem) {
                $old = DeductionItemEntry::where('name', $data['type'])
                    ->where('date', $data['date'])
                    ->where('employee_id', $employeeId)
                    ->first();
                if ($old) {
                    $old->update([
                        'amount' => $amount,
                    ]);
                } else {
                    $old = DeductionItemEntry::create([
                        'name' => $data['type'],
                        'date' => $data['date'],
                        'amount' => $amount,
                        'employee_id' => $employeeId,
                    ]);
                }
            }
        }
        Toastr::success('Deduction item entries updated');
        return redirect()->back();
    }
}
