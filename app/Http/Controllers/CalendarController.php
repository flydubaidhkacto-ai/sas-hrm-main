<?php

namespace App\Http\Controllers;

use App\Actions\GetCalendarByUSer;
use App\Models\Holiday;
use App\Models\User;
use App\Models\Roster;
use App\Models\RosterItem;
use App\Models\ShiftSchedule;
use App\Models\ShiftScheduleItem;
use App\Models\Shift;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function get_data(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $user = user();

        $events = GetCalendarByUSer::execute($user, $month, $year);

        return response()->json($events); // ✅ FullCalendar expects a flat array
    }
}
