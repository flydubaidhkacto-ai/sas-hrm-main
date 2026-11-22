<?php

namespace App\Http\Controllers;

use App\DataTables\GenerateLetterDataTable;
use App\Models\Letter;
use App\Http\Requests\StoreLetterRequest;
use App\Http\Requests\UpdateLetterRequest;
use App\Models\Template;
use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Http\Request;

class LetterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GenerateLetterDataTable $dataTable)
    {
        return $dataTable->render('letter.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $templates = Template::all();
        $employees = User::active_employees()->with('designation', 'department')->get();
        // dd($employees);
        return view('letter.create', compact('templates', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLetterRequest $request)
    {
        $formData = $request->validated();
        $letter = Letter::create($formData);

        return redirect()->route('letters.generate.index')
            ->with('open_link', route('letters.generate.download', $letter->id))
            ->with('success', 'Letter created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Letter $letter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Letter $generate)
    {
        $templates = Template::where('status', 1)->get();
        $employees = User::active_employees()->with('designation', 'department')->get();
        return view('letter.edit', compact('generate', 'templates', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLetterRequest $request, Letter $generate)
    {
        $formData = $request->validated();
        $generate->update($formData);

        return redirect()->route('letters.generate.index')
            ->with('success', 'Letter updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Letter $generate)
    {
        $generate->delete();
        return redirect()->route('letters.generate.index')->with('success', 'Letter deleted successfully.');
    }

    public function download($id)
    {
        $letter = Letter::findOrFail($id);
        $pdf = new Dompdf();
        $pdf->setPaper('a4', 'portrait');
        $pdf->loadHtml($letter->body);
        $pdf->render();
        return $pdf->stream($letter->title . '.pdf');
    }

    // public function download($id)
    // {
    //     // dd('hi');
    //     $letter = Letter::findOrFail($id);

    //     $pdf = new Dompdf();
    //     $pdf->setPaper('a4', 'portrait');
    //     $pdf->loadHtml($letter->body);
    //     $pdf->render();

    //     // Set the filename
    //     $fileName = $letter->title . '.pdf';
    //     // dd($fileName);
    //     // Set the correct headers for file download
    //     return response($pdf->output(), 200, [
    //         'Content-Type' => 'application/pdf',
    //         'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
    //         'Content-Length' => strlen($pdf->output()),
    //     ]);
    // }

}
