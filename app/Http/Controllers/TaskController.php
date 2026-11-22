<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use App\Models\TaskCategory;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;

class TaskController extends Controller
{
    public function index()
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        if (role_can('work-employee') && !user()->is_super_admin()) {
            // $tasks = Task::with('project', 'taskCategory')->where('created_by', user()->id)->orWhere('assigned_to', user()->id)->latest()->get();
            $tasks = Task::with('project', 'taskCategory')->where('assigned_to', user()->id)->latest()->get();
        } else {
            $tasks = Task::with('project', 'taskCategory')->latest()->get();
        }
        return view('works.task.index', compact('tasks'));
    }

    public function create()
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        $projects = Project::all();
        $categories = TaskCategory::all();
        return view('works.task.create', compact('projects', 'categories'));
    }

    public function store(Request $request)
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_category_id' => 'required|exists:task_categories,id',
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|exists:task_statuses,id',
        ]);

        // Task::create($request->all());
        $task = new Task();
        $task->project_id = $request->project_id;
        $task->task_category_id = $request->task_category_id;
        $task->title = $request->title;
        $task->start_date = $request->start_date ? Carbon($request->start_date)->format('Y-m-d') : NULL;
        $task->due_date = $request->due_date ? Carbon($request->due_date)->format('Y-m-d') : NULL;
        $task->description = $request->description;
        $task->priority = $request->priority;
        $task->assigned_to = $request->assigned_to;
        $task->status = $request->status;
        $task->created_by = user()->id;
        // dd($task);

        $task->save();

        Toastr::success('Task created successfully!');
        return redirect()->route('tasks.index');
    }

    public function edit(Task $task)
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        $projects = Project::all();
        $categories = TaskCategory::all();
        return view('works.task.edit', compact('task', 'projects', 'categories'));
    }

    public function update(Request $request, Task $task)
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_category_id' => 'required|exists:task_categories,id',
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|exists:task_statuses,id',
        ]);

        // $task->update($request->all());
        $task->project_id = $request->project_id;
        $task->task_category_id = $request->task_category_id;
        $task->title = $request->title;
        $task->start_date = $request->start_date ? Carbon($request->start_date)->format('Y-m-d') : NULL;
        $task->due_date = $request->due_date ? Carbon($request->due_date)->format('Y-m-d') : NULL;
        $task->description = $request->description;
        $task->priority = $request->priority;
        $task->assigned_to = $request->assigned_to;
        $task->status = $request->status;
        // dd($task);
        $task->save();

        Toastr::success('Task updated successfully!');
        return redirect()->route('tasks.index');
    }

    public function destroy(Task $task)
    {
        abort_if((!role_can('work-admin') && !role_can('work-employee')), 403);
        $task->delete();
        Toastr::success('Task deleted successfully!');
        return redirect()->route('tasks.index');
    }
    public function updateStatus(Task $task, Request $request)
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        $request->validate([
            'status' => 'required|exists:task_statuses,id'
        ]);

        $task->update([
            'status' => $request->status
        ]);

        Toastr::success('Task status updated successfully!');
        return redirect()->back();
    }
}
