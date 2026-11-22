<?php

use App\Models\User;
use App\Models\UserZkDeviceFingerprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        UserZkDeviceFingerprint::query()
            ->delete();
        User::query()
            ->whereNotNull('zk_device_fingerprint_json')
            ->get()
            ->each(function (User $user) {
                UserZkDeviceFingerprint::create([
                    'user_id' => $user->id,
                    'fingerprint_data' => json_decode($user->zk_device_fingerprint_json, true),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
