<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notice extends Model
{
    use HasFactory;
    protected $guarded = [];

    static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            $model->attachments()->delete();
        });
    }
    public function userView()
    {
        return $this->hasMany(NoticeView::class);
    }
    function attachments()
    {
        return $this->hasMany(NoticeAttachment::class);
    }
}
