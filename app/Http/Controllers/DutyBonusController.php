<?php

namespace App\Http\Controllers;

use App\DataTables\DutyBonusDataTable;
use App\Models\DutyBonus;
use App\Http\Requests\StoreDutyBonusRequest;
use App\Http\Requests\UpdateDutyBonusRequest;
use App\Models\DutyBonusRecord;

class DutyBonusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DutyBonusDataTable $dataTable)
    {
        return $dataTable->render('duty_bonus.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('duty_bonus.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDutyBonusRequest $request)
    {
        $formData = $request->validated();
        DutyBonus::create($formData);

        return redirect()->route('auto-duty-bonuses.index')->with('success','Auto Duty Bonus Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(DutyBonus $auto_duty_bonus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DutyBonus $auto_duty_bonus)
    {
        return view('duty_bonus.edit', compact('auto_duty_bonus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDutyBonusRequest $request, DutyBonus $auto_duty_bonus)
    {
        $formData = $request->validated();

        $auto_duty_bonus->update($formData);

        return redirect()->route('auto-duty-bonuses.index')->with('success','Auto Duty Bonus Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DutyBonus $auto_duty_bonus)
    {
        $auto_duty_bonus->delete();
        return redirect()->route('auto-duty-bonuses.index')->with('success','Auto Duty Bonus Deleted Successfully');
    }
    function bonuses()
    {
        $duty_bonuses = DutyBonusRecord::query()->orderBy('date', 'desc')->paginate();
        return view('duty_bonus.bonuses', [
            'duty_bonuses' => $duty_bonuses,
        ]);
    }
}
