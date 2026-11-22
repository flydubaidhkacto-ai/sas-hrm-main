<?php

namespace App\Http\Middleware;

use App\Models\ApiClients;
use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;

class ApiAuthorizedRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        $api_key = ApiKey::where('key', $token)->first();
        if(!$api_key)
        {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 401);
        }
        if($request->header('client_info'))
        {
            // register the client
            $client_info = json_decode($request->header('client_info'), true);
            $client = ApiClients::firstOrCreate([
                'device_name' => $client_info['name'],
                'device_mac' => $client_info['mac'] . ':::' . $client_info['uuid'],
                'api_key_id' => $api_key->id,
            ]);
            if($client->is_blocked)
            {
                return response()->json([
                    'message' => 'Client blocked!'
                ], 402);
            }
            $client->device_local_ip = $client_info['local_ips'];
            $client->device_ip = $request->ip();
            $client->device_os = $client_info['os'];
            $client->updated_at = now();
            $client->save();

            $request->merge(['client' => $client]);
        }
        else {
            // v2
        }
        return $next($request);
    }
}
