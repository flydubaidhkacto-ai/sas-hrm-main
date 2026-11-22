<?php

namespace App\Http\Controllers;

use App\Gates\User;
use App\Models\ApiClients;
use App\Models\ApiKey;
use App\Models\SuperDepartment;
use App\Models\ZkDevice;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class ApiKeysController extends Controller
{
    function index()
    {
        abort_if(!role_can('api_key'), 403, "Please get permission from Administrator");
        return view('api-keys.index', [
            'keys' => ApiKey::latest()
                ->with('clients')
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
        return redirect()->route('api-keys')->with('success', 'Api key generated successfully');
    }
    function block_client($client)
    {
        abort_if(!role_can('api_key'), 403, "Please get permission from Administrator");
        $cl = ApiClients::findOrFail($client);
        $cl->delete();
        // if($cl->is_blocked)
        // {
        //     $cl->update([
        //         'is_blocked' => 0
        //     ]);
        //     Toastr::success('Client unblocked successfully :)','Success');
        // }
        // else {
        //     $cl->update([
        //         'is_blocked' => 1
        //     ]);
        //     Toastr::success('Client blocked successfully :)','Success');
        // }
        return redirect()->back();
    }
    function delete($id)
    {
        abort_if(!role_can('api_key'), 403, "Please get permission from Administrator");
        $k = ApiKey::findOrFail($id);
        $k->delete();
        return redirect()->route('api-keys')->with('success', 'Api key deleted successfully');
    }
    function assign_department($client, Request $request)
    {
        abort_if(!role_can('api_key'), 403, "Please get permission from Administrator");
        $data = $request->validate([
            'departments' => 'array'
        ]);
        $cl = ApiClients::findOrFail($client);
        // dd($cl->departments);
        $cl->departments()->delete();
        // if()
        foreach($data['departments'] ?? [] as $d)
        {
            $cl->departments()->create([
                'department_id' => $d,
                // 'api_client_id' => $cl->id
                'inoutstate' => $request->inoutstate,
            ]);
        }
        Toastr::success('Department assigned successfully :)','Success');
        return redirect()->back();
    }
}
