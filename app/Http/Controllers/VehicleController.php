<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use App\Exports\VehicleList;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicle = Vehicle::latest()->get();

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return VehicleList::download_excel($vehicle, 'vehicle List.xlsx');
            }
            if ($as == 'pdf') {
                return VehicleList::download_pdf($vehicle, 'vehicles.pdf');
            }
            // if ($as == 'docx') {
            //     return EmployeeList::download_docx($data->get(), 'Employees.docx');
            // }
            if ($as == 'print') {
                return VehicleList::print($vehicle);
            }
        }

        return view('vehicle.index', [
            'data' => $vehicle
        ]);
    }
    public function create(){
        return view('vehicle.create');
    }
    public function store(Request $request){
        abort_if(!role_can('vehicle-management'), 403);
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'details' => 'required|string',
            'seller_details' => 'required|string',
            'sold_record' => 'boolean',
            'active_location' => 'nullable|string',
        ]);

        if($request->sold_record){
            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'details' => 'required|string',
                'seller_details' => 'required|string',
                'sold_record' => 'boolean',
                'new_owner_details' => 'required|string',
                'active_location' => 'nullable|string',
            ]);
        }

        $data['name'] = $request->name;
        $data['price'] = $request->price;
        $data['details'] = $request->details;
        $data['seller_details'] = $request->seller_details;
        $data['sold_record'] = $request->sold_record;
        $data['new_owner_details'] = $request->new_owner_details;
        $data['active_location'] = $request->active_location;
        // dd($data);

        Vehicle::create($data);
        Toastr::success('Vehicle added successfully!');
        return redirect()->route('vehicle');
    }
    public function edit($id){
        $vehicle = Vehicle::findOrFail($id);
        return view('vehicle.edit', [
            'vehicle'=> $vehicle
        ]);
    }
    public function update(Request $request){
        abort_if(!role_can('vehicle-management'),0);
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'details' => 'nullable|string',
            'sold_record' => 'boolean',
            'seller_details' => 'nullable|string',
            'active_location' => 'nullable|string',
        ]);

        if($request->sold_record){
            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'details' => 'required|string',
                'seller_details' => 'required|string',
                'sold_record' => 'boolean',
                'new_owner_details' => 'required|string',
                'active_location' => 'nullable|string',
            ]);
        }

        $data['name'] = $request->name;
        $data['price'] = $request->price;
        $data['details'] = $request->details;
        $data['seller_details'] = $request->seller_details;
        $data['sold_record'] = $request->sold_record;
        $data['new_owner_details'] = $request->new_owner_details;
        $data['active_location'] = $request->active_location;
        // dd($data);
        Vehicle::find($request->id)->update($data);
        Toastr::success('Vehicle update successfully!');
        return redirect()->route('vehicle');
    }

    public function delete($id){
        abort_if(!role_can('vehicle-management'), 403);
        $find = Vehicle::findOrFail($id);
        if($find->vehicleService()->count() > 0) {
            Toastr::error('Vehicle cannot be deleted because it has service records');
            return back();
        }
        $find->delete();
        Toastr::success('Vehicle deleted successfully!');
        return redirect()->route('vehicle');
    }
}
