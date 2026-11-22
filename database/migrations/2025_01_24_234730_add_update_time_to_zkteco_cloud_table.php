<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('zkteco_clouds', function (Blueprint $table) {
            $table->string('total_users')->nullable();
            $table->string('total_fingers')->nullable();
            $table->string('att_count')->nullable();
            $table->string('ip')->nullable();
            $table->string('finger_v')->nullable();
            $table->string('face_v')->nullable();
            $table->string('face_count')->nullable();
            $table->dateTime('last_connected')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zkteco_clouds', function (Blueprint $table) {
            $table->dropColumn('total_users');
            $table->dropColumn('total_fingers');
            $table->dropColumn('att_count');
            $table->dropColumn('ip');
            $table->dropColumn('finger_v');
            $table->dropColumn('face_v');
            $table->dropColumn('face_count');
            $table->dropColumn('last_connected');
        });
    }
};
