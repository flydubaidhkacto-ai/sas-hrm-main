<?php

namespace App\Services\Approval;

use App\Models\Notification;
use App\Models\SmsNotification;
use App\Models\User;
use App\Models\Asset;
use App\Jobs\SendAssetRequestEmail;

class AssetRequestService
{
    protected bool $checkMailPermission;

    public function __construct($modal)
    {
        $this->checkMailPermission = SmsNotification::where('user_id', $modal->employee_id)
            ->where('types', 'asset_request')
            ->exists();
    }


    public function handleRejection($modal, $user = null)
    {
        $user = $user ?? user();
        $rejectedBy = $user->name;
        $rejectedDate = now()->format('d F, Y h:i A');
        $asset = Asset::find($modal->asset_id);

        Notification::create([
            'title' => "Asset Request Rejected",
            'message' => "Your request for asset <strong>{$asset->name}</strong> has been rejected.<br>
                          Rejected by: {$rejectedBy}, Rejected Date: {$rejectedDate}.",
            'time_of_notify' => now(),
            'user_id' => $modal->employee_id,
            // 'url' => route('inventory_item_request'),
            // 'mobile_url' => "/invent-requests/" . $modal->id . "/show",
        ]);
        if ($this->checkMailPermission) {
            SendAssetRequestEmail::dispatch($modal, 'rejected', $rejectedBy, $rejectedDate);
        }
    }

    public function handleApproval($modal, $user = null)
    {
        $user = $user ?? user();
        $approvedBy = $user->name;
        $approvedDate = now()->format('d F, Y h:i A');
        $asset = Asset::find($modal->asset_id);

        Notification::create([
            'title' => "Asset Request Approved",
            'message' => "Your request for asset <strong>{$asset->name}</strong> has been approved.<br>
                            Approved by: {$approvedBy}, Approved Date: {$approvedDate}.",
            'time_of_notify' => now(),
            'user_id' => $modal->employee_id,
            // 'url' => route('inventory_item_request'),
            // 'mobile_url' => "/invent-requests/" . $modal->id . "/show",
        ]);

        if ($this->checkMailPermission) {
            SendAssetRequestEmail::dispatch($modal, 'approved', $approvedBy, $approvedDate);
        }
    }
}
