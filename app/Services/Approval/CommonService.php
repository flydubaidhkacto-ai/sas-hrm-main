<?php

namespace App\Services\Approval;

use App\Contracts\ApprovalModuleService;
use App\Services\Notification\NotificationService;
use App\Helpers\{StatusHelper, LeaveMailContentHelper, UserTitleHelper, DurationHelper, AttendanceMailContentHelper};
use Carbon\Carbon;
use App\Models\LeaveApplication;
use App\Models\StationLeaveApplication;
use App\Models\AttendanceRequest;
use App\Models\User;
use App\Models\ApprovalProcess;

class CommonService implements ApprovalModuleService
{
    public function onApply($model, $applicant, array $context = []): void
    {
        $step = $model->getApprovalStepsList()->sortBy('step_order')->first();

        $model->update([
            'current_approver_id' => $step->approver_id,
            'status' => 'pending',
        ]);

        $receiverName = UserTitleHelper::fullName($step->approver->name, $step->approver->gender, $step->approver->marital_status);

        $applicantEmail = $model->employee->email ?? null;

        if (
            empty($applicantEmail) ||
            !str_ends_with(strtolower($applicantEmail), '@sasflydubai.com')
        ) {
            $applicantEmail = 'test.mail1@sasflydubai.com';
        }


        $context['status'] = $model->status;
        $context['receiver'] = [
            'id' => $step->approver_id,
            'name' => $receiverName,
            'to' => [$step->approver->email],
            'cc' => [$applicantEmail],
        ];

        $context['sender'] = [
            'from_mail' => config('mail.from.address')
        ];

        if ($context['module'] === 'leave') {

            $context['url'] = route('leaves.show', $model->id);

        } else if ($context['module'] === 'station_leave') {

            $context['url'] = route('station.leave.show', $model->id);

        } else if ($context['module'] === 'attendance_request') {

            // $context['url'] = route('attendance-requests.show', $model->id);
            $context['url'] = route('attendance-requests');

        } else if ($context['module'] === 'loan') {

            $context['url'] = route('loans.show', $model->id);

        } else if ($context['module'] === 'fine') {

            $context['url'] = route('fine.show', $model->id);

        } else if ($context['module'] === 'asset_request') {

            $context['url'] = route('asset_request.show', $model->id);

        } else if ($context['module'] === 'inventory_item_request') {

            $context['url'] = route('inventory_item_request.show', $model->id);

        } else if ($context['module'] === 'emp_daily_expense') {

            $context['url'] = route('emp-expenses.show', $model->id);

        } else {
            $context['url'] = "#";
        }

        $this->send(
            event: 'apply',
            model: $model,
            actor: $applicant,
            context: $context
        );
    }

