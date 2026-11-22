<?php

namespace App\Http\Controllers;

use App\DataTables\GenerateOfficialFormDataTable;
use App\Models\GenerateOfficialForm;
use App\Http\Requests\StoreGenerateOfficialFormRequest;
use App\Http\Requests\UpdateGenerateOfficialFormRequest;
use App\Models\OfficialForm;

class GenerateOfficialFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GenerateOfficialFormDataTable $dataTable)
    {
        return $dataTable->render('offical_form_generate.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $officialForms = OfficialForm::all();
        return view('offical_form_generate.create', compact('officialForms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGenerateOfficialFormRequest $request)
    {
        $formData = $request->validated();
        GenerateOfficialForm::create($formData);

        return redirect()->route('official-form-generate.index')->with('success', 'Official Form generated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GenerateOfficialForm $official_form_generate)
    {
        return view('offical_form_generate.show', compact('official_form_generate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GenerateOfficialForm $official_form_generate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGenerateOfficialFormRequest $request, GenerateOfficialForm $official_form_generate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GenerateOfficialForm $official_form_generate)
    {
        $official_form_generate->delete();
        return redirect()->route('official-form-generate.index')->with('success', 'Official Form deleted successfully.');
    }
}
