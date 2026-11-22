<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use Brian2694\Toastr\Facades\Toastr;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        abort_if(!role_can('admin-accounts'), 403);
        $data = ChartOfAccount::all();
        return view('accounts.chart_accounts.index', compact('data'));
    }

    public function create()
    {
        abort_if(!role_can('admin-accounts'), 403);
        return view('accounts.chart_accounts.create');
    }

    public function store(Request $request)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'amount' => 'required|numeric'
        ]);

        $account = new ChartOfAccount();
        $account->name = $request->name;
        $account->type = $request->type;
        $account->amount = $request->amount;
        $account->description = $request->description;
        $account->save();

        Toastr::success('Account created successfully.');
        return redirect()->route('chart-of-accounts.index');
    }

    public function edit($id)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $data = ChartOfAccount::find($id);
        return view('accounts.chart_accounts.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'amount' => 'required|numeric'
        ]);

        $account = ChartOfAccount::find($id);
        $account->name = $request->name;
        $account->type = $request->type;
        $account->amount = $request->amount;
        $account->description = $request->description;
        $account->save();

        Toastr::success('Account updated successfully.');
        return redirect()->route('chart-of-accounts.index');
    }

    public function destroy($id)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $account = ChartOfAccount::find($id);
        $account->delete();

        Toastr::success('Account deleted successfully.');
        return redirect()->route('chart-of-accounts.index');
    }
}
