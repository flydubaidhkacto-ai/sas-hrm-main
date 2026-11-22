<?php

use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;


if (!function_exists('set_active')) {
    function set_active($routes, $class = 'active')
    {
        foreach ((array) $routes as $route) {
            // Check if the route matches exactly
            if (Route::currentRouteName() === $route) {
                return $class;
            }
            // Check if the route starts with the given prefix (e.g., interview.*)
            if (str_ends_with($route, '.*') && str_starts_with(Route::currentRouteName(), rtrim($route, '.*'))) {
                return $class;
            }
        }
        return '';
    }
}


/** for side bar menu active */
// if(!function_exists('set_active')){
//     function set_active( $route ) {
//         if( is_array( $route ) ) {
//             return in_array(Route::currentRouteName(), $route) ? 'active' : '';
//         }
//         return Route::currentRouteName() === $route ? 'active' : '';
//     }
// }

if (! function_exists('carbon')) {
    function carbon($time = null)
    {
        if($time instanceof \Carbon\Carbon) {
            return $time;
        }
        if ($time == null) {
            return Carbon\Carbon::now();
        }
        return Carbon\Carbon::parse($time);
    }
}

if (! function_exists('get_month_list_from_date')) {
    function get_month_list_from_date(\Carbon\Carbon $start, ?\Carbon\Carbon $end = null): array
    {
        $start->setDay(1);
        $end = $end ?? now();
        $months = [];
        foreach (\Carbon\CarbonPeriod::create($start, '1 month', \Carbon\Carbon::today()) as $month) {
            $months['01-' . $month->format('m-Y')] = $month->format('F Y');
        }
        if (count($months) == 0) {
            $months['01-' . $start->format('m-Y')] = $start->format('F Y');
        }
        return $months;
    }
}

if (! function_exists('is_even')) {
    function is_even($number): bool
    {
        return $number % 2 == 0;
    }
}
/**
 * Convert time to seconds
 * @param string $time "HH:mm:ss"
 * @return int seconds
 */
if (! function_exists('time_to_seconds')) {
    function time_to_seconds($time)
    {
        list($hours, $minutes, $seconds) = explode(':', $time);
        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }
}

if (! function_exists('seconds_to_hh_mm_ss')) {
    function seconds_to_hh_mm_ss(int $totalSeconds)
    {
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $timeFormatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        return $timeFormatted;
    }
}
if (! function_exists('seconds_to_hh_mm')) {
    function seconds_to_hh_mm(int $totalSeconds)
    {
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        // $seconds = $totalSeconds % 60;
        $timeFormatted = sprintf("%02d:%02d", $hours, $minutes);
        return $timeFormatted;
    }
}

if (! function_exists("is_app_local")) {
    function is_app_local()
    {
        return config('app.env') === 'local';
    }
}

if (! function_exists("company_data")) {
    function company_data()
    {
        $info = [];
        try {
            $company = Setting::where('key', 'company')->first();
            if ($company) {
                $info = json_decode($company->value, true);
            }
        } catch (\Throwable $th) {
        }
        return $info;
    }
}
if (! function_exists("add_query_to_current_route")) {
    function add_query_to_current_route(array $queries)
    {
        $currentUrl = url()->current();
        $queryParams = request()->query();
        foreach ($queries as $key => $value) {
            $queryParams[$key] = $value;
        }
        $newUrl = $currentUrl . '?' . http_build_query($queryParams);
        return $newUrl;
    }
}

if (! function_exists("late_time_missed")) {
    function late_time_missed($shift, $worked_from)
    {
        if(!$worked_from || !$shift) return '00:00';
        $start = carbon($shift->start);
        if ($shift->is_night_shift) $start->subDay();
        return $start->diff(
            carbon(
                carbon($worked_from)->format('H:i')
            )
        )->format('%H:%I');
    }
}

if (! function_exists("role_can")) {
    function role_can(
        string $permission,
        ?\App\Models\User $user = null,
        \App\Models\SuperDepartment|int|null $target_sup_department = null,
        \App\Models\Department|int|null $target_department = null,
    ) {
        $permission = \App\Models\Role::can($permission, $user, $target_sup_department, $target_department);
        return $permission;
    }
}
if (! function_exists("has_module")) {
    // check admin of super departments)
    function is_admin_of_super_department($sup_department_id = null, $user = null)
    {
        $user = $user ?? user();
        if (!$user) return false;
        if ($sup_department_id === null)
            return count($user->admin_of_super_departments ?: []) > 0;
        return is_array($user->admin_of_super_departments)
            && in_array($sup_department_id, $user->admin_of_super_departments);
    }
}