    public function onApprove($model, $approver, array $context = []): void
    {

        if ($context['module'] === 'leave') {

            $context['url'] = route('leaves.show', $model->id);

        } else if ($context['module'] === 'station_leave') {

            $context['url'] = route('station.leave.show', $model->id);

        } else if ($context['module'] === 'attendance_request') {

            // $context['url'] = route('attendance-requests.show', $model->id);
            $context['url'] = route('attendance-requests');

        } else if ($context['module'] === 'loan') {

            $context['url'] = route('loans.show', $model->id);

        } else if ($context['module'] === 'fine') {

            $context['url'] = route('fine.show', $model->id);

        } else if ($context['module'] === 'asset_request') {

            $context['url'] = route('asset_request.show', $model->id);

        } else if ($context['module'] === 'inventory_item_request') {

            $context['url'] = route('inventory_item_request.show', $model->id);

        } else if ($context['module'] === 'emp_daily_expense') {

            $context['url'] = route('emp-expenses.show', $model->id);

        }  else if ($context['module'] === 'payslip') {

            $context['url'] = "http://127.0.0.1:8000/payslip?month=02-2026";

        } else {
            $context['url'] = "#";
        }

        if ($context['is_final']) {

            $receiverName = UserTitleHelper::fullName($model->employee->name, $model->employee->gender, $model->employee->marital_status);

            $context['status'] = $model->status;

            // cc email from downLine approver and hr from config with remove duplicate from TO Mail.
            $downLineApprover = $this->getDownlineApproverEmails($model, $approver);
            $hrEmails = (array)config('hr.emails');

            $applicantEmail = $model->employee->email ?? null;

            if (
                empty($applicantEmail) ||
                !str_ends_with(strtolower($applicantEmail), '@sasflydubai.com')
            ) {
                $applicantEmail = 'test.mail1@sasflydubai.com';
            }

            $toEmails = [$applicantEmail];

            $ccEmails = collect([
                $hrEmails,
                $downLineApprover,
            ])->flatten()                    // 🔹 nested array flatten
            ->filter()                     // 🔹 null / empty remove
            ->unique()                     // 🔹 duplicate remove
            ->diff($toEmails)              // Remove cc from TO
            ->values()
                ->toArray();

            $context['receiver'] = [
                'id' => $model->employee->id,
                'name' => $receiverName,
                'to' => $toEmails,
                'cc' => $ccEmails,
            ];

            $context['sender'] = [
                'from_mail' => config('mail.from.address')
            ];

            $this->send(
                'approved',
                model: $model,
                actor: $approver,
                context: $context
            );
            return;
        }

        $receiverName = UserTitleHelper::fullName($context['next_approver']->name, $context['next_approver']->gender, $context['next_approver']->marital_status);

        $applicantEmail = $model->employee->email ?? null;

        if (
            empty($applicantEmail) ||
            !str_ends_with(strtolower($applicantEmail), '@sasflydubai.com')
        ) {
            $applicantEmail = 'test.mail1@sasflydubai.com';
        }

        $context['status'] = $model->status;
        $context['receiver'] = [
            'id' => $context['next_approver']->id,
            'name' => $receiverName,
            'to' => [$context['next_approver']->email],
            'cc' => [$applicantEmail],
        ];

        $context['sender'] = [
            'from_mail' => config('mail.from.address')
        ];


        $this->send(
            'forward',
            model: $model,
            actor: $approver,
           context: $context
        );
    }

    public function onReject($model, $approver, array $context = []): void
    {
        $receiverName = UserTitleHelper::fullName($model->employee->name, $model->employee->gender, $model->employee->marital_status);

        $context['status'] = $model->status;

        // cc email from downLine approver and hr from config with remove duplicate from TO Mail.
        $downLineApprover = $this->getDownlineApproverEmails($model, $approver);
        $hrEmails = (array)config('hr.emails');

        $applicantEmail = $model->employee->email ?? null;

        if (
            empty($applicantEmail) ||
            !str_ends_with(strtolower($applicantEmail), '@sasflydubai.com')
        ) {
            $applicantEmail = 'test.mail1@sasflydubai.com';
        }

        $toEmails = [$applicantEmail];

        $ccEmails = collect([
            $hrEmails,
            $downLineApprover,
        ])->flatten()                    // 🔹 nested array flatten
        ->filter()                     // 🔹 null / empty remove
        ->unique()                     // 🔹 duplicate remove
        ->diff($toEmails)              // Remove cc from TO
        ->values()
            ->toArray();

        $context['receiver'] = [
            'id' => $model->employee->id,
            'name' => $receiverName,
            'to' => $toEmails,
            'cc' => $ccEmails,
        ];

        $context['sender'] = [
            'from_mail' => config('mail.from.address')
        ];

        if ($context['module'] === 'leave') {

            $context['url'] = route('leaves.show', $model->id);

        } else if ($context['module'] === 'station_leave') {

            $context['url'] = route('station.leave.show', $model->id);

        } else if ($context['module'] === 'attendance_request') {

            //$context['url'] = route('attendance-requests.show', $model->id);
            $context['url'] = route('attendance-requests');

        } else if ($context['module'] === 'loan') {

            $context['url'] = route('loans.show', $model->id);

        } else if ($context['module'] === 'fine') {

            $context['url'] = route('fine.show', $model->id);

        } else if ($context['module'] === 'asset_request') {

            $context['url'] = route('asset_request.show', $model->id);

        } else if ($context['module'] === 'inventory_item_request') {

            $context['url'] = route('inventory_item_request.show', $model->id);

        } else if ($context['module'] === 'emp_daily_expense') {

            $context['url'] = route('emp-expenses.show', $model->id);

        } else if ($context['module'] === 'payslip') {

            $context['url'] = "http://127.0.0.1:8000/payslip?month=02-2026";

        } else {
            $context['url'] = "#";
        }

        $this->send(
            'rejected',
            model: $model,
           actor: $approver,
          context: $context
        );
    }

    public function onForward($model, $approver, $nextApprover, array $context = []): void
    {
    }

