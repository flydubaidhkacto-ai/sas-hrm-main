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
        Schema::create('auto_late_fines', function (Blueprint $table) {
            $table->id();
            $table->enum('late_days', ['consecutive_days', 'any_days_on_month']);
            $table->integer('late_days_amount');
            $table->integer('fine_amount');
            $table->enum('fine_amount_type', ['fixed', 'days']);
            $table->enum('fine_status', ['approved', 'pending']);
            $table->timestamps();
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('late_fine_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auto_late_fines');
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('late_fine_id');
        });
    }
};