if (! function_exists("has_module")) {
    function has_module(?string $module = null)
    {
        if($module === 'base_module') return true;
        $license = config('services');
        if($license && is_array($license) && !$module) return $license['modules'] ?? [];
        if ($license && is_array($license)) {
            $modules = $license['modules'] ?? [];
            if (in_array($module, $modules) || array_key_exists($module, $modules))
                return true;
        }
        return config('app.env') !== 'production';
    }
}


if (! function_exists("get_missing_modules")) {
    function get_missing_modules()
    {
        return [];
    }
}
/**
 * Get current user
 * @return \App\Models\User
 */
if (! function_exists("user")) {
    function user()
    {
        return auth()->user();
    }
}
if (! function_exists("generateDateRange")) {
    function generateDateRange(Carbon\Carbon $start_date, Carbon\Carbon $end_date)
    {
        $dates = [];

        for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
}

if (! function_exists("replaceSmsPlaceholders")) {
    function replaceSmsPlaceholders(string $template, array $replacements): string
    {
        $formattedTemplate = Str::of($template);
        foreach ($replacements as $placeholder => $replacement) {
            $formattedTemplate = $formattedTemplate->replace("$placeholder", $replacement);
        }
        return $formattedTemplate->__toString();
    }
}

if (! function_exists("leave_first_approval_permitted_users")) {
    function leave_first_approval_permitted_users($target_sup_department = null, $emp = null)
    {
        return \App\Models\User::query()
            ->where(function ($q) {
                $q->whereHas('assinged_role', function ($query) {
                    $query->where('permissions', 'LIKE', '%"leave.approve"%');
                });
            })
            ->orWhere(function ($q) use ($target_sup_department, $emp) {
                $q->whereHas('assinged_role', function ($query) {
                    $query->where('permissions', 'LIKE', '%"leave.approve.self.super_department"%');
                })
                    ->where(function ($query2) use ($target_sup_department, $emp) {
                        $query2->whereHas('department', function ($query) use ($target_sup_department) {
                            $query->where('super_department_id', $target_sup_department);
                        });
                        if ($emp) {
                            $query2->orWhereHas('managed_employees', function ($quer3) use ($emp) {
                                $quer3->where('extra_sup_department_employee_management.employee_id', $emp->id);
                            });
                        }
                    });
            })
            ->get();
    }
}

if (! function_exists("leave_final_approval_permitted_users")) {
    function leave_final_approval_permitted_users($target_sup_department = null, $emp = null)
    {
        return \App\Models\User::query()
            ->where(function ($q) {
                $q->whereHas('assinged_role', function ($query) {
                    $query->where('permissions', 'LIKE', '%"leave.approve.final"%');
                });
            })
            ->orWhere(function ($q) use ($target_sup_department, $emp) {
                $q->whereHas('assinged_role', function ($query) {
                    $query->where('permissions', 'LIKE', '%"leave.approve.final.self.super_department"%');
                })
                    ->where(function ($query2) use ($target_sup_department, $emp) {
                        $query2->whereHas('department', function ($query) use ($target_sup_department) {
                            $query->where('super_department_id', $target_sup_department);
                        });
                        if ($emp) {
                            $query2->orWhereHas('managed_employees', function ($quer3) use ($emp) {
                                $quer3->where('extra_sup_department_employee_management.employee_id', $emp->id);
                            });
                        }
                    });
            })
            ->get();
    }
}


if (! function_exists("isLocationWithinRadius")) {
    function isLocationWithinRadius($latitude, $longitude, $allowedLatitude, $allowedLongitude, $allowedRadius)
    {
        $earthRadius = 6371000; // Radius of the earth in meters

        // Convert degrees to radians
        $latFrom = deg2rad($latitude);
        $lonFrom = deg2rad($longitude);
        $latTo = deg2rad($allowedLatitude);
        $lonTo = deg2rad($allowedLongitude);

        // Haversine formula
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c; // Distance in meters

        return [
            'distance' => $distance, // in meters
            'result' => $distance <= $allowedRadius
        ];
    }
}


if (! function_exists("get_alphabet_from_number")) {
    function get_alphabet_from_number($number)
    {
        $letter = '';
        while ($number > 0) {
            $mod = ($number - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $number = (int)(($number - $mod) / 26);
        }
        return $letter;
    }
}

if (! function_exists("willComeFromSystem")) {
    function willComeFromSystem(string $category): bool
    {
        return in_array($category, ['rewards', 'emp_daily_expenses', 'loan', 'fine', 'employee_bill', 'overtime', 'duty_bonuses']);
    }
}

if (! function_exists("get_attendance_from_zkteco_cloud_archive")) {
    function get_attendance_from_zkteco_cloud_archive($dates = [])
    {
        foreach ($dates as $date) {
            $data = file_get_contents(public_path("iclock/logs/attendances/data-$date.log"));
            $lines = explode("\n", $data);
            foreach ($lines as $line) {
                if (trim($line) === '') {
                    continue; // Skip empty lines
                }
                $parts = explode(', ', $line);
                if (count($parts) < 3) {
                    continue; // Skip lines that don't have enough parts
                }
                $device = $parts[0];
                $user_id = $parts[1];
                $date_time = $parts[2];
                try{
                    \App\Actions\EmployeePunched\Execute::run(\App\Models\InOutRecord::create([
                        'user_id' => $user_id,
                        'date_time' => $date_time,
                        'user_id' => $user_id,
                        'status' => 0,
                        'zkteco_cloud_id' => (\App\Models\ZktecoCloud::where('device_sn', $device)->first())?->id,
                        'original_date_time' => $date_time,
                        'device_mac' => "ZKteco cloud attendance",
                        'device_uid' => $device,
                        'device_attendance_uid' => "ZKteco cloud attendance",
                    ]));
                }catch(\Exception $e) {}
            }
        }
    }
}
if (! function_exists("number_to_words")) {
    function number_to_words($number)
    {
        $words = [
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety'
        ];

        $levels = ['', 'Thousand', 'Million', 'Billion'];
        $number = (int) $number;

        if ($number === 0) {
            return 'Zero';
        }
        if ($number < 0) {
            return '(-) ' . number_to_words(abs($number));
        }

        $parts = [];
        $level = 0;

        while ($number > 0) {
            $chunk = $number % 1000; // Process 3 digits at a time
            if ($chunk > 0) {
                $chunkInWords = '';
                if ($chunk > 99) {
                    $chunkInWords .= $words[intval($chunk / 100)] . ' Hundred ';
                    $chunk %= 100;
                }
                if ($chunk > 19) {
                    $chunkInWords .= $words[intval($chunk / 10) * 10] . ' ';
                    $chunk %= 10;
                }
                $chunkInWords .= $words[$chunk];
                $parts[] = trim($chunkInWords) . ' ' . $levels[$level];
            }
            $number = intval($number / 1000);
            $level++;
        }

        return implode(' ', array_reverse($parts));
    }
}

function getRoadDistance(float $lat1, float $lng1, float $lat2, float $lng2): ?float
{
    try {
        // OSRM public demo server (note: not for heavy production use)
        $url = "https://router.project-osrm.org/route/v1/driving/{$lng1},{$lat1};{$lng2},{$lat2}?overview=false";

        $response = \Illuminate\Support\Facades\Http::timeout(4)
            ->retry(3)
            ->get($url);

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['routes'][0]['distance'])) {
                // Distance is returned in meters; convert to kilometers
                return round($data['routes'][0]['distance'] / 1000, 3);
            }
        }
    } catch (\Exception $e) {
        // Log or handle the exception as needed
        \Log::error("OSRM Distance Error: " . $e->getMessage());
    }

    return null; // return null if something goes wrong
}
function getSchemeAndHttpHost() {
    $url = request()->getSchemeAndHttpHost();

    if(config('app.env') == 'production') {
        $url = str_replace("http:", "https:", $url);
    }
    return $url;
}

function can_access_payroll_report(string $permission): bool
{
    $user = user();

    if (!$user) return false;

    // super admin bypass
    if ($user->role === 'super_admin') return true;

    // role check
    return role_can($permission);
}

function get_software_license()
{
    return true;
}