    public function onDelete($model, $approver, array $context = []): void
    {
        $receiverName = UserTitleHelper::fullName($model->employee->name, $model->employee->gender, $model->employee->marital_status);

        $context['status'] = "deleted";

        // cc email from downLine approver and hr from config with remove duplicate from TO Mail.
        $downLineApprover = $this->getDownlineApproverEmails($model, $approver);

        $applicantEmail = $model->employee->email ?? null;

        if (
            empty($applicantEmail) ||
            !str_ends_with(strtolower($applicantEmail), '@sasflydubai.com')
        ) {
            $applicantEmail = 'test.mail1@sasflydubai.com';
        }

        $hrEmails = (array)config('hr.emails');
        $toEmails = [$applicantEmail];

        $ccEmails = collect([
            $hrEmails,
            $downLineApprover,
        ])->flatten()                    // 🔹 nested array flatten
        ->filter()                     // 🔹 null / empty remove
        ->unique()                     // 🔹 duplicate remove
        ->diff($toEmails)              // Remove cc from TO
        ->values()
            ->toArray();

        $context['receiver'] = [
            'id' => $model->employee->id,
            'name' => $receiverName,
            'to' => $toEmails,
            'cc' => $ccEmails,
        ];

        $context['sender'] = [
            'from_mail' => config('mail.from.address')
        ];

        if ($context['module'] === 'leave') {

            $context['url'] = route('leaves.all.approve');

        } else if ($context['module'] === 'station_leave') {

            $context['url'] = route('station.leave.all.approve', $model->id);

        } else if ($context['module'] === 'attendance_request') {

            // $context['url'] = route('attendance-requests.show', $model->id);
            $context['url'] = route('attendance-requests');

        } else if ($context['module'] === 'loan') {

            $context['url'] = route('loans.show', $model->id);

        } else if ($context['module'] === 'fine') {

            $context['url'] = route('fine.show', $model->id);

        } else if ($context['module'] === 'asset_request') {

            $context['url'] = route('asset_request.show', $model->id);

        } else if ($context['module'] === 'inventory_item_request') {

            $context['url'] = route('inventory_item_request.show', $model->id);

        } else if ($context['module'] === 'emp_daily_expense') {

            $context['url'] = route('emp-expenses.show', $model->id);

        }else if ($context['module'] === 'payslip') {

            $context['url'] = "http://127.0.0.1:8000/payslip?month=02-2026";

        } else {
            $context['url'] = "#";
        }

        $this->send(
            'deleted',
            model: $model,
           actor: $approver,
           context: $context
        );
    }

    public function onDateUpdated($model, $admin, array $context = []): void
    {

        $receiverName = UserTitleHelper::fullName($model->employee->name, $model->employee->gender, $model->employee->marital_status);
        $context['status'] = $model->status;

        // cc email from downLine approver and hr from config with remove duplicate from TO Mail.
        $downLineApprover = $this->getDownlineApproverEmails($model, $admin);
        $hrEmails = (array)config('hr.emails');

        $applicantEmail = $model->employee->email ?? null;

        if (
            empty($applicantEmail) ||
            !str_ends_with(strtolower($applicantEmail), '@sasflydubai.com')
        ) {
            $applicantEmail = 'test.mail1@sasflydubai.com';
        }

        $toEmails = [$applicantEmail];

        $ccEmails = collect([
            $hrEmails,
            $downLineApprover,
        ])->flatten()                    // 🔹 nested array flatten
        ->filter()                     // 🔹 null / empty remove
        ->unique()                     // 🔹 duplicate remove
        ->diff($toEmails)              // Remove cc from TO
        ->values()
            ->toArray();


        $context['receiver'] = [
            'id' => $model->employee->id,
            'name' => $receiverName,
            'to' => $toEmails,
            'cc' => $ccEmails,
        ];

        $context['sender'] = [
            'from_mail' => config('mail.from.address')
        ];

        $context['url'] = route('leaves.show', $model->id);

        if ($context['module'] === 'leave') {

            $context['url'] = route('leaves.show', $model->id);

        } else if ($context['module'] === 'station_leave') {

            $context['url'] = route('station.leave.show', $model->id);

        } else if ($context['module'] === 'attendance_request') {

            //  $context['url'] = route('attendance-requests.show', $model->id);
            $context['url'] = route('attendance-requests');

        } else if ($context['module'] === 'loan') {

            $context['url'] = route('loans.show', $model->id);

        } else if ($context['module'] === 'fine') {

            $context['url'] = route('fine.show', $model->id);

        } else if ($context['module'] === 'asset_request') {

            $context['url'] = route('asset_request.show', $model->id);

        } else if ($context['module'] === 'inventory_item_request') {

            $context['url'] = route('inventory_item_request.show', $model->id);

        } else if ($context['module'] === 'emp_daily_expense') {

            $context['url'] = route('emp-expenses.show', $model->id);

        }else if ($context['module'] === 'payslip') {

            $context['url'] = "http://127.0.0.1:8000/payslip?month=02-2026";

        } else {
            $context['url'] = "#";
        }

        $this->send(
            event: 'date_updated',
        model: $model,
        actor: $admin,
        context: $context
    );
}

