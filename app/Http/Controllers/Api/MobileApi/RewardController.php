<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Http\Controllers\Controller;
use App\Models\EmployeeReward;
use App\Classes\ApiResponseClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->auth_user;

        $data = EmployeeReward::query();

        if ($request->employee_id) {
            $data->where('user_id', $request->employee_id);
        }

        $data->whereBetween('date', [$request->start_date, $request->end_date]);

        return ApiResponseClass::sendResponse(
            $data->orderBy('id', 'desc')
                ->with('employee')
                ->get(),
            'Employee Rewards Fetched Successfully'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'name' => 'required|max:255',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
        ]);

        $user = $request->auth_user;

        $data = EmployeeReward::create([
            'user_id' => $request->employee_id,
            'name' => $request->name,
            'date' => $request->date,
            'amount' => $request->amount,
            'added_by' => $user->id
        ]);

        return ApiResponseClass::sendResponse(
            $data,
            'Reward Created Successfully'
        );
    }

    public function destroy($id)
    {
        // $user = $request->auth_user;

        $data = EmployeeReward::find($id);

        $data->delete();
        return ApiResponseClass::sendResponse(
            [
                'success' => true,
                'message' => 'Reward deleted successfully'
            ],
            200
        );

    }
}
