<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Client;
use App\Models\Task;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index()
    {
        abort_if(!role_can('work-admin'), 403);
        $projects = Project::with(['client', 'category'])->get();
        return view('works.project.index', compact('projects'));
    }

    public function create()
    {
        abort_if(!role_can('work-admin'), 403);
        $clients = Client::all();
        $categories = ProjectCategory::all();
        return view('works.project.create', compact('clients', 'categories'));
    }

    public function store(Request $request)
    {
        abort_if(!role_can('work-admin'), 403);
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'category_id' => 'required|exists:project_categories,id',
            'name' => 'required|string|max:255',
            'short_code' => 'nullable|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'project_summary' => 'nullable|string',
            'project_notes' => 'nullable|string',
            'project_budget' => 'nullable|string',
            'estimate_hours' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:pending,in_progress,completed,on_hold',
        ]);

        // $data = $request->all();
        $project = new Project();
        $project->client_id = $request->client_id;
        $project->category_id = $request->category_id;
        $project->name = $request->name;
        $project->short_code = $request->short_code;
        $project->start_date = $request->start_date ? Carbon($request->start_date)->format('Y-m-d') : NULL;
        $project->end_date = $request->end_date ? Carbon($request->end_date)->format('Y-m-d') : NULL;
        $project->project_summary = $request->project_summary;
        $project->project_notes = $request->project_notes;
        $project->project_budget = $request->project_budget;
        $project->estimate_hours = $request->estimate_hours;
        $project->status = $request->status;

        if ($request->hasFile('image')) {
            $project->image = Storage::put('projects', $request->file('image'));
        }
        // dd($project);

        $project->save();

        Toastr::success('Project created successfully.');
        return redirect()->route('projects.index');
    }

    public function edit(Project $project)
    {
        abort_if(!role_can('work-admin'), 403);
        $clients = Client::all();
        $categories = ProjectCategory::all();
        return view('works.project.edit', compact('project', 'clients', 'categories'));
    }

    public function update(Request $request, Project $project)
    {
        abort_if(!role_can('work-admin'), 403);
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'category_id' => 'required|exists:project_categories,id',
            'name' => 'required|string|max:255',
            'short_code' => 'nullable|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'project_summary' => 'nullable|string',
            'project_notes' => 'nullable|string',
            'project_budget' => 'nullable|string',
            'estimate_hours' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
            'status' => 'nullable|in:pending,in_progress,completed,on_hold',
        ]);

        // $data = $request->all();
        $project->client_id = $request->client_id;
        $project->category_id = $request->category_id;
        $project->name = $request->name;
        $project->short_code = $request->short_code;
        $project->start_date = $request->start_date ? Carbon($request->start_date)->format('Y-m-d') : NULL;
        $project->end_date = $request->end_date ? Carbon($request->end_date)->format('Y-m-d') : NULL;
        $project->project_summary = $request->project_summary;
        $project->project_notes = $request->project_notes;
        $project->project_budget = $request->project_budget;
        $project->estimate_hours = $request->estimate_hours;
        $project->status = $request->status;

        if ($request->hasFile('image')) {
            if ($project->image && Storage::exists($project->image)) {
                Storage::delete($project->image);
            }
            $project->image = Storage::put('projects', $request->file('image'));
        }

        // dd($project);
        $project->save();

        Toastr::success('Project updated successfully.');
        return redirect()->route('projects.index');
    }

    public function destroy(Project $project)
    {
        abort_if(!role_can('work-admin'), 403);
        $project->delete();
        Toastr::success('Project deleted successfully.');
        return redirect()->route('projects.index');
    }

    public function updateStatus(Request $request, Project $project)
    {
        abort_if(!role_can('work-admin'), 403);
        $project->update(['status' => $request->status]);
        Toastr::success('Project status updated successfully!');
        return back();
    }

    public function show(Project $project)
    {
        abort_if(!role_can('work-admin'), 403);
        $tasks = Task::where('project_id', $project->id)->get();
        // dd($tasks);
        return view('works.project.show', compact('project', 'tasks'));
    }

    public function updateStatusKanban(Request $request)
    {
        $task = Task::findOrFail($request->task_id);
        $task->status = $request->status;
        $task->save();
        Toastr::success('Task status updated successfully!');
        return redirect()->back();
    }

}
