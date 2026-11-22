<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;

class BudgetController extends Controller
{
    public function index()
    {
        abort_if(!role_can('admin-accounts'), 403);
        $budgets = Budget::all();
        return view('accounts.budget.index', compact('budgets'));
    }

    public function create()
    {
        abort_if(!role_can('admin-accounts'), 403);
        return view('accounts.budget.create');
    }

    public function store(Request $request)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $request->validate([
            'category' => 'required|unique:budgets',
            'budget_limit' => 'required|numeric|min:0',
            'amount_spent' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        // Budget::create($request->all());
        $budget = new Budget();
        $budget->category = $request->category;
        $budget->budget_limit = $request->budget_limit;
        $budget->amount_spent = $request->amount_spent;
        $budget->description = $request->description;
        $budget->save();

        Toastr::success('Budget added successfully.');
        return redirect()->route('budgets.index');
    }

    public function edit(Budget $budget)
    {
        abort_if(!role_can('admin-accounts'), 403);
        return view('accounts.budget.edit', compact('budget'));
    }

    public function update(Request $request, Budget $budget)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $request->validate([
            'budget_limit' => 'required|numeric|min:0',
            'amount_spent' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $budget->budget_limit = $request->budget_limit;
        $budget->amount_spent = $request->amount_spent;
        $budget->description = $request->description;
        $budget->save();

        Toastr::success('Budget updated successfully.');
        return redirect()->route('budgets.index');
    }

    public function destroy(Budget $budget)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $budget->delete();
        Toastr::success('Budget deleted successfully.');
        return redirect()->route('budgets.index');
    }
}
