<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleService;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use App\Exports\VehicleServiceList;

class VehicleServiceController extends Controller
{
    public function index(Request $request){
        if(!user()->is_super_admin()){
            $data = VehicleService::where('responsible_person_id',user()->id)->get();
        }else{
            $data = VehicleService::latest()->get();
        }

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return VehicleServiceList::download_excel($data, 'Vehicle Service List.xlsx');
            }
            if ($as == 'pdf') {
                return VehicleServiceList::download_pdf($data, 'Vehicle Service List.pdf');
            }
            // if ($as == 'docx') {
            //     return EmployeeList::download_docx($data, 'Employees.docx');
            // }
            if ($as == 'print') {
                return VehicleServiceList::print($data);
            }
        }

        return view('vehicle_service.index',[
            'data'=> $data
        ]);
    }
    public function create()
    {
        $vehicle = Vehicle::all();
        $employee = User::where('role', 'employee')->where('is_active', 1)->get();
        return view('vehicle_service.create',[
            'vehicle'=> $vehicle,
            'employee'=> $employee
        ]);
    }
    public function store(Request $request){
        abort_if(!role_can('vehicle-management'), 403);
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'cost' => 'required|numeric',
            'reason' => 'required|string',
            'changed_parts' => 'required|string',
            'responsible_person_id' => 'required',
        ]);
        $data['vehicle_id'] = $request->vehicle_id;
        $data['cost'] = $request->cost;
        $data['reason'] = $request->reason;
        $data['changed_parts'] = $request->changed_parts;
        $data['repairman_name'] = $request->repairman_name;
        $data['repairman_location'] = $request->repairman_location;
        $data['responsible_person_id'] = $request->responsible_person_id;

        // dd($data);

        VehicleService::create($data);

        Toastr::success('Vehicle service added successfully!');
        return redirect()->route('vehicle_service');
    }
    public function edit($id){
        abort_if(!role_can('vehicle-management'), 403);
        $service = VehicleService::find($id);
        $vehicle = Vehicle::all();
        $employee = User::where('role', 'employee')->where('is_active', 1)->get();
        return view('vehicle_service.edit',[
            'vehicle'=> $vehicle,
            'employee'=> $employee,
            'service'=> $service
        ]);
    }
    public function update(Request $request){
        abort_if(!role_can('vehicle-management'), 403);
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'cost' => 'required|numeric',
            'reason' => 'required|string',
            'changed_parts' => 'required|string',
            'responsible_person_id' => 'required',
        ]);
        $data['vehicle_id'] = $request->vehicle_id;
        $data['cost'] = $request->cost;
        $data['reason'] = $request->reason;
        $data['changed_parts'] = $request->changed_parts;
        $data['repairman_name'] = $request->repairman_name;
        $data['repairman_location'] = $request->repairman_location;
        $data['responsible_person_id'] = $request->responsible_person_id;

        VehicleService::find($request->id)->update($data);
        Toastr::success('Vehicle service updated successfully!');
        return redirect()->route('vehicle_service');
    }

    public function delete($id){
        abort_if(!role_can('vehicle-management'), 403);
        VehicleService::findOrFail($id)->delete();
        Toastr::success('Vehicle service deleted successfully!');
        return redirect()->route('vehicle_service');
    }
}
