<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DiskUsageController extends Controller
{
    public function showDiskUsage()
    {
        // cache for 1 day
        return Cache::remember('disk_usage', 60 * 24, function () {
            // Get the path to the Laravel application directory
            $laravelPath = base_path();

            // Calculate the total disk space used by the Laravel application
            $totalSpace = $this->getDirectorySize($laravelPath);

            // Format the size for readability
            $formattedTotalSpace = $this->formatBytes($totalSpace);

            // Pass the total space to the view
            return $formattedTotalSpace;
        });
    }

    // Function to calculate the size of a directory recursively
    private function getDirectorySize($path)
    {
        $totalSize = 0;

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            $totalSize += $file->getSize();
        }

        return $totalSize;
    }

    // Function to format bytes into a human-readable format
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / (1 << (10 * $pow)), $precision) . ' ' . $units[$pow];
    }

    public function showDatabaseUsage()
    {
        // cache for 1 day
        return Cache::remember('database_usage', 60 * 24, function () {
            // Get the database connection name from your .env file
            $connection = config('database.default');

            // Get the name of the database
            $database = config("database.connections.$connection.database");

            // Query to retrieve the size of each table in the database
            $tableSizes = DB::select("
                SELECT
                    table_name AS `Table`,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS `Size_in_MB`
                FROM information_schema.TABLES
                    WHERE table_schema = '$database'
            ");

            // Calculate the total size of the database
            $totalSize = collect($tableSizes)->sum('Size_in_MB');

            // Pass the table sizes and total size to the view
            return $totalSize;
        });
    }

}
