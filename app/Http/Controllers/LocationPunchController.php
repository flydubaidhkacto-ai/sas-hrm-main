<?php

namespace App\Http\Controllers;

use App\Exports\Excel\CheckinReport;
use App\Models\EmployeeWeeklySchedule;
use App\Models\GeoLocationCheckIn;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationPunchController extends Controller
{
    function checkin(Request $request)
    {
        $user = $request->user();
        $todayWeekday = strtolower(now()->format('l'));

        // Get eligible locations for today based on weekly schedule
        $eligibleLocationIds = EmployeeWeeklySchedule::forUser($user->id)
            ->forWeekday($todayWeekday)
            ->active()
            ->pluck('location_id')
            ->toArray();

        // If no schedule is set for today, show all assigned locations
        $eligibleLocations = !empty($eligibleLocationIds)
            ? $user->assigned_locations()->whereIn('locations.id', $eligibleLocationIds)->get()
            : $user->assigned_locations;

        return view('locations.checkin', [
            'user' => $user,
            'employee' => $user,
            'eligible_locations' => $eligibleLocations,
            'last_checkin' => $user->geo_location_checkins()->latest()->first(),
            'today_weekday' => ucfirst($todayWeekday),
            'has_schedule' => !empty($eligibleLocationIds),
        ]);
    }
    function punch(Request $request)
    {
        $user = user();
        abort_if(!$user->assigned_locations->count(), 401, 'No assigned locations found.');

        $request->validate([
            'user_latitude' => 'required|numeric',
            'user_longitude' => 'required|numeric',
            'note' => 'nullable|max:500'
        ]);

        $todayWeekday = strtolower(now()->format('l'));

        // Get eligible locations for today
        $eligibleLocationIds = EmployeeWeeklySchedule::forUser($user->id)
            ->forWeekday($todayWeekday)
            ->active()
            ->pluck('location_id')
            ->toArray();

        // Get eligible locations with coordinates
        $eligibleLocations = !empty($eligibleLocationIds)
            ? $user->assigned_locations()->whereIn('locations.id', $eligibleLocationIds)->get()
            : $user->assigned_locations;

        if ($eligibleLocations->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'No eligible locations found for today.',
                'type' => 'no_locations'
            ], 422);
        }

        // Check which location the user is within
        $userLat = $request->user_latitude;
        $userLng = $request->user_longitude;
        $withinLocation = null;

        foreach ($eligibleLocations as $location) {
            $distance = $this->calculateDistance($userLat, $userLng, $location->latitude, $location->longitude);
            $radiusKm = $location->radius / 1000; // Convert meters to km

            if ($distance <= $radiusKm) {
                $withinLocation = $location;
                break;
            }
        }

        if (!$withinLocation) {
            // Calculate distances to all eligible locations for response
            $locationDistances = [];
            foreach ($eligibleLocations as $location) {
                $distance = $this->calculateDistance($userLat, $userLng, $location->latitude, $location->longitude);
                $locationDistances[] = [
                    'name' => $location->name,
                    'distance_km' => round($distance, 2)
                ];
            }

            return response()->json([
                'success' => false,
                'message' => 'You are not within the radius of any eligible location for today.',
                'type' => 'outside_radius',
                'eligible_locations' => $locationDistances
            ], 422);
        }

        // Create check-in record
        $checkin = GeoLocationCheckIn::create([
            'user_id' => $user->id,
            'location_id' => $withinLocation->id,
            'latitude' => $userLat,
            'longitude' => $userLng,
            'location_name' => $withinLocation->full_path,
            'note' => $request->note,
            'check_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully checked in at {$withinLocation->name}",
            'location' => $withinLocation->name,
            'checkin' => $checkin
        ]);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return $distance;
    }
    function checkin_report(Request $request)
    {
        $user = user();
        $selectedEmp = $request->get('employee');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $data = [];

        if($fromDate && $toDate) {
            $rawData = GeoLocationCheckIn::when($selectedEmp, function($q) use ($selectedEmp) {
                return $q->where('user_id', $selectedEmp);
            })
                ->whereBetween('check_at', [
                    carbon($fromDate)->format('Y-m-d 00:00:00'),
                    carbon($toDate)->format('Y-m-d 23:59:59')
                ])
                ->with(['location', 'user.department', 'user.designation', 'user.department.super_department', 'user.assigned_locations'])
                ->orderBy('check_at', 'asc')
                ->get();

            // Group by user_id and date, then aggregate location names
            $groupedData = $rawData->groupBy(function($item) {
                return $item->user_id . '_' . $item->check_at->format('Y-m-d');
            });

            foreach($groupedData as $group) {
                $firstRecord = $group->first();
                $locationNames = $group;

                // Calculate total distance for visited locations
                $totalDistance = 0;
                $visitedLocationIds = $group->pluck('location_id')->unique();

                if ($firstRecord->user && $firstRecord->user->assigned_locations) {
                    foreach ($firstRecord->user->assigned_locations as $assignedLocation) {
                        if ($visitedLocationIds->contains($assignedLocation->id)) {
                            $totalDistance += $assignedLocation->pivot->distance_km;
                        }
                    }
                }

                $data[] = (object) [
                    'user' => $firstRecord->user,
                    'check_at' => $firstRecord->check_at,
                    'locations' => $locationNames,
                    'total_checkins' => $group->count(),
                    'total_distance_km' => $totalDistance
                ];
            }

            // Sort by date descending
            $data = collect($data)->sortByDesc(function($item) {
                return $item->check_at;
            })->values()->all();
            if($request->export === 'xlsx') {
                return (new CheckinReport($data, $fromDate, $toDate))
                    ->download("Check in reports.xlsx");
            }
        }

        return view('locations.checkin_report', [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'data' => $data,
        ]);
    }
}
