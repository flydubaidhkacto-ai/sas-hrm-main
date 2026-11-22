<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountTransfer;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;

class AccountTransferController extends Controller
{
    public function index()
    {
        abort_if(!role_can('admin-accounts'), 403);
        $transfers = AccountTransfer::with('fromAccount', 'toAccount')->get();
        return view('accounts.transfer.index', compact('transfers'));
    }

    public function create()
    {
        abort_if(!role_can('admin-accounts'), 403);
        $accounts = ChartOfAccount::all();
        return view('accounts.transfer.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $request->validate([
            'from_account_id' => 'required|exists:chart_of_accounts,id',
            'to_account_id' => 'required|exists:chart_of_accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'transfer_date' => 'nullable|date', // Optional: Allow transfer date from request
    ]);

    try {
        // Check if source account has sufficient balance
        $fromAccount = ChartOfAccount::findOrFail($request->from_account_id);
        if ($fromAccount->amount < $request->amount) {
            Toastr::error('Insufficient balance in source account.');
            return back()->withInput();
        }

        // Start database transaction
        \DB::beginTransaction();

        $transfer = new AccountTransfer();
        $transfer->from_account_id = $request->from_account_id;
        $transfer->to_account_id = $request->to_account_id;
        $transfer->amount = $request->amount;
        $transfer->description = $request->description;
        $transfer->transfer_date = $request->transfer_date ?? Carbon::now();
        $transfer->save();

        Transaction::create([
            'from_account_id' => $request->from_account_id,
            'to_account_id' => $request->to_account_id,
            'amount' => $request->amount,
            'transaction_type' => 'debit',
            'transaction_category' => 'transfer',
            'transaction_status' => 'approved',
            'transaction_method' => 'bank',
            'description' => "Transfer to Account ID {$request->to_account_id}",
            'transaction_date' => $transfer->transfer_date,
        ]);

        Transaction::create([
            'from_account_id' => $request->to_account_id,
            'to_account_id' => $request->from_account_id,
            'amount' => $request->amount,
            'transaction_type' => 'credit',
            'transaction_category' => 'transfer',
            'transaction_status' => 'approved',
            'transaction_method' => 'bank',
            'description' => "Received from Account ID {$request->from_account_id}",
            'transaction_date' => $transfer->transfer_date,
        ]);

        $fromAccount->decrement('amount', $request->amount);
        ChartOfAccount::findOrFail($request->to_account_id)->increment('amount', $request->amount);

        \DB::commit();
        Toastr::success('Transfer recorded successfully.');
        return redirect()->route('transfers.index');

    } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Transfer failed: ' . $e->getMessage());
            Toastr::error('Transfer failed. Please try again.');
            return back()->withInput();
        }
    }

    public function destroy(AccountTransfer $transfer)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $transfer->delete();
        Toastr::success('Transfer deleted successfully.');
        return redirect()->route('transfers.index');
    }
}
