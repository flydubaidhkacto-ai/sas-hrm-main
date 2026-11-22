<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\InOutRecord;

class UpdateEmployeeInOutRecords implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $employees;
    protected $startDate;

    public function __construct(array $employees, $startDate)
    {
        $this->employees = $employees;
        $this->startDate = $startDate;
    }

    public function handle()
    {
        InOutRecord::whereIn('user_id', $this->employees)
            ->where('date_time', '>=', $this->startDate)
            ->update(['added' => 0]);
    }
}
