<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\SuperDepartment;
use App\Models\ZkDevice;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class ZkTecoController extends Controller
{

    function index(Request $request)
    {
        abort_if(!role_can('api_key'), 403, "Please get permission from Administrator");
        if($request->no_need_of_zkteco) {
            cache()->remember('no-need-of-zkteco', now()->addYear(), fn() => 1);
            return redirect()->back();
        }
        if(!ApiKey::count()) {
            ApiKey::create([
                'name' => "Main Branch",
                'key' => bin2hex(random_bytes(32)),
                'expire_at' => now()->addYear(),
            ]);
        }
        return view('zkteco.index', [
            'keys' => ApiKey::latest()
                ->with('devices')
                ->paginate(16, ['*'], 'keys'),
            'departments' => SuperDepartment::ordered()->get()
        ]);
    }
    function create(Request $request)
    {
        abort_if(!role_can('api_key'), 403, "Please get permission from Administrator");
        // generate a api key
        ApiKey::create([
            'name' => $request->name,
            'key' => bin2hex(random_bytes(32)),
            'expire_at' => now()->addYear(),
        ]);
        return redirect()->route('zkteco')->with('success', 'Device Api key generated successfully');
    }
    function delete($id)
    {
        abort_if(!role_can('api_key'), 403, "Please get permission from Administrator");
        $k = ApiKey::findOrFail($id);
        $k->delete();
        return redirect()->route('zkteco')->with('success', 'Device Api key deleted successfully');
    }
    function assign_department($device, Request $request)
    {
        abort_if(!role_can('api_key'), 403, "Please get permission from Administrator");
        $data = $request->validate([
            'departments' => 'array'
        ]);
        $cl = ZkDevice::findOrFail($device);

        $cl->update([
            'department_ids' => $data['departments'] ?? [],
            'inoutstate' => $request->inoutstate,
        ]);
        Toastr::success('Department assigned successfully :)','Success');
        return redirect()->back();
    }
    function delete_device($id)
    {
        abort_if(!role_can('api_key'), 403, "Please get permission from Administrator");
        $d = ZkDevice::findOrFail($id);
        $d->delete();
        Toastr::success('Device deleted successfully :)','Success');
        return redirect()->back();
    }
}
