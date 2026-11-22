<?php

namespace App\Console\Commands;

use App\Models\InOutRecord;
use App\Models\ZktecoCloud;
use App\Models\ZktecoCloudCommand;
use Illuminate\Console\Command;

class SyncZktecoCloudAttendances extends Command
{
    protected $signature = 'zkteco:sync-attendances';
    protected $description = 'Queue sync_attendances commands for active ZKTeco Cloud devices that are not auto-syncing.';

    public function handle()
    {
        $onlineSince = now()->subMinutes(15);
        $recentAttendanceSince = now()->subMinutes(15);
        $devices = ZktecoCloud::query()
            ->where('device_type', 'speedface_v3l')
            ->whereNotNull('last_connected')
            ->where('last_connected', '>=', $onlineSince)
            ->get();

        $queued = 0;

        foreach ($devices as $device) {
            $hasRecentSyncCommand = ZktecoCloudCommand::query()
                ->where('zkteco_cloud_id', $device->id)
                ->where('type', 'sync_attendances')
                ->where(function ($query) {
                    $query->whereNull('reserved_at')
                        ->orWhere('created_at', '>=', now()->subMinutes(30));
                })
                ->exists();

            if ($hasRecentSyncCommand) {
                continue;
            }

            $hasRecentAttendance = InOutRecord::query()
                ->where('device_uid', $device->device_sn)
                ->where('date_time', '>=', $recentAttendanceSince)
                ->exists();

            if ($hasRecentAttendance) {
                continue;
            }

            $commands = ZktecoCloudCommand::get_command('sync_attendances', $device);
            if (!$commands) {
                continue;
            }
            if (!is_array($commands)) {
                $commands = [$commands];
            }

            foreach ($commands as $command) {
                $nextId = (int) ZktecoCloudCommand::query()->max('id') + 1;
                ZktecoCloudCommand::create([
                    'type' => 'sync_attendances',
                    'zkteco_cloud_id' => $device->id,
                    'command' => "C:{$nextId}:{$command}",
                ]);
                $queued++;
            }
        }

        $this->info("Queued {$queued} sync_attendances command(s) for active ZKTeco Cloud devices.");

        return 0;
    }
}

