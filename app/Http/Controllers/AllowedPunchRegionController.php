<?php

namespace App\Http\Controllers;

use App\Models\AllowedPunchRegion;
use App\Models\Setting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class AllowedPunchRegionController extends Controller
{
    function create(Request $request)
    {
        return view('allowed-punch-region.create', [
            'model' => AllowedPunchRegion::find($request->id)
        ]);
    }
    function  store(Request $request)
    {
        $data = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_in_meters' => 'required|numeric',
            'name' => 'required|string|max:100|unique:allowed_punch_regions,name' . (
                    $request->id ? ',' . $request->id : ''
                ),
        ]);
        if($request->id)
        {
            AllowedPunchRegion::where('id', $request->id)->update($data);
            Toastr::success('Successfully Updated', 'Success');
        }else {
            AllowedPunchRegion::create($data);
            Toastr::success("Region successfully added", "Success");
        }
        return redirect()->route('settings.attendance.location');
    }
    function delete($id, Request $request)
    {
        AllowedPunchRegion::query()->where('id', $id)->delete();
        Toastr::success('Successfully Deleted', 'Success');
        return redirect()->route('settings.attendance.location');
    }
}
