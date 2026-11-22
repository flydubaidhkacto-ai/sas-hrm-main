<?php

namespace App\Http\Controllers;

use App\Actions\EmployeePunched;
use App\Gates\User;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\InOutRecord;
use App\Models\SuperDepartment;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class SuperDepartmentController extends Controller
{
    public function index()
    {
        abort_if(!role_can('super_department'), 403, "Please get permission from Administrator");
        $departments = SuperDepartment::with(['departments' => function ($q) {
            $q->ordered();
        }])->ordered()->get();
        return view('departments.index', [
            'departments' => $departments
        ]);
    }
    function department($id)
    {
        abort_if(!role_can('super_department'), 403, "Please get permission from Administrator");
        $suprt = SuperDepartment::findOrFail($id);
        return view('departments.department', [
            'department' => $suprt,
            'sub' => $suprt->departments()->ordered()->get(),
        ]);
    }

    public function save(Request $request)
    {
        abort_if(!role_can('super_department'), 403, "Please get permission from Administrator");
        $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);
        if(SuperDepartment::where('name',$request->name)->exists())
        {
            Toastr::error('Department already exists :)','Error');
            return redirect()->back();
        }
        SuperDepartment::create([
            'name' => $request->name,
            'order' => $request->order ?? 0,
        ]);
        Toastr::success('Add new department successfully :)','Success');
        return redirect()->route('departments.index');
    }
    public function update(Request $request)
    {
        abort_if(!role_can('super_department'), 403, "Please get permission from Administrator");
        $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);
        $department = SuperDepartment::findOrFail($request->id);
        $department->update([
            'name' => $request->name,
            'order' => $request->order ?? 0,
        ]);
        Toastr::success('Department updated successfully :)','Success');
        return redirect()->route('departments.index');
    }
    public function delete(Request $request)
    {
        abort_if(!role_can('super_department'), 403, "Please get permission from Administrator");
        SuperDepartment::destroy($request->id);
        Toastr::success('Department deleted successfully :)','Success');
        return redirect()->back();
    }
    function save_sub_department(Request $request)
    {
        abort_if(!role_can('super_department'), 403, "Please get permission from Administrator");
        $super = SuperDepartment::findOrFail($request->id);
        if($super->departments()->where('name',$request->name)->exists())
        {
            Toastr::error('Department already exists :)','Error');
            return redirect()->back();
        }
        $super->departments()->create([
            'name' => $request->name,
            'order' => $request->order ?? 0,
        ]);
        Toastr::success('Add new sub department successfully :)','Success');
        return redirect()->back();
    }

    //add department modal
    public function departmentModalSave(Request $request)
    {

        abort_if(!role_can('super_department'), 403, "Please get permission from Administrator");

        $super_department = SuperDepartment::findOrFail($request->id);

        if ($super_department->departments()->where('name', $request->name)->exists()) {
            return response()->json([
                'message' => 'Department already exists',
                'status' => 'error'
            ], 400);
        }

        $newD_department = $super_department->departments()->create([
            'name' => $request->name,
            'order' => $request->order ?? 0,
        ]);

        return response()->json([
            'message' => 'Department added successfully',
            'department' => $newD_department,
            'super_department' => [
                'id' => $super_department->id,
                'name' => $super_department->name
            ],
            'status' => 'success'
        ]);
    }


    /** update record department */
    public function updateRecordDepartment(Request $request)
    {
        abort_if(!role_can('super_department'), 403, "Please get permission from Administrator");
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $department = Department::findOrFail($request->id);
        $department->update([
            'name' => $request->name,
            'order' => $request->order ?? $department->order,
        ]);
        Toastr::success('Department updated successfully :)','Success');
        return redirect()->back();
    }

    /** delete record department */
    public function deleteRecordDepartment(Request $request)
    {
        abort_if(!role_can('super_department'), 403, "Please get permission from Administrator");
        Department::destroy($request->id);
        Toastr::success('Department deleted successfully :)','Success');
        return redirect()->back();
    }

    /** update department order */
    public function updateDepartmentOrder(Request $request)
    {
        abort_if(!role_can('super_department'), 403, "Please get permission from Administrator");
        $request->validate([
            'id' => 'required|exists:departments,id',
            'order' => 'required|integer|min:0',
        ]);
        Department::findOrFail($request->id)->update([
            'order' => $request->order
        ]);
        Toastr::success('Department order updated successfully :)','Success');
        return redirect()->back();
    }

    /** update super department order */
    public function updateSuperDepartmentOrder(Request $request)
    {
        abort_if(!role_can('super_department'), 403, "Please get permission from Administrator");
        $request->validate([
            'id' => 'required|exists:super_departments,id',
            'order' => 'required|integer|min:0',
        ]);
        SuperDepartment::findOrFail($request->id)->update([
            'order' => $request->order
        ]);
        Toastr::success('Super Department order updated successfully :)','Success');
        return redirect()->back();
    }
}

