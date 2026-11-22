<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveLocation;
use Illuminate\Support\Facades\Log;

class LiveLocationController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->auth_user;

        if($request->data) {
            foreach ($request->data as $item) {
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
                        'device_name' => 1,
                        'platform' => 'mobile',
                        'date_time' => $time,
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Live location saved successfully',
            'data' => $liveLocation
        ], 201);
    }
}
