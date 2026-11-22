<?php

namespace App\Http\Controllers;

use App\DataTables\UserActivityLogDataTable;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class UserActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!role_can('activity-log')) {
            return redirect()->back()->with('error', 'You are not authorized to access this page.');
        }

        $query = UserActivityLog::orderBy("created_at", "desc");

        if ($request->employee) {
            $query->where('user_id', $request->employee);
        }

        if ($request->url) {
            $query->where('model_name', $request->url);
        }

        $userActivityLogs = $query->simplePaginate(10);

        return view('activity_log.index', compact('userActivityLogs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(UserActivityLog $userActivityLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserActivityLog $userActivityLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserActivityLog $userActivityLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($userActivityLog)
    {
        if (!role_can('activity-log')) {
            return redirect()->back()->with('error', 'You are not authorized to access this page.');
        }
        UserActivityLog::truncate();
        return redirect()->back()->with('success', 'Activity log deleted successfully.');
    }
}
