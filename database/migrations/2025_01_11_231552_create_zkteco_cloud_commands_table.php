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
        Schema::create('zkteco_cloud_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zkteco_cloud_id')->constrained('zkteco_clouds');
            $table->text('command');
            $table->text('response')->nullable();
            $table->text('return_data')->nullable();
            $table->dateTime('reserved_at')->nullable();
            $table->dateTime('executed_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
        Schema::table('in_out_records', function (Blueprint $table) {
            $table->foreignId('zkteco_cloud_id')->nullable()->constrained('zkteco_clouds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('in_out_records', function (Blueprint $table) {
            $table->dropForeign(['zkteco_cloud_id']);
            $table->dropColumn('zkteco_cloud_id');
        });
        Schema::dropIfExists('zkteco_cloud_commands');
    }
};
