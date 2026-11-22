<?php

namespace App\Http\Controllers;

use App\Models\EarnHead;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Grade;
use App\Models\PayrollCard;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    function index()
    {
        abort_if(!role_can('grade'), 403);
        return view('grade.index', [
            'data' => Grade::orderBy('name')
                ->get()
        ]);
    }

    function create()
    {
        abort_if(!role_can('grade'), 403);
        return view('grade.create', [
            'heads' => EarnHead::query()
                ->get()
        ]);
    }

    function store(Request $request)
    {
        abort_if(!role_can('grade'), 403);
        // dd($request->all(), EarnHead::$categories);
        $data = $request->validate([
            'name' => "required|max:50|unique:grades,name",
            'amount' => 'required',
            'heads' => 'required|array',
            'heads.*.code' => 'required|max:50',
            'heads.*.name' => 'required|max:50',
            'heads.*.amount' => 'required|numeric',
            'heads.*.amount_type' => 'nullable|in:fixed,percentage,other',
            'heads.*.category' => 'required|in:' . implode(',', array_keys(EarnHead::$categories)),
        ]);
        $grade = Grade::create([
            'name' => $data['name'],
            'amount' => $data['amount'],
        ]);
        foreach ($request->heads as $head) {
            $grade->items()->create([
                'code' => $head['code'],
                'name' => $head['name'],
                'amount' => $head['amount'],
                'amount_type' => $head['amount_type'] ?? 'fixed',
                'category' => $head['category'],
            ]);
        }
        Toastr::success('Salary Grade added');
        return redirect()->route('grades.index');
    }

    function edit($id)
    {
        abort_if(!role_can('grade'), 403);
        return view('grade.edit', [
            'grade' => Grade::with('items')->findOrFail($id),
            'heads' => EarnHead::query()
                ->get()
        ]);
    }
    function update(Request $request, $id)
    {
        abort_if(!role_can('grade'), 403);
        // dd($request->all(), EarnHead::$categories);
        $grade = Grade::findOrFail($id);
        $data = $request->validate([
            'name' => "required|max:50|unique:grades,name," . $id,
            'amount' => 'required',
            'heads' => 'required|array',
            'heads.*.code' => 'required|max:50',
            'heads.*.name' => 'required|max:50',
            'heads.*.amount' => 'required|numeric',
            'heads.*.amount_type' => 'nullable|in:fixed,percentage,other',
            'heads.*.category' => 'required|in:' . implode(',', array_keys(EarnHead::$categories)),
        ]);
        $grade->update([
            'name' => $data['name'],
            'amount' => $data['amount'],
        ]);
        $grade->items()->delete();
        foreach ($request->heads as $head) {
            $grade->items()->create([
                'code' => $head['code'],
                'name' => $head['name'],
                'amount' => $head['amount'],
                'amount_type' => $head['amount_type'] ?? 'fixed',
                'category' => $head['category'],
            ]);
        }
        // update the salary cards
        PayrollCard::query()->where('grade_id', $id)->chunk(100, function($cards) {
            foreach($cards as $card) {
                $card->recalculateSalaryItems();
            }
        });
        
        Toastr::success('Salary Grade updated');
        return redirect()->route('grades.index');
    }

    function delete($id)
    {
        abort_if(!role_can('grade'), 403);
        $find = Grade::findOrFail($id);
        if ($find->payroll_cards()->count() > 0) {
            Toastr::error('Salary Grade cannot be deleted because it is assigned to some employee salary cards');
            return back();
        }
        $find->delete();
        Toastr::success('Salary Grade deleted');
        return redirect()->route('grades.index');
    }
}
