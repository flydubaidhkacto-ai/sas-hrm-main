<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class NoticeAttachment extends Model
{
    protected $guarded = [];
    protected $appends = [
        'file_name',
        'is_pdf',
        'url',
    ];
    static function boot() {
        parent::boot();
        // Will fire everytime an User is created
        static::deleting(function (self $model) {
            Storage::delete($model->path);
        });
    }
    function notice() {
        return $this->belongsTo(Notice::class);
    }
    function getFileNameAttribute() {
        $parts = explode("/", $this->path);
        if(!$this->type) return end($parts);
        $name = explode('.', end($parts));
        $prefix = $this->type . '.' . $name[1];
        return $prefix;
    }

    function getIsPdfAttribute() {
        return strtolower(pathinfo($this->path, PATHINFO_EXTENSION)) === 'pdf';
    }

    function getUrlAttribute() {
        return asset('uploaded_files/' . $this->path);
    }
}
