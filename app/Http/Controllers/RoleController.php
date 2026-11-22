<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class RoleController extends Controller
{

    function index()
    {

        return view('roles.index', [
            'roles' => Role::all()
        ]);
    }
    function create()
    {

        return view('roles.create', [
            'permissions' => Role::$permissions
        ]);
    }
    function edit(Role $role)
    {

        return view('roles.edit', [
            'role' => $role,
            'permissions' => Role::$permissions,
        ]);
    }
    function store(Request $request)
    {

        $request->validate([
            'name' => 'required|unique:roles|max:255',
            'permissions' => 'required|array',
        ]);
        $role = Role::create([
            'name' => $request->name,
            'permissions' => $request->permissions
        ]);
        Toastr::success("Role created!");
        return redirect()->route('role.index');
        // return
    }
    function update(Role $role, Request $request)
    {

        $request->validate([
            'name' => 'required|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'required|array',
        ]);
        $role->update([
            'name' => $request->name,
            'permissions' => $request->permissions
        ]);
        Toastr::success("Role updated!");
        return redirect()->route('role.index');
        // return
    }
    public function destroy(Role $role, Request $request)
    {
        $role->delete();
        Toastr::success("Role deleted!");
        return redirect()->back();
    }
}
