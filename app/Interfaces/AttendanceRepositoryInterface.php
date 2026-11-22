<?php

namespace App\Interfaces;

interface AttendanceRepositoryInterface
{

    public function all();
    public function outSideEntry($request, $user): array|object|null;
    public function outSideOut($request, $user): array|object|null;
}
