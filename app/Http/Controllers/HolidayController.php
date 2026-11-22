<?php

namespace App\Http\Controllers;

use App\Models\SuperDepartment;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Holiday;
use App\Models\HolidayRange;

/**
 * Normalize the branch selection from a request into a clean array of ids.
 * Empty / missing selection => null (organization-wide, backward compatible).
 */
function holiday_branches_from_request(Request $request): ?array
{
    $input = $request->input('branches');

    if (is_array($input)) {
        $ids = array_filter(array_map('intval', $input), fn($id) => $id > 0);
        $ids = array_values(array_unique($ids));
        return count($ids) ? $ids : null;
    }

    if (is_string($input) && trim($input) !== '' && trim($input) !== '0') {
        // comma separated fallback
        $ids = array_filter(array_map('intval', explode(',', $input)), fn($id) => $id > 0);
        $ids = array_values(array_unique($ids));
        return count($ids) ? $ids : null;
    }

    return null;
}

class HolidayController extends Controller
{
    // holidays
    public function holiday()
    {
        abort_if(!role_can('holidays'), 403);
        $holiday = Holiday::orderBy('date')->get();
        $branches = SuperDepartment::ordered()->get();
        return view('holidays', compact('holiday', 'branches'));
    }

    // holidays_ranges
    public function holidayRange(Request $request)
    {
        $year = $request->year ?? date('Y');
        $holiday = HolidayRange::whereYear('start_date', $year)->get();
        $branches = SuperDepartment::ordered()->get();
        return view('holidayrange', compact('holiday', 'year', 'branches'));
    }

    // save record
    public function saveRangeRecord(Request $request)
    {
        abort_if(!role_can('holidays'), 403);
        $request->validate([
            'name' => 'required|string|max:255|min:3',
            'start_date' => 'required|date|before_or_equal:end_date|max:255',
            'end_date' => 'required|date|max:255',
        ]);
        $start = carbon($request->start_date);
        $end = carbon($request->end_date);
        $branches = holiday_branches_from_request($request);

        // Check for conflicts - only if the new holiday is organization-wide
        foreach(generateDateRange($start, $end) as $date) {
            $existingHoliday = Holiday::where('date', $date)->first();
            if ($existingHoliday && empty($branches)) {
                return redirect()->back()->withErrors(['date' => carbon($date)->format('d F, Y') . ' already has a holiday!']);
            }
        }

        $holiday = new HolidayRange;
        $holiday->name = $request->name;
        $holiday->start_date = $start;
        $holiday->end_date = $end;
        $holiday->branches = $branches;
        $holiday->save();
        $holiday->__created();
        return redirect()->back()->with('success', 'Holiday added successfully!');
    }

    public function saveRecord(Request $request)
    {
        abort_if(!role_can('holidays'), 403);
        $request->validate([
            'nameHoliday' => 'required|string|max:255|min:3',
            'holidayDate' => 'required|date|max:255',
        ]);

        $holidayDate = carbon($request->holidayDate);
        $branches = holiday_branches_from_request($request);

        // Check for conflicts - only if the new holiday is organization-wide
        $existingHoliday = Holiday::where('date', $holidayDate)->first();
        if ($existingHoliday && empty($branches)) {
            return redirect()->back()->withErrors(['holidayDate' => carbon($holidayDate)->format('d F, Y') . ' already has a holiday!']);
        }

        $holiday = new Holiday;
        $holiday->name = $request->nameHoliday;
        $holiday->date  = $holidayDate;
        $holiday->branches = $branches;
        $holiday->save();
        Toastr::success('Holiday added successfully :)','Success');
        return redirect()->back();
    }

    // update
    public function updateRangeRecord(Request $request)
    {
        abort_if(!role_can('holidays'), 403);
        $request->validate([
            'name' => 'required|string|max:255|min:3',
            'start_date' => 'required|date|before_or_equal:end_date|max:255',
            'end_date' => 'required|date|max:255',
        ]);
        $start = carbon($request->start_date);
        $end = carbon($request->end_date);
        $branches = holiday_branches_from_request($request);

        // Check for conflicts - only if the updated holiday is organization-wide
        foreach(generateDateRange($start, $end) as $date) {
            $existingHoliday = Holiday::where('date', $date)
                ->where('holiday_range_id', '!=', $request->id)
                ->first();
            if ($existingHoliday && empty($branches)) {
                Toastr::error(carbon($date)->format('d F, Y') . " already has a holiday!");
                return redirect()->back();
            }
        }

        $holiday = HolidayRange::find($request->id);
        $holiday->__updating();
        $holiday->update([
            'name' => $request->name,
            'start_date' => $start,
            'end_date' => $end,
            'branches' => $branches,
        ]);

        $holiday->refresh();
        $holiday->__updated();
        Toastr::success('Holiday updated successfully :)','Success');
        return redirect()->back();
    }

    public function updateRecord(Request $request)
    {
        abort_if(!role_can('holidays'), 403);
        $request->validate([
            'name' => 'required|string|max:255|min:3',
            'date' => 'required|date|max:255',
        ]);
        Holiday::where('id', $request->id)->update([
            'name' => $request->name,
            'date' => carbon($request->date),
            'branches' => holiday_branches_from_request($request),
        ]);
        Toastr::success('Holiday updated successfully :)','Success');
        return redirect()->back();
    }

    public function delete(Request $request)
    {
        abort_if(!role_can('holidays'), 403);

        // Check if it's a HolidayRange or individual Holiday
        $holidayRange = HolidayRange::find($request->id);
        if ($holidayRange) {
            $holidayRange->__deleting();
            $holidayRange->delete();
            Toastr::success('Holiday range deleted successfully :)', 'Success');
        } else {
            $holiday = Holiday::findOrFail($request->id);
            $holiday->delete();
            Toastr::success('Holiday deleted successfully :)', 'Success');
        }

        return redirect()->back();
    }
}
