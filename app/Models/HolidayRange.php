<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Log;

class HolidayRange extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'departments' => 'array',
        'branches' => 'array',
    ];

    static function add_date_to_holiday(Carbon $start, Carbon $end, $name, $id, $branches = null)
    {
        $dates = generateDateRange($start, $end);
        foreach ($dates as $date)
        {
            $result = Holiday::where('date', $date)->first();
            if ($result)
            {
                $result->name = $name;
                $result->holiday_range_id = $id;
                $result->branches = $branches;
                $result->save();
            }
            else {
                $result = Holiday::create([
                    'name' => $name,
                    'date' => $date,
                    'holiday_range_id' => $id,
                    'branches' => $branches,
                ]);
            }
        }
    }
    function __created() {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $name = $this->name;
        self::add_date_to_holiday($startDate, $endDate, $name, $this->id, $this->branches);

        if(config('app.customer') === 'asp') {
            User::employees()
                ->each(function ($user) use ($startDate, $endDate) {
                    Fine::asp_delete_auto_absent_fines_if_present_later(
                        generateDateRange($startDate, $endDate),
                        $user->id
                    );
                });
        }
    }
    function __updated() {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $name = $this->name;
        self::add_date_to_holiday($startDate, $endDate, $name, $this->id, $this->branches);


        if(config('app.customer') == 'asp') {
            User::active_employees()
                ->each(function ($user) use ($startDate, $endDate) {
                    Fine::asp_delete_auto_absent_fines_if_present_later(
                        generateDateRange($startDate, $endDate),
                        $user->id
                    );
                });
        }
    }
    function __updating() {
        Holiday::where('holiday_range_id', $this->id)->delete();
    }
    function __deleting() {
        Holiday::where('holiday_range_id', $this->id)->delete();
    }
}
