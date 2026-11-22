<?php

namespace App\Actions;

use App\Models\ApprovalProcessStep;
use App\Models\ApprovalRecord;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\StationLeaveApplication;
use Illuminate\Support\Facades\DB;
use App\Services\Approval\ApprovalServiceFactory;

class ApproveModal
{
    public static function execute($modal, $step, $user, $request, $module)
    {
        return DB::transaction(function () use ($modal, $step, $user, $request, $module) {

            /* ==========================================================
             | 🔁 UPDATE DATES ONLY (NO APPROVAL FLOW)
             |==========================================================*/
            if ($request->update_dates) {

                abort_if(
                    !$user->is_super_admin(),
                    403,
                    'You are not authorized to update dates'
                );

                if (in_array($module, ['leave', 'station_leave'])) {

                    $startDate = carbon($request->start_date ?? $modal->start_date);
                    $endDate = carbon($request->end_date ?? $modal->end_date);

                    if ($startDate->year !== $endDate->year) {
                        throw new \Exception('Start and end date must be in same year');
                    }

                    /* ---------- LEAVE VALIDATION ---------- */
                    if ($module === 'leave') {

                        $employee = $modal->employee;
                        $isHourly = $modal->is_hourly;

                        // Attendance conflict
                        $attendanceExists = Attendance::where('employee_id', $employee->id)
                            ->whereBetween('date', [
                                $startDate->copy()->startOfDay(),
                                $endDate->copy()->endOfDay()
                            ])
                            ->exists();

                        if ($attendanceExists && !$isHourly) {
                            throw new \Exception('Employee is present on selected date range');
                        }

                        // Overlapping approved leave
                        $overlap = LeaveApplication::where('employee_id', $employee->id)
                            ->where('status', 'approved')
                            ->where('id', '!=', $modal->id)
                            ->where(function ($q) use ($startDate, $endDate) {
                                $q->whereBetween('start_date', [$startDate, $endDate])
                                    ->orWhereBetween('end_date', [$startDate, $endDate])
                                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                                        $q2->where('start_date', '<=', $startDate)
                                            ->where('end_date', '>=', $endDate);
                                    });
                            })
                            ->exists();

                        if ($overlap) {
                            throw new \Exception('Approved leave already exists in this date range');
                        }
                    }

                    /* ---------- STATION LEAVE VALIDATION ---------- */
                    if ($module === 'station_leave') {

                        $employee = $modal->employee;

                        $exists = StationLeaveApplication::where('employee_id', $employee->id)
                            ->where('status', 'approved')
                            ->where('id', '!=', $modal->id)
                            ->where(function ($q) use ($startDate, $endDate) {
                                $q->whereBetween('start_date', [$startDate, $endDate])
                                    ->orWhereBetween('end_date', [$startDate, $endDate]);
                            })
                            ->exists();

                        if ($exists) {
                            throw new \Exception('Station Leave already approved in this date range');
                        }
                    }


                    $oldStartDate = $modal->start_date;
                    $oldEndDate = $modal->end_date;

                    /* ---------- UPDATE DATES ---------- */
                    $modal->update([
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                    ]);

                    if (method_exists($modal, 'updateDuration')) {
                        $modal->updateDuration();
                    }

                    /* 🔔 SEND MAIL / NOTIFICATION VIA SERVICE */
                    $service = ApprovalServiceFactory::resolve('common');

                    $service->onDateUpdated($modal, $user, [
                        'old_dates' => [
                            'start_date' => $oldStartDate,
                            'end_date' => $oldEndDate,
                        ],
                        'module' => $module
                    ]);

                    return [
                        'moduleText' => ucfirst(str_replace('_', ' ', $module)),
                        'status' => 'updated',
                    ];
                }
            }

            /* ==========================================================
             | ✅ NORMAL APPROVAL FLOW
             |==========================================================*/
            $isApproved = (bool)$request->approve;

            /** -----------------------------
             * 1️⃣ SAVE APPROVAL RECORD
             * ----------------------------- */
            ApprovalRecord::create([
                "{$module}_id" => $modal->id,
                'approval_step_id' => $step->id,
                'status' => $isApproved ? 'approved' : 'rejected',
                'note' => $request->note,
            ]);

            $service = ApprovalServiceFactory::resolve('common');

            /** -----------------------------
             * 2️⃣ REJECT FLOW
             * ----------------------------- */
            if (!$isApproved) {

                $modal->update(['status' => 'rejected']);

                if (method_exists($modal, 'rejected_event')) {
                    $modal->rejected_event($user);
                }

                $service->onReject($modal, $user,
                    [
                        'module' => $module
                    ]);

                return [
                    'moduleText' => ucfirst(str_replace('_', ' ', $module)),
                    'status' => 'rejected',
                ];
            }

            /** -----------------------------
             * 3️⃣ FIND NEXT STEP
             * ----------------------------- */
            $steps = $modal->getApprovalStepsList();
            $lastStepOrder = $steps ?->max('step_order');

            $isFinal = $step->step_order == $lastStepOrder;

            $nextStep = ApprovalProcessStep::query()
                ->where('approval_process_id', $step->approval_process_id)
                ->where('step_order', '>', $step->step_order)
                ->orderBy('step_order')
                ->first();

            $alreadyApprovedLater = ApprovalRecord::where("{$module}_id", $modal->id)
                ->whereHas('step', function ($q) use ($steps, $step) {
                    $q->where('approval_process_id', $step->approval_process_id)
                      ->where('step_order', '>', $step->step_order);
                })
                ->where('status', 'approved')
                ->exists();

            $trulyFinal = $isFinal || $user->is_super_admin() || $alreadyApprovedLater;

            if ($trulyFinal) {
                $modal->update([
                    'status' => 'approved',
                    'current_approver_id' => null,
                ]);

                if (method_exists($modal, 'approved_event')) {
                    $modal->approved_event($user);
                }
            } else {
                $modal->update([
                    'current_approver_id' => $nextStep ?->approver_id,
                    'status' => 'pending',
                ]);
            }

            /** -----------------------------
             * 5️⃣ CALL MODULE SERVICE
             * ----------------------------- */
            $service->onApprove($modal, $user, [
                'is_final' => $trulyFinal,
                'next_approver' => $nextStep ?->approver,
                'module' => $module
            ]);

            /** -----------------------------
             * 6️⃣ PUBLISHER STEP
             * ----------------------------- */
            if ($step->is_publisher) {
                $modal->update(['published_for_view' => true]);
            }

            return [
                'moduleText' => ucfirst(str_replace('_', ' ', $module)),
                'status' => 'approved',
            ];
        });
    }
}
