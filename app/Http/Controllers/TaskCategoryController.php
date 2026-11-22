<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskCategory;
use Brian2694\Toastr\Facades\Toastr;

class TaskCategoryController extends Controller
{
    public function index()
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        $categories = TaskCategory::latest()->get();
        return view('works.task_category.index', compact('categories'));
    }

    public function create()
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        return view('works.task_category.create');
    }

    public function store(Request $request)
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        $request->validate([
            'name' => 'required|unique:task_categories,name',
        ]);

        $taskCategory = new TaskCategory();
        $taskCategory->name = $request->name;
        $taskCategory->save();

        Toastr::success('Category created successfully!');
        return redirect()->route('task-categories.index');
    }

    public function edit(TaskCategory $taskCategory)
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        return view('works.task_category.edit', compact('taskCategory'));
    }

    public function update(Request $request, TaskCategory $taskCategory)
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        $request->validate([
            'name' => 'required|unique:task_categories,name,' . $taskCategory->id,
        ]);

        $taskCategory->name = $request->name;
        $taskCategory->save();

        Toastr::success('Category updated successfully!');
        return redirect()->route('task-categories.index');
    }

    public function destroy(TaskCategory $taskCategory)
    {
        abort_if((!role_can('work-admin') && !role_can('work-employee')), 403);
        if ($taskCategory->tasks()->exists()) {
            Toastr::error('Category cannot be deleted as it has associated tasks.');
            return redirect()->route('task-categories.index');
        }
        $taskCategory->delete();
        Toastr::success('Category deleted successfully!');
        return redirect()->route('task-categories.index');
    }
}
