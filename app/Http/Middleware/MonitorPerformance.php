<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MonitorPerformance
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        // Proceed with the request
        $response = $next($request);

        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2); // in ms

        // Collect metrics
        $data = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status' => $response->status(),
            'execution_time_ms' => $executionTime,
            'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'cpu_usage_percent' => $this->getCpuUsage(),
        ];

        // Log or store the metrics
        $fp = fopen(public_path('stats/performance-' . date('Y-m-d') . '.json'), 'a');
        fwrite($fp, "\n" . json_encode($data));
        fclose($fp);

        return $response;
    }

    private function getCpuUsage()
    {
        // Get CPU usage percentage (Linux-only example)
        if (PHP_OS_FAMILY === 'Linux') {
            $load = sys_getloadavg();
            return $load[0] * 100; // 1-minute average
        }

        return null; // Add implementation for other OS if needed
    }
}
