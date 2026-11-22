<?php

namespace App\Http\Controllers;

use App\Models\AbsentFineRule;
use App\Http\Requests\StoreAbsentFineRuleRequest;
use App\Http\Requests\UpdateAbsentFineRuleRequest;

class AbsentFineRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $absentFineRules = AbsentFineRule::all();
        // dd($absentFineRules);
        return view('fines.absent_fine_rule', compact('absentFineRules'));
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
    public function create_or_update(StoreAbsentFineRuleRequest $request)
    {
        try {
            AbsentFineRule::create($request->validated());
            return redirect()->route('fines-absent.index')->with('success', 'Absent fine rules updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AbsentFineRule $absentFineRule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AbsentFineRule $absentFineRule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAbsentFineRuleRequest $request, AbsentFineRule $absentFineRule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AbsentFineRule $absentFineRule)
    {
        //
    }
}
