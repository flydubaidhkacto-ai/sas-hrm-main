<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bill;
use Carbon\Carbon;

class AccountPayableController extends Controller
{
    public function index()
    {
        abort_if(!role_can('admin-accounts'), 403);
        $payables = Bill::where('status', 'unpaid')
                        ->orWhere('status', 'partially_paid')
                        ->with(['vendor', 'transactions'])
                        ->get();

        $totalPayable = $payables->sum('amount');

        $aging = [
            '0-30' => $payables->where('due_date', '>=', now()->subDays(30))->sum('amount'),
            '31-60' => $payables->whereBetween('due_date', [now()->subDays(60), now()->subDays(31)])->sum('amount'),
            '61-90' => $payables->whereBetween('due_date', [now()->subDays(90), now()->subDays(61)])->sum('amount'),
            '90+' => $payables->where('due_date', '<', now()->subDays(90))->sum('amount')
        ];

        return view('accounts.payable.index', compact('payables', 'totalPayable', 'aging'));
    }
}
