<?php

use App\Actions\EmployeePunched;
use App\Models\Attendance;
use App\Models\InOutRecord;
use App\Models\PayrollCard;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(
        decrypt_internal(encrypt_internal(" fawef awef awef awef awef we"))
    );
})->purpose('Display an inspiring quote');

Artisan::command('reset-pass', function () {
    User::query()
        ->update([
            'password' => Hash::make('12345678')
        ]);
});;
Artisan::command('generate-inouts', function () {
    $totalRecords = InOutRecord::query()
        ->where('date_time', '>', now()->subWeeks(1))
        ->count();
    $bar = $this->output->createProgressBar($totalRecords);
    $bar->start();
    InOutRecord::query()
        ->where('date_time', '>', now()->subWeeks(1))
        ->chunk(100, function ($records) use ($bar) {
            foreach ($records as $record) {
                \App\Actions\EmployeePunched\Execute::run($record);
                $bar->advance(); // Move the progress bar forward
            }
        });

    $bar->finish(); // Finish the progress bar
    $this->info("\nProcessing complete!"); // Show completion message
});


Artisan::command('recalculate-salary', function () {
    PayrollCard::query()
        // ->where('id', 72)
        ->chunk(100, function ($cards) {
            foreach ($cards as $card) {
                $card->recalculateSalaryItems();
                // dd($card->toArray());
            }
        });
});
