<?php

namespace App\Console\Commands;

use App\Models\DeviceSession;
use Illuminate\Console\Command;

class CleanupDeviceSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device-sessions:cleanup
                            {--days=30 : Number of days after which to consider sessions old}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old device sessions that haven\'t been active for specified days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Looking for device sessions inactive for more than {$days} days...");

        // Get old sessions
        $oldSessions = DeviceSession::where('last_activity', '<', now()->subDays($days));

        $count = $oldSessions->count();

        if ($count === 0) {
            $this->info('No old device sessions found to cleanup.');
            return 0;
        }

        if ($dryRun) {
            $this->info("DRY RUN: Would delete {$count} device sessions:");

            $oldSessions->with('user')->chunk(100, function ($sessions) {
                foreach ($sessions as $session) {
                    $this->line("- User: {$session->user->name} ({$session->user->email}), Device: {$session->device_name}, Last Activity: {$session->last_activity->format('Y-m-d H:i:s')}");
                }
            });

            return 0;
        }

        if ($this->confirm("Are you sure you want to delete {$count} old device sessions?")) {
            $deletedCount = DeviceSession::cleanupOldSessions($days);

            $this->info("Successfully cleaned up {$deletedCount} old device sessions.");
        } else {
            $this->info('Cleanup cancelled.');
        }

        return 0;
    }
}
