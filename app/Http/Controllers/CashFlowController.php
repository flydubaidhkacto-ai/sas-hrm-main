<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $startDate = Carbon::parse($request->query('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->query('end_date', now()->endOfMonth()))->endOfDay();

        $cashIn = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('transaction_type', 'credit')
            ->sum('amount');

        $cashOut = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('transaction_type', 'debit')
            ->sum('amount');

        $netCashFlow = $cashIn - $cashOut;

        $operatingCashFlow = Transaction::where('cash_flow_category', 'operating')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $investingCashFlow = Transaction::where('cash_flow_category', 'investing')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $financingCashFlow = Transaction::where('cash_flow_category', 'financing')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        return view('accounts.cash_flow.index', compact('cashIn', 'cashOut', 'netCashFlow', 'operatingCashFlow', 'investingCashFlow', 'financingCashFlow', 'startDate', 'endDate'));
    }
}
