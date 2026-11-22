<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\ChartOfAccount;
use Carbon\Carbon;

class ProfitLossController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!role_can('admin-accounts'), 403);

        $startDate = Carbon::parse($request->query('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->query('end_date' , now()->endOfMonth()))->endOfDay();

        $totalRevenue = Transaction::where('transaction_category', 'sale')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $totalExpenses = Transaction::where('transaction_category', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $profitOrLoss = $totalRevenue - $totalExpenses;

        return view('accounts.profit_loss.index', compact('totalRevenue', 'totalExpenses', 'profitOrLoss', 'startDate', 'endDate'));
    }
}
