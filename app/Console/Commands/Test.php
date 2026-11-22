<?php

namespace App\Console\Commands;

use App\Actions\EmployeePunched\Calculate24HourShift;
use App\Actions\GenerateAttendanceAction;
use Illuminate\Console\Command;
use stdClass;
use App\Models\User;
use App\Models\Attendance;
use App\Models\InOutRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test some actions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Calculate24HourShift::execute((object) [
            'date_time' => '2024-09-03 00:00:00',
            'status' => 1, 'cloud_device' => null, 'device' => null,
        ]);
    }
}
