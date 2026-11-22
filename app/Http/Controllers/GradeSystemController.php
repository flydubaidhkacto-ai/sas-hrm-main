<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Grade;

class GradeSystemController extends Controller
{
    function index()
    {
        return view('grade_system.index', [
            'data' => Grade::latest()->get()
        ]);
    }

    function create()
    {
        return view('grade_system.create');
    }

    function store(Request $request)
    {
        abort_if(!role_can('salary_grade'), 403);
        $data = $request->validate([
            'name' => "required|max:50|unique:grades,name",
            'amount' => 'required|numeric|min:1',
        ]);
        $data['name'] = $request->name;
        $data['amount'] = $request->amount;
        Grade::create($data);
        Toastr::success('Salary Grade  added');
        return redirect()->route('grade_system');
    }

    function edit($id)
    {
        abort_if(!role_can('salary_grade'), 403);
        return view('grade_system.edit', [
            'data' => Grade::findOrFail($id)
        ]);
    }
    function update(Request $request, $id)
    {
        abort_if(!role_can('salary_grade'), 403);
        $data = $request->validate([
            'name' => "required|max:50|unique:grades,name," . $id,
            'amount' => 'required|numeric|min:1',
        ]);
        $data['name'] = $request->name;
        $data['amount'] = $request->amount;
        Grade::where('id', $id)->update($data);
        Toastr::success('Salary Grade  updated');
        return redirect()->route('grade_system');
    }

    function delete($id)
    {
        abort_if(!role_can('salary_grade'), 403);
        $find = Grade::findOrFail($id);
        if ($find->payroll_cards()->count() > 0) {
            Toastr::error('Salary Grade  cannot be deleted because it is assigned to some employee salary cards');
            return back();
        }
        $find->delete();
        Toastr::success('Salary Grade  deleted');
        return redirect()->route('grade_system');
    }
}
