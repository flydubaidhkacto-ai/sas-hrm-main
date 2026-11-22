<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'amount', 'description', 'code'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromAccount()
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }

}
