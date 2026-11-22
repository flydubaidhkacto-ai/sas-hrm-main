<?php

namespace App\Http\Controllers;

use App\DataTables\HalfDayFineDataTable;
use App\Models\HalfDayFine;
use App\Http\Requests\StoreHalfDayFineRequest;
use App\Http\Requests\UpdateHalfDayFineRequest;

class HalfDayFineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(HalfDayFineDataTable $dataTable)
    {
        return $dataTable->render('half_day_fine.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('half_day_fine.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHalfDayFineRequest $request)
    {
        $formData = $request->validated();
        $formData['created_by'] = user()->id;
        HalfDayFine::create($formData);
        return redirect()->route('half-day-fines.index')->with('success', 'Half Day Fine created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(HalfDayFine $half_day_fine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HalfDayFine $half_day_fine)
    {
        return view('half_day_fine.edit', compact('half_day_fine'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHalfDayFineRequest $request, HalfDayFine $half_day_fine)
    {
        $formData = $request->validated();

        $half_day_fine->update($formData);

        return redirect()->route('half-day-fines.index')->with('success', 'Half Day Fine updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HalfDayFine $half_day_fine)
    {
        $half_day_fine->delete();

        return redirect()->route('half-day-fines.index')->with('success', 'Half Day Fine deleted successfully.');
    }
}