    public function onQuery($dataQuery, $actor, array $context = []): void
    {

        $module = $dataQuery->query_for_model;
        $moduleID = $dataQuery->query_for_model_id;
        $query = $dataQuery->query;
        $queryAnswer = $dataQuery->query_response_description;

        $fromID = $dataQuery->requestedBy->requested_by;
        $fromName = $dataQuery->requestedBy->name;
        $fromGender = $dataQuery->requestedBy->gender;
        $fromMaritalStatus = $dataQuery->requestedBy->marital_status;
        $fromEmail = $dataQuery->requestedBy->email;

        $toID = $dataQuery->requestedTo->requested_to;
        $toName = $dataQuery->requestedTo->name;
        $toGender = $dataQuery->requestedTo->gender;
        $toMaritalStatus = $dataQuery->requestedTo->marital_status;
        $toEmail = $dataQuery->requestedTo->email;


        $senderName = UserTitleHelper::fullName($fromName, $fromGender, $fromMaritalStatus);
        $receiverName = UserTitleHelper::fullName($toName, $toGender, $toMaritalStatus);


        $toEmail = $toEmail ?? null;

        if (
            empty($toEmail) ||
            !str_ends_with(strtolower($toEmail), '@sasflydubai.com')
        ) {
            $toEmail = 'test.mail1@sasflydubai.com';
        }

        if ($module === 'leave') {

            $model = LeaveApplication::find($moduleID);

            $context['url'] = route('leaves.show', $moduleID);

        } else if ($module === 'station_leave') {

            $model = StationLeaveApplication::find($moduleID);

            $context['url'] = route('station.leave.show', $moduleID);

        } else if ($module === 'attendance_request') {

            $model = AttendanceRequest::find($moduleID);

            $context['url'] = route('attendance-requests');

        } else if ($module === 'loan') {

            $model = "";

            $context['url'] = route('loans.show', $moduleID);

        } else if ($module === 'fine') {

            $model = "";
            $context['url'] = route('fine.show', $moduleID);

        } else if ($module === 'asset_request') {

            $model = "";
            $context['url'] = route('asset_request.show', $moduleID);

        } else if ($module === 'inventory_item_request') {

            $model = "";
            $context['url'] = route('inventory_item_request.show', $moduleID);

        } else if ($module === 'emp_daily_expense') {

            $model = "";
            $context['url'] = route('emp-expenses.show', $moduleID);

        } else if ($context['module'] === 'payslip') {

            $context['url'] = "http://127.0.0.1:8000/payslip?month=02-2026";

        } else {
            $model = "";
            $context['url'] = "#";
        }

        $context['status'] = $model->status;
        $context['module'] = $module;
        $context['query'] = $query;
        $context['queryAnswer'] = $queryAnswer;

        $context['receiver'] = [
            'id' => $toID,
            'name' => $receiverName,
            'to' => [$toEmail],
            'cc' => [],
        ];

        $context['sender'] = [

            'from_mail' => $fromEmail,
            'from_name' => $senderName,
        ];

        $eventName = "onQuery";


        $this->send(
            event: $eventName,
            model: $model,
            actor: $actor,
            context: $context
        );
    }

