<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Classes\ApiResponseClass;
use App\Http\Controllers\Controller;
use App\Models\EmpDailyExpenseCategory;
use Illuminate\Http\Request;

class EmpDailyExpenseCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = EmpDailyExpenseCategory::all();
        if (!$categories->count()) {
            return ApiResponseClass::sendResponse(
                [],
                'No Employee Daily Expense Categories found'
            );
        }
        return ApiResponseClass::sendResponse([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories
        ], 200);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:emp_daily_expense_categories,name',
        ]);
        $category = EmpDailyExpenseCategory::create([
            'name' => $request->name,
            'status' => $request->status ?? '1',
        ]);
        return ApiResponseClass::sendResponse(
            $category,
            'Category created successfully'
        );
    }

    public function edit($id)
    {
        $category = EmpDailyExpenseCategory::find($id);
        return ApiResponseClass::sendResponse(
            [
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => $category
            ],
            200
        );
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:emp_daily_expense_categories,name,' . $id,
        ]);
        $category = EmpDailyExpenseCategory::find($id);
        $category->update([
            'name' => $request->name,
            'status' => $request->status ?? '1',
        ]);

        return ApiResponseClass::sendResponse(
            [
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
            ],
            200
        );
    }

    public function delete($id)
    {
        $category = EmpDailyExpenseCategory::find($id);
        $category->delete();
        return ApiResponseClass::sendResponse(
            [
                'success' => true,
                'message' => 'Category deleted successfully'
            ],
            200
        );
    }
}
