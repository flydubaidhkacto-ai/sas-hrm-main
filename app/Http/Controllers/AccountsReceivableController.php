<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
class AccountsReceivableController extends Controller
{
    public function index()
    {
        abort_if(!role_can('admin-accounts'), 403);
        $receivables = Transaction::where('transaction_category', 'sale')
            // ->where('transaction_status', 'pending')
            ->orderBy('transaction_date', 'desc')
            ->get();

        return view('accounts.receivable.index', compact('receivables'));
    }

    public function make_received($id)
    {
        $transaction = Transaction::find($id);
        $transaction->transaction_status = 'approved';
        $transaction->save();

        Toastr::success('Transaction marked as received successfully.');
        return redirect()->back();
    }
}
