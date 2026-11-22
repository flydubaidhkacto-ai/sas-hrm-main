<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeBillsDataTable;
use App\Models\EmpBill;
use App\Http\Requests\StoreEmpBillRequest;
use App\Http\Requests\UpdateEmpBillRequest;
use App\Models\EmployeeBillsCategory;
use App\Models\User;

class EmpBillController extends Controller
{
    public function can()
    {
        return abort_if(
            !role_can('emp-bills') && !is_admin_of_super_department()
            , 403, 'You are not authorized to access this page'
        );
    }
    /**
     * Display a listing of the resource.
     */
    public function index(EmployeeBillsDataTable $dataTable)
    {
        $this->can();
        return $dataTable->render("payroll.employee_bills.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->can();
        $empBillsCategories = EmployeeBillsCategory::where('is_active', true)->get();
        return view("payroll.employee_bills.create", compact('empBillsCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmpBillRequest $request)
    {
        abort_if(
            !role_can('create-emp-bills') && !is_admin_of_super_department(
                User::find($request->user_id)?->department?->super_department_id
            )
            , 403, 'You are not authorized to access this page'
        );

        $formData = $request->validated();
        EmpBill::create($formData);
        return redirect()->route('emp-bills.index')->with('success', 'Employee Bills Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmpBill $empBill)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmpBill $empBill)
    {
        $this->can();
        $empBillsCategories = EmployeeBillsCategory::where('is_active', true)->get();
        return view('payroll.employee_bills.edit', compact('empBill', 'empBillsCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmpBillRequest $request, EmpBill $empBill)
    {
        abort_if(
            !role_can('edit-emp-bills') && !is_admin_of_super_department(
                $empBill->user?->department?->super_department_id
            )
            , 403, 'You are not authorized to access this page'
        );
        $formData = $request->validated();
        $empBill->update($formData);

        return redirect()->route('emp-bills.index')->with('success', 'Employee Bills Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmpBill $empBill)
    {
        abort_if(
            !role_can('delete-emp-bills') && !is_admin_of_super_department(
                $empBill->user?->department?->super_department_id
            )
            , 403, 'You are not authorized to access this page'
        );
        $empBill->delete();
        return redirect()->route('emp-bills.index')->with('success', 'Employee Bills Delete Successfully');
    }
}
