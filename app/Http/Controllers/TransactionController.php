<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\ChartOfAccount;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;

class TransactionController extends Controller
{
    public function index()
    {
        abort_if(!role_can('admin-accounts'), 403);
        $data = Transaction::with('fromAccount', 'toAccount')->get();
        return view('accounts.transaction.index', compact('data'));
    }

    public function create()
    {
        abort_if(!role_can('admin-accounts'), 403);
        $accounts = ChartOfAccount::all();
        return view('accounts.transaction.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $request->validate([
            'from_account_id' => 'required|exists:chart_of_accounts,id',
            'to_account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:debit,credit',
            'transaction_category' => 'required|in:sale,purchase,expense,revenue,other',
            'transaction_status' => 'nullable|in:pending,approved,rejected',
            'transaction_method' => 'required|in:cash,bank,credit_card,other',
            'cash_flow_category' => 'required|in:operating,investing,financing',
            'transaction_number' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $transaction = new Transaction();
        $transaction->from_account_id = $request->from_account_id;
        $transaction->to_account_id = $request->to_account_id;
        $transaction->amount = $request->amount;
        $transaction->transaction_type = $request->transaction_type;
        $transaction->transaction_category = $request->transaction_category;
        // $transaction->transaction_status = $request->transaction_status;
        $transaction->transaction_status = 'approved';
        $transaction->transaction_method = $request->transaction_method;
        $transaction->cash_flow_category = $request->cash_flow_category;
        $transaction->transaction_number = $request->transaction_number;
        $transaction->description = $request->description;
        $transaction->transaction_date = Carbon::now();

        $transaction->save();
        // Update balances
        $fromAccount = ChartOfAccount::find($request->from_account_id);
        $toAccount = ChartOfAccount::find($request->to_account_id);

        $fromAccount->amount -= $request->amount;
        $toAccount->amount += $request->amount;

        $fromAccount->save();
        $toAccount->save();

        Toastr::success('Transaction recorded successfully.');
        return redirect()->route('transactions.index');
    }

    public function edit($id)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $transaction = Transaction::find($id);
        $accounts = ChartOfAccount::all();
        return view('accounts.transaction.edit', compact('transaction', 'accounts'));
    }

    public function update(Request $request, $id)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $request->validate([
            'from_account_id' => 'required|exists:chart_of_accounts,id',
            'to_account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:debit,credit',
            'transaction_category' => 'required|in:sale,purchase,expense,revenue,other',
            'transaction_status' => 'nullable|in:pending,approved,rejected',
            'transaction_method' => 'required|in:cash,bank,credit_card,other',
            'cash_flow_category' => 'required|in:operating,investing,financing',
            'transaction_number' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $transaction = Transaction::find($id);

        // Check if amount or accounts have changed
        $amountChanged = $transaction->amount != $request->amount;
        $accountsChanged = $transaction->from_account_id != $request->from_account_id ||
                          $transaction->to_account_id != $request->to_account_id;

        if ($amountChanged || $accountsChanged) {
            $oldFromAccount = ChartOfAccount::find($transaction->from_account_id);
            $oldToAccount = ChartOfAccount::find($transaction->to_account_id);

            $oldFromAccount->amount += $transaction->amount;
            $oldToAccount->amount -= $transaction->amount;

            $oldFromAccount->save();
            $oldToAccount->save();

            $newFromAccount = ChartOfAccount::find($request->from_account_id);
            $newToAccount = ChartOfAccount::find($request->to_account_id);

            $newFromAccount->amount -= $request->amount;
            $newToAccount->amount += $request->amount;

            $newFromAccount->save();
            $newToAccount->save();
        }

        $transaction->from_account_id = $request->from_account_id;
        $transaction->to_account_id = $request->to_account_id;
        $transaction->amount = $request->amount;
        $transaction->transaction_type = $request->transaction_type;
        $transaction->transaction_category = $request->transaction_category;
        // $transaction->transaction_status = $request->transaction_status;
        $transaction->transaction_status = 'approved';  
        $transaction->transaction_method = $request->transaction_method;
        $transaction->cash_flow_category = $request->cash_flow_category;
        $transaction->transaction_number = $request->transaction_number;
        $transaction->description = $request->description;
        // $transaction->transaction_date = Carbon::now();
        $transaction->save();

        Toastr::success('Transaction updated successfully.');
        return redirect()->route('transactions.index');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->fromAccount->amount += $transaction->amount;
        $transaction->toAccount->amount -= $transaction->amount;

        $transaction->fromAccount->save();
        $transaction->toAccount->save();

        $transaction->delete();

        Toastr::success('Transaction deleted successfully.');
        return redirect()->route('transactions.index');
    }
}
