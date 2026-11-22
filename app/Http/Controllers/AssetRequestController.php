<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetRequest;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class AssetRequestController extends Controller
{
    public function index()
    {
        abort_if(
            !role_can('asset-request') && !role_can('asset-manage')
            && !\App\Models\ApprovalProcess::canApprove('asset_request', user())
            , 403);
        $requests = AssetRequest::where('status', 'pending')->latest();
        if(!role_can('asset-manage'))
        {
            $emps = \App\Models\ApprovalProcess::canApproveEmployees('asset_request', user());
            $requests->whereIn('employee_id', [
                user()->id,
                ...($emps ? $emps['employees']->pluck('id')->toArray() : [])
            ]);
        }
        return view('asset_request.index', [
            'asset_request' => $requests->get()
        ]);
    }

    public function allRequest()
    {
        abort_if(
            !role_can('asset-request') && !role_can('asset-manage')
            && !\App\Models\ApprovalProcess::canApprove('asset_request', user())
            , 403);

        $requests = AssetRequest::latest();
        if(!role_can('asset-manage'))
        {
            $emps = \App\Models\ApprovalProcess::canApproveEmployees('asset_request', user());
            $requests->whereIn('employee_id', [
                user()->id,
                ...($emps ? $emps['employees']->pluck('id')->toArray() : [])
            ]);
        }
        return view('asset_request.all_request', [
            'asset_request' => $requests->get()
        ]);
    }

    public function report(Request $request)
    {
        abort_if(!role_can('asset-request') && !role_can('asset-manage')
            && !\App\Models\ApprovalProcess::canApprove('asset_request', user()), 403);

        $query = AssetRequest::query();

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                $request->input('date_from') . ' 00:00:00',
                $request->input('date_to') . ' 23:59:59'
            ]);
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('employee') && $request->input('employee') !== 'all') {
            $query->where('employee_id', $request->input('employee'));
        }

        if ($request->filled('asset_id') && $request->input('asset_id') !== 'all') {
            $query->where('asset_id', $request->input('asset_id'));
        }

        $asset_requests = $query->with(['employee', 'asset'])->latest()->get();

        $employees = \App\Models\User::active_employees()->get();
        $assets = Asset::all();

        return view('asset_request.report', [
            'asset_requests' => $asset_requests,
            'employees' => $employees,
            'assets' => $assets,
            'filters' => $request->all()
        ]);
    }

    public function create()
    {
        return view('asset_request.create', [
            'asset' => Asset::all()
        ]);
    }
    public function store(Request $request)
    {
        abort_if(
            !role_can('asset-request') && !role_can('asset-manage')
            , 403);
        $request->validate([
            'asset_id' => 'required',
            'reason' => 'required',
        ]);

        if ($request->start_date) {
            $data['start_dt'] = date('Y-m-d', strtotime($request->start_date)) . ' ' . date('H:i:s');
        }
        if ($request->end_date) {
            $data['end_dt'] = date('Y-m-d', strtotime($request->end_date)) . ' ' . date('H:i:s');
        }

        $data['asset_id'] = $request->asset_id;
        $data['employee_id'] = $request->employee_id;
        $data['reason'] = $request->reason;
        $data['status'] = $request->status;
        if(!role_can('asset-manage')) {
            $data['employee_id'] = user()->id;
        }

        $AssetRequest = AssetRequest::create($data);

        if ($data['status'] == 'approved') {
            if (user()->is_super_admin() || role_can('asset-request')) {
                $AssetRequest->approvedAll();
            }
        }

        Toastr::success('Asset Requested Successfully');
        return redirect()->route('asset_request');
    }

    public function edit($id)
    {
        abort_if(!role_can('asset-request'), 403);
        $asset_request = AssetRequest::find($id);
        return view('asset_request.edit', [
            'asset' => Asset::all(),
            'asset_request' => $asset_request
        ]);
    }

    public function update(Request $request)
    {
        abort_if(
            !role_can('asset-request') && !role_can('asset-manage')
            , 403);
        $request->validate([
            'asset_id' => 'required',
            'reason' => 'required',
        ]);

        $data['asset_id'] = $request->asset_id;
        $data['employee_id'] = $request->employee_id;
        $data['reason'] = $request->reason;
        $data['status'] = $request->status;
        if(!role_can('asset-manage')) {
            $data['employee_id'] = user()->id;
        }
        AssetRequest::where('id', $request->id)->update($data);
        Toastr::success('Asset Request Updated Successfully');
        return redirect()->route('asset_request');
    }

    public function delete($id)
    {
        abort_if(
            !role_can('asset-request') && !role_can('asset-manage')
            , 403);
        if(!role_can('asset-manage')) {
            $asset_request = AssetRequest::where('id', $id)->first();
            if($asset_request->employee_id != user()->id) {
                abort(403);
            }
        }
        AssetRequest::destroy($id);
        Toastr::success('Asset Request deleted successfully!');
        return redirect()->route('asset_request');
    }

    public function assetRequestEditShow($id)
    {
        $asset_request = AssetRequest::find($id);
        return view('asset_request.show', [
            'asset_request' => $asset_request
        ]);
    }
}



