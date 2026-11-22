<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\SuperDepartment;
use App\Models\ZktecoCloud;
use App\Models\ZktecoCloudCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZktecoCloudController extends Controller
{
    public function can()
    {
        abort_if(!role_can('zkteco-cloud'), 403, 'Unauthorized');
    }
    public function index()
    {
        $this->can();
        $devices = ZktecoCloud::query()
            ->orderBy('id', 'desc')
            ->get();
        return view('zkteco-cloud.index', [
            'devices' => $devices
        ]);
    }
    function create()
    {
        $this->can();
        return view('zkteco-cloud.create');
    }

    function store(Request $request)
    {
        $this->can();
        $request->validate([
            'name' => 'required|max:50',
            'device_sn' => 'required|max:100|unique:zkteco_clouds,device_sn',
            'device_type' => 'required|max:50|in:' . join(',', array_keys(ZktecoCloudCommand::$supported_devices)),
        ]);

        $device = new ZktecoCloud();
        $device->name = $request->name;
        $device->device_sn = $request->device_sn;
        $device->device_type = $request->device_type;
        $device->data = [];
        $device->save();

        return redirect()->route('zkteco-cloud')
            ->with('success', 'Device added successfully');
    }

    function edit($id)
    {
        $this->can();
        $device = ZktecoCloud::find($id);
        return view('zkteco-cloud.edit', compact('device'));
    }

    function update(Request $request, $id)
    {
        $this->can();
        $request->validate([
            'name' => 'required|max:50',
            'device_sn' => 'required|max:100|unique:zkteco_clouds,device_sn,' . $id,
            'device_type' => 'required|in:' . join(',', array_keys(ZktecoCloudCommand::$supported_devices)),
        ]);

        $device = ZktecoCloud::find($id);
        $device->name = $request->name;
        $device->device_sn = $request->device_sn;
        $device->device_type = $request->device_type;
        $device->save();
        return redirect()->route('zkteco-cloud')->with('success', 'Device updated successfully');
    }

    function delete($id)
    {
        $this->can();
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        $device = ZktecoCloud::find($id);
        $device->delete();
        return redirect()->route('zkteco-cloud')->with('success', 'Device deleted successfully');
    }

    function device($id)
    {
        $this->can();
        $device = ZktecoCloud::findOrFail($id);
        // ZktecoCloudCommand::query()->delete();

        return view('zkteco-cloud.device', [
            'device' => $device,
            'departments' => SuperDepartment::ordered()->get(),
            'assigned_departments' => $device->departments->pluck('id')->toArray(),
            'commands' => ZktecoCloudCommand::query()
                ->where('zkteco_cloud_id', $id)
                ->orderBy('id', 'desc')
                ->paginate(10)
        ]);
    }

    function sync($id, $type)
    {
        $this->can();
        $device = ZktecoCloud::find($id);
        $commands = ZktecoCloudCommand::get_command($type, $device);
        if(!is_array($commands)) $commands = [$commands];

        foreach($commands as $command) {
            $nextId = ZktecoCloudCommand::query()->max('id') + 1;
            (ZktecoCloudCommand::create([
                'type' => $type,
                'zkteco_cloud_id' => $id,
                'command' => "C:{$nextId}:{$command}",
            ]));
        }
        return redirect()->back()
            ->with('success', 'Command sent to queue successfully');
    }

    function delete_command($id)
    {
        $this->can();
        $command = ZktecoCloudCommand::findOrFail($id);
        $command->delete();
        return redirect()->back()->with('success', 'Command deleted successfully');
    }
    function assign_department(Request $request, $device)
    {
        $this->can();
        $device = ZktecoCloud::findOrFail($device);
        $request->validate([
            'departments' => 'nullable|array',
            'departments.*' => 'required|exists:departments,id',
        ]);
        $device->departments()->sync($request->departments ?? []);
        $device->update([
            'inoutstate' => $request->inoutstate,
        ]);
        return redirect()->back()->with('success', 'Departments assigned successfully');
    }
}
