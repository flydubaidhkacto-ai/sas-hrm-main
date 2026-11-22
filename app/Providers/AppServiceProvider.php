<?php

namespace App\Providers;

use App\Models\SuperDepartment;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->setVersion();

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFour();

        // CACHE FIX (VERY IMPORTANT)
        View::share('company_info', Cache::remember('company_info', 3600, function () {
            return company_data();
        }));

        config(['app.employee_limit' => 500]);

        config([
            'have_multi_sup_dept' => Cache::remember('multi_sup_dept', 3600, function () {
                try {
                    return SuperDepartment::count() > 1;
                } catch (\Throwable $e) {
                    return false;
                }
            })
        ]);
    }

    private function setVersion(): void
    {
        config([
            'app.version' =>
                json_decode(file_get_contents(base_path('composer.json')), true)['version'] ?? '1.0.0'
        ]);
    }
}