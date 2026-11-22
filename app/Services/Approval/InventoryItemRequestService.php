<?php

namespace App\Services\Approval;

use App\Models\Notification;
use App\Models\SmsNotification;
use App\Models\User;
use App\Models\InventoryItem;
use App\Jobs\SendInventoryItemEmail;

class InventoryItemRequestService
{

    protected bool $checkMailPermission;

    public function __construct($modal)
    {
        $this->checkMailPermission = SmsNotification::where('user_id', $modal->employee_id)
            ->where('types', 'inventory_item_request')
            ->exists();
    }

    public function handleRejection($modal, $user = null)
    {
        $user = $user ?? user();
        $rejectedBy = $user->name;
        $itemsList = $modal->inventoryItems->map(fn($i) => "{$i->name} ({$i->pivot->quantity})")->join(', ');
        $rejectedDate = now()->format('d F, Y h:i A');

        Notification::create([
            'title' => "Inventory Item Request Rejected",
            'message' => "Your request for inventory items {$itemsList} has been rejected.<br>
                         Rejected by: {$rejectedBy}, Rejected Date: {$rejectedDate}.",
            'time_of_notify' => now(),
            'user_id' => $modal->employee_id,
            'url' => route('inventory_item_request'),
            'mobile_url' => "/invent-requests/" . $modal->id . "/show",
        ]);

        if ($this->checkMailPermission) {
            SendInventoryItemEmail::dispatch($modal, 'rejected', $rejectedBy, $rejectedDate);
        }
    }

    public function handleApproval($modal, $user = null)
    {
        $user = $user ?? user();
        $approvedBy = $user->name;
        $itemsList = $modal->inventoryItems->map(fn($i) => "{$i->name} ({$i->pivot->quantity})")->join(', ');
        $approvedDate = now()->format('d F, Y h:i A');

        Notification::create([
            'title' => "Inventory Item Request Approved",
            'message' => "Your request for inventory items {$itemsList} has been approved.<br>
                            Approved by: {$approvedBy}, Approved Date: {$approvedDate}.",
            'time_of_notify' => now(),
            'user_id' => $modal->employee_id,
            'url' => route('inventory_item_request'),
            'mobile_url' => "/invent-requests/" . $modal->id . "/show",
        ]);

        if ($this->checkMailPermission) {
            SendInventoryItemEmail::dispatch($modal, 'approved', $approvedBy, $approvedDate);
        }
    }
}
