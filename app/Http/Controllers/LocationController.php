<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    /**
     * Display location management page
     */
    public function index()
    {
        abort_if(!role_can('locations') && !role_can('locations.location') && !user()->locations_admin->count() && !user()->can_create_location, 403);

        $locations = Location::with(['parent', 'children'])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->when(!role_can('locations') && !role_can('locations.location') && (count(user()->locations_admin_areas()) || user()->can_create_location), function($q) {
                $q->where(function ($q) {
                    $q->whereIn('id', user()->locations_admin_areas() ?: [-1])
                        ->orWhere('created_by', user()->id);
                });
            })
            ->orderBy('name')
            ->get();
        // dd($locations, Location::get());

        return view('locations.index', compact('locations'));
    }

    /**
     * Get locations by parent ID (for AJAX requests)
     */
    public function getByParent($parentId = null)
    {
        abort_if(!role_can('locations') && !role_can('locations.location') && !user()->locations_admin->count() && !user()->can_create_location, 403);

        $locations = Location::query()
            ->when($parentId, function($q) use ($parentId) {
                return $q->where('parent_id', $parentId);
            }, function($q) {
                return $q->whereNull('parent_id');
            })
            ->when(!role_can('locations') && !role_can('locations.location') && (count(user()->locations_admin_areas()) || user()->can_create_location), function($q) {
                $q->where(function ($q) {
                    $q->whereIn('id', user()->locations_admin_areas() ?: [-1])
                        ->orWhere('created_by', user()->id);
                });
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($locations);
    }

    /**
     * Get location hierarchy (for dropdown/tree view)
     */
    public function getHierarchy()
    {
        abort_if(!role_can('locations') && !role_can('locations.location') && !user()->locations_admin->count() && !user()->can_create_location && !user()->geo_location_departments, 403);

        $locations = Location::with('children.children.children.children.children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->where('type', 'area')
            ->when(!role_can('locations') && !role_can('locations.location') && (count(user()->locations_admin_areas()) || user()->can_create_location), function($q) {
                $q->where(function ($q) {
                    $q->whereIn('id', user()->locations_admin_areas() ?: [-1])
                        ->orWhere('created_by', user()->id);
                });
            })
            ->orderBy('name')
            ->get();

        return response()->json($locations);
    }
    public static function getFullHierarchy(Request $request)
    {
        $location = Location::findOrFail($request->id);
        return $location->getFullHierarchy();
    }

    /**
     * Store a new location
     */
    public function store(Request $request)
    {
        abort_if(!role_can('locations') && !role_can('locations.location') && !user()->locations_admin->count() && !user()->can_create_location, 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'parent_id' => 'nullable|exists:locations,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:50000',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
        ]);

        // Validate coordinates if radius is provided
        if ($request->radius && (!$request->latitude || !$request->longitude)) {
            return redirect()->back()->withErrors(['coordinates' => 'Latitude and longitude are required when radius is specified.']);
        }

        try {
            DB::beginTransaction();

            $location = Location::create([
                'name' => $request->name,
                'type' => $request->type,
                'parent_id' => $request->parent_id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius' => $request->radius,
                'description' => $request->description,
                'phone' => $request->phone,
                'is_active' => true,
                'created_by' => !role_can('locations') && !role_can('locations.location') ? user()->id : null,
            ]);
            // auto assign to him
            if(user()->can_create_location)
            {
                if(!$location->parent_id) {

                }
                if($location->type === 'location') {
                    // calculate distance from home
                    if(user()->home_latitude && user()->home_longitude)
                    {
                        $distance = getRoadDistance(
                            user()->home_latitude, user()->home_longitude,
                            $location->latitude, $location->longitude
                        );
                        if($distance !== null) {
                            user()->assigned_locations()->attach([
                                $location->id => [
                                    'distance_km' => $distance
                                ]
                            ]);
                        }
                    }

                }
            }
            user()->locations_admin()->attach($location->id);
            DB::commit();

            Toastr::success('Location added successfully!', 'Success');
            return redirect()->route('locations.index');

        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Failed to add location: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }

    /**
     * Update location
     */
    public function update(Request $request)
    {
        abort_if(!role_can('locations') && !role_can('locations.location') && !user()->locations_admin->count() && !user()->can_create_location, 403);

        $request->validate([
            'id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'parent_id' => 'nullable|exists:locations,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:50000',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
        ]);

        // Prevent self-referencing parent
        if ($request->parent_id == $request->id) {
            return redirect()->back()->withErrors(['parent_id' => 'Location cannot be its own parent.']);
        }

        // Validate coordinates if radius is provided
        if ($request->radius && (!$request->latitude || !$request->longitude)) {
            return redirect()->back()->withErrors(['coordinates' => 'Latitude and longitude are required when radius is specified.']);
        }

        try {
            DB::beginTransaction();

            $location = Location::findOrFail($request->id);

            // Check if making this location a child would create a circular reference
            if ($request->parent_id) {
                $ancestors = Location::find($request->parent_id)->ancestors();
                if ($ancestors->contains('id', $request->id)) {
                    DB::rollback();
                    return redirect()->back()->withErrors(['parent_id' => 'This would create a circular reference.']);
                }
            }

            $location->update([
                'name' => $request->name,
                'type' => $request->type,
                'parent_id' => $request->parent_id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius' => $request->radius,
                'description' => $request->description,
                'phone' => $request->phone,
            ]);
            if($location->type === 'location' && user()->assigned_locations->contains($location->id))
            {
                // calculate distance from home
                if(user()->home_latitude && user()->home_longitude)
                {
                    $distance = getRoadDistance(
                        user()->home_latitude, user()->home_longitude,
                        $location->latitude, $location->longitude
                    );
                    if($distance !== null) {
                        user()->assigned_locations()->detach($location->id);
                        user()->assigned_locations()->attach([
                            $location->id => [
                                'distance_km' => $distance
                            ]
                        ]);
                    }
                }
            }
            DB::commit();

            Toastr::success('Location updated successfully!', 'Success');
            return redirect()->route('locations.index');

        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Failed to update location: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }

    /**
     * Delete location
     */
    public function destroy(Request $request)
    {
        abort_if(!role_can('locations') && !role_can('locations.location'), 403);

        $request->validate([
            'id' => 'required|exists:locations,id',
        ]);
        $location = Location::findOrFail($request->id);
        $location->delete();
        Toastr::success('Location deleted successfully!', 'Success');
        return redirect()->route('locations.index');
    }

    /**
     * Get location details (for editing modal)
     */
    public function show($id)
    {
        abort_if(!role_can('locations') && !role_can('locations.location') && !user()->locations_admin->count() && !user()->can_create_location, 403);

        $location = Location::with('parent')->findOrFail($id);
        return response()->json($location);
    }

    /**
     * Search locations for attendance validation
     */
    public function searchForAttendance(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1',
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $maxRadius = $request->radius ?? 1000; // default 1km

        $locations = Location::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotNull('radius')
            ->where('is_active', true)
            ->get()
            ->filter(function($location) use ($latitude, $longitude) {
                return $location->isWithinRadius($latitude, $longitude);
            })
            ->values();

        return response()->json([
            'locations' => $locations,
            'found' => $locations->count() > 0,
        ]);
    }

    /**
     * Get coordinates for a location (used by map)
     */
    public function getCoordinates($id)
    {
        abort_if(!role_can('locations') && !role_can('locations.location') && !user()->locations_admin->count(), 403);

        $location = Location::findOrFail($id);

        if (!$location->hasCoordinates()) {
            return response()->json(['error' => 'Location has no coordinates'], 404);
        }

        return response()->json([
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'radius' => $location->radius,
            'name' => $location->name,
        ]);
    }
}
