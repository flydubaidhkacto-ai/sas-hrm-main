<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveLocation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LiveLocationController extends Controller
{
    public function get(Request $request)
    {
        Log::info('Live location get api called', $request->all());
        $validator = Validator::make($request->all(), [
            'from' => 'required|date:Y-m-d H:i:s',
            'to' => 'required|date:Y-m-d H:i:s',
            'page' => 'nullable|integer',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $liveLocations = LiveLocation::whereBetween('date_time', [$request->from, $request->to])
            ->where('user_id', $request->auth_user->id)
            ->orderBy('date_time', 'desc')
            ->paginate($request->page ?? 1);

        return response()->json(array_merge([
            'message' => 'Live location retrived successfully',
        ], $liveLocations->toArray()), 200);
    }
    public function store(Request $request)
    {
        $user = $request->auth_user;

        if(!has_module('live-location-tracking')) {
            return response()->json([
                'message' => 'Module is not active',
            ], 403);
        }
        if($request->locations) {
            foreach ($request->locations as $item) {
                $time = date('Y-m-d H:i:s', $item['timestamp'] / 1000);
                if(
                    !LiveLocation::where('user_id', $user->id)
                        ->where('date_time', $time)
                        ->exists()
                ) {
                    $liveLocation = LiveLocation::create([
                        'user_id' => $user->id,
                        'latitude' => $item['latitude'],
                        'longitude' => $item['longitude'],
                        'ui_id' => 1,
                        'device_name' => $request->header('user_agent') ?:  $request->userAgent(),
                        'platform' => 'mobile',
                        'date_time' => $time,
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Live location saved successfully',
        ], 201);
    }
}
