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
        Schema::table('auto_late_fines', function (Blueprint $table) {
            $table->string('type')->default('late');
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('early_out_fine_id')->nullable();
            $table->boolean('did_early_check_out')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auto_late_fines', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('early_out_fine_id');
            $table->dropColumn('did_early_check_out');
        });
    }
};
