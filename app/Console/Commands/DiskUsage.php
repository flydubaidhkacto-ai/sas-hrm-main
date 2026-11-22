<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DiskUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disk:usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show disk space usage';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = shell_exec('dir /s ' . base_path());
        } else {
            $output = shell_exec('du -sh ' . base_path());
        }

        $this->info($output);
    }
}
