<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fine;
use App\Models\User;
use Carbon\Carbon;
use App\Classes\ApiResponseClass;
use App\Models\ApprovalProcess;
use Illuminate\Support\Facades\Log;

class FineController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->auth_user;
        $data = Fine::query();

        // filter limitations according to access
        $canApproveEmps = ApprovalProcess::canApproveEmployees('fine', $user);
        if(
            role_can('fines', $user)
        )
        {
            // if()
        }else
        if($canApproveEmps && $canApproveEmps['employees'])
        {
            $data->whereIn('user_id', [...$canApproveEmps['employees']->pluck('id'), $user->id]);
        }
        else {
            $data->where('user_id', $user->id);
        }

        if ($request->employee_id) {
            $data->where('user_id', $request->employee_id);
        }

        $finesData = $data->orderBy('id', 'desc')
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->with('employee')
            ->get();

        $fines = [];
        foreach ($finesData as $item) {
            $item->paid_amount = $item->paid_amount_till_now->sum('amount') ?? 0;
            $fines[] = $item->paid_amount;
        }

        return ApiResponseClass::sendResponse(
            [
                'data' => $finesData,
                'paid_amount' => $fines
            ],
            'Fines Fetched Successfully'
        );
    }

    public function store(Request $request)
    {
        $user = $request->auth_user;
        abort_if(!role_can('fines', $user), 403);
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:99999999999',
            'reason' => 'required|string',
            'cut_off_as' => 'in:once,monthly',
            'cut_off_amount' => 'nullable',
            'employee_id' => 'required|exists:users,id',
            'date' => 'required|date',
        ]);

        $data['user_id'] = $data['employee_id'];
        unset($data['employee_id']);
        $data['added_by_user_id'] = $user->id;

        if ($data['cut_off_as'] == 'once') {
            $data['cut_off_amount'] = null;
        }

        if ($data['cut_off_as'] == 'monthly') {
            $data['cut_off_amount'] = $data['amount'];
        }

        if ($user->is_super_admin() && role_can('fines', $user)) {
            $data['status'] = 'approved';
        }
        $data['created_at'] = Carbon::parse($data['date'])->format('Y-m-d');
        unset($data['date']);


        $fine = Fine::create($data);

        if ($user->is_super_admin() && role_can('fines', $user)) {
            $fine->approvedAll();
        }

        return ApiResponseClass::sendResponse(
            $fine,
            'Fine Created Successfully'
        );
    }

    public function show($id, Request $request)
    {
        $user = $request->auth_user;
        $fine = Fine::findOrFail($id);
        $fine->load('employee', 'added_by');
        $fineData = $fine->toArray();
        $fineData['approvalSteps'] = $fine->getApprovalStepsApi($user->id) ?: [];
        return ApiResponseClass::sendResponse($fineData, 'Fine Fetched Successfully');
    }
}
