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
            $table->tinyInteger('inoutstate')->nullable();
        });
        Schema::table('zk_devices', function (Blueprint $table) {
            $table->tinyInteger('inoutstate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zk_devices', function (Blueprint $table) {
            $table->dropColumn('inoutstate');
        });
        Schema::table('zkteco_clouds', function (Blueprint $table) {
            $table->dropColumn('inoutstate');
        });
    }
};
