<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserZkDeviceFingerprint extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'fingerprint_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a string is a valid hex string.
     */
    public static function isHex($string): bool
    {
        return ctype_xdigit($string) && strlen($string) % 2 === 0;
    }

    /**
     * Encode all templates in a collection to Hex.
     */
    public static function encodeAllToHex(Collection $collection)
    {
        return $collection->map(function ($zkFinger) {
            $data = $zkFinger->toArray();
            $template = $data['fingerprint_data']['template'];
            $data['fingerprint_data']['fid'] = intval($data['fingerprint_data']['fid']);
            $data['fingerprint_data']['uid'] = intval($data['fingerprint_data']['uid']);
            $data['fingerprint_data']['size'] = intval($data['fingerprint_data']['size']);
            if (!self::isHex($template)) {
                $data['fingerprint_data']['template'] = bin2hex(base64_decode($template));
                $data['fingerprint_data']['size'] = strlen($data['fingerprint_data']['template']);
            }
            return $data;
        });
    }

    /**
     * Encode all templates in a collection to Base64.
     */
    public static function encodeAllToBase64(Collection $collection)
    {
        return $collection->map(function ($zkFinger) {
            $data = $zkFinger->toArray();
            $data['fingerprint_data']['fid'] = intval($data['fingerprint_data']['fid']);
            $data['fingerprint_data']['uid'] = intval($data['fingerprint_data']['uid']);
            $data['fingerprint_data']['size'] = intval($data['fingerprint_data']['size']);
            $template = $data['fingerprint_data']['template'];
            if (self::isHex($template)) {
                $data['fingerprint_data']['template'] = base64_encode(hex2bin($template));
                $data['fingerprint_data']['size'] = strlen($data['fingerprint_data']['template']);
            }
            return $data;
        });
    }
}
