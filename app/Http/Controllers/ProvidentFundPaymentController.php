<?php

namespace App\Http\Controllers;

use App\Models\ProvidentFundPayment;
use App\Models\ProvidentFundPaymentRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class ProvidentFundPaymentController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!role_can('provident_fund_payment'), 403);

        return view('provident-fund-payment.index', [
            'pf_payments' => ProvidentFundPaymentRecord::query()
                ->with(['user.department.super_department', 'user.designation', 'paidBy'])
                ->latest('paid_at')
                ->limit(2000)
                ->when($request->super_department, function ($query) use ($request) {
                    return $query->whereHas('user', function ($query) use ($request) {
                        return $query->whereHas('department', function ($query) use ($request) {
                            return $query->where('super_department_id', $request->super_department);
                        });
                    });
                })
                ->when($request->department, function ($query) use ($request) {
                    return $query->whereHas('user', function ($query) use ($request) {
                        return $query->where('department_id', $request->department);
                    });
                })
                ->when($request->designation, function ($query) use ($request) {
                    return $query->whereHas('user', function ($query) use ($request) {
                        return $query->where('designation_id', $request->designation);
                    });
                })
                ->when($request->employee, function ($query) use ($request) {
                    return $query->where('user_id', $request->employee);
                })
                ->when($request->date_from, function ($query) use ($request) {
                    return $query->whereDate('paid_at', '>=', $request->date_from);
                })
                ->when($request->date_to, function ($query) use ($request) {
                    return $query->whereDate('paid_at', '<=', $request->date_to);
                })
                ->get(),
        ]);
    }

    public function pay(Request $request)
    {
        abort_if(!role_can('provident_fund_payment'), 403);

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'user_id' => 'required|exists:users,id',
            'note' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($request->user_id);

        // Get the latest provident fund balance for this user
        $latestPfRecord = ProvidentFundPayment::where('user_id', $user->id)
            ->orderBy('month_date', 'desc')
            ->first();

        if (!$latestPfRecord) {
            return response()->json([
                'message' => 'No provident fund records found for this employee.',
            ], 422);
        }

        // Calculate current available balance (total - previous payments)
        $totalPaidAmount = ProvidentFundPaymentRecord::where('user_id', $user->id)
            ->sum('paid_amount');

        $currentAvailableBalance = $latestPfRecord->balance_with_interest - $totalPaidAmount;

        if ($request->amount > $currentAvailableBalance) {
            return response()->json([
                'message' => 'Payment amount cannot exceed available provident fund balance. Available: ' . number_format($currentAvailableBalance, 2) . ' Tk',
            ], 422);
        }

        $remainingBalance = $currentAvailableBalance - $request->amount;

        $payment = ProvidentFundPaymentRecord::create([
            'user_id' => $user->id,
            'paid_amount' => $request->amount,
            'total_balance' => $latestPfRecord->balance_with_interest,
            'remaining_balance' => $remainingBalance,
            'note' => $request->note,
            'paid_by' => user()->id,
            'paid_at' => now(),
        ]);

        return response()->json([
            'message' => 'Provident fund payment recorded successfully',
            'data' => $payment,
        ]);
    }

    public function getEmployeeBalance(Request $request)
    {
        abort_if(!role_can('provident_fund_payment'), 403);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // Get the latest provident fund balance for this user
        $latestPfRecord = ProvidentFundPayment::where('user_id', $user->id)
            ->orderBy('month_date', 'desc')
            ->first();

        if (!$latestPfRecord) {
            return response()->json([
                'message' => 'No provident fund records found for this employee.',
                'balance' => 0,
            ], 422);
        }

        // Get total payments made to this user
        $totalPaidAmount = ProvidentFundPaymentRecord::where('user_id', $user->id)
            ->sum('paid_amount');

        $currentBalance = $latestPfRecord->balance_with_interest - $totalPaidAmount;

        return response()->json([
            'balance' => $currentBalance,
            'total_fund_balance' => $latestPfRecord->balance_with_interest,
            'total_paid' => $totalPaidAmount,
            'user' => $user,
            'last_updated' => $latestPfRecord->month_date,
        ]);
    }
}
