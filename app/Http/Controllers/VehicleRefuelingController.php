<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRefuel;
use Illuminate\Http\Request;
use App\Exports\VehicleRefuelList;

class VehicleRefuelingController extends Controller
{
    public function index(Request $request)
    {
        $refuelings = VehicleRefuel::with('vehicle', 'driver')->get();

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return VehicleRefuelList::download_excel($refuelings, 'Vehicle Refueling List.xlsx');
            }
            if ($as == 'pdf') {
                return VehicleRefuelList::download_pdf($refuelings, 'Vehicle Refueling List.pdf');
            }
            // if ($as == 'docx') {
            //     return EmployeeList::download_docx($data->get(), 'Employees.docx');
            // }
            if ($as == 'print') {
                return VehicleRefuelList::print($refuelings);
            }
        }

        return view('vehicle.refuelings.index', compact('refuelings'));
    }

    public function create()
    {
        $vehicles = Vehicle::all();
        $drivers = User::active_employees()->get();
        $fuelTypes = ['Diesel', 'Petrol', 'CNG', 'LPG', 'Octane'];

        return view('vehicle.refuelings.create', compact('vehicles', 'drivers', 'fuelTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'fuel_type' => 'required',
            'fuel_station' => 'required|string',
            'budget' => 'required|numeric',
            'place' => 'required|string',
            'km_per_unit' => 'required|numeric',
            'max_unit' => 'required|numeric',
            'driver_id' => 'required|exists:users,id',
            'odometer_at_refuel' => 'required|numeric',
            'unit_taken' => 'required|numeric',
            'fuel_slip_scan_copy' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        if ($request->hasFile('fuel_slip_scan_copy')) {
            $validated['fuel_slip_scan_copy'] = $request->file('fuel_slip_scan_copy')->store('fuel_slips');
        }

        VehicleRefuel::create($validated);

        return redirect()->route('refuelings.index')->with('success', 'Refueling added successfully.');
    }

    public function show(VehicleRefuel $refueling)
    {
        return view('vehicle.refuelings.show', compact('refueling'));
    }

    public function edit(VehicleRefuel $refueling)
    {
        $vehicles = Vehicle::all();
        $drivers = User::all();
        $fuelTypes = ['Diesel', 'Petrol', 'CNG', 'LPG', 'Octane'];

        return view('vehicle.refuelings.edit', compact('refueling', 'vehicles', 'drivers', 'fuelTypes'));
    }

    public function update(Request $request, Refueling $refueling)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'fuel_type' => 'required',
            'fuel_station' => 'required|string',
            'budget' => 'required|numeric',
            'place' => 'required|string',
            'km_per_unit' => 'required|numeric',
            'max_unit' => 'required|numeric',
            'driver_id' => 'required|exists:users,id',
            'odometer_at_refuel' => 'required|numeric',
            'unit_taken' => 'required|numeric',
            'fuel_slip_scan_copy' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        if ($request->hasFile('fuel_slip_scan_copy')) {
            $validated['fuel_slip_scan_copy'] = $request->file('fuel_slip_scan_copy')->store('fuel_slips');
        }

        $refueling->update($validated);

        return redirect()->route('refuelings.index')->with('success', 'Refueling updated successfully.');
    }

    public function destroy(VehicleRefuel $refueling)
    {
        $refueling->delete();
        return redirect()->route('refuelings.index')->with('success', 'Refueling deleted successfully.');
    }
}
