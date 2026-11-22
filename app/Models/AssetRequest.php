<?php

namespace App\Models;

use App\Traits\ApprovalProcessModal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetRequest extends Model
{
    use HasFactory, ApprovalProcessModal;

    protected $approval_process_type = 'asset_request';

    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function approved_event()
    {
        // $data = [
        //     'start_dt' => date('Y-m-d H:i:s'),
        //     'status' => 'approved',
        // ];
        // $this->update($data);
    }
}
