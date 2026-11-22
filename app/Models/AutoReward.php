<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoReward extends Model
{
    protected $table = "auto_rewards";

    protected $fillable = ['type', 'amount'];
}
