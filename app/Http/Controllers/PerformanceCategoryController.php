<?php

namespace App\Http\Controllers;

use App\Models\PerformanceCategory;
use App\Models\PerformanceOfMonth;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class PerformanceCategoryController extends Controller
{
    function index()
    {
        return view('performance.categories.index', [
            'data' => PerformanceCategory::all(),
        ]);
    }
    function create(Request $request)
    {
        $request->validate([
            'name' => 'required|min:1|max:255',
            'max_value' => 'nullable|numeric',
        ]);
        PerformanceCategory::create([
            'name' => $request->name,
            'max_value' => $request->max_value ?: 10,
        ]);
        Toastr::success('Category created successfully', 'Success');
        return redirect()->back();
    }
    function delete($id)
    {
        PerformanceCategory::findOrFail($id)->delete();
        Toastr::success('Category deleted successfully', 'Success');
        return redirect()->back();
    }
    function status($id)
    {
        $category = PerformanceCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();
        Toastr::success('Category status changed successfully', 'Success');
        return redirect()->back();
    }
    function assign($month, Request $request)
    {
        $month = PerformanceOfMonth::findOrFail($month);
        $category = PerformanceCategory::findOrFail($request->category);
        ($month->user)->generate_days($month, carbon($month->month), $category);
        Toastr::success('Assigned successfully', 'Success');
        return redirect()->back();
    }
}
