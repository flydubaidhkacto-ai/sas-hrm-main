<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class SmartCache
{
    /**
     * Smart cache wrapper:
     * - Local → file cache
     * - Production → redis cache
     */
    public static function remember(string $key, int $seconds, callable $callback)
    {
        // LOCAL ENV → file cache (no redis needed)
        if (app()->environment('local')) {
            return Cache::remember($key, $seconds, $callback);
        }

        // PRODUCTION → redis cache
        return Cache::store('redis')->remember($key, $seconds, $callback);
    }

    /**
     * Forget cache safely (works both env)
     */
    public static function forget(string $key)
    {
        if (app()->environment('local')) {
            return Cache::forget($key);
        }

        return Cache::store('redis')->forget($key);
    }

    /**
     * Flush all (use carefully)
     */
    public static function flush()
    {
        if (app()->environment('local')) {
            return Cache::flush();
        }

        return Cache::store('redis')->flush();
    }
}