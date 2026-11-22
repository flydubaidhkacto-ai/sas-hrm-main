<?php

namespace App\Http\Controllers;

use App\Actions\EmployeePunched;
use App\Jobs\EmployeePunchedJob;
use App\Jobs\ProccessInOutRecords;
use App\Models\ApiKey;
use App\Models\Attendance;
use App\Models\InOutRecord;
use App\Models\User;
use App\Models\UserZkDeviceFingerprint;
use App\Models\ZkDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class AppApiController extends Controller
{
    function get_users(Request $request)
    {
        $departments = $request->client->departments->pluck('department_id')->toArray();
        return User::employees()
            ->whereIn('department_id', $departments)
            ->get(['id', 'name', 'is_active']);
    }
    function attendances(Request $request)
    {
        $data = $request->data;
        $res = [];

        if (is_array($data)) {
            $records = [];
            foreach ($data as $d) {
                if ($d['timestamp']) {
                    $check = InOutRecord::where('original_date_time', $d['timestamp'])
                        ->where('user_id', $d['user_id'])
                        ->first();
                    if (!$check) {
                        try {
                            $check = InOutRecord::firstOrCreate([
                                'user_id' => $d['user_id'],
                                'status' => $d['status'],
                                'date_time' => $d['timestamp'],
                                'original_date_time' => $d['timestamp'],
                                'device_mac' => $request->mac ?? "Invalid",
                                'device_uid' => $d['uid'],
                                'device_attendance_uid' => $d['uid'],
                                'unique_data' => $d['timestamp'] . '-' . $d['user_id'],
                            ]);
                        } catch (\Throwable $th) {
                            // Log::error($th);
                        }
                    }
                    $records[] = $check;
                }
                $d['added'] = true;
                $d['idd'] = $check->id;
                $res[] = $d;
            }
        }
        return $res;
    }
    function v2_client(Request $request)
    {
        $action = $request->action;
        if (!$action) return [];
        $device = $request->device;

        unset($device['action']);
        if ($device['password'] ?? null) unset($device['password']);

        $db_device = ZkDevice::where('uuid', $device['uuid'])->first();
        if (!$db_device) {
            $db_device = ZkDevice::create(
                array_merge($device, [
                    'api_key_id' => ApiKey::where('key', $request->bearerToken())->first()?->id,
                ])
            );
        } else {
            $db_device->update($device);
        }
        $db_device->update(['updated_at' => now()]);

        if (!$db_device->department_ids) return [];
        // $have_permission_to_sync_finger
        // Log::debug($action, $request->all());
        if ($action === 'get_users') {
            // [id, id]
            $deleted_ids = User::terminated_employees()
                ->withTrashed()
                ->whereIn('department_id', $db_device->department_ids)
                ->orderBy('deleted_at', 'desc')
                ->get('id')
                ->pluck('id')
                ->toArray();
            // {id, name, privilege, password, fingerprint_json}
            $active = User::query()
                ->whereIn('department_id', $db_device->department_ids)
                ->orderBy('updated_at', 'desc')
                ->whereNotIn('id', $deleted_ids)
                ->with(['zk_fingerprints'])
                ->get()
                ->map(function ($user) {
                    $zk_fingers = UserZkDeviceFingerprint::encodeAllToHex(
                        $user->zk_fingerprints
                    );
                    $all_fingers_data = [];
                    foreach ($zk_fingers as $zk_finger) {
                        $all_fingers_data[] = $zk_finger['fingerprint_data'];
                    }

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'privilege' => $user->zk_device_privilege,
                        'password' => $user->zk_device_password,
                        'card' => $user->zk_device_card,
                        'fingerprint_json' => $user->zk_device_fingerprint_json,
                        'all_fingers' => $all_fingers_data,
                    ];
                });
            return [
                'deleted_ids' => $deleted_ids,
                'active' => $active
            ];
        } else
        if ($action === 'push_attendances') {
            $records = is_array($request->data) ? $request->data : json_decode($request->data, true);
            $added = [];
            $inOuts = [];
            foreach ($records as $record) {
                try {
                    $check = InOutRecord::firstOrCreate([
                        'user_id' => $record['user_id'],
                        'status' => $record['status'],
                        'date_time' => $record['timestamp'],
                        'original_date_time' => $record['timestamp'],
                        'device_mac' => $request->mac ?? "Invalid",
                        'device_uid' => $db_device['uid'],
                        'device_attendance_uid' => $record['uid'],
                        'unique_data' => $record['timestamp'] . '-' . $record['user_id'],
                        'added' => 0
                    ]);
                    $added[] = $check->toArray();
                    $inOuts[] = $check;
                    if ($request->is_live) {
                        EmployeePunchedJob::dispatch($check);
                    }
                } catch (\Exception $e) {
                    // if (config('app.debug')) {
                    //     Log::error($e->getMessage());
                    // }
                }
            }
            // foreach ($inOuts as $inOut) {
            //     \App\Actions\EmployeePunched\Execute::run($inOut);
            //     $inOut->update([
            //         'added' => 1
            //     ]);
            // }
            return $added;
        } else
        if ($action === 'upload_user_fingerprint') {
            if (is_array($request->fingers)) {
                foreach ($request->fingers as $finger) {
                    $user = User::find($finger['uid']);
                    if ($user) {
                        $user->update([
                            'zk_device_fingerprint_json' => json_encode($finger)
                        ]);
                        $check = UserZkDeviceFingerprint::query()
                            ->where('fingerprint_data', 'LIKE',  '%' . ($finger['template'] ?? '') . '%')
                            ->first();
                        if ($check) {
                            $check->update(['user_id' => $user->id]);
                        } else {
                            UserZkDeviceFingerprint::query()->firstOrCreate([
                                'user_id' => $user->id,
                                'fingerprint_data' => $finger
                            ]);
                        }
                        /**
                         * DELETE t1 FROM user_zk_device_fingerprints t1 INNER JOIN user_zk_device_fingerprints t2 WHERE t1.id < t2.id AND t1.user_id = t2.user_id AND t1.fingerprint_data = t2.fingerprint_data;
                         */
                    }
                }
                return [];
            }
        }
        return [];
    }
}