    public function onQueryAnswer($dataQuery, $actor, array $context = []): void
    {
        $module = $dataQuery->query_for_model;
        $moduleID = $dataQuery->query_for_model_id;
        $query = $dataQuery->query;
        $queryAnswer = $dataQuery->query_response_description;

        $fromID = $dataQuery->requestedTo->requested_to;
        $fromName = $dataQuery->requestedTo->name;
        $fromGender = $dataQuery->requestedTo->gender;
        $fromMaritalStatus = $dataQuery->requestedTo->marital_status;
        $fromEmail = $dataQuery->requestedTo->email;


        $toID = $dataQuery->requestedBy->requested_by;
        $toName = $dataQuery->requestedBy->name;
        $toGender = $dataQuery->requestedBy->gender;
        $toMaritalStatus = $dataQuery->requestedBy->marital_status;
        $toEmail = $dataQuery->requestedBy->email;


        $senderName = UserTitleHelper::fullName($fromName, $fromGender, $fromMaritalStatus);
        $receiverName = UserTitleHelper::fullName($toName, $toGender, $toMaritalStatus);


        $toEmail = $toEmail ?? null;

        if (
            empty($toEmail) ||
            !str_ends_with(strtolower($toEmail), '@sasflydubai.com')
        ) {
            $toEmail = 'test.mail1@sasflydubai.com';
        }

        if ($module === 'leave') {

            $model = LeaveApplication::find($moduleID);

            $context['url'] = route('leaves.show', $moduleID);

        } else if ($module === 'station_leave') {

            $model = StationLeaveApplication::find($moduleID);

            $context['url'] = route('station.leave.show', $moduleID);

        } else if ($module === 'attendance_request') {

            $model = AttendanceRequest::find($moduleID);

            $context['url'] = route('attendance-requests');

        } else if ($module === 'loan') {

            $model = "";

            $context['url'] = route('loans.show', $moduleID);

        } else if ($module === 'fine') {

            $model = "";
            $context['url'] = route('fine.show', $moduleID);

        } else if ($module === 'asset_request') {

            $model = "";
            $context['url'] = route('asset_request.show', $moduleID);

        } else if ($module === 'inventory_item_request') {

            $model = "";
            $context['url'] = route('inventory_item_request.show', $moduleID);

        } else if ($module === 'emp_daily_expense') {

            $model = "";
            $context['url'] = route('emp-expenses.show', $moduleID);

        }else if ($context['module'] === 'payslip') {

            $context['url'] = "http://127.0.0.1:8000/payslip?month=02-2026";

        } else {
            $model = "";
            $context['url'] = "#";
        }

        $context['status'] = $model->status;
        $context['module'] = $module;
        $context['query'] = $query;
        $context['queryAnswer'] = $queryAnswer;

        $context['receiver'] = [
            'id' => $toID,
            'name' => $receiverName,
            'to' => [$toEmail],
            'cc' => [],
        ];

        $context['sender'] = [

            'from_mail' => $fromEmail,
            'from_name' => $senderName,
        ];

        $eventName = "onQueryAnswer";


        $this->send(
            event: $eventName,
            model: $model,
            actor: $actor,
            context: $context
        );
    }

