<?php

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
        Schema::create('zk_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_key_id')->constrained()->cascadeOnDelete();
            $table->string('uuid')->index();
            $table->string('name');
            $table->string('ip');
            $table->string('port');
            $table->string('status')->nullable();
            $table->string('device_name')->nullable();
            $table->string('users_used')->nullable();
            $table->string('users_capacity')->nullable();
            $table->string('fingers_used')->nullable();
            $table->string('fingers_capacity')->nullable();
            $table->string('cards')->nullable();
            $table->string('attendance_used')->nullable();
            $table->string('attendance_capacity')->nullable();
            $table->json('department_ids')->nullable();
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('zk_device_password')->nullable();
            $table->text('zk_device_fingerprint_json')->nullable();
            $table->string('zk_device_privilege')->default('user');
            $table->string('zk_device_card')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zk_devices');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('zk_device_password');
            $table->dropColumn('zk_device_fingerprint_json');
            $table->dropColumn('zk_device_privilege');
            $table->dropColumn('zk_device_card');
            $table->dropSoftDeletes();
        });
    }
};
