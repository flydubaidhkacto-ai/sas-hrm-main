<?php

namespace App\Console\Commands;

use App\Jobs\EmployeePunchedJob;
use App\Models\InOutRecord;
use App\Models\ZktecoCloud;
use Illuminate\Console\Command;

class ZktecoCloudLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zkteco-cloud:log {SN} {user_id} {time} {inoutstate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sn = $this->argument('SN');
        $user_id = $this->argument('user_id');
        $time = $this->argument('time');
        $inoutstate = $this->argument('inoutstate') ?: 0;

        $zktecoCloud = ZktecoCloud::where('device_sn', $sn)
            ->first();
        if (!$zktecoCloud) {
            $this->error('Zkteco Cloud not found');
            return;
        }
        if($zktecoCloud->inoutstate !== null) $inoutstate = $zktecoCloud->inoutstate;
        
        $find = InOutRecord::where('user_id', $user_id)
            ->where('date_time', $time)
            ->first();
        if (!$find) {
            $check = InOutRecord::firstOrCreate([
                'user_id' => $user_id,
                'status' => $inoutstate,
                'date_time' => $time,
                'zkteco_cloud_id' => $zktecoCloud->id,
                'original_date_time' => $time,
                'device_mac' => "ZKteco cloud attendance",
                'device_uid' => $sn,
                'device_attendance_uid' => "ZKteco cloud attendance",
            ]);
            EmployeePunchedJob::dispatch($check);
            $this->info('Zkteco Cloud log created');
        }

    }
}
