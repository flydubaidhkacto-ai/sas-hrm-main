<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;

class VendorController extends Controller
{
    public function index()
    {
        abort_if(!role_can('admin-accounts'), 403);
        $vendors = Vendor::all();
        return view('accounts.vendor.index', compact('vendors'));
    }

    public function create()
    {
        abort_if(!role_can('admin-accounts'), 403);
        return view('accounts.vendor.create');
    }

    public function store(Request $request)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:vendors,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $vendor = new Vendor();
        $vendor->name = $request->name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->address = $request->address;
        $vendor->save();

        Toastr::success('Vendor added successfully.');
        return redirect()->route('vendors.index');
    }

    public function edit(Vendor $vendor)
    {
        abort_if(!role_can('admin-accounts'), 403);
        return view('accounts.vendor.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        abort_if(!role_can('admin-accounts'), 403);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:vendors,email,' . $vendor->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $vendor->name = $request->name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->address = $request->address;
        $vendor->save();

        Toastr::success('Vendor updated successfully.');
        return redirect()->route('vendors.index');
    }

    public function destroy(Vendor $vendor)
    {
        abort_if(!role_can('admin-accounts'), 403);
        if ($vendor->bills()->exists()) {
            Toastr::error('Vendor has bills, please delete the bills first.');
            return redirect()->route('vendors.index');
        }
        $vendor->delete();
        Toastr::success('Vendor deleted successfully.');
        return redirect()->route('vendors.index');
    }
}
