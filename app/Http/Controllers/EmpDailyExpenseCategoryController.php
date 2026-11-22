<?php

namespace App\Http\Controllers;

use App\Models\EmpDailyExpenseCategory;
use App\Http\Requests\StoreEmpDailyExpenseCategoryRequest;
use App\Http\Requests\UpdateEmpDailyExpenseCategoryRequest;

class EmpDailyExpenseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_if(!role_can('emp-expenses'), 403);
        $empExpenseCategories = EmpDailyExpenseCategory::simplePaginate(15);
        return view('emp_expenses_category.index', compact('empExpenseCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('emp_expenses_category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmpDailyExpenseCategoryRequest $request)
    {
        abort_if(!role_can('emp-expenses'), 403);
        $formData = $request->validated();
        EmpDailyExpenseCategory::create($formData);
        return redirect()->route('emp-expenses.category.index')->with('success','Employee Expense Category Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmpDailyExpenseCategory $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmpDailyExpenseCategory $category)
    {
        return view('emp_expenses_category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmpDailyExpenseCategoryRequest $request, EmpDailyExpenseCategory $category)
    {
        abort_if(!role_can('emp-expenses'), 403);
        $formData = $request->validated();
        $category->update($formData);
        return redirect()->route('emp-expenses.category.index')->with('success','Employee Expense Category Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmpDailyExpenseCategory $category)
    {
        abort_if(!role_can('emp-expenses'), 403);
        $category->delete();
        return redirect()->route('emp-expenses.category.index')->with('error','Employee Expenses Category Deleted Successfully');
    }
}
