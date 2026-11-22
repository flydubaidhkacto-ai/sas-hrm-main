<?php

namespace App\Repositories\Interfaces;

interface AttendanceRepositoryInterface extends BaseRepositoryInterface
{
    // Define your methods here

    public function outSideEntry($request, $user): array|object|null;
    public function outSideOut($request, $user): array|object|null;
}
