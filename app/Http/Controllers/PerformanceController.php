<?php

namespace App\Http\Controllers;

use App\Models\DaysPerformanceMonth;
use App\Models\PerformanceCategory;
use App\Models\PerformanceOfMonth;
use App\Models\User;
use Illuminate\Http\Request;
use stdClass;

class PerformanceController extends Controller
{
    function entry(Request $request)
    {
        $data = null;
        if ($request->employee && $request->month) {
            $data = new stdClass();
            $data->emp = User::active_employees()->findOrFail($request->employee);
            $data->performance = $data->emp->get_performance('01-' . $request->month);
            $data->date = carbon('01-' . $request->month);
            $data->total_data =  (new ReportController)->generate_total_duty_of_employee($data->emp, $data->date->copy(), $data->date->copy()->endOfMonth());
        }
        return view('performance.entry', [
            'cates' => PerformanceCategory::all(),
            'data' => $data,
        ]);
    }
    function update_day(Request $request)
    {
        $data = DaysPerformanceMonth::findOrFail($request->id);
        if ($request->value > $data->max_value) {
            $data->update([
                'value' => $data->max_value,
            ]);
            return ['too_big' => $data->max_value];
        }
        $data->update([
            'value' => $request->value,
        ]);
        return ['error' => false];
    }
    function update_notes($id, Request $request)
    {
        PerformanceOfMonth::findOrFail($id)->update([
            'notes' => $request->value,
        ]);
    }
    function report(Request $request)
    {
        $data = null;
        if ($request->employee && $request->month) {
            $data = new stdClass();
            $data->emp = User::active_employees()->findOrFail($request->employee);
            $data->performance = $data->emp->get_performance('01-' . $request->month);
            $data->date = carbon('01-' . $request->month);
            $data->total_data =  (new ReportController)->generate_total_duty_of_employee($data->emp, $data->date->copy(), $data->date->copy()->endOfMonth());
        }
        return view('performance.report', [
            'data' => $data,
        ]);
    }
}
