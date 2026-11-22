<?php

namespace App\Console;

use App\Jobs\CheckEmployeeReward;
use App\Jobs\GenerateAutoEarnLeave;
use App\Jobs\GenerateEmployeePayslipBalance;
use App\Jobs\GenerateLongAbsentReport;
use App\Jobs\InitAutoAbsentFineEmployees;
use App\Jobs\InitAutoLateFineEmployees;
use App\Jobs\ProccessInOutRecords;
use App\Jobs\RunScheduledTerminationActivation;
use App\Jobs\RunSMSJob;
use App\Jobs\SendLateWarningSMS;
use App\Jobs\SendLeaveApplicationDecisionSMS;
use App\Models\InOutRecord;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(ProccessInOutRecords::class)->everyMinute();
        $schedule->job(SendLateWarningSMS::class)->everyFifteenMinutes();
        $schedule->job(SendLeaveApplicationDecisionSMS::class)->everyTenMinutes();
        $schedule->job(InitAutoLateFineEmployees::class)->hourly();
        $schedule->job(RunScheduledTerminationActivation::class)->everyFiveMinutes();
        $schedule->job(RunSMSJob::class)->everyMinute();

        // run this at the end of the month
        $schedule->job(CheckEmployeeReward::class)->monthlyOn(now()->endOfMonth()->day, '23:59');
        $schedule->job(CheckEmployeeReward::class)->monthlyOn(now()->endOfMonth()->day, '23:55');

        $schedule->job(GenerateAutoEarnLeave::class)->twiceDaily();
        $schedule->job(GenerateEmployeePayslipBalance::class)->dailyAt('01:00');
        $schedule->job(GenerateLongAbsentReport::class)->dailyAt('01:00');

        // run every hour and every
        $schedule->command('backup:run')->daily()->withoutOverlapping();
        $schedule->command('backup:clean')->daily()->withoutOverlapping();

        // Clean up old device sessions weekly
        $schedule->command('device-sessions:cleanup')->weekly();

        // Sync ZKTeco Cloud devices that are online but not auto-syncing attendance
        $schedule->command('zkteco:sync-attendances')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer();

        // Daily — every day 10:50 AM
        $schedule->command('report:attendance-daily')
            ->dailyAt('10:50')
            ->withoutOverlapping()
            ->onOneServer();
        // Weekly — Sunday 10:50 AM
        $schedule->command('report:attendance-weekly')
            ->weeklyOn(0, '10:50') // 0 = Sunday
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('approvals:send-reminders')
            ->hourly()
            ->withoutOverlapping()
            ->onOneServer();

        // Daily — every day 10:50 AM
//        $schedule->command('report:attendance-daily')
//            ->everyMinute()
//            ->withoutOverlapping()
//            ->onOneServer();
//
//        // Weekly — Sunday 10:50 AM
//        $schedule->command('report:attendance-weekly')
//            ->everyMinute() // 0 = Sunday
//            ->withoutOverlapping()
//            ->onOneServer();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $commands = [
            Commands\DiskUsage::class,
            Commands\SendPendingApprovalReminders::class,
        ];
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
