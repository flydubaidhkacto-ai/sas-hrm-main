<?php

namespace App\Http\Controllers;

use App\DataTables\OfficialFormDataTable;
use App\Models\OfficialForm;
use App\Http\Requests\StoreOfficialFormRequest;
use App\Http\Requests\UpdateOfficialFormRequest;

class OfficialFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(OfficialFormDataTable $dataTable)
    {
        return $dataTable->render('official_form.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('official_form.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOfficialFormRequest $request)
    {
        $formData = $request->validated();
        OfficialForm::create($formData);

        return redirect()->route('official-form.index')
            ->with('success', 'Official Form created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(OfficialForm $official_form)
    {
        return view('official_form.show', compact('official_form'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OfficialForm $official_form)
    {
        return view('official_form.edit', compact('official_form'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOfficialFormRequest $request, OfficialForm $official_form)
    {
        $formData = $request->validated();
        $official_form->update($formData);

        return redirect()->route('official-form.index')
            ->with('success', 'Official Form updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OfficialForm $official_form)
    {
        $official_form->delete();

        return redirect()->route('official-form.index')
            ->with('success', 'Official Form deleted successfully.');
    }
}