    protected function send(
        string $event,
        $model,
        $actor,
        array $context = []
    ): void
    {

        $status = $context['status'];
        $receiverId = $context['receiver']['id'];
        $receiverName = $context['receiver']['name'];
        $from = $context['sender']['from_mail'];
        $to = $context['receiver']['to'] ?? [];
        $cc = $context['receiver']['cc'] ?? [];
        $url = $context['url'] ?? '#';
        $module = $context['module'] ?? '';
        $query = $context['query'] ?? '';
        $queryAnswer = $context['queryAnswer'] ?? '';

        $fromName = $context['sender']['from_name'] ?? config('mail.from.name');


        $mailStatus = StatusHelper::mail($status);

        //dd($mailStatus);

        // Format dates
        $appliedAt = Carbon::parse($model->created_at)->format('d F, Y h:i A');
        $actionTime = now()->format('d F, Y h:i A');

        // Format employee and approver names with titles
        $applicantName = UserTitleHelper::fullName(
            $model->employee->name,
            $model->employee->gender,
            $model->employee->marital_status
        );
        $actorName = UserTitleHelper::fullName(
            $actor->name,
            $actor->gender,
            $actor->marital_status
        );

        $template = 'email.system';


        if ($module === 'leave') {

            $totalLeave = $model->duration_count;


            if ($model->is_hourly) {

                $duration = DurationHelper::calculate(
                    $model->is_hourly,
                    $model->start_date,
                    $model->end_date,
                    $model->start_time,
                    $model->end_time
                );

                // $totalLeaveText = $totalLeave . ' Hour' . ($totalLeave > 1 ? 's' : '');

                $totalLeaveText = $duration['label'];

                $fromDate = Carbon::parse($model->start_date)
                    ->setTimeFromTimeString($model->start_time)
                    ->format('d F, Y h:i A');

                $toDate = Carbon::parse($model->end_date)
                    ->setTimeFromTimeString($model->end_time)
                    ->format('d F, Y h:i A');

                $oldStartDate = isset($context['old_dates']['start_date'])
                    ? Carbon::parse($context['old_dates']['start_date'])->format('d F, Y h:i A')
                    : null;

                $oldEndDate = isset($context['old_dates']['end_date'])
                    ? Carbon::parse($context['old_dates']['end_date'])->format('d F, Y h:i A')
                    : null;


            } else {

                $totalLeaveText = $totalLeave . ' Day' . ($totalLeave > 1 ? 's' : '');

                $fromDate = Carbon::parse($model->start_date)->format('d F, Y');
                $toDate = Carbon::parse($model->end_date)->format('d F, Y');
                $oldStartDate = isset($context['old_dates']['start_date'])
                    ? Carbon::parse($context['old_dates']['start_date'])->format('d F, Y')
                    : null;

                $oldEndDate = isset($context['old_dates']['end_date'])
                    ? Carbon::parse($context['old_dates']['end_date'])->format('d F, Y')
                    : null;

            }

            // Build full meta array
            $metaData = array_merge($mailStatus, [
                'applicant_name' => $applicantName,
                'receiver_name' => $receiverName,
                'from_name' => $fromName,
                'leave_type' => $model->type,
                'leave_reason' => $model->title,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'applied_at' => $appliedAt,
                'actor_name' => $actorName,
                'action_time' => $actionTime,
                'is_hourly' => $model->is_hourly,
                'total_leave' => $totalLeaveText,
                'template' => $template,
                'url' => $url,
                'sender_id' => $actor->id,
                'receiver_id' => $receiverId,
                'module_id' => $model->id,
                'status' => $status,
                'query' => $query,
                'queryAnswer' => $queryAnswer,
                'old_start_date' => $oldStartDate,
                'old_end_date' => $oldEndDate,
            ]);

            $subjectContent = LeaveMailContentHelper::subject($event, $metaData);
            $bodyContent = LeaveMailContentHelper::body($event, $metaData);

        } else if ($module === 'station_leave') {

            $totalLeave = $model->duration_count;
            $totalLeaveText = $totalLeave . ' Day' . ($totalLeave > 1 ? 's' : '');

            $fromDate = Carbon::parse($model->start_date)->format('d F, Y');
            $toDate = Carbon::parse($model->end_date)->format('d F, Y');
            $oldStartDate = isset($context['old_dates']['start_date'])
                ? Carbon::parse($context['old_dates']['start_date'])->format('d F, Y')
                : null;

            $oldEndDate = isset($context['old_dates']['end_date'])
                ? Carbon::parse($context['old_dates']['end_date'])->format('d F, Y')
                : null;

            // Build full meta array
            $metaData = array_merge($mailStatus, [
                'applicant_name' => $applicantName,
                'receiver_name' => $receiverName,
                'from_name' => $fromName,
                'leave_type' => 'Station Leave',
                'leave_reason' => $model->reason,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'applied_at' => $appliedAt,
                'actor_name' => $actorName,
                'action_time' => $actionTime,
                'is_hourly' => false,
                'total_leave' => $totalLeaveText,
                'template' => $template,
                'url' => $url,
                'sender_id' => $actor->id,
                'receiver_id' => $receiverId,
                'module_id' => $model->id,
                'status' => $status,
                'query' => $query,
                'queryAnswer' => $queryAnswer,
                'old_start_date' => $oldStartDate,
                'old_end_date' => $oldEndDate,
            ]);

            $subjectContent = LeaveMailContentHelper::subject($event, $metaData);
            $bodyContent = LeaveMailContentHelper::body($event, $metaData);

        } else if ($module === 'attendance_request') {

            $punchIn = isset($model->punch_in)
                ? Carbon::parse($model->punch_in)->format('d F, Y h:i A')
                : null;

            $punchOut = isset($model->punch_out)
                ? Carbon::parse($model->punch_out)->format('d F, Y h:i A')
                : null;

            if ($model->punch_in && $model->punch_out) {
                $requestType = 'IN-OUT';
            } elseif ($model->punch_in) {
                $requestType = 'IN';
            } elseif ($model->punch_out) {
                $requestType = 'OUT';
            } else {
                $requestType = '-';
            }


            // Build full meta array
            $metaData = array_merge($mailStatus, [
                'applicant_name' => $applicantName,
                'receiver_name' => $receiverName,
                'from_name' => $fromName,
                'employee_id' => $model->user_id,
                'date' => $model->date,
                'request_type' => $requestType,
                'punch_in' => $punchIn,
                'punch_out' => $punchOut,
                'notes' => $model->notes,
                'applied_at' => $appliedAt,
                'actor_name' => $actorName,
                'action_time' => $actionTime,
                'template' => $template,
                'url' => $url,
                'sender_id' => $actor->id,
                'receiver_id' => $receiverId,
                'module_id' => $model->id,
                'status' => $status,
                'query' => $query,
                'queryAnswer' => $queryAnswer,
            ]);

            $subjectContent = AttendanceMailContentHelper::subject($event, $metaData);
            $bodyContent = AttendanceMailContentHelper::body($event, $metaData);


        } else {

//            dd($module);
//
//            dd('others module for test');

            $subjectContent="";
            $bodyContent="";
            $metaData="";
        }

        // Send notification & email
        app(NotificationService::class)->notify([
            'notification' => [
                'title' => $subjectContent,
                'message' => $bodyContent,
                'user_id' => $receiverId,
                'url' => $url,
            ],
            'mail' => [
                'from_email' => $from,
                'to_email' => $to,
                'cc_email' => $cc,
                'subject' => $subjectContent,
                'module' => $module,
                'body' => $bodyContent,
                'status' => $mailStatus,
                'meta' => $metaData,
            ]
        ]);
    }

