<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bill;
use App\Models\Vendor;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;

class BillController extends Controller
{
    public function index()
    {
        abort_if(!role_can('admin-accounts'), 403);
        $bills = Bill::with('vendor')->get();
        return view('accounts.bill_management.index', compact('bills'));
    }

    public function create()
    {
        abort_if(!role_can('admin-accounts'), 403);
        $vendors = Vendor::all();
        return view('accounts.bill_management.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $request->validate([
            'bill_number' => 'required|unique:bills',
            'vendor_id' => 'required|exists:vendors,id',
            'due_date' => 'required|date',
            'amount_due' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'status' => 'required|in:pending,partially paid,paid',
            'description' => 'nullable|string',
        ]);

        $bill = new Bill();
        $bill->bill_number = $request->bill_number;
        $bill->vendor_id = $request->vendor_id;
        $bill->due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        $bill->amount_due = $request->amount_due;
        $bill->amount_paid = $request->amount_paid;
        $bill->status = $request->status;
        $bill->description = $request->description;
        $bill->save();
        // dd($bill);

        Toastr::success('Bill added successfully.');
        return redirect()->route('bills.index');
    }

    public function edit(Bill $bill)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $vendors = Vendor::all();
        return view('accounts.bill_management.edit', compact('bill', 'vendors'));
    }

    public function update(Request $request, Bill $bill)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'due_date' => 'required|date',
            'amount_due' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'status' => 'required|in:pending,partially paid,paid',
            'description' => 'nullable|string',
        ]);

        $bill->vendor_id = $request->vendor_id;
        $bill->due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        $bill->amount_due = $request->amount_due;
        $bill->amount_paid = $request->amount_paid;
        $bill->status = $request->status;
        $bill->description = $request->description;
        $bill->save();

        Toastr::success('Bill updated successfully.');
        return redirect()->route('bills.index');
    }

    public function destroy(Bill $bill)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $bill->delete();
        Toastr::success('Bill deleted successfully.');
        return redirect()->route('bills.index');
    }
}
