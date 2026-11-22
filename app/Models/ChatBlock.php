<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBlock extends Model
{
    protected $table = "chat_blocks";

    protected $fillable = ['chat_id', 'blocked_by_id'];
}
