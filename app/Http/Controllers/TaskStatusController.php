<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskStatus;
use Brian2694\Toastr\Facades\Toastr;

class TaskStatusController extends Controller
{
    public function index()
    {
        $statuses = TaskStatus::all();
        return view('works.task_status.index', compact('statuses'));
    }

    public function create()
    {
        return view('works.task_status.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:task_statuses,name',
            'color' => 'required',
        ]);

        // TaskStatus::create($request->all());
        $taskStatus = new TaskStatus();
        $taskStatus->name = $request->name;
        $taskStatus->color = $request->color;
        $taskStatus->save();
        Toastr::success('Task status created successfully.');
        return redirect()->route('task-status.index');
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:task_statuses,name,' . $request->id,
            'color' => 'required',
        ]);

        $taskStatus = TaskStatus::find($request->id);
        $taskStatus->name = $request->name;
        $taskStatus->color = $request->color;
        $taskStatus->save();
        Toastr::success('Task status updated successfully.');
        return redirect()->route('task-status.index');
    }

    public function destroy(Request $request)
    {
        $taskStatus = TaskStatus::find($request->id);
        $taskStatus->delete();
        Toastr::success('Task status deleted successfully.');
        return redirect()->route('task-status.index');
    }
}
