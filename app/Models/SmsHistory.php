<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsHistory extends Model
{
    use HasFactory;
    protected $guarded = [];

    static function entry($recived_numbers, $text_body)
    {
        if(empty($text_body)) return;
        return self::create([
            'recived_numbers' => $recived_numbers,
            'sender_id' => $sender_id ?? '',
            'text_body' => $text_body
        ]);
    }
}
