<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use Carbon\Carbon;

class BalanceSheetController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!role_can('admin-accounts'), 403);

        $startDate = Carbon::parse($request->query('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->query('end_date', now()->endOfMonth()))->endOfDay();

        $totalAssets = ChartOfAccount::where('type', 'asset')
            ->join('transactions', 'chart_of_accounts.id', '=', 'transactions.from_account_id')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->sum('transactions.amount');

        $totalLiabilities = ChartOfAccount::where('type', 'liability')
            ->join('transactions', 'chart_of_accounts.id', '=', 'transactions.from_account_id')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->sum('transactions.amount');

        $totalEquity = ChartOfAccount::where('type', 'equity')
            ->join('transactions', 'chart_of_accounts.id', '=', 'transactions.from_account_id')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->sum('transactions.amount');

        $totalRevenue = ChartOfAccount::where('type', 'Revenue')
            ->join('transactions', 'chart_of_accounts.id', '=', 'transactions.from_account_id')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->sum('transactions.amount');

        $totalExpense = ChartOfAccount::where('type', 'Expense')
            ->join('transactions', 'chart_of_accounts.id', '=', 'transactions.from_account_id')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->sum('transactions.amount');

        $netIncome = $totalRevenue - $totalExpense;

        return view('accounts.balance_sheet.index', compact('totalAssets', 'totalLiabilities', 'totalEquity', 'totalRevenue', 'totalExpense', 'netIncome', 'startDate', 'endDate'));
    }
}
