<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectCategory;
use Brian2694\Toastr\Facades\Toastr;

class ProjectCategoryController extends Controller
{
    public function index()
    {
        abort_if(!role_can('work-admin'), 403);
        $categories = ProjectCategory::latest()->get();
        return view('works.project_category.index', compact('categories'));
    }

    public function create()
    {
        abort_if(!role_can('work-admin'), 403);
        return view('works.project_category.create');
    }

    public function store(Request $request)
    {
        abort_if(!role_can('work-admin'), 403);
        $request->validate([
            'name' => 'required|unique:project_categories,name',
        ]);

        $projectCategory = new ProjectCategory();
        $projectCategory->name = $request->name;
        $projectCategory->save();

        Toastr::success('Category created successfully.');
        return redirect()->route('project-categories.index');
    }

    public function edit(ProjectCategory $projectCategory)
    {
        abort_if(!role_can('work-admin'), 403);
        return view('works.project_category.edit', compact('projectCategory'));
    }

    public function update(Request $request, ProjectCategory $projectCategory)
    {
        abort_if(!role_can('work-admin'), 403);
        $request->validate([
            'name' => 'required|unique:project_categories,name,' . $projectCategory->id,
        ]);

        $projectCategory->name = $request->name;
        $projectCategory->save();

        Toastr::success('Category updated successfully.');
        return redirect()->route('project-categories.index');
    }

    public function destroy(ProjectCategory $projectCategory)
    {
        abort_if(!role_can('work-admin'), 403);
        if ($projectCategory->projects()->exists()) {
            Toastr::error('Category cannot be deleted as it has associated projects.');
            return redirect()->route('project-categories.index');
        }
        $projectCategory->delete();
        Toastr::success('Category deleted successfully.');
        return redirect()->route('project-categories.index');
    }
}
