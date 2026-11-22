<?php

namespace App\Http\Controllers;

use App\Models\AutoAbsentFine;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class AutoAbsentFineController extends Controller
{

    function auto_absent_fine()
    {
        return view('fines.auto_absent_fine', [
            'data' => AutoAbsentFine::all(),
        ]);
    }
    function auto_absent_fine_store(Request $request)
    {
        abort_if(!role_can('fines'), 403);
        $data = $request->validate([
            'fine_status' => 'required|in:approved,pending',
            'absent_days_amount' => 'required|numeric|min:1|max:365',
            'absent_days' => 'required|in:consecutive_days,any_days_on_month',
            'fine_amount' => 'nullable|numeric|min:0|max:9999999',
            'fine_amount_days' => 'nullable|numeric|min:0|max:9999999',
        ]);

        if (!$data['fine_amount'] && !$data['fine_amount_days']) {
            Toastr::error('Fine amount or fine amount days is required', 'Error');
            return redirect()->back()->withInput();
        }
        if ($data['fine_amount'] && $data['fine_amount_days']) {
            Toastr::error('Fine amount or fine amount days is required, Not Both', 'Error');
            return redirect()->back()->withInput();
        }
        if ($data['fine_amount_days']) {
            $data['fine_amount'] = $data['fine_amount_days'];
            $data['fine_amount_type'] = 'days';
        }
        try {
            unset($data['fine_amount_days']);
        } catch (\Exception $e) {
        }
        AutoAbsentFine::create($data);
        Toastr::success('Auto Absent Fine Added Successfully', 'Success');
        return redirect()->back();
    }
    function auto_absent_fine_delete($id)
    {
        abort_if(!role_can('fines'), 403);
        $fine = AutoAbsentFine::findOrFail($id);
        $fine->delete();
        Toastr::success('Auto Absent Fine Deleted Successfully', 'Success');
        return redirect()->back();
    }
}
