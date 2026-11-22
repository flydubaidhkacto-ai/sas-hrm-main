<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $fillable = ['template_id', 'employee_id', 'body', 'title'];


    public function rel_to_emp() {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function rel_to_template() {
        return $this->belongsTo(Template::class, 'template_id');
    }
}
