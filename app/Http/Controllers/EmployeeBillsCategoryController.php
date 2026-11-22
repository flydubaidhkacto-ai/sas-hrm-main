<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeBillsCategoryDataTable;
use App\Models\EmployeeBillsCategory;
use App\Http\Requests\StoreEmployeeBillsCategoryRequest;
use App\Http\Requests\UpdateEmployeeBillsCategoryRequest;

class EmployeeBillsCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(EmployeeBillsCategoryDataTable $dataTable)
    {
        return $dataTable->render("payroll.employee_bills.employee_bills_category.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("payroll.employee_bills.employee_bills_category.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeBillsCategoryRequest $request)
    {
        $formData = $request->validated();

        $employeeBillsCategory = EmployeeBillsCategory::create($formData);
        return redirect()->route("emp-bill-category.index")->with("success", "Emp. Bills Category Created Successfully");
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeBillsCategory $emp_bills_category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeBillsCategory $emp_bills_category)
    {
        return view("payroll.employee_bills.employee_bills_category.edit", compact("emp_bills_category"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeBillsCategoryRequest $request, EmployeeBillsCategory $emp_bills_category)
    {
        $formData = $request->validated();

        $emp_bills_category->update($formData);

        return redirect()->route("emp-bill-category.index")->with("success", "Emp. Bills Category Updated Successfully");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeBillsCategory $emp_bills_category)
    {
        abort_if(
            !role_can('emp-bills'),
            403,
            'You are not authorized to access this page'
        );
        $emp_bills_category->delete();

        return redirect()->route("emp-bill-category.index")->with("success", "Emp. Bills Category Deleted Successfully");
    }
}
