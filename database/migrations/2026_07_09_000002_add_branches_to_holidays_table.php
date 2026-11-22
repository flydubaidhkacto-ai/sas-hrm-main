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
        // Branch-specific holiday support.
        // NULL / empty array  => applies to the entire organization (existing behavior).
        // Non-empty array     => applies only to the listed branch/location ids.
        Schema::table('holidays', function (Blueprint $table) {
            $table->json('branches')->nullable();
        });

        Schema::table('holiday_ranges', function (Blueprint $table) {
            $table->json('branches')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn('branches');
        });

        Schema::table('holiday_ranges', function (Blueprint $table) {
            $table->dropColumn('branches');
        });
    }
};
