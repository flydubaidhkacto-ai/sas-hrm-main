<?php

namespace App\Services\Approval;

use App\Contracts\ApprovalModuleService;

class ApprovalServiceFactory
{
    public static function resolve(string $module): ApprovalModuleService
    {
        return match($module){
        'leave' => app(LeaveService::class),
        'station_leave' => app(StationLeaveService::class),
        'loan' => app(LoanService::class),
        'fine' => app(FineService::class),
        'common' => app(CommonService::class),
		'payslip' =>  app(PayslipService::class),
         default => throw new \Exception("Unsupported module: {$module}")
        };
    }
}
