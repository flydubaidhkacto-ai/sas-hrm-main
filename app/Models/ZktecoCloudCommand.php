<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZktecoCloudCommand extends Model
{
    protected $guarded = [];
    static $supported_devices = [
        'speedface_v3l' => [
            'send_users',
            'send_fingerprints',
            'get_fingerprints',
            'sync_attendances',
            'sync_time',
            'reboot',
            'delete_attendances',
            'delete_users',
            'delete_fingerprints',
        ],
        'other' => [
            'send_users',
            'send_fingerprints',
            'reboot',
            'send_faces'
        ],
    ];

    static function set_add_user_command(User $user): bool
    {
        $devices = ZktecoCloud::query()
            ->where(function ($query) use ($user) {
                $query->doesntHave('departments')
                    ->orWhereHas('departments', function ($query) use ($user) {
                        $query->where('departments.id', $user->department_id);
                    });
            })
            ->get();
        foreach ($devices as $device) {
            if ($device->device_type == 'speedface_v3l') {
                $command = "Pin={$user->id}\tName={$user->name}";
                if ($user->zk_device_password) {
                    $command .= "\tPassword={$user->zk_device_password}";
                }
                if ($user->zk_device_card) {
                    $command .= "\tCardNo={$user->zk_device_card}";
                }
                // if($emp->zk_device_privilege == 'admin') {
                //     $userCommands[$emp->id] .= "\tPri=14";
                // }
                $command .= "\tGroup=1";
            }
            else {
                $command = "DATA USER PIN={$user->id}\tName={$user->name}";
                if ($user->zk_device_password) {
                    $command .= "\tPasswd={$user->zk_device_password}";
                }
                if ($user->zk_device_card) {
                    $command .= "\tCard={$user->zk_device_card}";
                }
                if ($user->zk_device_privilege == 'admin') {
                    $command .= "\tPri=14";
                } else if ($user->zk_device_privilege == 'non_ac_user') {
                    $command .= "\tPri=2";
                } else {
                    $command .= "\tPri=0";
                }
            }

            $nextId = ZktecoCloudCommand::query()->max('id') + 1;
            ZktecoCloudCommand::create([
                'type' => 'send_user',
                'zkteco_cloud_id' => $device->id,
                'command' => "C:{$nextId}:{$command}",
            ]);
        }
        return true;
    }
    static function set_delete_user_command(User $user): bool
    {
        $devices = ZktecoCloud::query()
            ->where(function ($query) use ($user) {
                $query->doesntHave('departments')
                    ->orWhereHas('departments', function ($query) use ($user) {
                        $query->where('departments.id', $user->department_id);
                    });
            })
            ->get();
        if (!$devices->count()) return false;

        foreach ($devices as $device) {
            $nextId = ZktecoCloudCommand::query()->max('id') + 1;
            if ($device->device_type == 'speedface_v3l') {
                ZktecoCloudCommand::create([
                    'type' => 'delete_user',
                    'zkteco_cloud_id' => $device->id,
                    'command' => "C:{$nextId}:DATA DELETE user Pin={$user->id}",
                ]);
            } else {
                ZktecoCloudCommand::create([
                    'type' => 'delete_user',
                    'zkteco_cloud_id' => $device->id,
                    'command' => "C:{$nextId}:DATA DEL_USER PIN={$user->id}",
                ]);
            }
        }
        return true;
    }

    static function get_command($type, ZktecoCloud $device): string|array
    {
        $departments = $device->departments->pluck('id')->toArray();
        $device_type = $device->device_type;
        if ($type == 'send_users') {
            $returnData = [];
            User::employees_and_students()
                ->when($departments, function ($query) use ($departments) {
                    $query->where(function ($q) use($departments) {
                        $q->whereIn('department_id', $departments)
                            ->orWhereNull('department_id');
                    });
                })
                ->chunk(10, function ($emps) use (&$userCommands, &$returnData, $device_type) {
                    $userCommands = []; // CardNo, Pin, Name, Password
                    if ($device_type == 'speedface_v3l') {
                        $userCommands = []; // CardNo, Pin, Name, Password
                        foreach ($emps as $emp) {
                            $userCommands[$emp->id] = "Pin={$emp->id}\tName={$emp->name}";
                            if ($emp->zk_device_password) {
                                $userCommands[$emp->id] .= "\tPassword={$emp->zk_device_password}";
                            }
                            if ($emp->zk_device_card) {
                                $userCommands[$emp->id] .= "\tCardNo={$emp->zk_device_card}";
                            }
                            // if($emp->zk_device_privilege == 'admin') {
                            //     $userCommands[$emp->id] .= "\tPri=14";
                            // }
                            $userCommands[$emp->id] .= "\tGroup=1";
                        }
                        $returnData[] = "DATA UPDATE user " . implode("\n", $userCommands);
                    } else {
                        foreach ($emps as $emp) {
                            $userCommands[$emp->id] = "DATA USER PIN={$emp->id}\tName={$emp->name}";
                            if ($emp->zk_device_password) {
                                $userCommands[$emp->id] .= "\tPasswd={$emp->zk_device_password}";
                            }
                            if ($emp->zk_device_card) {
                                $userCommands[$emp->id] .= "\tCard={$emp->zk_device_card}";
                            }
                            if ($emp->zk_device_privilege == 'admin') {
                                $userCommands[$emp->id] .= "\tPri=14";
                            } else if ($emp->zk_device_privilege == 'non_ac_user') {
                                $userCommands[$emp->id] .= "\tPri=2";
                            } else {
                                $userCommands[$emp->id] .= "\tPri=0";
                            }

                            $returnData[] = $userCommands[$emp->id];
                        }
                    }
                });

            User::terminated_employees()
                ->when($departments, function ($query) use ($departments) {
                    $query->whereIn('department_id', $departments);
                })
                ->chunk(1, function ($emps) use (&$returnData, $device_type) {
                    foreach ($emps as $emp) {
                        if ($device_type == 'speedface_v3l') {
                            $returnData[] = "DATA DELETE user Pin={$emp->id}";
                        } else
                            $returnData[] = "DATA DEL_USER PIN={$emp->id}";
                    }
                });
            if (count($returnData) == 0) return "OK";
            // dd($device->device_type, $device_type, $returnData);
            return $returnData;
        }
        if ($type == 'send_fingerprints') {
            $returnData = [];
            UserZkDeviceFingerprint::query()
                ->when($departments, function ($query) use ($departments) {
                    $query->whereHas('user', function ($query) use ($departments) {
                        $query->whereIn('department_id', $departments);
                    });
                })
                ->chunk(10, function ($fingers) use (&$returnData, $device_type) {
                    $decoded = UserZkDeviceFingerprint::encodeAllToBase64($fingers);
                    foreach ($decoded as $data) {
                        if ($device_type == 'speedface_v3l') {
                            $finger = (object) ($data['fingerprint_data']);
                            $returnData[] = "DATA UPDATE templatev10 Pin={$data['user_id']}\tFingerID={$finger->fid}\tValid=1\tTemplate=" . $finger->template;
                            // $returnData[] = "FP PIN={$data['user_id']}\tFID={$finger->fid}\tSize={$finger->size}\tValid=1\tTMP=" . $finger->template;
                        } else {
                            $finger = (object) ($data['fingerprint_data']);
                            $returnData[] = "DATA FP PIN={$data['user_id']}\tFID={$finger->fid}\tSize={$finger->size}\tValid=1\tTMP=" . $finger->template;
                        }
                    }
                });
            // dd($returnData);
            return $returnData;
        }
        if ($type == 'send_faces') {
            $returnData = [];
            // $faces = UserZkTecoFace::query()->latest()
            //     // ->where('fid', '>', 0)
            //     ->get();
            UserZkTecoFace::query()
                ->chunk(50, function ($faces) use (&$returnData, $device_type) {
                    if ($device_type == 'speedface_v3l') {
                    } else {
                        foreach ($faces as $face) {
                            $tmp = base64_encode($face->template);
                            $size = strlen($tmp);
                            $returnData[] = "DATA FACE PIN={$face->user_id}\tFID={$face->fid}\tSize={$size}\tValid=1\tTMP=" . $tmp;
                        }
                    }
                });
            return $returnData;
        }
        if ($type == 'sync_attendances') {
            if ($device_type == 'speedface_v3l') {
                //return "DATA QUERY tablename=transaction,fielddesc=*,filter=*";
                $start = now()->subDays(7)->format('Y-m-d H:i:s');
                $end = now()->format('Y-m-d H:i:s');
                return "DATA QUERY ATTLOG StartTime=$start  EndTime=$end";
            }
        }
        if ($type == 'get_fingerprints') {
            if ($device_type == 'speedface_v3l') {
                return "DATA QUERY tablename=templatev10,fielddesc=*,filter=*";
            }
            //            return "QUERY ATTLOG StartTime=2020-12-12 00:00:00\tEndTime=2025-01-24 23:59:59";
        }
        if ($type == 'reboot') {
            return "REBOOT";
        }
        if ($type == 'sync_time') {
            return "SET OPTIONS DateTime=" . self::oldEncodeTime(
                date('Y'),
                date('m'),
                date('d'),
                date('H'),
                date('i'),
                date('s'),
            );
        }
        if ($type == 'delete_attendances') {
            if ($device_type == 'speedface_v3l') {
                return "DATA DELETE transaction *";
            }
        }
        if ($type == 'delete_users') {
            if ($device_type == 'speedface_v3l') {
                return "DATA DELETE user *";
            }
        }
        if ($type == 'delete_fingerprints') {
            if ($device_type == 'speedface_v3l') {
                return "DATA DELETE templatev10 *";
            }
        }
        if ($type == 'delete_photos') {
            if ($device_type == 'speedface_v3l') {
            }
        }
        return "";
    }
    static function oldEncodeTime($year, $mon, $day, $hour, $min, $sec)
    {
        $tt = (($year - 2000) * 12 * 31 + (($mon - 1) * 31) + $day - 1) * (24 * 60 * 60)
            + ($hour * 60 + $min) * 60 + $sec;
        return $tt;
    }
}