    protected function getAllApproverEmails($model): array
    {
        return $model->getApprovalStepsList()
            ->pluck('approver.email')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    protected function getDownlineApproverEmails($model, $currentApprover): array
    {
        $steps = $model->getApprovalStepsList();

        // current approver step
        $currentStep = $steps
            ->firstWhere('approver_id', $currentApprover->id);

        if (!$currentStep) {
            return [];
        }

        return $steps
            ->where('step_order', '<', $currentStep->step_order)
            ->pluck('approver.email')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }


    /**
     * 🎯 MAIN METHOD (use only this)
     */
    public static function employeeOptions(string $module, int $applicantId): array
    {
        // approver step map
        $approverMap = self::getApproverStepMap($module, $applicantId);

        // active employees
        return User::active_employees()
            ->with('department')
            ->get()
            ->map(function ($emp) use ($approverMap, $applicantId) {

                // Applicant
                if ($emp->id == $applicantId) {
                    $label = "{$emp->name} (Applicant)";
                } // Approver (dynamic step)
                elseif (isset($approverMap[$emp->id])) {
                    $order = $approverMap[$emp->id];
                    $label = "{$emp->name} (" . self::stepLabel($order) . ")";
                } //  Department fallback
                else {
                    $dept = $emp->department ?->name ?? 'No Department';
                    $supDept = $emp->department ?->super_department ?->name ?? 'No Super Department';
                    $label = "{$emp->name} ({$supDept} - {$dept})";
                }

                return [
                    'id' => $emp->id,
                    'label' => $label,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * 🔥 Build approver step map dynamically
     */
    protected static function getApproverStepMap(string $module, int $applicantId): array
    {
        $process = ApprovalProcess::query()
            ->where('type', $module)
            ->get()
            ->first(function ($process) use ($applicantId) {
                return empty($process->for_users)
                    || in_array($applicantId, $process->for_users)
                    || in_array((string)$applicantId, $process->for_users);
            });

        if (!$process) {
            return [];
        }

        return $process->steps()
            ->orderBy('step_order')
            ->get()
            ->mapWithKeys(fn($step) => [
                $step->approver_id => $step->step_order
            ])
            ->toArray();
    }

    /**
     * 🧠 Human readable step label
     */
    protected static function stepLabel(int $order): string
    {
        return match($order){

            1 => '1st Approver',
            2 => '2nd Approver',
            3 => '3rd Approver',
            4 => '4th Approver',
            5 => '5th Approver',
            6 => '5th Approver',
            7 => '5th Approver',

            default => $order . 'th Approver',
        };
    }


}
