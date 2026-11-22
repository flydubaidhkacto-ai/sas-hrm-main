<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    use HasFactory;
    protected $guarded = [];

    static function initFirst()
    {
        if(self::count() == 0)
        {
            StudentClass::create([
                'name' => 'Class 1',
            ]);
        }
    }

    public function students()
    {
        return $this->hasMany(User::class, 'class_id');
    }
}
